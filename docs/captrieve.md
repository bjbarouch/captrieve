# Captrieve

**Capture in the moment. Retrieve in context.**

This is the top-level product document for Captrieve.
It is the over-arching view that spans both halves of the project: the captrieve.com marketing website (this
repo) and the Flutter app (the captrieve-app repo).
It holds the product purpose, the model, the tiers, the privacy stance, and the cross-cutting launch checklist.
The deeper over-arching documents (the full specification, the reasoning behind the product, and the development
plan) live alongside this one and are linked from the documentation map below.

Captrieve solves a single problem: you have a thought you do not want to lose and cannot act on right now, and you
want to encounter it later, at a moment when it will be actionable.

A captured thought does not go into a notes app, a doc, or a spreadsheet, where it waits unindexed for you to
remember it is there.
It does not go into a task list, where one item fires once on one trigger.
Captrieve makes the thought reappear when and where you tell it to – cued by the real world, not by you remembering
to look.

## The two moments

The whole product is built around two moments and nothing in between.

-  **Capture.** The thought is fleeing, so the UI gets out of the way. Speak it. Transcription happens in the
   background and the audio is kept as backup. You are not blocked waiting for anything.
-  **Retrieve.** You set the cue. The app honors it. There is no algorithm deciding when you are ready, and no
   black box.

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

Cues combine, with AND conditions and delays – "when I leave work, but only after 5 PM," "20 minutes after I get
home," "the store geofence or the fridge tag, whichever comes first."
The always-visible cue list is the reliability backstop, so nothing is ever trapped behind a cue that has not
fired – you can browse, search, and surface anything by hand.

## Tiers

-  **Free, forever.** The everyday app: the full capture-and-retrieve loop with no retrieve cap, and the everyday
   cues used one at a time – date and time (unlimited), app-open, an NFC tag tap (up to 5 tags), and a Wi-Fi
   network (up to 2). The cap counts tags, not captures. No account, nothing leaves the device.
-  **Solo – one-time, about $10.** Unlocks the power tier: the rest of the cues (geofence, Bluetooth, charger,
   Focus), cue composition (AND conditions, delays, multiple cues with OR), and removal of the tag and network
   caps. No subscription, no account, no cloud. Geofence is paid in v1 so the free app needs no always-on
   location, and it may move to free in a later version.
-  **Connected – $2.99/month or $24.99/year, Family Sharing on.** Adds an opt-in sharing layer: presence events,
   shared captures, capture forwarding, and a presence log, all routed through an end-to-end encrypted backend.
   Built for families, partners, and caregivers, and includes the Solo power tier. One subscription covers up to
   five family members. After 12 consecutive months, the Solo power tier is permanently unlocked even if the
   subscription later ends.
-  **Optional Supporter purchase.** A voluntary way to support development that unlocks only a thank-you, never a
   feature.

## Privacy

Captures stay on the device.
Data leaves only by the user's explicit choice – captures shared on the Connected Tier, which travel end-to-end
encrypted with keys generated on the device, where the server holds public keys and ciphertext only and can read
nothing.
The one thing on by default is anonymous diagnostics, which carry no identifier and no capture content and turn off
with a single toggle.
There is no algorithm deciding when you are ready, at any tier.

## Platform and stack

Flutter / Dart, one codebase targeting IOS and Android.
It is fundamentally a phone app: capture is impulse-driven, and notification response happens wherever you are.

-  Local storage: `sqflite` or `hive`. No backend required for the local tiers.
-  Maps and geofencing: `flutter_map` with OpenStreetMap tiles (no Google API key), background geofencing via a
   platform-native Flutter package.
-  Notifications: `flutter_local_notifications` for time- and geofence-triggered local notifications.
-  Voice: device microphone via Flutter audio packages, with background transcription.

The data layer is abstracted from the UI behind a repository pattern from day one, so a later web companion can
talk to the same contract without rearchitecting storage.
The app's own build-and-run instructions live in the app repo (see the documentation map), not here.

## Documentation map

The project is two repos, and the docs split into over-arching (this folder) and project-specific (each repo).

**Over-arching, here in `captrieve/docs`:**

-  This document, the top-level product view and the launch checklist.
-  [`captrieve-spec.md`](captrieve-spec.md) – the authoritative product specification: data model, every cue type,
   capture and inbox behavior, the Connected Tier and caregiver use case, monetization, and the open items still
   to resolve before build.
-  [`captrieve-the-thinking.md`](captrieve-the-thinking.md) – the reasoning behind the product: the memory science
   it rests on, and the claim it deliberately refuses to make.
-  [`devplan.md`](devplan.md) – the development plan that sequences the whole product from an empty project to a
   launched app and the deferred backend tier.

**Website-specific (this repo):**
The captrieve.com marketing site is a custom Nunjucks + Sass build (`build.js`, `src/` compiled to the repo root).
The site source and the build are self-documenting.
There is no separate prose doc for them.

**App-specific (the `captrieve-app` repo):**

-  `README.md` – the app's public framing and the build-and-test commands.
-  `FEATURES.md` – the feature inventory, user-visible and engineering, and the raw material for release notes.
-  `dev-notes.md` – the app's working memory: the roadmap, the standing decisions, and the operational facts.
-  `flutter-setup-macos.md` – a first-timer's guide to a Flutter dev environment on macOS.

---

# Before launching

A running checklist of tasks to finish before the public launch (see [`devplan.md`](devplan.md) Phase 5).
Add items here as they surface during development, so launch prep is a checklist rather than a memory exercise.
Each item records what the task is and why it matters, so a later reader does not have to reconstruct the
reasoning.

## User-facing docs

### Set expectations about reminder timing and Android battery settings

The product promise is that time-based reminders fire on time, to the minute.
We schedule them as exact OS alarms (the USE_EXACT_ALARM path on Android), so "remind me at 10:00" means 10:00.
That promise holds in the normal case, but we do not have complete control over the exact firing instant in two
cases, and the user-facing docs must say so plainly rather than imply perfect precision.

1. Aggressive device battery management.
   Some phones, and some manufacturer battery-saver modes, can delay a scheduled alarm when the app has been put
   in a restricted background state.
   The fix is a single setting: mark Captrieve as Unrestricted, or exempt it from battery optimization.
   The help docs should show users how to do this, ideally with the steps for the common manufacturers, since the
   menu wording differs across devices.

2. Location-relative reminders, for example "remind me 20 minutes after I leave".
   The 20-minute timer itself is exact, but it can only start once the phone detects that you have left, and that
   detection can lag by a few minutes on some devices.
   So a "20 minutes after" reminder is really "20 minutes after we noticed you left", which is usually prompt but
   can run a little late.
   Say this directly rather than implying the start point is instantaneous.

Why this is a launch task and not a hidden caveat:
A user who hits a late reminder with no explanation concludes the app is unreliable and stops trusting it.
A user who was told the one thing up front, and given the single battery setting that fixes the common case,
keeps trusting it even when a reminder is occasionally a minute off.
This is the honest-provenance stance: describe what the app can and cannot guarantee, and never present a "will
not always be perfect" as if it were a "cannot be done".

Where this copy lands:
The website help and FAQ (faq.html), and the in-app onboarding or help, both at launch.

### Tell users the OS can revoke permissions after long non-use, and add an "it used to work" FAQ

Android automatically resets a rarely-used app's runtime permissions, and can hibernate the app, after an extended
stretch of non-use.
The system wording says "months", and other permissions may get the same treatment as location, not just location
alone.
We guide users to turn off "Pause app activity if unused" during onboarding, but that is not a guarantee.
After a long gap with no use, the OS can still strip location, and possibly notifications or background access, at
which point cues silently stop firing.
We cannot fully prevent this, so we have to set the expectation honestly and make recovery easy.

Two parts:
1. An FAQ entry titled like "It used to work and now it doesn't".
   It should explain that a long stretch of not opening the app can make Android remove its permissions, and walk
   the user back through the same settings checklist to restore them.
2. The app should actively detect missing permissions and guide re-granting rather than failing silently, on EVERY
   run, not only at onboarding.
   Re-check the relevant state on each launch and resume.
   Make the warning cue-aware: only warn about a permission the user's actual cues depend on.
   The named case: if location was turned off and the user has any geofenced cues, tell them those cues will not
   fire until it is fixed, ask if they want to fix it, then guide them to the right screen.
   The flow is ask then guide, never auto-set, since the platform forbids an app granting itself these and the user
   must stay in control. The app only detects and navigates.
   Surface the breakage per-cue too: a cue that cannot currently fire shows a literal red flag on its row in the
   Stored Cues list (the always-visible list of the user's cues), so the user sees which captures are at risk, not
   just a global banner.
   See the active-permission-help note for the full design.
3. Request permissions just-in-time and in context, not as an up-front gauntlet.
   When the user authors a cue that needs a signal, ask for that signal's permission right then, so the reason is
   self-evident (a place cue asks for location, an NFC cue asks to turn NFC on).
   Onboarding becomes an explainer rather than a permission wall.
   The same cue-to-permission mapping drives acquisition here, the every-run re-check above, and the per-cue flag.
   Notifications and the battery exemption are the two with no single cue trigger: prompt for notifications at the
   first cue, and keep the battery exemption as a guided reliability step.
   The "always" location grant is a two-step Android trip, so a place cue saves as pending-permission and activates
   once the user completes the escalation. Never block authoring or lose the capture if a permission is declined.

This is the honest-provenance stance again.
Say plainly that we cannot guarantee delivery across months of disuse, and give the one recovery path, rather than
implying set-and-forget is permanent.

### Add "unrestricted mobile data access" to the Android settings guidance, for Connected cues

Connected cues depend on the cross-device conduit (FCM push plus sync), which needs background data to receive a
cue another phone sent.
Under Android Data Saver, background data for the app can be throttled or blocked, which delays or drops an
incoming Connected cue.
Add "Allow unrestricted mobile data usage" (the per-app Data Saver exemption) to the guided settings checklist and
to the help docs, alongside the battery-optimization and background-location items.
This one only matters once Connected cues ship, so it can be gated to the Connected onboarding rather than the
first-run flow.

## App

### Remove or hide the developer scaffolding

The GeoProbeScreen and the CueBenchScreen are throwaway dev tools for exercising the engine by hand.
They must not ship in the launched app.
Remove them, or gate them behind a developer-only flag, before the public build.

### Group or coalesce a burst of notifications when many cues fire at once

Observed on-device 2026-06-28: a single wake fires every due cue at once, so when several cues come due together the
app posts that many separate notifications in a burst.
This happens for real in two ways: a catch-up wake after the app was dead for a while surfaces all the cues that
came due meanwhile, and several cues can simply be due at the same time.
A burst of separate notifications is jarring on the phone and worse on a watch, where it spams the wrist.
Resolve later: group, thread, or summarize co-firing cues (for example one summary notification that expands, or a
grouped notification channel) rather than firing N independent alerts.
Ties to the grouping/threading point in the watch-notification design.

### Validate scheduled exact alarms on-device, at low battery

The scheduler was built and unit-tested, but exact-alarm timing through Doze and OEM battery management can only
be proven on a real device.
Validate the three named failure cases on the Moto, deliberately drained to a low or middling battery, since a
high charge masks the aggressive background-kill behavior that is the real risk.

1. A "remind at 10:00" cue (a bare time cue) must arrive at 10:00, not 10:02, with the app backgrounded and with
   the app force-quit.
2. A "remind 5 minutes before" cue must arrive on time.
3. A "remind 20 minutes after I leave" cue (a delay) must arrive 20 minutes after the detected exit, with the
   residual exit-detection lag measured separately on the active-tracking probe.
4. Reboot the phone with a future time cue armed, and confirm it still fires (the plugin reschedules on boot).
5. Let a time cue come due while the phone is powered off, then power on, and confirm it fires promptly on boot.

### Cover the deferred v1 scheduling gaps before launch

The scheduler intentionally does not arm an exact alarm for three cases, because each is reconciled lazily and
the always-visible Stored Cues list is the backstop.
Decide for each whether the lazy path is good enough for launch or needs real scheduling.

1. A compound cue whose time is a gate, for example "at home AND after 5pm", has no wake if you are already home
   when 5pm passes.
   This is the compound-gate problem and is the most likely to surprise a user.
2. A cooldown re-arm over a time cue is deferred, since the cooldown-plus-clock interaction is fuzzy and the combo
   is uncommon.
3. Expiry is a silent cleanup with no alert, so a late expiry has no user-visible cost and is never scheduled.

## See also

The devplan Phase 5 section already lists the launch-day mechanics and is not duplicated here:
remove the preview-auth gate on the website, and make the Digital Asset Links and apple-app-site-association
files live before anyone installs.
</content>
</invoke>
