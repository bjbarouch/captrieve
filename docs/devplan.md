# Captrieve Development Plan

This plan sequences Captrieve from an empty project to a launched product and the deferred backend tier.
It is written for a solo, part-time developer who is strong in software generally but new to Flutter, Dart, and on-device
mobile work.

Two directives govern everything below.
Build Android first, but design every piece to the stricter iOS constraints from day one, so iOS becomes a port rather than a
rewrite.
Do not start the paid Apple Developer subscription until there is real momentum, so the renewal clock begins when there is a
proven product to justify it.

The ordering principle throughout is to retire the biggest risk as early and as cheaply as possible, and to defer anything
expensive, review-heavy, or unvalidated until the product has earned it.

---

## Governing principles

-  Cross-platform from day one, designed to the stricter of the two platforms, which is almost always iOS.
-  Android first to build, test, and release, because it is cheaper to iterate, needs no paid membership to start, and allows
   direct installs onto the test device.
-  The Apple Developer subscription starts only at the iOS phase, once Android momentum exists.
-  The thinnest vertical slice first, then the cheapest cue first, so the core promise is proven before breadth is added.
-  The free app must stand alone with no backend and no always-on location permission.
-  Defer the expensive and the risky: geofence and background location, in-app billing, the Connected backend, and iOS, each
   to the latest phase that still makes sense.
-  The always-visible inbox is the reliability backstop behind every cue, so no cue failure ever loses a capture.

---

## Cross-cutting disciplines

These apply to every phase, not to any single one.

**Architecture.**
The repository pattern sits between the UI and local storage from the first commit.
Every operating-system capability that differs across platforms is reached through a thin abstraction with a stubbed
implementation on day one: NFC, local notifications, location and geofencing, in-app billing, and later push.
This is what lets iOS slot in as a second implementation behind an interface that already exists.

**The iOS risk register.**
A living document, started in Phase 0, listing every iOS-only unknown that cannot be validated on Android.
The known entries are background NFC tag reading, the Associated Domains and apple-app-site-association handshake, APNs, Focus
and notification behavior, and StoreKit for purchases.
Each feature built on Android records the iOS design assumption it is relying on, so the eventual iOS spike has a checklist
rather than a memory exercise.

**Testing.**
The Moto G Power is the primary rig precisely because its aggressive battery management is a worst case for background
delivery.
A cue that fires reliably there will fire on gentler devices.

**Graceful degradation.**
Every cue degrades to the inbox.
A missed trigger is a missed convenience, never a lost thought.

---

## Phase 0 – Foundations and toolchain de-risking (Android)

**Goal.**
Prove you can build, run on the real device, debug on-device, and ship to an internal track, before writing a single feature.
For a developer new to mobile, the toolchain and the submission pipeline are larger risks than any feature, so they go first.

**Build.**
-  Flutter and Dart environment, Android Studio, device debugging against the Moto G Power. (See the separate macOS setup
   guide.)
-  Project skeleton with the repository pattern and the platform abstractions listed above, all stubbed.
-  Local storage wired up with `sqflite` or `hive`, and the Capture and Trigger data models created.
-  A trivial running app: record audio, save a local note, list it. Nothing context-aware yet.
-  A signed release build uploaded to Google Play Console on the internal testing track.
-  The iOS risk register created and seeded.

**Out of scope.**
Cues, trigger-fired notifications, payment, location, the backend, and any iOS work.

**Risk retired.**
The toolchain, the on-device debug loop, the project architecture, and the proof that you can get a build into the store
pipeline.

**Rough size.**
2 to 4 weeks part-time, weighted toward learning the tools rather than writing code.

---

## Phase 1 – The core loop on the cheapest cue (Android)

**Goal.**
The irreducible product: capture a thought, attach the simplest possible cue, receive a real notification when it fires, and
see everything in an always-visible inbox.

**Build.**
-  Capture flow for voice and text, with on-device transcription in the background and a "transcribing..." state.
-  The inbox list and the capture detail view, with review-and-confirm of the transcript on detail open.
-  Exactly one cue type: date and time, because it needs no permissions and no background location.
-  Reliable local notification scheduling and delivery via `flutter_local_notifications`.
-  Snooze, dismiss, and the stale-capture surface-count behavior.

**Out of scope.**
NFC, Wi-Fi, location, composition, paid gating, the backend, and iOS.

**Risk retired.**
Background notification reliability, the single largest technical risk, validated on the cheapest and most controllable cue.

**Rough size.**
3 to 6 weeks part-time.

---

## Phase 2 – The signature cues: NFC and Wi-Fi (Android, designed to iOS limits)

**Goal.**
Add the two distinctive free-tier real-world cues, building the NFC architecture to iOS constraints so it never needs
redesign.

**Build.**
-  NFC registration: mint a UUIDv4, write the single Universal Link as the first and only NDEF record, and lock the tag, with
   the blank, adopt, and refuse branches from the spec.
-  NFC retrieval by background tap on Android, via an intent filter on the NDEF URI with autoVerify and a Digital Asset Links
   file, structured so the iOS Background Tag Reading path is a drop-in later.
-  The local uuid-to-name map, tag rename, many captures per tag, and the backup-hygiene behavior.
-  Wi-Fi cues on network connect and disconnect.
-  Audio playback on retrieve for the user's own recordings, free and local, including the audio-primary voice-note case.

**Out of scope.**
Geofence and background location, composition, payment, the backend, and the iOS implementation of these same features.

**Risk retired.**
The hardest distinctive feature, NFC, plus the proof that its design holds against the iOS constraints it was built to.

**Rough size.**
4 to 8 weeks part-time, with NFC dominating.

---

## Phase 3 – Free-tier polish, onboarding, and the first real-world test (Android)

**Goal.**
A genuinely usable, shippable free Android app for the target users, with no location, no backend, and no payment.

**Build.**
-  Onboarding for non-technical and older users, including hands-on first NFC setup.
-  Accessibility, TTS reading of transcripts, and quiet-hours and Do Not Disturb handling, including the rule that audio never
   autoplays during a quiet period.
-  Inbox search and manual surfacing.
-  Hardening against Android OEM battery management, using the Moto as the worst case.
-  Real dogfooding: daily use by you and at least one family member, and ideally a first small senior-center or ADHD-forum test
   on a closed testing track.

**Out of scope.**
Payment, location, composition, the backend, and iOS.

**Milestone.**
The first version you can put in front of real people, and the first cheap validation of the go-to-market teaching motion.

**Rough size.**
3 to 6 weeks part-time, plus the calendar time a real-world test takes to run.

---

## Phase 4 – Monetization: the locked threshold, billing, and the paid cues (Android)

**Goal.**
Introduce the free-to-paid boundary and the paid power features on Android billing, and absorb the location-review burden here
rather than earlier.

**The locked threshold.**
Free, unlimited except where noted: date and time, app-open, an NFC tag tap up to five tags, and a Wi-Fi network up to two,
single-condition only. The tag cap counts physical tags, not captures.
Paid (Solo): geofence, charger, Focus, and Bluetooth cues, all composition (AND conditions, delays, multiple cues with OR),
and removal of the tag and network caps.
The upgrade prompt fires at a moment of demonstrated value: a sixth tag, a third network, any paid cue type, or the first
attempt to combine cues.

**Build.**
-  The threshold and the upgrade prompt, fired in context rather than on a timer.
-  Google Play Billing: the one-time Solo purchase, purchase restore, and the optional Supporter purchase.
-  Composition, the differentiated paid hook and the cleanest thing to charge for.
-  The remaining paid cue types: charger, Focus, and Bluetooth.
-  Geofence and background location, built here so the free app stayed review-clean. Budget extra calendar time for the Google
   background-location review and the location-permission user experience.

**A deliberate v2 option.**
Geofence is paid in v1 specifically so the free app needs no always-on location and stays clear of the background-location
review.
It may migrate to the free tier in a later version, since users expect location reminders to be free, but only after the
Android launch, because free geofence re-adds exactly that permission and review burden.

**Out of scope.**
The Connected backend, iOS, and any sharing.

**Risk retired.**
The billing pipeline and the background-location review, both deferred until there is a proven product to carry them.

**Rough size.**
4 to 8 weeks part-time, with the review cycle adding unpredictable calendar time.

---

## Phase 5 – Android public launch and momentum (Android)

**Goal.**
Ship to the Play Store production track and run the community go-to-market motion.

**Build and do.**
-  Production release, the store listing, and the website going live, which means removing the preview-auth gate and making the
   Digital Asset Links and apple-app-site-association files live before anyone installs.
-  The retail-proof stage of the go-to-market plan: free educational talks at senior communities, low-key ADHD-forum presence,
   and gathering real stories. (Real stories also let you replace the illustrative testimonials with attributed ones.)
-  Stabilization against whatever a wider install base surfaces, especially background-delivery edge cases across devices.

**Milestone.**
Stable real-world use and the beginnings of word of mouth.
Reaching momentum here is the trigger condition for starting iOS and the Apple subscription.

**Rough size.**
2 to 4 weeks of release work, then ongoing, because the go-to-market motion is slow compounding rather than a single push.

---

## Phase 6 – iOS: start the subscription, validate the unknowns, port

**Goal.**
Now that momentum justifies the cost, enroll in the Apple Developer Program and bring the proven app to iOS.

**Build.**
-  First task, before any porting: the iOS risk-register spike on real Apple hardware. Validate background NFC tag reading, the
   apple-app-site-association handshake, APNs, and Focus and notification behavior. De-risk before committing to the full port.
-  Apple Developer Program enrollment and the App Store Small Business Program for the reduced commission.
-  The iOS implementations behind the platform abstractions, plus the NFC Tag Reading and Associated Domains entitlements.
-  StoreKit for the purchases that Play Billing handled on Android.
-  App Store submission and its stricter review.

**Out of scope.**
Connected, which is a separate phase regardless of platform.

**Risk retired.**
The deferred iOS unknowns, now addressed with a proven product behind them and a subscription the revenue can justify.

**Rough size.**
6 to 10 weeks part-time, with the spike and the App Store review adding calendar time. Smaller than building iOS from scratch,
because the architecture was built to its limits from day one.

---

## Phase 7 – Connected: the backend tier (both platforms)

**Goal.**
The subscription, sharing, and caregiver layer, which is the only part of the product that needs a server.

**Build.**
-  Minimal accounts and the device-registration and connection graph.
-  The end-to-end encrypted relay, with keys generated on-device and the server holding only public keys and ciphertext.
-  Push routing through FCM on Android and APNs on iOS.
-  Shared captures with recipient-side cues, capture forwarding, and opt-in shared audio for the cases where the voice is the
   point, length-bounded and encrypted, with offline deferral.
-  Presence, built passive-first to match the spec and the website. The signals are a home geofence reporting leaving and
   returning, a home Wi-Fi join or leave as a cheap indoor corroborator, and first phone activity or the overnight charger
   coming off as up-and-about. A scheduled reminder firing is a visible signal that the reminder went out, never a claim it was
   acted on. An NFC tag tap is an optional active check-in, never the backbone, because a tap is directionless and a missed tap
   means nothing. The actionable event is the absence of the usual pattern, which is the caregiver's cue to call.
-  The caregiver view, and Family Sharing so one subscription covers up to five family members.

**Out of scope.**
Anything that belongs to a different product.

**Risk retired.**
The recurring-cost infrastructure is built only after the free and Solo product has proven demand.

**Rough size.**
The largest phase by far, on the order of two to four months part-time or more, and reasonably treated as its own project.

---

## Deliberately deferred, and not in this plan

-  Wearables. The stance is phone-first. A watch can capture and set a cue that migrates to the phone, but watches cannot scan
   NFC, and this is a later release at the earliest.
-  A web companion. The repository pattern keeps it possible, but it is not a v1 concern.
-  Live cloud sync beyond JSON export and import. The only privacy-clean cross-ecosystem path is the Connected backend.
-  Any cue or feature that would require the free app to request always-on location.

---

## Honest caveats on sizing

The week ranges are planning aids, not commitments, and they assume part-time work.
The early phases carry a new-to-stack tax that is real and easy to underestimate, so Phase 0 and Phase 1 will likely feel
slower than their size suggests, and that is the toolchain and platform learning, not the difficulty of the code.
The two largest sources of unpredictable calendar time are the platform reviews, especially Google's background-location review
in Phase 4, and the iOS spike in Phase 6, because both depend on parties other than you.
The plan is ordered so that if you stop at the end of any phase, what you have built still stands as a coherent, shippable thing
rather than a half-finished one.
