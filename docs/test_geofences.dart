// test_geofences.dart
//
// Hardcoded test geofences for the spike. No map UI – edit this file directly.
//
// HOW TO FIND COORDINATES:
//   Google Maps – right-click any point on the map. The coordinates appear at
//   the top of the context menu. Click them to copy.
//   Apple Maps – right-click > Drop Pin, then tap the pin to see coordinates.
//
// RADIUS GUIDANCE:
//   100–150m   Specific building. Minimum recommended to avoid GPS drift
//              false-positives in urban environments.
//   300–500m   City block or small neighborhood.
//   800–1500m  Large venue, campus, or general area.
//   3000m+     Neighborhood, district, or airport approach zone.
//
// WHAT TO TEST WITH EACH FENCE:
//   "home"        – your primary test location. You will cross this boundary
//                   repeatedly across all test scenarios. Set radius to 150m.
//   "nearby"      – a place you can walk to from home in under 10 minutes.
//                   Tests enter/exit in a normal active state.
//   "large_zone"  – a district or neighborhood you pass through when driving.
//                   Tests large-radius reliability and city-scale use cases.
//
// REPLACE THE PLACEHOLDER COORDINATES before running the spike.
// The placeholders are in San Francisco and will not match your location.

class TestGeofence {
    final String id;
    final String label;
    final double lat;
    final double lng;
    final double radiusMeters;

    const TestGeofence({
        required this.id,
        required this.label,
        required this.lat,
        required this.lng,
        required this.radiusMeters,
    });

    @override
    String toString() {
        return "TestGeofence($id, ${lat.toStringAsFixed(5)}, "
            "${lng.toStringAsFixed(5)}, r=${radiusMeters.toStringAsFixed(0)}m)";
    }
}

// ── Edit these ───────────────────────────────────────────────────────────────

const List<TestGeofence> testGeofences = [
    TestGeofence(
        id: "home",
        label: "Home",
        lat: 37.7749,       // ← replace with your home latitude
        lng: -122.4194,     // ← replace with your home longitude
        radiusMeters: 150,
    ),
    TestGeofence(
        id: "nearby",
        label: "Nearby (walkable)",
        lat: 37.7751,       // ← replace with a nearby destination
        lng: -122.4180,
        radiusMeters: 150,
    ),
    TestGeofence(
        id: "large_zone",
        label: "Large zone",
        lat: 37.7700,       // ← replace with a district or neighborhood center
        lng: -122.4300,
        radiusMeters: 800,
    ),
];
