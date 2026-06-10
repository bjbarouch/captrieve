// trace.dart
//
// Tracing and event logging for the Captrieve geofence spike.
//
// TWO DISTINCT LOGS:
//
//   Trace buffer – a circular in-memory buffer of the last 500 internal
//   operations. Always written to, never printed unless _printTrace is true
//   or an error occurs. When an error occurs, the buffer is dumped to console
//   automatically so the path to the error is visible without a rerun.
//   Viewable in the app's Debug tab.
//
//   Event log – the user-facing record of geofence events (entered, exited,
//   registered, etc.). Always shown in the app's Event tab. Also persisted
//   to a flat file on disk so events written by the headless callback (when
//   the app is terminated) survive until the next app open.
//
// HEADLESS CONTEXT NOTE:
//   When a geofence fires and the app is fully terminated, iOS/Android wake
//   a minimal Dart isolate to run the geofenceCallback. That isolate has no
//   access to Flutter widgets or the normal app state. The persistent file
//   is the only way to get data from that context into the UI. The in-memory
//   trace buffer is NOT available across isolates – each isolate has its own.
//   The headless callback therefore writes directly to the file; the main
//   isolate reads the file on startup and on foreground resume.

import "dart:io";
import "package:path_provider/path_provider.dart";

// ── Configuration ────────────────────────────────────────────────────────────

// Set to true to echo every trace() call to the console.
// Leave false in normal use – the buffer captures everything regardless.
// Flip to true only if you need real-time console output during a debug session.
const bool _printTrace = false;

// Maximum entries in the circular trace buffer.
// 500 entries at ~100 bytes each = ~50KB – negligible.
const int _traceBufferMax = 500;

// Maximum entries in the persistent event log file.
// Older entries are trimmed when this limit is reached.
const int _persistentLogMax = 200;

// Filename for the persistent event log in the app documents directory.
const String _logFileName = "geofence_spike_events.log";

// ── In-memory trace buffer ───────────────────────────────────────────────────

final List<String> _traceBuffer = [];

// Write a trace entry. Always goes into the buffer. Printed to console only
// if _printTrace is true. Call this for internal state transitions, decisions,
// and anything that would help explain what happened before an error.
void trace(String className, String method, String message) {
    final String entry =
        "${_timestamp()} [$className.$method] $message";
    if (_traceBuffer.length >= _traceBufferMax) {
        _traceBuffer.removeAt(0);
    }
    _traceBuffer.add(entry);
    if (_printTrace) {
        print(entry);
    }
}

// Write a structured error entry and immediately dump the full trace buffer
// to console. The dump always prints regardless of _printTrace so that errors
// are always accompanied by their context without any configuration change.
// Call this from every catch block.
void traceError(
    String className,
    String method,
    String message,
    Object error,
    StackTrace stack,
) {
    trace(className, method, "ERROR – $message");
    trace(className, method, "  exception: $error");

    // Include the first 8 frames of the stack trace, collapsed to one line
    // per frame, so the buffer entry is readable without being enormous.
    final List<String> frames = stack.toString().split("\n").take(8).toList();
    trace(className, method, "  stack: ${frames.join(" | ")}");

    // Errors always dump the buffer to console regardless of _printTrace.
    _dumpTraceBufferToConsole(className, method);
}

void _dumpTraceBufferToConsole(String className, String method) {
    print("=== TRACE BUFFER DUMP triggered by [$className.$method] ===");
    for (final String entry in _traceBuffer) {
        print(entry);
    }
    print("=== END TRACE BUFFER (${_traceBuffer.length} entries) ===");
}

// Returns a copy of the current trace buffer for display in the debug UI tab.
List<String> getTraceBuffer() {
    return List<String>.from(_traceBuffer);
}

void clearTraceBuffer() {
    _traceBuffer.clear();
}

// ── Persistent event log ─────────────────────────────────────────────────────
//
// The event log is the user-facing record. It is written to disk so that
// events from the headless callback (terminated-app scenario) are visible
// after the user reopens the app.
//
// Format: one line per entry, plain text, newest entries appended at the bottom.
// The UI reverses the list for display (newest at top).

// Write a geofence event to the in-memory event log and append it to the
// persistent file. Safe to call from the headless isolate.
//
// batteryLevel: 0–100, or -1 if unavailable.
// isLowPower: true if iOS Low Power Mode or Android Battery Saver is active.
// appState: short description of app state at callback time, e.g. "terminated",
//   "background", "foreground".
Future<void> logEvent({
    required String geofenceId,
    required String direction,
    required int batteryLevel,
    required bool isLowPower,
    required String appState,
}) async {
    final String entry = _buildEventEntry(
        geofenceId: geofenceId,
        direction: direction,
        batteryLevel: batteryLevel,
        isLowPower: isLowPower,
        appState: appState,
    );

    // Append to persistent file. If this fails we catch and trace but do not
    // rethrow – a logging failure should never crash the geofence callback.
    try {
        final File file = await _logFile();
        await file.writeAsString("$entry\n", mode: FileMode.append, flush: true);
        await _trimLogFile(file);
    } catch (error, stack) {
        // Cannot call traceError here because we may be in the headless isolate
        // where the trace buffer is separate. Print directly.
        print("[trace.logEvent] ERROR writing to persistent log: $error");
        print(stack.toString().split("\n").take(4).join(" | "));
    }
}

String _buildEventEntry({
    required String geofenceId,
    required String direction,
    required int batteryLevel,
    required bool isLowPower,
    required String appState,
}) {
    final String battery = batteryLevel >= 0
        ? "${batteryLevel}%${isLowPower ? " LOW-POWER" : ""}"
        : "battery-unknown";
    return "${_timestamp()} | $direction | $geofenceId | $battery | app:$appState";
}

// Read all persisted events from disk. Called on app startup and foreground
// resume to pick up any events written by the headless callback.
Future<List<String>> loadPersistedEvents() async {
    try {
        final File file = await _logFile();
        if (!await file.exists()) {
            return [];
        }
        final String contents = await file.readAsString();
        final List<String> lines = contents
            .split("\n")
            .where((String line) { return line.trim().isNotEmpty; })
            .toList();
        // Return newest first for UI display.
        return lines.reversed.toList();
    } catch (error, stack) {
        print("[trace.loadPersistedEvents] ERROR reading persistent log: $error");
        print(stack.toString().split("\n").take(4).join(" | "));
        return [];
    }
}

Future<void> clearPersistedEvents() async {
    try {
        final File file = await _logFile();
        if (await file.exists()) {
            await file.delete();
        }
    } catch (error, stack) {
        print("[trace.clearPersistedEvents] ERROR: $error");
        print(stack.toString().split("\n").take(4).join(" | "));
    }
}

// Trim the log file to _persistentLogMax lines if it has grown beyond that.
// Keeps the most recent entries.
Future<void> _trimLogFile(File file) async {
    try {
        final String contents = await file.readAsString();
        final List<String> lines = contents
            .split("\n")
            .where((String line) { return line.trim().isNotEmpty; })
            .toList();
        if (lines.length > _persistentLogMax) {
            final List<String> trimmed = lines.sublist(lines.length - _persistentLogMax);
            await file.writeAsString("${trimmed.join("\n")}\n", flush: true);
        }
    } catch (error) {
        // Non-fatal. If trimming fails the file just grows a little.
        print("[trace._trimLogFile] WARNING: $error");
    }
}

Future<File> _logFile() async {
    final Directory dir = await getApplicationDocumentsDirectory();
    return File("${dir.path}/$_logFileName");
}

// ── Utilities ────────────────────────────────────────────────────────────────

String _timestamp() {
    return DateTime.now().toLocal().toIso8601String().substring(0, 23);
}
