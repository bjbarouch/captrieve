// main.dart
//
// Entry point for the Captrieve geofence spike.
//
// HEADLESS CALLBACK:
//   geofenceCallback() is the function iOS and Android call when a geofence
//   boundary is crossed and the app is not running. It must be:
//     1. A top-level function (not a method or closure).
//     2. Annotated with @pragma("vm:entry-point") so the Dart compiler does
//        not tree-shake it out of the release build.
//     3. Registered via NativeGeofence.registerHeadlessGeofenceTask() before
//        runApp() is called – the OS needs to know about it at startup so it
//        can invoke it when waking the app from a terminated state.
//
//   The headless isolate is minimal. It does NOT have access to:
//     - The main isolate's memory (including the trace buffer)
//     - Flutter widgets or BuildContext
//     - Any state initialized by main() or runApp()
//   It DOES have access to:
//     - Dart core libraries
//     - Package code (including our trace.dart and path_provider)
//     - The device filesystem
//
//   This is why the persistent log file exists: it is the only reliable
//   bridge between the headless isolate and the UI.

import "package:flutter/material.dart";
import "package:flutter/widgets.dart";
import "package:native_geofence/native_geofence.dart";
import "package:flutter_local_notifications/flutter_local_notifications.dart";
import "package:battery_plus/battery_plus.dart";
import "trace.dart";
import "spike_page.dart";

// ── Notification configuration ───────────────────────────────────────────────

// Channel ID and name must match between the channel creation (in spike_page.dart)
// and the notification details used here. Android uses channels; iOS ignores them
// but the details struct still needs to be provided.
const String _notificationChannelId   = "geofence_channel";
const String _notificationChannelName = "Geofence Events";

const NotificationDetails _notificationDetails = NotificationDetails(
    android: AndroidNotificationDetails(
        _notificationChannelId,
        _notificationChannelName,
        channelDescription: "Fires when a geofence boundary is crossed",
        importance: Importance.max,
        priority: Priority.high,
        // Show notification even when app is in foreground, so we can observe
        // events without having to background the app first.
        playSound: true,
    ),
    iOS: DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
    ),
);

// ── Headless geofence callback ───────────────────────────────────────────────

@pragma("vm:entry-point")
Future<void> geofenceCallback(GeofenceCallbackParams params) async {
    // Must call ensureInitialized – this isolate may have been freshly created
    // by the OS with no prior Flutter initialization.
    WidgetsFlutterBinding.ensureInitialized();

    final String direction = params.event == GeofenceEvent.enter ? "ENTERED" : "EXITED";
    final String geofenceId = params.region.id;

    // Capture battery state at the moment the callback fires.
    // This is one of the primary data points for the low-battery test scenario.
    int batteryLevel = -1;
    bool isLowPower = false;
    try {
        final Battery battery = Battery();
        batteryLevel = await battery.batteryLevel;
        isLowPower = await battery.isInBatterySaveMode;
    } catch (error, stack) {
        // Battery info is best-effort. A failure here must not prevent the
        // geofence event from being recorded and notified.
        print("[main.geofenceCallback] WARNING: could not read battery state: $error");
        print(stack.toString().split("\n").take(3).join(" | "));
    }

    // Determine app state. In the headless callback, the app is by definition
    // not in the foreground – it is either terminated or backgrounded.
    // native_geofence only invokes this callback when the app is not in the
    // foreground, so we can infer "terminated or background" without further
    // inspection. The distinction between the two is not available from within
    // the callback itself, so we label it "background/terminated" and let the
    // timestamp correlation with the test log tell the full story.
    const String appState = "background/terminated";

    // Write to the persistent log file. This is the primary record for the
    // terminated-app test scenario – the in-memory event list in spike_page.dart
    // is not available in this isolate.
    await logEvent(
        geofenceId: geofenceId,
        direction: direction,
        batteryLevel: batteryLevel,
        isLowPower: isLowPower,
        appState: appState,
    );

    // Deliver the local notification. Initializes its own plugin instance
    // because the main isolate's instance is not accessible here.
    await _deliverNotification(
        geofenceId: geofenceId,
        direction: direction,
        batteryLevel: batteryLevel,
        isLowPower: isLowPower,
    );
}

Future<void> _deliverNotification({
    required String geofenceId,
    required String direction,
    required int batteryLevel,
    required bool isLowPower,
}) async {
    try {
        final FlutterLocalNotificationsPlugin plugin = FlutterLocalNotificationsPlugin();
        await plugin.initialize(
            const InitializationSettings(
                android: AndroidInitializationSettings("@mipmap/ic_launcher"),
                iOS: DarwinInitializationSettings(),
            ),
        );

        final String batteryNote = batteryLevel >= 0
            ? " [batt ${batteryLevel}%${isLowPower ? " LOW-POWER" : ""}]"
            : "";

        // Use a timestamp-based ID so rapid successive events each get their
        // own notification rather than replacing each other.
        final int notificationId = DateTime.now().millisecondsSinceEpoch ~/ 1000;

        await plugin.show(
            notificationId,
            "$direction: $geofenceId",
            "Geofence event$batteryNote",
            _notificationDetails,
        );
    } catch (error, stack) {
        print("[main._deliverNotification] ERROR: $error");
        print(stack.toString().split("\n").take(4).join(" | "));
    }
}

// ── Entry point ──────────────────────────────────────────────────────────────

void main() {
    WidgetsFlutterBinding.ensureInitialized();

    // Register the headless callback before runApp().
    // If this line is missing or called after runApp(), terminated-app geofence
    // delivery will silently fail – the OS has nowhere to dispatch the event.
    NativeGeofence.registerHeadlessGeofenceTask(geofenceCallback);

    runApp(const GeofenceSpikeApp());
}

// ── App shell ────────────────────────────────────────────────────────────────

class GeofenceSpikeApp extends StatelessWidget {
    const GeofenceSpikeApp({super.key});

    @override
    Widget build(BuildContext context) {
        return MaterialApp(
            title: "Geofence Spike",
            debugShowCheckedModeBanner: false,
            theme: ThemeData(
                colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepOrange),
                useMaterial3: true,
            ),
            home: const SpikePage(),
        );
    }
}
