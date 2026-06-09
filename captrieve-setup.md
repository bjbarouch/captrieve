# Captrieve – Flutter Environment Setup and Geofence Spike

---

## Part 1 – Tools to Install

Everything below assumes macOS (required for iOS builds).
Install in the order listed.

---

### 1. Xcode

**Where:** Mac App Store  
**URL:** https://apps.apple.com/us/app/xcode/id497799835

Download and open it at least once so it completes its first-launch setup.
Then run this in Terminal to accept the license and configure the command-line tools:

```
sudo sh -c 'xcode-select -s /Applications/Xcode.app/Contents/Developer && xcodebuild -runFirstLaunch'
sudo xcodebuild -license accept
```

Xcode is large (~15 GB). Start this first.

---

### 2. Homebrew (if not already installed)

**URL:** https://brew.sh

```
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

After install, follow the printed instructions to add Homebrew to your PATH.
Verify: `brew --version`

---

### 3. Flutter SDK (includes Dart – do not install Dart separately)

**URL:** https://docs.flutter.dev/get-started/install/macos/mobile-ios

The VS Code path is the simplest. It downloads the SDK and configures PATH in one step.

**Alternative (manual, explicit):**

```
cd ~/develop          # or wherever you want the SDK to live
curl -O https://storage.googleapis.com/flutter_infra_release/releases/stable/macos/flutter_macos_arm64_3.44.1-stable.zip
unzip flutter_macos_arm64_3.44.1-stable.zip
```

Then add to your shell profile (~/.zshrc):

```
export PATH="$HOME/develop/flutter/bin:$PATH"
```

Reload: `source ~/.zshrc`  
Verify: `flutter --version`

Check for Apple Silicon vs Intel: if your Mac is M1/M2/M3/M4, use the arm64 zip.
If Intel, use the x64 zip. Check with: `uname -m` (arm64 = Apple Silicon, x86_64 = Intel)

---

### 4. CocoaPods

Required for iOS dependency management.

```
sudo gem install cocoapods
```

If that fails on Apple Silicon (Ruby version issues):

```
brew install cocoapods
```

Verify: `pod --version`

---

### 5. VS Code (recommended editor)

**URL:** https://code.visualstudio.com

After installing, open VS Code and install these two extensions:
- **Flutter** (by Dart Code) – also installs the Dart extension automatically
- Search "Flutter" in the Extensions panel (⌘⇧X)

---

### 6. Android Studio (for Android SDK only – you don't have to use it as your editor)

**URL:** https://developer.android.com/studio

You need this for the Android SDK even if you write all code in VS Code.
After installing, open Android Studio, go through initial setup, then:

- Open SDK Manager (More Actions > SDK Manager)
- Under SDK Tools tab, check "Android SDK Command-line Tools" and install it
  (This is the step that most setups miss and flutter doctor complains about)

---

### 7. Verify everything

```
flutter doctor -v
```

Work through every warning it reports. The goal is all green checkmarks.
Common issues:
- "Android toolchain – missing command line tools" → install via SDK Manager as above
- "CocoaPods not installed" → `brew install cocoapods`
- "Xcode not configured" → run the `xcode-select` command from step 1

---

## Part 2 – Create the Geofence Spike Project

### Create the project

```
cd ~/develop          # or wherever your projects live
flutter create captrieve_geofence_spike
cd captrieve_geofence_spike
```

### Add dependencies

Open `pubspec.yaml` and replace the `dependencies:` section with:

```yaml
dependencies:
  flutter:
    sdk: flutter
  native_geofence: ^2.0.0
  flutter_local_notifications: ^18.0.0
  permission_handler: ^11.0.0
```

Then run:

```
flutter pub get
```

---

### iOS configuration

**ios/Runner/Info.plist** – add these keys inside the outermost `<dict>`:

```xml
<key>NSLocationAlwaysAndWhenInUseUsageDescription</key>
<string>Captrieve needs your location to deliver captures when you arrive at or leave a place.</string>
<key>NSLocationAlwaysUsageDescription</key>
<string>Captrieve needs your location to deliver captures when you arrive at or leave a place.</string>
<key>NSLocationWhenInUseUsageDescription</key>
<string>Captrieve needs your location to deliver captures when you arrive at or leave a place.</string>
<key>UIBackgroundModes</key>
<array>
  <string>location</string>
  <string>fetch</string>
</array>
```

**ios/Runner/Runner.entitlements** – create this file if it doesn't exist:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
  <key>com.apple.developer.location.push</key>
  <false/>
</dict>
</plist>
```

---

### Android configuration

**android/app/src/main/AndroidManifest.xml** – add these permissions before the `<application>` tag:

```xml
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION" />
<uses-permission android:name="android.permission.RECEIVE_BOOT_COMPLETED" />
<uses-permission android:name="android.permission.FOREGROUND_SERVICE" />
<uses-permission android:name="android.permission.FOREGROUND_SERVICE_LOCATION" />
<uses-permission android:name="android.permission.POST_NOTIFICATIONS" />
```

In `android/app/build.gradle`, confirm `minSdkVersion` is at least 23:

```
minSdkVersion 23
```

---

## Part 3 – The Geofence Spike Code

Replace the entire contents of `lib/main.dart` with the code in `captrieve_geofence_spike.dart`
(provided separately).

---

## Part 4 – Getting the App onto Your Devices

### iPhone (iOS)

You do not need a paid Apple Developer account ($99/year) for this spike.
A free Apple ID is sufficient with one limitation: the provisioning profile expires every 7 days
and must be re-signed. For a spike that runs for a few days of testing, that's fine.

**Steps:**

1. Connect your iPhone to your Mac via USB cable.
2. On the iPhone, trust the computer if prompted ("Trust This Computer").
3. Open Xcode: `open ios/Runner.xcworkspace` from your project directory.
4. In Xcode, go to **Xcode > Settings > Accounts** and add your Apple ID with the "+" button.
5. In the project navigator (left panel), click **Runner** (the top item).
6. Click the **Runner** target (under Targets).
7. Click **Signing & Capabilities**.
8. Under Team, select your personal team (your name, "Personal Team").
9. Xcode will auto-generate a provisioning profile. If it shows an error about bundle ID,
   change the Bundle Identifier to something unique like `com.yourname.captrievespike`.
10. Select your iPhone from the device picker at the top of Xcode (next to the play button).
11. Click the **Play** button (or ⌘R).
12. First run: your iPhone will show "Untrusted Developer." Go to:
    **Settings > General > VPN & Device Management > [your Apple ID] > Trust**
13. The app will launch on your phone.

**For subsequent runs during development**, from Terminal:

```
flutter run
```

Flutter will detect your connected device and deploy automatically.

---

### Android Tablet

**Steps:**

1. On the tablet, go to **Settings > About tablet** (or About device).
2. Tap **Build number** seven times. This enables Developer Options.
3. Go to **Settings > Developer Options**.
4. Enable **USB debugging**.
5. Connect the tablet to your Mac via USB.
6. On the tablet, accept the "Allow USB debugging?" prompt and check "Always allow from this computer."
7. Verify Flutter sees it: `flutter devices` – your tablet should appear in the list.
8. Run: `flutter run` – if you have both iPhone and tablet connected, Flutter will ask which device.
   Or specify: `flutter run -d <device-id>` using the ID shown in `flutter devices`.

Android is significantly simpler than iOS for development deployment. No signing ceremony.

---

## Part 5 – Notes on the Spike

The spike app has a hardcoded list of test geofences you can edit directly in the code –
no map UI needed. See the `_testGeofences` list in `main.dart`. Each entry takes a name,
latitude, longitude, and radius in meters.

To find the coordinates of a place you want to test:
- Google Maps: right-click any location > the coordinates appear at the top of the context menu.
- Apple Maps: right-click > Drop Pin, then click the pin for coordinates.

The app logs every geofence event to the screen with a timestamp so you can observe
exactly when and whether triggers fire as you move around.

The four scenarios worth testing explicitly, in order of importance:
1. Normal background (app backgrounded, phone active) – should work reliably.
2. App fully terminated (swipe it closed) – the critical test; this is where packages differ.
3. Low-power mode enabled – iOS may defer; note the delay.
4. Reboot – geofence should re-register; expect the known double-fire on first event.
