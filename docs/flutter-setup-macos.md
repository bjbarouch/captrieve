# Setting Up a Flutter Development Environment on macOS

This is a first-timer's guide to getting Flutter, Dart, and on-device development working on a Mac, using Android Studio for the
Android toolchain, Visual Studio Code as the editor, and Xcode for the iOS side.
It follows the project's order of work: get everything you need for Android first, and treat the iOS pieces as a later step you
can skip until you actually start iOS.

A note before you begin.
The broad shape of this setup has been stable for years and is what is described here, but exact version numbers, download
buttons, and a few commands change over time.
When a step does not match what you see, the authoritative source is the official install page at
`https://docs.flutter.dev/get-started/install/macos`, and `flutter doctor` is the tool that tells you what is still missing.

Throughout, lines beginning with a `$` are commands you type into the Terminal app (in Applications, Utilities).
Type the part after the `$`.

---

## 0. Before you start

Know your chip.
Click the Apple menu, About This Mac, and note whether it says Apple silicon (M1, M2, M3, and so on) or Intel.
A few steps differ between them, and this guide calls those out.

Make sure you have several gigabytes free.
Xcode alone is very large, and Android Studio plus the Android SDK and an emulator add several more.

On Apple silicon, install Rosetta, which lets a few older developer tools run.

```bash
$ softwareupdate --install-rosetta --agree-to-license
```

Your Mac's shell is almost certainly zsh, and its configuration file is `~/.zshrc`.
Several steps below add lines to that file.
After editing it, either open a new Terminal window or run `source ~/.zshrc` to load the changes.

---

## 1. Command-line tools and Git

Install Apple's command-line developer tools, which include Git and the compilers Flutter relies on.

```bash
$ xcode-select --install
```

A dialog appears.
Click Install and wait for it to finish.
This is not the full Xcode app, just the small command-line tools, and it is all you need for Android work.

---

## 2. Homebrew

Homebrew is a package manager that makes installing developer tools a one-line affair.
Get the current install command from `https://brew.sh`, which today is:

```bash
$ /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

When it finishes, it prints two or three lines telling you to add Homebrew to your PATH.
Run exactly what it prints.
On Apple silicon those commands point at `/opt/homebrew`, and on Intel at `/usr/local`.
Confirm it works.

```bash
$ brew --version
```

---

## 3. The Flutter SDK (this also gives you Dart)

Dart comes bundled inside Flutter, so installing Flutter installs both.

Download the macOS Flutter SDK for your chip from the official install page above, choosing the Apple silicon or Intel build to
match your Mac.
You will get a zip file.
Create a working folder and unzip it there.

```bash
$ mkdir -p ~/development
$ cd ~/development
$ unzip ~/Downloads/flutter_macos_*.zip
```

Now add Flutter to your PATH so the `flutter` command works from any folder.
Open `~/.zshrc` in an editor and add this line at the end:

```bash
export PATH="$HOME/development/flutter/bin:$PATH"
```

Load it and confirm.

```bash
$ source ~/.zshrc
$ flutter --version
```

If `flutter --version` prints version information, the SDK is installed.
If the command is not found, the PATH line is the problem: check the folder name matches where you actually unzipped it, and
that you opened a fresh Terminal.

(There are other ways to install Flutter, including a Homebrew cask and an offer from the VS Code extension in step 5 to install
it for you. The manual download above is the most predictable for a first-timer, which is why it is the one shown here.)

---

## 4. Android Studio and the Android toolchain

You will use VS Code as your editor, but you still need Android Studio, because it carries the Android SDK, the device drivers,
the emulator, and the licenses Flutter checks for.

Download and install Android Studio from `https://developer.android.com/studio`.
Open it, and let its first-run setup wizard download the Android SDK, the platform tools, and a default emulator image.
Accept the defaults unless you have a reason not to.

Then add one component the wizard often leaves out.
In Android Studio, open Settings, then Languages and Frameworks, then Android SDK, then the SDK Tools tab.
Check "Android SDK Command-line Tools (latest)" and apply.
Flutter needs this, and its absence is the most common thing `flutter doctor` complains about.

Finally, accept the Android licenses from the Terminal.

```bash
$ flutter doctor --android-licenses
```

Press `y` through each one.

---

## 5. Visual Studio Code

Install VS Code from `https://code.visualstudio.com`.

Open it, go to the Extensions panel (the square icon in the left bar), search for "Flutter", and install the Flutter extension.
It automatically pulls in the Dart extension, so you do not install that separately.
This gives you run and debug buttons, hot reload, a device picker in the status bar, and code completion for Dart.

To confirm the editor sees your tools, open the Command Palette with Shift, Command, P, type "Flutter", and you should see
commands like "Flutter: New Project".

---

## 6. Run flutter doctor and fix what it flags

`flutter doctor` is your single source of truth for what is set up and what is not.

```bash
$ flutter doctor
```

It checks Flutter itself, the Android toolchain, Xcode, Chrome for web, Android Studio, VS Code, and connected devices.
Each line is a check mark, an exclamation mark, or a cross.
Work down the list and resolve anything that is not a check mark.
For more detail on any item, run the verbose form.

```bash
$ flutter doctor -v
```

For Android-first work, a check mark on Flutter, the Android toolchain, Android Studio, and VS Code is enough to start.
The Xcode line can stay unresolved until you reach the iOS step.

---

## 7. Your test phone (the Moto G Power)

To run on a real Android device, turn on developer access.

On the phone, open Settings, About phone, and tap Build number seven times until it says you are now a developer.
Then open Settings, System, Developer options, and turn on USB debugging.

Connect the phone to the Mac with a USB cable.
The phone shows a prompt asking to allow USB debugging from this computer.
Accept it, and check the box to always allow if you like.

Confirm the Mac sees it.

```bash
$ flutter devices
```

Your phone should appear in the list.
One device-specific note worth knowing: this phone manages battery aggressively, which can delay background notifications.
That is useful for testing worst-case delivery, but during development you may want to exclude your app from battery
optimization in the phone's settings so your own test cues fire promptly.

---

## 8. An Android emulator (optional)

When you do not have the phone connected, an on-screen emulator works for most things.
Create one in Android Studio under the Device Manager, or list and launch from the Terminal.

```bash
$ flutter emulators
$ flutter emulators --launch <emulator_id>
```

The emulator cannot do NFC or real geofencing, so the physical phone remains your rig for those.

---

## 9. Your first app

Create, enter, and run a starter app.

```bash
$ flutter create my_first_app
$ cd my_first_app
$ flutter run
```

If more than one device or emulator is available, it asks which to use.
While it is running, press `r` in the Terminal for a hot reload that applies code changes in about a second, or `R` for a full
hot restart.
You can also just press the Run and Debug button in VS Code instead of using the Terminal.

When this shows the default counter app on your phone or emulator, your environment works end to end.

---

## 10. The iOS toolchain (later, when you start iOS)

Per the development plan, you can skip this entire section until you begin iOS work, which is also when the paid Apple Developer
Program subscription starts.
Nothing here is needed for Android-first development.

Install Xcode from the Mac App Store.
It is a very large download, so allow time.
Once installed, do the one-time setup.

```bash
$ sudo xcode-select --switch /Applications/Xcode.app/Contents/Developer
$ sudo xcodebuild -runFirstLaunch
$ sudo xcodebuild -license accept
```

Install CocoaPods, which manages the native dependencies many Flutter plugins need on iOS.
On modern macOS the cleanest route is Homebrew, rather than the older system-Ruby gem install.

```bash
$ brew install cocoapods
```

The iOS Simulator comes with Xcode.

```bash
$ open -a Simulator
```

Run `flutter doctor` again, and the Xcode line should now be a check mark.
Running on a real iPhone, as opposed to the simulator, requires the paid Apple Developer Program, which is the deliberate
trigger for starting the subscription clock once Android has momentum.

---

## Keeping things current

Update Flutter and Dart together when you want the latest stable release.

```bash
$ flutter upgrade
```

Re-run `flutter doctor` after any macOS update, Xcode update, or Android Studio update, since those occasionally knock a tool
out of alignment and the doctor will tell you exactly what to fix.

---

## A first-timer's short list of gotchas

-  The single most common problem is PATH. If a command is "not found," the tool is installed but the shell cannot see it.
   Check the `~/.zshrc` line and open a fresh Terminal. `which flutter` tells you whether the shell can find it.
-  Do not fight `flutter doctor`. Treat its list as your to-do list and clear it top to bottom.
-  The Android SDK Command-line Tools component is easy to miss in step 4 and causes a confusing doctor warning. Install it.
-  On Apple silicon, if a tool behaves strangely, the Rosetta step in section 0 is often the missing piece.
-  You do not need Xcode, CocoaPods, or any iOS setup to build and ship the Android app. Resist installing them until the iOS
   phase, both to save disk and to keep the doctor output focused on what matters now.
