// spike_page.dart
//
// Main UI for the geofence spike.
//
// THREE TABS:
//   Events   – geofence events in reverse chronological order, loaded from
//              the persistent log file on startup and on foreground resume.
//              This is the primary test observation surface.
//   Regions  – currently registered geofences, polled every 5 seconds so you
//              can see whether the OS has kept registrations alive across
//              app restarts and reboots.
//   Debug    – the in-memory trace buffer. Shows internal state transitions
//              and the path to any errors. Tap "Dump to console" to also
//              print the full buffer to the terminal.
//
// TEST SCENARIOS (run in this order for systematic coverage):
//   1. Baseline         – app backgrounded, phone active, normal battery.
//                         Cross a boundary. Notification should arrive promptly.
//   2. Terminated       – swipe the app closed. Cross a boundary. Reopen.
//                         The event should appear in the Events tab. This is
//                         the most important scenario for Captrieve's promise.
//   3. Low battery      – enable Low Power Mode (iOS) or Battery Saver (Android).
//                         Cross a boundary. Note delivery latency.
//   4. Reboot           – reboot the phone with geofences registered. Cross a
//                         boundary. The first event may fire twice (known
//                         native_geofence behavior on iOS post-reboot).
//   5. Airplane mode    – enable airplane mode, cross a boundary (GPS may still
//                         work), disable airplane mode. Note whether the event
//                         was held and delivered on reconnect, or silently dropped.

import "dart:async";
import "dart:io";
import "package:flutter/material.dart";
import "package:flutter/services.dart";
import "package:native_geofence/native_geofence.dart";
import "package:flutter_local_notifications/flutter_local_notifications.dart";
import "package:permission_handler/permission_handler.dart";
import "package:battery_plus/battery_plus.dart";
import "trace.dart";
import "test_geofences.dart";

// ── Constants ────────────────────────────────────────────────────────────────

const String _cls = "SpikePage";

const String _notificationChannelId   = "geofence_channel";
const String _notificationChannelName = "Geofence Events";

const AndroidNotificationChannel _androidChannel = AndroidNotificationChannel(
    _notificationChannelId,
    _notificationChannelName,
    description: "Fires when a geofence boundary is crossed",
    importance: Importance.max,
);

// ── SpikePage (stateful shell) ───────────────────────────────────────────────

class SpikePage extends StatefulWidget {
    const SpikePage({super.key});

    @override
    State<SpikePage> createState() {
        return _SpikePageState();
    }
}

class _SpikePageState extends State<SpikePage> with WidgetsBindingObserver {
    // Initialization state
    bool _initialized          = false;
    bool _permissionsGranted   = false;
    String _statusMessage      = "Tap Initialize to begin.";

    // Data displayed in the tabs
    List<String> _eventEntries  = [];     // from persistent log, newest first
    List<ActiveGeofenceRegion> _regions = [];
    List<String> _traceEntries  = [];     // snapshot of trace buffer

    // Battery info shown in status bar
    int _batteryLevel   = -1;
    bool _isLowPower    = false;

    // Timers
    Timer? _regionPollTimer;
    Timer? _batteryPollTimer;

    final FlutterLocalNotificationsPlugin _notifications =
        FlutterLocalNotificationsPlugin();

    final Battery _battery = Battery();

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    @override
    void initState() {
        super.initState();
        trace(_cls, "initState", "SpikePage initializing");
        WidgetsBinding.instance.addObserver(this);
        _initNotifications();
        _loadPersistedEvents();
        _startPolling();
        _refreshBattery();
    }

    @override
    void dispose() {
        trace(_cls, "dispose", "SpikePage disposing");
        WidgetsBinding.instance.removeObserver(this);
        _regionPollTimer?.cancel();
        _batteryPollTimer?.cancel();
        super.dispose();
    }

    // Called when the app returns to the foreground. Reload persisted events
    // to pick up anything the headless callback wrote while we were away.
    @override
    void didChangeAppLifecycleState(AppLifecycleState state) {
        trace(_cls, "didChangeAppLifecycleState", "state=$state");
        if (state == AppLifecycleState.resumed) {
            _loadPersistedEvents();
            _refreshBattery();
            _refreshRegions();
        }
    }

    // ── Initialization ────────────────────────────────────────────────────────

    Future<void> _initNotifications() async {
        trace(_cls, "_initNotifications", "initializing notification plugin");
        try {
            await _notifications.initialize(
                const InitializationSettings(
                    android: AndroidInitializationSettings("@mipmap/ic_launcher"),
                    iOS: DarwinInitializationSettings(
                        requestAlertPermission: true,
                        requestBadgePermission: true,
                        requestSoundPermission: true,
                    ),
                ),
            );

            if (Platform.isAndroid) {
                await _notifications
                    .resolvePlatformSpecificImplementation<
                        AndroidFlutterLocalNotificationsPlugin>()
                    ?.createNotificationChannel(_androidChannel);
                trace(_cls, "_initNotifications", "Android channel created");
            }

            trace(_cls, "_initNotifications", "notification plugin ready");
        } catch (error, stack) {
            traceError(_cls, "_initNotifications", "notification init failed", error, stack);
            _setStatus("Notification setup failed – see Debug tab");
        }
    }

    Future<void> _requestPermissions() async {
        trace(_cls, "_requestPermissions", "beginning permission sequence");
        _setStatus("Requesting permissions...");

        // iOS requires when-in-use before always. Requesting Always directly
        // is silently rejected on iOS 14+. This two-step sequence is mandatory.
        trace(_cls, "_requestPermissions", "requesting locationWhenInUse");
        final PermissionStatus whenInUse = await Permission.locationWhenInUse.request();
        trace(_cls, "_requestPermissions", "locationWhenInUse result: $whenInUse");

        if (!whenInUse.isGranted) {
            _setStatus(
                "Location permission denied. "
                "Geofences cannot function. "
                "Grant location access in Settings > Privacy > Location Services.",
            );
            return;
        }

        trace(_cls, "_requestPermissions", "requesting locationAlways");
        final PermissionStatus always = await Permission.locationAlways.request();
        trace(_cls, "_requestPermissions", "locationAlways result: $always");

        if (!always.isGranted) {
            // Partial permission – foreground geofences may work but terminated-app
            // delivery will not. We continue so partial testing is possible, but
            // we surface a clear warning.
            _setStatus(
                "WARNING: Background location not granted. "
                "Geofences will NOT fire when app is terminated. "
                "For full testing: Settings > Privacy > Location Services > "
                "Geofence Spike > Always.",
            );
            trace(_cls, "_requestPermissions",
                "WARNING: locationAlways denied – terminated-app scenario will not work");
        }

        if (Platform.isAndroid) {
            trace(_cls, "_requestPermissions", "requesting Android notification permission");
            final PermissionStatus notif = await Permission.notification.request();
            trace(_cls, "_requestPermissions", "notification result: $notif");
        }

        setState(() {
            _permissionsGranted = true;
        });
        trace(_cls, "_requestPermissions", "permission sequence complete");
    }

    Future<void> _initialize() async {
        trace(_cls, "_initialize", "begin");

        if (!_permissionsGranted) {
            await _requestPermissions();
            if (!_permissionsGranted) {
                trace(_cls, "_initialize", "aborting – permissions not granted");
                return;
            }
        }

        try {
            trace(_cls, "_initialize", "calling NativeGeofence.initialize()");
            await NativeGeofence.initialize();
            setState(() {
                _initialized = true;
            });
            _setStatus("Ready. Tap Register to activate geofences.");
            trace(_cls, "_initialize", "NativeGeofence initialized successfully");
        } catch (error, stack) {
            traceError(_cls, "_initialize", "NativeGeofence.initialize() failed", error, stack);
            _setStatus("Initialization failed – see Debug tab for details");
        }
    }

    // ── Geofence management ───────────────────────────────────────────────────

    Future<void> _registerAll() async {
        trace(_cls, "_registerAll", "registering ${testGeofences.length} geofences");

        if (!_initialized) {
            _setStatus("Initialize first.");
            return;
        }

        int registered = 0;
        int failed = 0;

        for (final TestGeofence fence in testGeofences) {
            trace(_cls, "_registerAll", "registering: $fence");
            try {
                await NativeGeofence.createGeofence(
                    region: GeofenceRegion(
                        id: fence.id,
                        center: Location(
                            latitude: fence.lat,
                            longitude: fence.lng,
                        ),
                        radius: fence.radiusMeters,
                        // Monitor both entry and exit for complete data collection.
                        triggers: {GeofenceEvent.enter, GeofenceEvent.exit},
                    ),
                    // geofenceCallback is defined in main.dart and registered
                    // as the headless entry point. We reference it here too so
                    // it also fires when the app is in the foreground/background.
                    callback: _foregroundCallback,
                );
                registered++;
                trace(_cls, "_registerAll", "registered: ${fence.id}");
            } catch (error, stack) {
                failed++;
                traceError(_cls, "_registerAll",
                    "failed to register ${fence.id}", error, stack);
            }
        }

        await _refreshRegions();
        _setStatus(
            "Registered $registered geofence${registered == 1 ? "" : "s"}."
            "${failed > 0 ? " $failed failed – see Debug tab." : ""}",
        );
        trace(_cls, "_registerAll",
            "complete: $registered registered, $failed failed");
    }

    Future<void> _removeAll() async {
        trace(_cls, "_removeAll", "removing all geofences");
        try {
            await NativeGeofence.removeAllGeofences();
            await _refreshRegions();
            _setStatus("All geofences removed.");
            trace(_cls, "_removeAll", "done");
        } catch (error, stack) {
            traceError(_cls, "_removeAll", "removeAllGeofences() failed", error, stack);
            _setStatus("Remove failed – see Debug tab");
        }
    }

    // ── Polling ───────────────────────────────────────────────────────────────

    void _startPolling() {
        // Poll active regions every 5 seconds to detect if the OS has silently
        // dropped registrations – a known issue on some Android OEMs.
        _regionPollTimer = Timer.periodic(
            const Duration(seconds: 5),
            (_) { _refreshRegions(); },
        );

        // Poll battery every 30 seconds so the status bar stays current.
        _batteryPollTimer = Timer.periodic(
            const Duration(seconds: 30),
            (_) { _refreshBattery(); },
        );
    }

    Future<void> _refreshRegions() async {
        try {
            final List<ActiveGeofenceRegion> regions =
                await NativeGeofence.getRegisteredGeofences();
            setState(() {
                _regions = regions;
            });
            trace(_cls, "_refreshRegions",
                "${regions.length} active region${regions.length == 1 ? "" : "s"}");
        } catch (error, stack) {
            // Non-fatal polling failure. Don't update UI or set error status –
            // a transient poll failure is noise. Do trace it.
            traceError(_cls, "_refreshRegions",
                "getRegisteredGeofences() failed", error, stack);
        }
    }

    Future<void> _refreshBattery() async {
        try {
            final int level = await _battery.batteryLevel;
            final bool lowPower = await _battery.isInBatterySaveMode;
            setState(() {
                _batteryLevel = level;
                _isLowPower = lowPower;
            });
        } catch (error) {
            // Battery info is best-effort. Silent failure is fine here.
            trace(_cls, "_refreshBattery", "WARNING: battery read failed: $error");
        }
    }

    // ── Event log ─────────────────────────────────────────────────────────────

    Future<void> _loadPersistedEvents() async {
        trace(_cls, "_loadPersistedEvents", "loading from file");
        try {
            final List<String> events = await loadPersistedEvents();
            setState(() {
                _eventEntries = events;
            });
            trace(_cls, "_loadPersistedEvents", "loaded ${events.length} entries");
        } catch (error, stack) {
            traceError(_cls, "_loadPersistedEvents",
                "loadPersistedEvents() failed", error, stack);
        }
    }

    Future<void> _clearEvents() async {
        trace(_cls, "_clearEvents", "clearing persistent log and in-memory list");
        await clearPersistedEvents();
        setState(() {
            _eventEntries = [];
        });
    }

    // Foreground callback – used when the app is running (foreground or background).
    // The headless callback in main.dart handles the terminated-app case.
    // Both write to the persistent log; only this one can also update UI state.
    Future<void> _foregroundCallback(GeofenceCallbackParams params) async {
        final String direction =
            params.event == GeofenceEvent.enter ? "ENTERED" : "EXITED";
        final String geofenceId = params.region.id;

        trace(_cls, "_foregroundCallback",
            "event: $direction $geofenceId");

        int batteryLevel = -1;
        bool isLowPower = false;
        try {
            batteryLevel = await _battery.batteryLevel;
            isLowPower = await _battery.isInBatterySaveMode;
        } catch (error) {
            trace(_cls, "_foregroundCallback",
                "WARNING: battery read failed: $error");
        }

        await logEvent(
            geofenceId: geofenceId,
            direction: direction,
            batteryLevel: batteryLevel,
            isLowPower: isLowPower,
            appState: "foreground/background",
        );

        // Reload the event list from file so the UI reflects the new entry.
        await _loadPersistedEvents();
    }

    // ── Trace panel ───────────────────────────────────────────────────────────

    void _refreshTrace() {
        setState(() {
            _traceEntries = getTraceBuffer();
        });
    }

    void _dumpTraceToConsole() {
        final List<String> buffer = getTraceBuffer();
        print("=== MANUAL TRACE DUMP (${buffer.length} entries) ===");
        for (final String entry in buffer) {
            print(entry);
        }
        print("=== END MANUAL TRACE DUMP ===");
    }

    void _clearTrace() {
        clearTraceBuffer();
        setState(() {
            _traceEntries = [];
        });
    }

    // ── Utilities ─────────────────────────────────────────────────────────────

    void _setStatus(String message) {
        trace(_cls, "_setStatus", message);
        setState(() {
            _statusMessage = message;
        });
    }

    void _copyToClipboard(String text) {
        Clipboard.setData(ClipboardData(text: text));
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
                content: Text("Copied to clipboard"),
                duration: Duration(seconds: 2),
            ),
        );
    }

    // ── Build ─────────────────────────────────────────────────────────────────

    @override
    Widget build(BuildContext context) {
        return DefaultTabController(
            length: 3,
            child: Scaffold(
                appBar: AppBar(
                    title: const Text("Geofence Spike"),
                    backgroundColor: Theme.of(context).colorScheme.inversePrimary,
                    bottom: const TabBar(
                        tabs: [
                            Tab(text: "Events"),
                            Tab(text: "Regions"),
                            Tab(text: "Debug"),
                        ],
                    ),
                ),
                body: Column(
                    children: [
                        _StatusBar(
                            message: _statusMessage,
                            batteryLevel: _batteryLevel,
                            isLowPower: _isLowPower,
                        ),
                        _ControlRow(
                            initialized: _initialized,
                            onInitialize: _initialize,
                            onRegister: _registerAll,
                            onRemove: _removeAll,
                        ),
                        Expanded(
                            child: TabBarView(
                                children: [
                                    _EventTab(
                                        entries: _eventEntries,
                                        onClear: _clearEvents,
                                        onCopy: _copyToClipboard,
                                    ),
                                    _RegionsTab(regions: _regions),
                                    _DebugTab(
                                        entries: _traceEntries,
                                        onRefresh: _refreshTrace,
                                        onDump: _dumpTraceToConsole,
                                        onClear: _clearTrace,
                                        onCopy: _copyToClipboard,
                                    ),
                                ],
                            ),
                        ),
                    ],
                ),
            ),
        );
    }
}

// ── Status bar ───────────────────────────────────────────────────────────────

class _StatusBar extends StatelessWidget {
    final String message;
    final int batteryLevel;
    final bool isLowPower;

    const _StatusBar({
        required this.message,
        required this.batteryLevel,
        required this.isLowPower,
    });

    @override
    Widget build(BuildContext context) {
        final String batteryText = batteryLevel >= 0
            ? "  🔋 $batteryLevel%${isLowPower ? " LOW-POWER" : ""}"
            : "";

        return Container(
            width: double.infinity,
            color: isLowPower ? Colors.orange.shade100 : Colors.orange.shade50,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: Text(
                "$message$batteryText",
                style: const TextStyle(fontSize: 12),
            ),
        );
    }
}

// ── Control row ───────────────────────────────────────────────────────────────

class _ControlRow extends StatelessWidget {
    final bool initialized;
    final VoidCallback onInitialize;
    final VoidCallback onRegister;
    final VoidCallback onRemove;

    const _ControlRow({
        required this.initialized,
        required this.onInitialize,
        required this.onRegister,
        required this.onRemove,
    });

    @override
    Widget build(BuildContext context) {
        return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            child: Wrap(
                spacing: 8,
                children: [
                    ElevatedButton(
                        onPressed: initialized ? null : onInitialize,
                        child: const Text("Initialize"),
                    ),
                    ElevatedButton(
                        onPressed: initialized ? onRegister : null,
                        child: const Text("Register all"),
                    ),
                    ElevatedButton(
                        onPressed: initialized ? onRemove : null,
                        style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.red.shade100,
                        ),
                        child: const Text("Remove all"),
                    ),
                ],
            ),
        );
    }
}

// ── Events tab ────────────────────────────────────────────────────────────────

class _EventTab extends StatelessWidget {
    final List<String> entries;
    final VoidCallback onClear;
    final void Function(String) onCopy;

    const _EventTab({
        required this.entries,
        required this.onClear,
        required this.onCopy,
    });

    @override
    Widget build(BuildContext context) {
        return Column(
            children: [
                Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    child: Row(
                        children: [
                            Text(
                                "${entries.length} event${entries.length == 1 ? "" : "s"}",
                                style: const TextStyle(fontSize: 12, color: Colors.grey),
                            ),
                            const Spacer(),
                            TextButton(
                                onPressed: () { onCopy(entries.join("\n")); },
                                child: const Text("Copy all", style: TextStyle(fontSize: 12)),
                            ),
                            TextButton(
                                onPressed: onClear,
                                child: const Text("Clear", style: TextStyle(fontSize: 12)),
                            ),
                        ],
                    ),
                ),
                Expanded(
                    child: entries.isEmpty
                        ? const Center(
                            child: Text(
                                "No events yet.\n\n"
                                "Register geofences, then cross a boundary.\n"
                                "Events written by the headless callback\n"
                                "appear here when you reopen the app.",
                                textAlign: TextAlign.center,
                                style: TextStyle(color: Colors.grey, fontSize: 13),
                            ),
                        )
                        : ListView.builder(
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            itemCount: entries.length,
                            itemBuilder: (BuildContext context, int index) {
                                final String entry = entries[index];
                                final bool isEnter = entry.contains("ENTERED");
                                final bool isLowPower = entry.contains("LOW-POWER");
                                return Padding(
                                    padding: const EdgeInsets.symmetric(vertical: 1),
                                    child: Text(
                                        entry,
                                        style: TextStyle(
                                            fontSize: 11,
                                            fontFamily: "monospace",
                                            color: isLowPower
                                                ? Colors.orange.shade800
                                                : isEnter
                                                    ? Colors.green.shade800
                                                    : Colors.blue.shade800,
                                        ),
                                    ),
                                );
                            },
                        ),
                ),
            ],
        );
    }
}

// ── Regions tab ───────────────────────────────────────────────────────────────

class _RegionsTab extends StatelessWidget {
    final List<ActiveGeofenceRegion> regions;

    const _RegionsTab({required this.regions});

    @override
    Widget build(BuildContext context) {
        if (regions.isEmpty) {
            return const Center(
                child: Text(
                    "No geofences currently registered with the OS.\n\n"
                    "After registering, this list is polled every 5 seconds.\n"
                    "If registered geofences disappear here between app opens,\n"
                    "the OS is dropping them – a critical finding.",
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.grey, fontSize: 13),
                ),
            );
        }

        return ListView.builder(
            padding: const EdgeInsets.all(12),
            itemCount: regions.length,
            itemBuilder: (BuildContext context, int index) {
                final ActiveGeofenceRegion region = regions[index];
                return Card(
                    child: Padding(
                        padding: const EdgeInsets.all(10),
                        child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                                Text(
                                    region.id,
                                    style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 13,
                                    ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                    "lat ${region.center.latitude.toStringAsFixed(6)}  "
                                    "lng ${region.center.longitude.toStringAsFixed(6)}",
                                    style: const TextStyle(
                                        fontSize: 11,
                                        fontFamily: "monospace",
                                        color: Colors.grey,
                                    ),
                                ),
                                Text(
                                    "radius ${region.radius.toStringAsFixed(0)} m",
                                    style: const TextStyle(
                                        fontSize: 11,
                                        fontFamily: "monospace",
                                        color: Colors.grey,
                                    ),
                                ),
                            ],
                        ),
                    ),
                );
            },
        );
    }
}

// ── Debug tab ─────────────────────────────────────────────────────────────────

class _DebugTab extends StatelessWidget {
    final List<String> entries;
    final VoidCallback onRefresh;
    final VoidCallback onDump;
    final VoidCallback onClear;
    final void Function(String) onCopy;

    const _DebugTab({
        required this.entries,
        required this.onRefresh,
        required this.onDump,
        required this.onClear,
        required this.onCopy,
    });

    @override
    Widget build(BuildContext context) {
        return Column(
            children: [
                Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    child: Wrap(
                        spacing: 8,
                        children: [
                            TextButton(
                                onPressed: onRefresh,
                                child: const Text("Refresh", style: TextStyle(fontSize: 12)),
                            ),
                            TextButton(
                                onPressed: onDump,
                                child: const Text("Dump to console",
                                    style: TextStyle(fontSize: 12)),
                            ),
                            TextButton(
                                onPressed: () { onCopy(entries.join("\n")); },
                                child: const Text("Copy all", style: TextStyle(fontSize: 12)),
                            ),
                            TextButton(
                                onPressed: onClear,
                                child: const Text("Clear",
                                    style: TextStyle(fontSize: 12, color: Colors.red)),
                            ),
                        ],
                    ),
                ),
                Expanded(
                    child: entries.isEmpty
                        ? const Center(
                            child: Text(
                                "Tap Refresh to load the trace buffer.\n\n"
                                "The buffer captures internal operations continuously.\n"
                                "When an error occurs it is dumped to console automatically.",
                                textAlign: TextAlign.center,
                                style: TextStyle(color: Colors.grey, fontSize: 13),
                            ),
                        )
                        : ListView.builder(
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            itemCount: entries.length,
                            itemBuilder: (BuildContext context, int index) {
                                final String entry = entries[index];
                                final bool isError = entry.contains("ERROR");
                                final bool isWarning = entry.contains("WARNING");
                                return Padding(
                                    padding: const EdgeInsets.symmetric(vertical: 1),
                                    child: Text(
                                        entry,
                                        style: TextStyle(
                                            fontSize: 10,
                                            fontFamily: "monospace",
                                            color: isError
                                                ? Colors.red
                                                : isWarning
                                                    ? Colors.orange.shade800
                                                    : Colors.grey.shade700,
                                        ),
                                    ),
                                );
                            },
                        ),
                ),
            ],
        );
    }
}
