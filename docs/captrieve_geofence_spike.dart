import "dart:async";
import "dart:io";
import "package:flutter/material.dart";
import "package:native_geofence/native_geofence.dart";
import "package:flutter_local_notifications/flutter_local_notifications.dart";
import "package:permission_handler/permission_handler.dart";

// ── Notification setup ──────────────────────────────────────────────────────

final FlutterLocalNotificationsPlugin notifications = FlutterLocalNotificationsPlugin();

const AndroidNotificationChannel channel = AndroidNotificationChannel(
    "geofence_channel",
    "Geofence Events",
    description: "Fires when a geofence boundary is crossed",
    importance: Importance.max,
);

const NotificationDetails notificationDetails = NotificationDetails(
    android: AndroidNotificationDetails(
        "geofence_channel",
        "Geofence Events",
        channelDescription: "Fires when a geofence boundary is crossed",
        importance: Importance.max,
        priority: Priority.high,
    ),
    iOS: DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
    ),
);

// ── Headless callback (runs when app is terminated) ──────────────────────────
//
// This must be a top-level function, not a method or closure.
// Flutter requires it to be registered before runApp().

@pragma("vm:entry-point")
void geofenceCallback(GeofenceCallbackParams params) {
    final String direction = params.event == GeofenceEvent.enter ? "ENTERED" : "EXITED";
    final String message = "$direction: ${params.region.id}";
    _deliverNotification(message);
    _logEvent(message);
}

// Notification delivery from headless context.
// Initializes its own plugin instance since the main isolate may not be running.
void _deliverNotification(String message) {
    final FlutterLocalNotificationsPlugin plugin = FlutterLocalNotificationsPlugin();
    plugin.initialize(
        const InitializationSettings(
            android: AndroidInitializationSettings("@mipmap/ic_launcher"),
            iOS: DarwinInitializationSettings(),
        ),
    );
    plugin.show(
        DateTime.now().millisecondsSinceEpoch ~/ 1000,
        "Geofence event",
        message,
        notificationDetails,
    );
}

// In-memory log shared across the app session.
// Headless events write here; UI reads from it on foreground resume.
final List<String> _eventLog = [];

void _logEvent(String message) {
    final String entry = "${DateTime.now().toLocal().toString().substring(0, 19)} – $message";
    _eventLog.insert(0, entry);
}

// ── Test geofences ───────────────────────────────────────────────────────────
//
// Edit this list to match places you can physically visit during testing.
// Coordinates: right-click in Google Maps to copy lat/lng.
// Radius is in meters. 150m minimum recommended to avoid GPS drift false positives.

const List<_TestGeofence> _testGeofences = [
    _TestGeofence(
        id: "home",
        label: "Home",
        lat: 37.7749,     // replace with your actual home coordinates
        lng: -122.4194,
        radiusMeters: 150,
    ),
    _TestGeofence(
        id: "coffee_shop",
        label: "Coffee shop",
        lat: 37.7751,     // replace with a nearby place you can walk to
        lng: -122.4180,
        radiusMeters: 100,
    ),
    _TestGeofence(
        id: "large_zone",
        label: "Large zone (city-scale test)",
        lat: 37.7700,     // replace with a neighborhood or district center
        lng: -122.4300,
        radiusMeters: 800,
    ),
];

class _TestGeofence {
    final String id;
    final String label;
    final double lat;
    final double lng;
    final double radiusMeters;

    const _TestGeofence({
        required this.id,
        required this.label,
        required this.lat,
        required this.lng,
        required this.radiusMeters,
    });
}

// ── Entry point ──────────────────────────────────────────────────────────────

void main() {
    WidgetsFlutterBinding.ensureInitialized();

    // Register the headless callback before runApp so the OS can find it
    // when waking the app after termination.
    NativeGeofence.registerHeadlessGeofenceTask(geofenceCallback);

    runApp(const GeofenceSpikeApp());
}

// ── App ──────────────────────────────────────────────────────────────────────

class GeofenceSpikeApp extends StatelessWidget {
    const GeofenceSpikeApp({super.key});

    @override
    Widget build(BuildContext context) {
        return MaterialApp(
            title: "Geofence Spike",
            theme: ThemeData(
                colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepOrange),
                useMaterial3: true,
            ),
            home: const SpikePage(),
        );
    }
}

// ── Main screen ──────────────────────────────────────────────────────────────

class SpikePage extends StatefulWidget {
    const SpikePage({super.key});

    @override
    State<SpikePage> createState() {
        return _SpikePageState();
    }
}

class _SpikePageState extends State<SpikePage> {
    bool _initialized = false;
    bool _permissionsGranted = false;
    List<ActiveGeofenceRegion> _activeRegions = [];
    String _statusMessage = "Not initialized";
    Timer? _refreshTimer;

    @override
    void initState() {
        super.initState();
        _initNotifications();
        _refreshTimer = Timer.periodic(
            const Duration(seconds: 5),
            (_) {
                _refreshActiveRegions();
            },
        );
    }

    @override
    void dispose() {
        _refreshTimer?.cancel();
        super.dispose();
    }

    Future<void> _initNotifications() async {
        await notifications.initialize(
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
            await notifications
                .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
                ?.createNotificationChannel(channel);
        }
    }

    Future<void> _requestPermissions() async {
        setState(() {
            _statusMessage = "Requesting permissions...";
        });

        // Location permission: when-in-use first, then always.
        // iOS requires this two-step sequence; asking for Always directly is rejected.
        final PermissionStatus whenInUse = await Permission.locationWhenInUse.request();
        if (!whenInUse.isGranted) {
            setState(() {
                _statusMessage = "Location permission denied. Cannot run geofence spike.";
            });
            return;
        }

        final PermissionStatus always = await Permission.locationAlways.request();
        if (!always.isGranted) {
            setState(() {
                _statusMessage = "Background location denied. "
                    "iOS/Android will not fire geofences when app is terminated. "
                    "Grant 'Always' in Settings > Privacy > Location to test fully.";
            });
            // Continue anyway – partial testing is still useful.
        }

        if (Platform.isAndroid) {
            await Permission.notification.request();
        }

        setState(() {
            _permissionsGranted = true;
            _statusMessage = "Permissions granted.";
        });
    }

    Future<void> _initialize() async {
        if (!_permissionsGranted) {
            await _requestPermissions();
        }

        try {
            await NativeGeofence.initialize();
            setState(() {
                _initialized = true;
                _statusMessage = "Initialized. Ready to register geofences.";
            });
        } catch (error) {
            setState(() {
                _statusMessage = "Initialization failed: $error";
            });
        }
    }

    Future<void> _registerAll() async {
        if (!_initialized) {
            setState(() {
                _statusMessage = "Initialize first.";
            });
            return;
        }

        int registered = 0;
        int failed = 0;

        for (final _TestGeofence fence in _testGeofences) {
            try {
                await NativeGeofence.createGeofence(
                    region: GeofenceRegion(
                        id: fence.id,
                        center: Location(
                            latitude: fence.lat,
                            longitude: fence.lng,
                        ),
                        radius: fence.radiusMeters,
                        triggers: {GeofenceEvent.enter, GeofenceEvent.exit},
                    ),
                    callback: geofenceCallback,
                );
                registered++;
            } catch (error) {
                failed++;
                _logEvent("REGISTER FAILED: ${fence.id} – $error");
            }
        }

        await _refreshActiveRegions();

        setState(() {
            _statusMessage = "Registered $registered geofences. "
                "${failed > 0 ? "$failed failed – check log." : ""}";
        });
    }

    Future<void> _removeAll() async {
        try {
            await NativeGeofence.removeAllGeofences();
            await _refreshActiveRegions();
            setState(() {
                _statusMessage = "All geofences removed.";
            });
        } catch (error) {
            setState(() {
                _statusMessage = "Remove failed: $error";
            });
        }
    }

    Future<void> _refreshActiveRegions() async {
        try {
            final List<ActiveGeofenceRegion> regions = await NativeGeofence.getRegisteredGeofences();
            setState(() {
                _activeRegions = regions;
            });
        } catch (error) {
            // Non-fatal. Don't update UI on polling errors.
        }
    }

    void _clearLog() {
        setState(() {
            _eventLog.clear();
        });
    }

    // ── Build ─────────────────────────────────────────────────────────────────

    @override
    Widget build(BuildContext context) {
        return Scaffold(
            appBar: AppBar(
                title: const Text("Geofence Spike"),
                backgroundColor: Theme.of(context).colorScheme.inversePrimary,
            ),
            body: Column(
                children: [
                    _StatusBar(message: _statusMessage),
                    _ButtonRow(
                        initialized: _initialized,
                        onInitialize: _initialize,
                        onRegister: _registerAll,
                        onRemove: _removeAll,
                        onClearLog: _clearLog,
                    ),
                    _ActiveRegionsList(regions: _activeRegions),
                    const Divider(),
                    _EventLog(entries: _eventLog),
                ],
            ),
        );
    }
}

// ── Sub-widgets ──────────────────────────────────────────────────────────────

class _StatusBar extends StatelessWidget {
    final String message;

    const _StatusBar({required this.message});

    @override
    Widget build(BuildContext context) {
        return Container(
            width: double.infinity,
            color: Colors.orange.shade50,
            padding: const EdgeInsets.all(12),
            child: Text(
                message,
                style: const TextStyle(fontSize: 13),
            ),
        );
    }
}

class _ButtonRow extends StatelessWidget {
    final bool initialized;
    final VoidCallback onInitialize;
    final VoidCallback onRegister;
    final VoidCallback onRemove;
    final VoidCallback onClearLog;

    const _ButtonRow({
        required this.initialized,
        required this.onInitialize,
        required this.onRegister,
        required this.onRemove,
        required this.onClearLog,
    });

    @override
    Widget build(BuildContext context) {
        return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
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
                        style: ElevatedButton.styleFrom(backgroundColor: Colors.red.shade100),
                        child: const Text("Remove all"),
                    ),
                    OutlinedButton(
                        onPressed: onClearLog,
                        child: const Text("Clear log"),
                    ),
                ],
            ),
        );
    }
}

class _ActiveRegionsList extends StatelessWidget {
    final List<ActiveGeofenceRegion> regions;

    const _ActiveRegionsList({required this.regions});

    @override
    Widget build(BuildContext context) {
        if (regions.isEmpty) {
            return const Padding(
                padding: EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                child: Text(
                    "No active geofences registered",
                    style: TextStyle(color: Colors.grey, fontSize: 13),
                ),
            );
        }

        return SizedBox(
            height: 90,
            child: ListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                itemCount: regions.length,
                itemBuilder: (BuildContext context, int index) {
                    final ActiveGeofenceRegion region = regions[index];
                    return Text(
                        "◎ ${region.id}  "
                        "${region.center.latitude.toStringAsFixed(5)}, "
                        "${region.center.longitude.toStringAsFixed(5)}  "
                        "r=${region.radius.toStringAsFixed(0)}m",
                        style: const TextStyle(fontSize: 12, fontFamily: "monospace"),
                    );
                },
            ),
        );
    }
}

class _EventLog extends StatelessWidget {
    final List<String> entries;

    const _EventLog({required this.entries});

    @override
    Widget build(BuildContext context) {
        if (entries.isEmpty) {
            return const Expanded(
                child: Center(
                    child: Text(
                        "No events yet.\nRegister geofences, then cross a boundary.",
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.grey),
                    ),
                ),
            );
        }

        return Expanded(
            child: ListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                itemCount: entries.length,
                itemBuilder: (BuildContext context, int index) {
                    final String entry = entries[index];
                    final bool isEnter = entry.contains("ENTERED");
                    final bool isError = entry.contains("FAILED");
                    return Padding(
                        padding: const EdgeInsets.symmetric(vertical: 2),
                        child: Text(
                            entry,
                            style: TextStyle(
                                fontSize: 12,
                                fontFamily: "monospace",
                                color: isError
                                    ? Colors.red
                                    : isEnter
                                        ? Colors.green.shade800
                                        : Colors.orange.shade800,
                            ),
                        ),
                    );
                },
            ),
        );
    }
}
