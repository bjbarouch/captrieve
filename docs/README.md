# Captrieve

**Capture in the moment. Retrieve in context.**

Captrieve solves a single problem: you have a thought you do not want to lose and cannot act on right now, and you want to
encounter it later, at a moment when it will be actionable.

A captured thought does not go into a notes app, a doc, or a spreadsheet, where it waits unindexed for you to remember it is
there.
It does not go into a task list, where one item fires once on one trigger.
Captrieve makes the thought reappear when and where you tell it to – cued by the real world, not by you remembering to look.

## The two moments

The whole product is built around two moments and nothing in between.

-  **Capture.** The thought is fleeing, so the UI gets out of the way. Speak it. Transcription happens in the background and
   the audio is kept as backup. You are not blocked waiting for anything.
-  **Retrieve.** You set the cue. The app honors it. There is no algorithm deciding when you are ready, and no black box.

## The cue model

A cue is what brings a capture back. Cues can be set on:

-  time (a datetime, or quick options like tonight and tomorrow morning)
-  place (geofence arrival or departure)
-  Wi-Fi network (join or leave)
-  Bluetooth device (connect or disconnect)
-  charger (connect or disconnect)
-  Focus / Do Not Disturb (enter)
-  an NFC tag tap
-  opening the app

Cues combine, with AND conditions and delays – "when I leave work, but only after 5 PM," "20 minutes after I get home,"
"the store geofence or the fridge tag, whichever comes first."
The inbox is always visible, so nothing is ever trapped behind a cue that has not fired – you can browse, search, and surface
anything by hand.

## Tiers

-  **Free.** Full local functionality, no account, capped at 20 retrieves. Nothing leaves the device.
-  **Solo – $7.99, one-time.** Unlimited retrieves and all local features. No subscription, no account, no cloud.
-  **Connected – $2.99/month or $24.99/year.** Adds an opt-in sharing layer: presence events, shared captures, capture
   forwarding, and a presence log, all routed through an end-to-end encrypted backend. Built for families, partners, and
   caregivers. After 12 consecutive months, Solo is permanently unlocked even if the subscription later ends.

## Privacy

Captures stay on the device.
Data leaves only by the user's explicit choice – captures shared on the Connected Tier, which travel end-to-end encrypted with
keys generated on the device, where the server holds public keys and ciphertext only and can read nothing.
The one thing on by default is anonymous diagnostics, which carry no identifier and no capture content and turn off with a
single toggle.
There is no algorithm deciding when you are ready, at any tier.

## Platform and stack

Flutter / Dart, one codebase targeting IOS and Android.
It is fundamentally a phone app: capture is impulse-driven, and notification response happens wherever you are.

-  Local storage: `sqflite` or `hive`. No backend required for the local tiers.
-  Maps and geofencing: `flutter_map` with OpenStreetMap tiles (no Google API key), background geofencing via a platform-native
   Flutter package.
-  Notifications: `flutter_local_notifications` for time- and geofence-triggered local notifications.
-  Voice: device microphone via Flutter audio packages, with background transcription.

The data layer is abstracted from the UI behind a repository pattern from day one, so a later web companion can talk to the same
contract without rearchitecting storage.

## Build and run

Standard Flutter workflow:

```
flutter pub get
flutter run            # on a connected device or simulator
flutter build IOS      # release builds
flutter build apk
```

Background geofencing, Wi-Fi, Bluetooth, NFC, and Focus cues depend on platform entitlements and behave differently on a real
device than in a simulator – test cue delivery on hardware.

## The spec

This README is a summary.
The authoritative product specification lives in [`docs/captrieve-spec.md`](docs/captrieve-spec.md) – data model, every cue
type, capture and inbox behavior, the Connected Tier and caregiver use case, monetization, and the open items still to be
resolved before build.
