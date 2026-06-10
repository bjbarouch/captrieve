# Captrieve — Product Specification

## Purpose and Philosophy

**Captrieve: Capture in the moment. Retrieve in context.**

This is the product in six words.
Every design decision serves it.

Captrieve solves a single problem: you have a thought you do not want to lose and cannot act on right now, and you want to
encounter it later, at a moment when it will be actionable.

A captured thought does not go into a black hole called your faulty memory, that doesn't remember it at the right time or fully
formed.
It doesn't go into a Google doc, a spreadsheet, a notes app, a list app, or any place else where it passively waits for you to
remember that it's there and go looking for it, unindexed.
It doesn't go into a reminder app, where a task you remembered to type and remembered to configure fires once, on one trigger,
into a list you've learned to swipe away – an app built around tasks you assign yourself, not thoughts that arrive unbidden.

Captrieve makes your ideas reappear when and where you tell them to.

### Design Principles

-  **Capture is the critical moment.** The thought is fleeing. The UI must get out of the way.
   The capture flow must complete before the thought is gone.
-  **Retrieval is assured, not ambient.** The user sets the cue. The app honors it. There is no algorithm deciding when you
   are ready. There is no black box.
-  **The inbox is visible.** Captures do not disappear. The user can browse, search, and surface anything deliberately, not only
   when a cue fires.
-  **No data leaves the device.** All captures are stored locally. This is a feature, not a limitation. Privacy is a selling
   point.
-  **The user is in control.** Triggers are explicit. Conditions are evaluable. Nothing fires unexpectedly and nothing fails to
   fire silently.

---

## Marketing

### Two-Sentence Pitch

You just had a thought you cannot afford to lose and cannot act on right now.
Captrieve captures it before it's gone and delivers it back to you exactly when and where you need it —
future you will thank you.

### Two-Paragraph Pitch

Every person loses thoughts that mattered.
The idea that arrived in the shower, the question you meant to ask the doctor, the thing you needed to say before the
negotiation, the errand that only made sense on the way home — gone, because the moment of remembering and the moment of
usefulness never coincided.
Notes apps and voice memos solve the capture half and abandon the retrieval half entirely.
Reminder apps solve a sliver of retrieval – one trigger per task, configured by hand, for things you already knew were tasks –
and abandon the capture half: there is no place in them for a melody, a phrasing, a half-formed idea.

Captrieve solves both.
Capture is three steps: tap, speak, done — fast enough that the thought doesn't escape.
Then you tell Captrieve when and where you want it back — a time, a date, a place, or a combination.
When that moment arrives, it finds you.
Not in a pile of notes you have to dig through.
Not dependent on you remembering to look.
It surfaces when it is useful, because you told it to.
Your thoughts stay private — nothing leaves your phone.
No subscriptions, no cloud, no algorithm deciding when you are ready.
Future you will thank you for taking the moment to capture it.

### Investor Pitch

Captrieve addresses a universal problem — the gap between when a thought arrives and when it is useful — that no existing
product solves cleanly.
Reminder apps – including Apple's, which has offered location triggers since 2011 – are task lists with a trigger bolted on:
one trigger per item, no composition, no capture flow, and delivery reliability that their own users describe as a coin flip.
Notes apps require the user to remember to look.
Voice memo apps solve capture and ignore retrieval entirely.
Google removed location-based reminders from Android entirely in late 2025, vacating the category on the platform where
Apple's free incumbent does not exist.
AI assistants surface things algorithmically and unpredictably, with no user control and no privacy.

Captrieve's v1 architecture is intentionally minimal: fully on-device, no backend, no subscription, one-time purchase at
$7.99.
This is not a constraint — it is a positioning decision.
The no-data-leaves-the-device principle is a genuine differentiator in a market where every competing tool is cloud-first and
data-hungry.
It is especially resonant for the three highest-value user segments: creative professionals, people with ADHD, and older adults
managing cognitive load.
Each of these segments is large, underserved by existing tools, and motivated to pay for something that genuinely works.

The freemium model converts on demonstrated value — 20 free retrieves, then a single $7.99 purchase — meaning users pay only
after the product has delivered on its promise twenty times.

Version 2 introduces the Connected Tier: a subscription layer for users who want to share presence, captures, and context
with people they trust — family members, caregivers, partners, close colleagues.
The connected tier has real ongoing backend costs and delivers real ongoing value, making subscription the honest pricing model
for it.
Solo users are unaffected — their one-time purchase and fully local experience does not change.
The connected tier is purely additive.

The connected tier is the primary subscription driver and the most defensible long-term revenue stream.
The caregiver use case within it — presence signaling, shared captures, presence log with history — addresses a population
with high willingness to pay and strong word-of-mouth in exactly the communities where Captrieve's value is most legible.
Pricing: $7.99 one-time for Solo; $2.99/month or $24.99/year for Connected.
After 12 consecutive months of Connected subscription, Solo is permanently unlocked regardless of whether the subscription
continues.

### What to Tell Family

"You know how you think of something you need to remember, and then it's just gone by the time it matters?
I'm building an app where you speak the thought before it escapes, tell it when or where you want to be reminded — like when
you walk into the doctor's office, or when you land somewhere, or tomorrow morning — and it just shows up then.
No digging through notes. It finds you.
I want you to be the first people to try it." 

### Privacy Promise (Consumer-Facing)

Four points, stated plainly, as they appear on the marketing page:

-  **No account required.** There is nothing to sign up for and nothing to log into — unless you choose the Connected Tier,
   which requires an account to identify you to the people you share with.
-  **No cloud.** Captures live in local storage on the device. Full stop — unless you choose to share them with a connected
   person, in which case only what you explicitly share leaves the device, end-to-end encrypted.
-  **No subscription.** One purchase. No recurring charges. No ongoing relationship to manage — unless you choose the
   Connected Tier, which has ongoing backend costs and is priced accordingly.
-  **No algorithm.** Nothing decides when the user is ready. They set the cue. The app honors it. This is unconditional
   and does not change at any tier.

The qualifications above are honest, not apologetic.
Every exception is the user's own choice, made explicitly, for a feature that requires it.
Nothing happens to anyone's data without their action.

### Customer Quotes

These quotes are for the website and App Store page.
Each illustrates a specific human moment rather than describing a feature.

> "I was in the shower and a chord progression came to me — I just started humming it, different parts, the rhythm, the feel.
> I dried off just enough to handle my phone. Then tap, record my mouth doing its best to sound like a rock band, and said,
> "Give this to me when I get to the studio." Then I finished my shower.
> When I got to the studio, my phone vibrated, and presented the riff.
> I built the whole bridge from that two-minute voice memo, without having to worry about what part I might have forgotten."
> — Jordan K., singer-songwriter

> "I used to walk into meetings and remember, right as they started, that there was something I wanted to bring up, but damn
> me if I knew what it was.
> Now I capture it the second I think of it and it's there when I walk in the door.
> My manager has noticed. I've noticed.
> I assigned my phone's action button to Captrieve."
> — Priya S., project coordinator

> "Ten minutes before we sat down to negotiate the licensing terms, my phone surfaced a note I'd captured six weeks earlier —
> something their CEO had said offhand at a conference about their biggest pressure point.
> I walked in knowing exactly where to push and how to frame what we had to offer."
> — R. Whitfield, VP of Sales

> "My daughter used to worry that I'd forget my questions at the doctor and just say everything was fine.
> Now I capture them when I think of them, and they're there when I walk in.
> Last visit I actually remembered to ask about the medication interaction.
> Turns out it mattered."
> — Carolyn M., retired teacher

### Example Use Cases

The range of what Captrieve handles is intentionally broad.
A few examples across very different contexts — in every case, future you is glad present you captured it before it was gone:

-  The question you want to ask at your doctor's appointment next month, captured the moment you thought of it — surfaced when
   you arrive at the clinic.
-  The thing you want to pick up the next time you are in New York — surfaced when you land.
-  The idea that won't let you sleep — captured in the dark in thirty seconds, surfaced tomorrow morning at 8am so you can
   evaluate it rested.
-  The medication side effect you noticed on Wednesday — surfaced at your Thursday appointment.
-  The weekly review you keep meaning to do — surfaced every Sunday at 7:30am.
-  The errand you need to run on the way home — surfaced when you leave the office, only if it's after 5pm.
-  The thing to tell the dog walker — surfaced when you arrive home and they are likely there.
-  The follow-up you meant to send — surfaced Tuesday at 3pm when you said you would be at your desk.
-  The book title someone mentioned at dinner — surfaced the next time you open the app, when you are ready to look it up.
-  The observation about your parent's health — surfaced when you arrive at their next care appointment.
-  The ingredient you are out of — surfaced the next time you are at the grocery store.
-  The song you want to learn — surfaced when you get to the recording studio.
-  The errand that only makes sense on the way — surfaced 20 minutes after you leave work, when you are close enough to the
   store to stop.
-  The thing to remember when you sit down at the piano — surfaced when you tap the NFC tag on the bench.
-  The question for your colleague — surfaced when you join the office Wi-Fi network.
-  The grocery item you thought of at midnight — surfaced when you join the supermarket's Wi-Fi or enter its geofence.
-  The reminder to call ahead — surfaced when you are 15 minutes from home, set via a large-radius geofence on your home
   location.
-  The thing you need when you get in the car — surfaced when your phone connects to your car audio system.


---

## Competitive Landscape

Added June 2026 after a late discovery: Apple Reminders has had location triggers since iOS 5 (2011), and earlier drafts of
this spec and the site copy claimed or implied otherwise.
This section exists so that every marketing claim is written against what incumbents actually do, and so the "doesn't my
phone already do this?" objection is answered before a reviewer asks it.
Standing rule: no claim of the form "your phone can't do X" ships without being checked against this section, and this
section gets re-verified each release.

### What incumbents actually do

**Apple Reminders (free, preinstalled, iOS/macOS/watchOS)**

-  Geofence triggers, arriving or leaving, with adjustable radius (~100m minimum to ~150 miles), since iOS 5.
-  Siri creation by voice, including "when I get home / leave work" via contact-card addresses.
-  Car triggers: getting in / out of the car, via CarPlay or car Bluetooth connect/disconnect.
-  "When Messaging" a chosen person.
-  Shared lists with assignment, subtasks, attachments, early reminders, iCloud sync.

What it does not do: capture-first flow (no voice-note-becomes-item gesture), one trigger per item, no AND conditions, no
time offsets ("20 minutes after leaving"), no multi-cue OR, no NFC, no Wi-Fi network triggers, no arbitrary Bluetooth
devices, no caregiver layer, no presence events, no Android.
Reliability of its geofences is a long-running user complaint – community threads describe roughly coin-flip delivery.

**iOS Shortcuts personal automations (free, preinstalled)**

-  Triggers include: NFC tag, Wi-Fi network join, Bluetooth device connect, arrive/leave location, time of day, app opened,
   charger, Focus mode, CarPlay.
   This overlaps most of the Captrieve trigger taxonomy.

What it does not do: it is a programming tool, not a product.
No capture model, no inbox, no per-capture cue attachment – the user builds and maintains one automation per behavior.
The overlap is real for power users and irrelevant for the target segments (creative flow, ADHD, older adults, caregivers).
The honest framing: Shortcuts proves the OS primitives exist – Captrieve is the product shape on top of them.

**Google / Android**

-  Google migrated Keep reminders into Google Tasks (rollout late 2025) and removed location-based reminders entirely –
   they can no longer be created or received.
-  Displaced users are being pointed at Samsung Reminder (Samsung devices only), TickTick, and Tasks.org.

This is a tailwind: the platform without Apple's free incumbent just lost its first-party location reminders.

**Third-party geofence reminder apps (iOS App Store)**

-  A persistent small-app category since Checkmark (2012), whose differentiator against Reminders was exactly time offsets
   before/after location events.
   Current examples: GeoNudge, GeoReminder, Remind There – typically $1–$20 lifetime or ~$10/year, geofence-only,
   single-trigger, ad- or subscription-funded, several with data-collection practices that contradict Captrieve's privacy
   posture.

None observed offers the combination of NFC + Wi-Fi + BLE + composition + capture-first + caregiver layer + local-only privacy.

### Where the moat actually is

The trigger, taken alone, is commoditized.
The defensible territory, in order of strength:

1. The Connected/caregiver layer – presence events, shared captures with recipient-side cues, the log-not-a-map model.
   Nothing in any incumbent resembles it.
2. Capture-first UX – speak before the thought escapes, attach the cue after.
   Reminders structurally cannot do this without becoming a different product.
3. Cue composition – AND conditions, delays, multi-cue OR.
   Thirteen years of third parties differentiating on fragments of this suggests Apple will not absorb it.
4. NFC-as-product for normal humans – the deliberate tap ritual, packaged, named, and habit-framed, vs Shortcuts DIY.
5. Cross-platform, with Android freshly vacated by Google.
6. Local-only privacy as architecture, against a third-party field that is largely tracking-funded.

### Positioning rules derived from the above

-  Never claim incumbents lack location triggers.
   Claim what they lack: the capture flow, the composition, the reliability engineering, the connected layer, the system.
-  The enemy in marketing copy is not "your phone can't" but "what you have makes you do all the work at the wrong moment":
   remember to type the task, remember to configure the trigger, accept one trigger, hope it fires.
-  Preempt the objection by asking it ourselves, in the FAQ and on the site, in plain words: "Doesn't Apple Reminders
   already do this?"
-  Lead marketing with NFC, composition, and the caregiver story – geofence is the most commoditized and least reliable
   trigger and should not be the headline.
-  Apple-risk note for investors: the composition and capture features are absorbable by Apple in principle; the caregiver
   layer, cross-platform presence, and privacy-architected sharing are the parts that survive an Apple sherlocking.

---

## FAQ Page (captrieve.com/faq)

A second page on the marketing site, distinct from the main landing page.
Searchable — a static list of eight entries becomes useless quickly; this page will grow.

### Purpose and Audience

The FAQ serves two distinct audiences with different needs.
Prospective users want reassurance and context — they are deciding whether to trust the product.
Existing users hitting a problem want a direct answer fast, in language that matches how they would describe the problem
("my reminder didn't go off," not "geofence cue failure").
Entries should be written with enough natural-language keywords that a frustrated user's search surfaces the right entry.

### Entry Structure

Each entry follows this structure consistently:

1. **What the behavior is** — stated plainly, without hedging.
2. **Why it happens** — with platform attribution where the constraint originates in iOS or Android, not Captrieve.
3. **What the user can do about it** — always included if there is anything actionable. Never a dead end.

### Known Entries (seed list)

These are the entries the FAQ launches with.
Each will be fleshed out into full prose before the page goes live.

-  Doesn't Apple Reminders already do location reminders?
   (Yes — answer honestly. Arriving/leaving geofences since 2011, Siri creation, car triggers. Then the difference:
   Reminders is a task list you must remember to type into and configure, one trigger per item, no capture flow, no
   combinations, no NFC/Wi-Fi/Bluetooth-device cues, no caregiver layer, and delivery its own users describe as
   hit-or-miss. Captrieve is built around the capture moment and the retrieval moment — the task list in between is
   what it eliminates.)
-  Can't I build this myself with iOS Shortcuts?
   (A fair amount of it, yes, if you enjoy that sort of thing — answer with respect for the tinkerer. Shortcuts can fire
   on NFC, Wi-Fi, Bluetooth, and location. What it can't give you is the product: a capture flow, an inbox, a cue attached
   to a thought rather than an automation attached to a trigger, or anything for the person you care for. If you build it
   and it serves you, genuinely, good. Captrieve is for everyone who won't.)
-  I'm on Android — didn't Google have location reminders?
   (It did. Google removed them when Keep reminders migrated to Google Tasks in late 2025 — they can no longer be created
   or received. Captrieve is, in part, what replaces them.)
-  Why didn't my location cue fire?
   (Covers: phone off during boundary crossing, low-power mode deferral, region cap prioritization — all attributed to iOS/Android.)
-  Can Captrieve tell which floor of a building I'm on?
   (No. GPS does not resolve altitude at floor scale on any phone. iOS and Android do not expose this. Not a Captrieve limitation.)
-  Why did my cue fire twice right after I restarted my phone?
   (Known iOS behavior on reboot. One-line explanation, no action needed — Captrieve discards the duplicate automatically.)
-  What happens if I have a lot of location cues set?
   (iOS limits how many locations your phone monitors simultaneously. Captrieve prioritizes the ones nearest your current
   position. Mitigation: dismiss captures you've already acted on.)
-  What happens if my phone is off when a cue was supposed to fire?
   (Time-based cues are evaluated on next boot. Geofence cues cannot be recovered — the device wasn't present to
   detect the crossing. Recommendation: set a fallback time cue for anything critical.)
-  Does Captrieve send my data anywhere?
   (No. All captures live on your device. No account, no cloud, no server.)
-  Can I get my data out?
   (Yes. Export everything as JSON from Settings at any time.)
-  Why does voice transcription sometimes get words wrong?
   (On-device transcription quality varies by device, OS version, accent, and background noise. This is a platform
   limitation. The original audio is always retained so nothing is lost.)
-  Can Captrieve tell the difference between my house and my neighbor's house?
   (Probably not reliably. Consumer GPS accuracy is typically 5–30 meters depending on conditions, and iOS and Android add
   their own uncertainty on top of that to prevent false cues from position drift. Locations closer than 150–200 meters
   apart — adjacent houses, nearby offices — may not be consistently distinguished. This is a physics and platform
   limitation, not a Captrieve limitation. Mitigation: use a single geofence for the area and rely on the capture label
   to clarify which context it belongs to.)
-  My location cue didn't fire and my phone is an older Android. What's wrong?
   (Older Android devices and some manufacturers — Samsung, Huawei, Xiaomi, and others — have battery management systems
   that stop background processes more aggressively than Android itself requires. This can prevent location cues from
   firing when Captrieve is not in the foreground. This is the device's behavior, not Android's or Captrieve's.
   Mitigation: go to Settings > Battery > Battery Optimization, find Captrieve, and set it to "Don't optimize." The exact
   path varies by device and Android version. After whitelisting, location cues should behave normally.)
-  I have a tablet. Will location cues work?
   (It depends on your tablet's hardware. Most tablets are WiFi-only — they have no cellular radio. Without cell towers,
   location fixes come from GPS and WiFi positioning only. GPS requires a clear view of the sky and takes longer to
   acquire a fix after the radio has been idle. WiFi positioning works well near known networks and poorly away from them.
   Location cues will generally work but may be less reliable and slower to fire than on a phone with cellular.
   For most Captrieve use cases — arriving at the doctor's office, getting home — you will be near WiFi, and the
   difference will be small. In motion away from known networks, expect more variability.)
-  What if I set up NFC tags and after a while I just... don't need them anymore?
   (Then you spent a few dollars on some small stickers and permanently improved your memory in a way that had eluded
   you for years. That is not a failure of the app. That is the app working at its best. Captrieve is designed to make
   itself unnecessary, one habit at a time. The tag on your nightstand was never the point — the bedtime routine that
   no longer requires a reminder is. Keep the tags where they are. Tap them anyway. Something else worth remembering
   will come along.)

-  Why is location triggering less reliable in some places than others?
   (Location accuracy depends on what signals are available: GPS satellites (best outdoors with clear sky), cell towers
   (good coverage in most areas, cellular devices only), and WiFi networks (good indoors near known networks, poor
   elsewhere). Dense urban areas with tall buildings can interfere with GPS. Underground locations have no GPS at all.
   iOS and Android do the best they can with what is available. Captrieve works with what the device provides — it has
   no access to signals the device cannot receive.)
-  My Wi-Fi cue didn't fire when I arrived. What happened?
   (Wi-Fi cues fire on network join, which requires the device to actively connect to that network. If your phone
   connected to a different network nearby, or stayed connected to a previous network, the join event does not occur.
   Mitigation: confirm the network name matches exactly, including capitalization. If you are near multiple networks and
   your phone auto-selects a different one, consider combining with a geofence cue as a fallback.)
-  How do I set up an NFC tag?
   (Select "When I tap an NFC tag" as your cue type, then hold your phone against the tag when prompted. Captrieve
   reads the tag's unique identifier and registers it. If the tag is blank, Captrieve writes an identifier to it. You can
   name the tag — "Piano", "Desk", "Car" — and reuse it across multiple captures. NFC tags are inexpensive and available
   online and at office supply stores.)
-  Does NFC work in the background, or do I have to open the app first?
   (On Android, NFC tags can wake Captrieve automatically when tapped. On iOS, NFC requires either the app to be open or
   a lock screen NFC shortcut to be configured. iOS does not allow fully background NFC scanning. The intentional tap is
   the intended interaction on all platforms — hold your phone to the tag, see what you needed to remember.)
-  What is a good radius for an en-route cue?
   (If you want a capture to surface while you are still traveling toward a destination — for example, to call ahead before
   you arrive home — set a geofence_arrival trigger on that location with a generous radius, such as 5–15 kilometers.
   The cue fires when you enter that radius, while you are still in transit. Experiment with the radius slider to find
   the distance that gives you enough lead time for your specific use case.)

### Hardware Expectations

The FAQ should include a plain-language summary of what hardware works best, so users can calibrate expectations before
they encounter a problem.

Recommended device characteristics for reliable location triggering:

-  A current or recent smartphone (iPhone or Android) with cellular capability.
   Cellular triangulation significantly improves position accuracy and update frequency,
   especially indoors and when GPS signal is weak.
-  GPS hardware, which is present on all modern phones and most tablets.
-  A recent OS version. iOS 14+ and Android 8+ are the tested baseline.
   Older OS versions may have geofencing behavior that differs from current platform documentation.

Devices that will work with caveats:

-  WiFi-only tablets — location cues will function but with reduced reliability,
   particularly in motion or away from known WiFi networks.
-  Older Android devices — battery management whitelisting may be required (see above).
   Geofencing behavior on Android improved significantly in Android 8 and again in Android 10.
   Devices running Android 7 or earlier may have unreliable background cue delivery.

Devices that are not supported:

-  Devices without location hardware (no GPS, no WiFi, no cellular).
   These cannot determine position and cannot evaluate geofence cues.
   Time-based cues still work on these devices.

### Writing Standard for Device and Hardware Entries

The same attribution principle that applies to OS constraints applies to hardware constraints:
name the actual cause, not the app.

-  Correct: "Most tablets are WiFi-only, which means location fixes rely on GPS and nearby WiFi networks.
   This affects how quickly and reliably location cues fire."
-  Incorrect: "Captrieve location cues may not work reliably on tablets."

The first is honest and informative. The second implies a product deficiency.
Hardware limitations are the device's characteristics, not Captrieve's choices.

### Maintenance

The FAQ is a living document.
It is updated alongside each version release.
Open items in the spec that resolve during development — geofencing package behavior, transcription quality specifics —
generate FAQ entries when they do.
User support questions that recur become FAQ entries.

---

## Special Use Cases

### Creative People

Creatives already know the pain of a good idea evaporating before they could act on it.
A notes app fails them at the retrieval step — the idea gets buried in a pile of other captures and is never seen again at the
right moment.
Captrieve's retrieval guarantee changes that: the melodic idea surfaces when they arrive at the studio, the character
observation surfaces on Sunday morning when they write, the visual concept surfaces when they are standing in front of a canvas.
The tool fits naturally into a creative workflow without requiring the creative person to build or maintain a system — which
they will not.

### People with ADHD

This is the strongest fit.
The ADHD experience includes exactly the problem Captrieve solves: thoughts arrive at high velocity and disappear just as fast,
working memory is unreliable, and the gap between "I should do that" and "I forgot I was going to do that" is very short.
The capture flow must be fast enough that the thought does not escape before it is committed.
Any friction is a real barrier for this population.
Context-triggered retrieval also addresses a specific ADHD failure mode: reminders that fire at arbitrary times get dismissed
and forgotten.
A reminder that fires when you walk into the pharmacy is harder to ignore because the context makes it immediately actionable.
The "assured retrieval in context" framing is almost a description of an ADHD accommodation tool.
There is significant overlap between creative people and people with ADHD; this is not a coincidence.

### People Managing Their Health and Memory

A different and more serious use case: people with early-stage cognitive decline, post-concussion symptoms, chemo brain,
or other acquired memory impairments.
For this population, a reliable external memory that surfaces things in the right context is not a convenience — it is a
meaningful quality-of-life tool.
The privacy positioning is especially important here: people navigating memory loss are often already managing loss of autonomy
and may be resistant to tools that feel surveillant or that share their data without explicit control.
Captrieve's no-data-leaves-the-device principle, with the explicit exception of caregiver sharing (see below), directly
addresses this concern.

The UI simplicity enforced throughout this spec is not merely an aesthetic preference.
It is a functional requirement for the ADHD and memory-impairment use cases specifically.
Any added friction in capture is a real barrier for these users.
This constraint should be treated as non-negotiable when evaluating new features.

---

## Platform

Flutter/Dart, targeting iOS and Android from a single codebase.

The product is fundamentally a phone app.
Capture is impulse-driven and happens in the moment.
Notification response is immediate and happens wherever you are.
The phone is always present; other surfaces are not.

### Language Standard for OS-Imposed Constraints

Any user-facing text describing a limitation that originates in iOS or Android behavior — not a Captrieve product decision —
must attribute it accurately.

The standard: name the platform, not the app.

-  Correct: "iOS limits the number of locations your phone can monitor simultaneously."
-  Incorrect: "Captrieve can only monitor 20 locations at a time."

The first is true and honest. The second implies a product decision that could have been made differently.
This applies to onboarding copy, FAQ entries, in-app prompts, and App Store description language.
Constraints in this category include: the simultaneous geofence region cap, geofence delivery deferral in low-power mode,
geofences missed while the device is off, vertical location precision (floor-level discrimination is not possible because
GPS does not resolve altitude at that scale on any phone), and transcription quality variation by device and OS version.
Each entry should follow the structure: what the behavior is, why it happens (with platform attribution), and what the user
can do about it if anything.



The data layer is abstracted from the UI layer via a repository pattern from day one.
The repository sits between the UI and local storage, and defines a clean data contract.
A web interface added later talks to the same repository contract — it does not require rearchitecting storage or data models.
This discipline is enforced from the start, not retrofitted.

The Flutter web target is deferred but not foreclosed.
A future web companion would primarily serve capture via keyboard (longer thoughts, better editing) and browsing the capture
inbox on a larger screen.
Notification delivery on web is secondary since the phone handles that reliably.

---

## Technology Stack

### Local Storage

`sqflite` or `hive` for structured local storage.
No backend required for v1.
All data lives on the device.

### Voice Capture

Device microphone via Flutter audio packages.
Audio is saved immediately on recording stop.
Transcription runs in the background — the user is not blocked waiting for it.
A "transcribing..." indicator appears on the capture in the inbox.
The review-and-confirm step is available when the user next opens that capture's detail view, not as a blocking step in the
capture flow.
Audio is retained alongside the transcript as backup.
The user may discard audio to save storage.

### Maps and Geofencing

`flutter_map` with OpenStreetMap tiles — no Google API key required, no third-party dependency.
Geocoding via a compatible OpenStreetMap geocoding package, supporting place name search, city search, airport codes, and street
addresses.

Background geofencing via platform-native APIs through a Flutter geofencing package.
Geofencing is a first-class feature, not an add-on.
Reliable background cue delivery is a core product promise.

### Notifications

`flutter_local_notifications` for time-based and geofence-triggered local notifications.
No push server required for v1.
All notification logic runs on-device.

---

## Data Model

### Capture

The primary entity.
Everything the user stores is a Capture.

| Field | Type | Notes |
|---|---|---|
| `id` | UUID | |
| `createdAt` | DateTime | |
| `body` | String | Transcribed text, or typed text |
| `audioPath` | String? | Local path to audio recording, if kept |
| `photoPath` | String? | Local path to photo, if attached |
| `triggers` | List\<Trigger\> | One or more; OR logic across the list |
| `status` | CaptureStatus | pending, surfaced, snoozed, dismissed |
| `snoozedUntil` | DateTime? | Set when status is snoozed |
| `label` | String? | Optional user-defined name shown in inbox instead of auto-generated cue summary |
| `autoDismiss` | bool | If true, dismiss automatically after first notification is acted on |
| `surfaceCount` | int | Incremented each time a cue fires and a notification is delivered. Never reset. Used to detect stale captures. |

### Trigger

Each Capture has one or more Triggers.
Each Cue fires independently on its primary event, then evaluates any AND conditions at the moment of firing.
If all conditions pass, the notification is delivered.
If any condition fails, the cue does not fire — and does not retry.
There is no ongoing state between evaluation attempts.

OR logic across multiple cues is simply a matter of setting multiple cues on the same Capture.
Any cue that fires delivers the notification.

| Field | Type | Notes |
|---|---|---|
| `id` | UUID | |
| `type` | TriggerType | datetime, geofence_arrival, geofence_departure, wifi_join, wifi_leave, bluetooth_connect, bluetooth_disconnect, nfc_checkin, app_open |
| `fireAt` | DateTime? | For datetime triggers |
| `locationId` | UUID? | Reference to a saved Location |
| `inlineLat` | double? | For one-off geofences not saved as a Location |
| `inlineLng` | double? | |
| `inlineRadius` | double? | Meters |
| `wifiSsid` | String? | For wifi_join and wifi_leave triggers |
| `bluetoothDeviceName` | String? | For bluetooth_connect and bluetooth_disconnect triggers |
| `nfcTagId` | String? | For nfc_checkin triggers; the tag's unique identifier |
| `delayMinutes` | int? | Delay after primary event before notification fires |
| `repeatAfterMinutes` | int? | If set, fires again N minutes after initial delivery |
| `andConditions` | List\<Condition\> | All must pass at moment of firing |

### Condition

A point-in-time predicate evaluated at the moment a Trigger's primary event occurs.

| Field | Type | Notes |
|---|---|---|
| `type` | ConditionType | time_after, time_before, inside_location, outside_location |
| `time` | TimeOfDay? | For time_after and time_before |
| `locationId` | UUID? | For inside_location and outside_location |

**Examples of compound cue logic:**

-  "Remind me when I leave the office, but only if it's after 5pm" — geofence_departure trigger on the office location, with a
   time_after 17:00 AND condition.
-  "Remind me at 5pm, but only if I'm not at the office" — datetime cue at 17:00, with an outside_location AND condition on
   the office location.
-  "Either of the above, or remind me at 7pm regardless" — the two cues above, plus a plain datetime cue at 19:00. OR
   across all three.
-  "Remind me when I leave the office, then again 20 minutes later" — geofence_departure trigger with repeatAfterMinutes set to
   20.
-  "Remind me when I leave the office, but wait 20 minutes first" — geofence_departure trigger with delayMinutes set to 20.
-  "Remind me when I land" — geofence_arrival trigger on the destination city or airport, with a generous radius (several
   kilometers). The user searches for the destination on the map rather than dragging a pin from their current location.

---

## Cue Types

Retrieval is the core value of Captrieve.
The richness of available cue types is the direct implementation of that value.
Each type below is a first-class cue — it appears in the cue picker, can carry AND conditions, and participates in OR
logic across multiple cues on the same capture.

### datetime

Fires at a specific date and time.
The baseline cue type.
Useful for thoughts tied to a known future moment: an appointment, a deadline, a morning review.
Siri, Alexa, and calendar apps already do this.
Captrieve includes it because completeness requires it and because it composes with AND conditions in ways those tools do not
support.

### geofence_arrival

Fires when the device enters a defined circular region.
The region is defined by a center point and radius, ranging from approximately 100 meters (a specific building) to 50
kilometers (a city or airport approach zone).
Delivery is handled by platform-native background geofencing APIs and is subject to OS deferral in low-power mode.
See the FAQ for platform attribution on geofence reliability.

Use cases: arrive at the doctor's office, arrive at the grocery store, land in a city, return to a neighborhood.

### geofence_departure

Fires when the device leaves a defined circular region.
Same region model as geofence_arrival.
Particularly useful with AND conditions — "when I leave the office, but only if it's after 5pm."

Use cases: leave work, leave the house, leave a meeting venue.

### wifi_join

Fires when the device joins a specific Wi-Fi network by SSID.
This is a high-precision indoor location signal that GPS cannot provide.
It is passive — no user action required beyond having Wi-Fi enabled — and requires no additional hardware.

This cue type was not in the original spec and was added because it solves a real problem geofencing handles poorly:
distinguishing between nearby locations (your office vs. the coffee shop next door), or identifying indoor arrival at a
specific place (the dentist's office, the airport terminal, a friend's home) where GPS is unreliable.

Use cases: arrive at home network, arrive at office network, arrive at the airport, arrive at a specific venue.

Implementation note: Wi-Fi SSID scanning in the background is supported on both iOS and Android with the appropriate
permissions.
On iOS, the CNCopyCurrentNetworkInfo API requires the Access Wi-Fi Information entitlement.
This should be treated as a required entitlement, not optional — wifi_join is a core cue type.

### wifi_leave

Fires when the device disconnects from a specific Wi-Fi network.
The complement of wifi_join.

Use cases: leave home, leave the office.

### bluetooth_connect

Fires when the device connects to a specific Bluetooth device, identified by device name.
The most common real-world case is car audio — connecting to your car's system is a reliable proxy for "I am now in my car."
A home speaker or work headset can serve similar roles.

This cue type requires no additional hardware beyond devices the user already owns.
It is lower priority than geofence and Wi-Fi cues in the cue picker UI, but fully supported.

Use cases: get in the car, arrive at a location where a known device lives.

### bluetooth_disconnect

Fires when the device disconnects from a specific Bluetooth device.
The complement of bluetooth_connect.

Use cases: get out of the car, leave a location where a known device lives.

### nfc_checkin

Fires when the device reads a specific NFC tag.
NFC requires physical proximity — effectively contact — between the phone and the tag.
This is a feature, not a limitation.
The tap is a deliberate micro-ritual: I am here now, what do I need to know?

NFC tags are inexpensive (under $1 each), widely available, and can be placed anywhere: a desk, a piano, a car dashboard,
a medicine cabinet, a front door, an airplane seat armrest.
The user writes a unique identifier to each tag during setup.
When the phone reads that identifier, the associated capture surfaces.

**The check-in habit.**
For users with ADHD or age-related memory impairment, passive cues fire at them.
An NFC check-in puts the user in an active, intentional relationship with their own memory.
Six tags placed at consistent locations — desk, piano, kitchen, car, front door, bedside — build a physical routine that is
easier to maintain than remembering to open an app.
The physicality is the cue.
This is not a workaround for a missing feature.
It is a genuinely different retrieval mode: not "ambush me at the right moment" but "I am here, what did I need?"

**Common tag locations.**
Tags are inexpensive enough to place liberally.
Typical home locations: front door, back door or garage, bedside table, bed headboard, kitchen counter, refrigerator,
medicine cabinet, bathroom mirror, home office desk, computer monitor, couch or reading chair.
Creative and hobby locations: piano, guitar case or strap, recording desk, easel or art table, sewing machine, workbench,
workshop or studio entrance.
Work locations: desk, work monitor, conference room tables (one tag per room), filing cabinet.
Vehicle and transit: car dashboard or steering wheel, sun visor, bicycle handlebar.
Health and routine: pill organizer or medicine shelf, exercise mat, therapy or meditation cushion.
Disposable or temporary: airplane seat armrest, hotel room desk, gym locker.

The medicine cabinet or pill organizer placement is a meaningful health management use case: captures about medications,
dosage questions, or side effects surface when the user is physically at the cabinet — the moment of highest relevance.
NFC tags are inexpensive enough to treat as disposable for one-off contexts.
Stick a tag on an airplane seat armrest: tap on boarding, tap again when gathering your things to depart — the tap fires
whatever capture was set for that tag.
This solves a use case that geofencing handles poorly: airport geofences are large and imprecise, firing on approach or
during layovers.
An NFC tap on departure is unambiguous.

**Nothing pending feedback.**
When the user taps an NFC tag and no captures are pending for that tag, the app must respond visibly.
Silence is ambiguous — the user took a deliberate action and needs to know it was received.
The response: a brief, non-alarming message — "Nothing pending for Piano" (using the tag's saved name) — displayed
for two to three seconds, then dismissed automatically.
This confirms the tap registered, the tag is recognized, and the absence of a retrieve is meaningful rather than a
malfunction.
The user does not need to tap again.
When the user selects nfc_checkin as a cue type, they are prompted to tap a tag to register it.
The app reads the tag's unique ID and stores it as `nfcTagId` on the trigger.
If the tag has no ID written, the app writes one.
The user can name the tag (e.g. "Piano", "Desk", "Car") — this name is stored locally and used in the cue summary.
Named tags are saved to a tag library analogous to the saved Locations list and can be reused across captures.

Implementation note: Flutter NFC packages support tag reading and writing on both iOS and Android.
iOS requires the NFCReaderSession API, which requires user initiation — background NFC scanning is not available on iOS.
Android supports more permissive background NFC behavior.
On iOS, the check-in model (user taps intentionally) maps naturally to the platform constraint.
This should be presented to the user as the intended interaction, not as a limitation.

### app_open

Fires the next time the user opens Captrieve.
This is "until I ask" — the user knows they will want this capture soon and trusts themselves to open the app when the moment
is right.
It is not a degenerate case or a fallback.
It is a deliberate retrieval mode for captures that don't have a clean time or place cue but have a short horizon.

The cue is consumed on first app open after it is set.
If the app is already open when the capture is created, the cue fires on the next foreground resume, not immediately.

---

### Location

A saved named place.
Referenced by Triggers and Conditions.

| Field | Type | Notes |
|---|---|---|
| `id` | UUID | |
| `name` | String | User-defined: "Home", "Doctor", "Office", "SFO" |
| `lat` | double | |
| `lng` | double | |
| `radius` | double | Meters; adjustable per location; range supports neighborhood to city scale |

### CaptureStatus

`pending` — cue has not yet fired. `surfaced` — cue fired, notification delivered, awaiting user action. `snoozed` —
user deferred; `snoozedUntil` set. `dismissed` — user released the capture.
No further notifications.

---

## Capture Modalities

### Voice (Primary)

One tap opens the record view.
Tap to record, tap to stop.
Audio is saved immediately.
Transcription runs in the background.
The cue picker opens right away — the user does not wait for transcription.
The capture body shows "transcribing..." until complete.
Total time from thought to committed capture: fast enough that the thought does not escape.

### Text

For longer or more precise thoughts where voice is impractical.
A plain text input with no formatting, no structure, no fields.
Just the thought.

### Photo

For the thing you see, not the thing you think.
Capture opens the camera or photo library.
The photo is stored locally.
An optional caption can be added by voice or text.

---

## Trigger Setting UI

After capture, the user is immediately presented with the cue picker.
This is not optional — a capture with no cue is a note, not a Captrieve.
The cue picker must be fast and opinionated.

### Quick Options (one tap)

The first option shown is the user's selected default cue.
The default default is "Tomorrow morning." The user can change their default in settings.
Other quick options:

-  Next time I open the app
-  Specific time — opens time/date picker
-  When I arrive somewhere — opens Location picker
-  When I leave somewhere — opens Location picker
-  When I join a Wi-Fi network — opens Wi-Fi picker (lists nearby networks; user can type an SSID manually)
-  When I connect a Bluetooth device — opens Bluetooth device picker (lists paired devices)
-  When I tap an NFC tag — initiates tag registration flow

### Saved Locations

A list of named locations the user has previously saved.
Selecting one applies it immediately.
A "New location" option opens the map.

### Map View

For setting a one-off geofence or creating a new saved Location.

The map opens centered on the user's current position by default.
A search bar at the top supports place name, city, airport code, and street address lookup via geocoding — the map navigates to
the result and drops a pin.
The user can then drag the pin to fine-tune position.
A current-location button returns the map to the user's present position.

A radius ring is displayed around the pin.
A radius slider below the map adjusts it, with a range from approximately 100 meters to 50 kilometers, supporting use cases from
a specific building to an entire city or airport approach zone.

The user can save the pin as a named Location or use it once without saving.

### AND Conditions

Available after a primary cue is set, as an optional "But only if..." addition.
Presented plainly with the available condition types.
Not required; most captures will have none.

### Multiple Triggers

An "Add another cue" option appends a second cue to the same capture.
The UI makes clear these are OR — any one firing delivers the notification.

---

## Editing and Deleting Captures

Any capture can be edited or deleted from the inbox or archive at any time.

Editable fields: body text, photo, audio recording, all cues, auto-dismiss setting.

Deleting a capture from the inbox cancels all pending cues and removes it permanently.
Deleting from archive removes it permanently.
No recovery after delete.

---

## Inbox and Browsing

The inbox has two sections:

**Surfaced** — captures whose cue has fired but have not yet been acted on.
Shown at the top.
The app badge count reflects the number of surfaced captures.
On foreground, surfaced captures are displayed prominently.
They persist until the user explicitly snoozes or dismisses.

**Pending** — captures with cues that have not yet fired, ordered by next cue time.

### Inbox Row Layout

Each row is divided horizontally: one third for the label, two thirds for the body preview.
The default row height is one line.
If the label requires wrapping it drives the row taller, up to a maximum of three lines.
The body preview occupies the same vertical space as the label but never drives row height — it always truncates with ellipsis
at whatever space it is given.
A label that exceeds three wrapped lines is also truncated with ellipsis at that point.

The label column has two levels.
The label name occupies the top of the column in normal weight text, wrapping up to the 3-line maximum; if the name exceeds that
it is truncated with ellipsis.
Directly below it, always present, is the creation timestamp in smaller, lighter text — for example "2026-02-03 11:53 AM".
The timestamp is always shown in full and never truncated.
If no label name has been set, the timestamp appears at the top of the column in normal weight as the sole identifier.

The body preview shows the beginning of the capture text — enough to identify the thought without opening it.

The 1/3 – 2/3 horizontal split is a starting point to validate against a real device.
The label column needs enough width for the name to wrap readably; the body column needs enough width to show meaningful preview
text.
Adjust after first build.

This allows the user to scan the inbox and immediately recognize groupings — for example, three captures all labeled
"dermatologist" visible before any appointment, ready to review or consolidate.

The label can be set or changed from the inbox row or the detail view.
Once set it appears everywhere the capture is shown.
The auto-generated cue summary remains visible in the detail view regardless of whether a label is set.
The label is also the primary searchable and scannable identifier, making "add to existing" fast to navigate.

### Inbox Actions

From the inbox the user can:

-  Tap any capture to see full detail — text, photo, audio player, all cues and their status.
-  Snooze a surfaced capture — sets a new cue from a quick-option menu or free picker.
-  Dismiss a capture — marks it done, moves to archive.
-  Edit any capture's body, photo, or cues.
-  Delete a capture permanently.
-  Search all captures by text content.
-  Select multiple captures and merge them — combines their bodies in order, unifies their cues (OR across all), and deletes
   the originals. Useful when several captures have accumulated for the same upcoming context and the user wants a single
   consolidated notification. Each captured body becomes one top-level bullet in the merged result. Plain text bodies are
   wrapped in a single bullet. Bodies that already contain list structure are preserved intact and nested under a top-level
   bullet whose header is the capture's label name and creation timestamp, formatted inline as: "dermatologist, 2026-02-03 11:53
   AM". If no label is set, the timestamp alone is used. This format is always deterministic and unambiguous even when labels
   repeat. No existing structure is flattened. After merge the user is dropped into an edit view to review and adjust the
   combined body before committing.
-  Add a new capture to an existing one — at capture time, instead of creating a new capture, the user can select "Add to
   existing." A searchable list of existing captures opens, browsable by label or cue summary. Selecting one appends the new
   body as a bullet item to the existing capture's body. No new cue is created; the existing cues remain unchanged.

---

## Archive

Dismissed captures move to the archive.
The archive is searchable and browsable.
Captures in the archive can be viewed in full detail, restored to pending with a new cue, or permanently deleted.
Nothing is automatically deleted from the archive.

Auto-dismiss: if a capture has `autoDismiss` set to true, it moves to archive automatically when the user acts on the
notification (taps it and opens the detail view).
This is appropriate for one-and-done captures.
The setting is per-capture and set at capture time, with a global default in settings.

---

## Notification Delivery

When a cue fires and all AND conditions pass:

-  A local notification is delivered with the capture body as the notification text.
-  Tapping the notification opens Captrieve directly to that capture's detail view.
-  The capture status moves to `surfaced`.
-  The app badge count increments.
-  `surfaceCount` is incremented by 1.

If the user does not act on a surfaced capture, it remains in the surfaced section of the inbox on next app open.
The badge count reflects all unacted surfaced captures.

### Stale Capture Prompt

When a geofence cue fires and `surfaceCount` is already 2 or more, the notification is delivered as normal, but the
detail view also surfaces a non-intrusive prompt:

> You've been here a few times without acting on this. Still relevant?

Three exits: **Keep it** (dismiss the prompt, capture remains active), **Done with it** (dismiss the capture to archive),
or **Change the cue** (opens the cue picker).

The prompt is informational, not accusatory.
It does not block interaction with the capture.
It does not appear for datetime cues — repeated datetime surfacing is expected for recurring-style captures and carries
less signal.
It applies to geofence, Wi-Fi, Bluetooth, and NFC cues — all context-based cue types where repeated surfacing without
action is meaningful signal.
The threshold of 2 is a starting point; adjust after observing real usage patterns.

This prompt serves a secondary function: it naturally surfaces candidates for dismissal when the user is approaching the
20-region iOS geofence cap, without ever exposing the cap to the user as a technical constraint.

### Phone Off or Rebooted

Time-based cues whose fire time passed while the phone was off are evaluated on next boot.
Any whose time has passed are surfaced immediately on next app open.

Geofence cues that fired while the phone was off are unrecoverable — the device was not present to detect the boundary
crossing.
This is an honest limitation of on-device geofencing and is noted in onboarding.
The user should set a fallback datetime cue for any geofence capture where missing the cue would be a significant
problem.

---

## Do Not Disturb

A global, momentary notification gate.
Not the same as snooze, which operates on individual captures.
DND says: I am not available right now — hold everything.

The user was just pulled into an unplanned meeting.
A timer is about to go off.
They need one tap to suppress all incoming notifications until they are ready.

### What It Does

DND is a delivery gate, not a state change.
While active, cues continue to evaluate normally.
If a cue fires and all conditions pass, the capture moves to `surfaced` and the badge count increments —
but no notification is delivered to the lock screen or notification center.
The capture is waiting in the inbox when the user is ready.

DND does not affect cue scheduling, capture status, or the cue evaluation pipeline.
It is purely a notification delivery gate, applied at the moment of delivery.

### Activation

One tap from anywhere in the app.
DND is a persistent element in the navigation chrome — not buried in settings.
It must be reachable in the moment something is about to happen.

On activation, the user is offered quick duration options:
-  30 minutes
-  1 hour
-  2 hours
-  Until I turn it off (manual)

A free time picker is also available for "until [specific time]."
The default option shown first is the one used most recently, with 1 hour as the initial default.

### Deactivation

Three mechanisms, whichever comes first:
-  The selected duration expires — DND lifts automatically, no interaction required.
-  The user taps the DND indicator to turn it off manually.
-  The app is force-quit and relaunched — DND does not survive a full app termination.
   A session that ends is assumed to have ended for a reason.

### Visual Treatment

While DND is active, a persistent indicator is visible in the navigation chrome —
unambiguous, not alarming, dismissible with one tap.
The indicator shows the remaining duration if a duration was set: "DND – 47 min remaining."
Manual DND shows: "DND – tap to end."

The app badge continues to reflect surfaced captures during DND.
The user knows things are waiting; they chose when to look.

### Held Notifications on DND End

When DND lifts, no flood of notifications is delivered.
Captures that surfaced during DND are already in the surfaced section of the inbox.
The badge count already reflects them.
No additional notification is sent — the inbox is the retrieval surface, not the lock screen.

### Auto-Dismiss Interaction

A capture with `autoDismiss` set to true normally archives itself when the notification is tapped.
If that capture surfaces during DND, the notification is never delivered, so the tap never occurs.
The capture remains in `surfaced` state — it does not auto-dismiss.
This is correct behavior: auto-dismiss requires the user to have consciously encountered the capture.
DND does not constitute that encounter.

### Data Model

DND state is session-only and not persisted to the database.
It is held in application state: `dndActive` bool and `dndExpiresAt` DateTime?.
No schema change required.

---

## Data Export

The user can export all captures, locations, and settings as a single JSON file at any time from settings.
The export reflects the complete local database at the moment of export.
This is consistent with the no-data-leaves-device philosophy — the user controls their data and can take it with them.

---

## Settings

-  Default cue — the quick option shown first in the cue picker (default: tomorrow morning)
-  Default "tomorrow morning" time (default: 8:00am)
-  Auto-dismiss default — whether new captures default to auto-dismiss on or off
-  Audio retention preference — keep all, keep none, ask each time
-  Saved Locations — manage, edit, delete
-  Saved Wi-Fi Networks — manage named networks used as cue SSIDs
-  Saved NFC Tags — manage named tags; rename, delete, reassign
-  Storage usage summary
-  Export all data as JSON
-  Do Not Disturb default duration — the duration pre-selected when DND is activated (default: 1 hour)

---

## Open Items

-  Connected Tier backend — select infrastructure provider and define minimum viable backend: push notification routing
   (FCM for Android, APNs for iOS), end-to-end encrypted relay for presence events and shared captures, presence log
   storage. Scope must stay narrow — no general sync, no data platform. Define data retention and deletion policy before
   launch.
-  Connected Tier account model — define minimum account: email, device registration, connection graph. No profile, no
   social features. The account exists solely to identify the user to the routing layer.
-  Subscription pricing — validate $2.99/month and $24.99/year against projected backend infrastructure costs before
   committing. Confirm App Store and Google Play subscription mechanics.
-  Which geofencing Flutter package — `native_geofence` is the current candidate and the reliability spike (background,
   terminated, low-power, reboot scenarios) is underway. iOS limits the number of simultaneously monitored regions and may
   defer delivery in low-power mode. Commit only after spike results are in; do not build cue UI before then.
-  Transcription — on-device only, or optional cloud transcription for accuracy? On-device preferred given the
   no-data-leaves-device principle; evaluate quality on both platforms first.
-  Snooze options — confirm quick-option menu: 1 hour, tonight, tomorrow morning, plus free datetime picker.
   For captures whose cue fired on a location event (geofence, Wi-Fi, NFC), the snooze menu should offer location-based
   options first — "snooze until I arrive here again," "snooze until I get to [another saved location]" — before time options.
   Offering time-only snooze on a location-triggered capture is a UX mismatch; the capture surfaced because of context, and
   the natural deferral is back to that context.
-  Wi-Fi cue — confirm CNCopyCurrentNetworkInfo entitlement approval process for App Store submission. Evaluate whether
   SSID is accessible in background on both platforms without foreground requirement.
-  Bluetooth cue — evaluate flutter_blue or equivalent for device name enumeration and connection state monitoring in
   background. Confirm battery impact is acceptable.
-  NFC cue — evaluate flutter_nfc_kit or equivalent. Confirm tag write/read flow on both platforms. On iOS, confirm that
   the intentional tap model is sufficient and that background NFC scanning is not needed for the check-in use case.
-  NFC tag library UI — design the saved tag list analogous to saved Locations. Confirm naming and reuse flow before building.
-  "Next time I open the app" cue — flag checked on foreground resume; confirm sufficient before building around it.
-  Geocoding package for flutter_map — evaluate options for place name, city, and airport search.
-  Radius slider UX — confirm range and step behavior feels natural across neighborhood, city, and airport-approach scales.
-  TTS playback — text-to-speech reading of capture body on notification or detail view open. Low implementation cost,
   high value for elderly users, drivers, and anyone in a hands-free context. Global preference with per-capture override.
   Auto-enable heuristic when a Bluetooth audio device is connected warrants evaluation. Tier decided: free, all tiers
   (see Open Questions).
-  Voice cue input — natural language voice input for cue setting ("the cue for this is when I leave the
   house") as an alternative to tapping the cue picker UI. Requires constrained NLP parsing against the cue model.
   Keeps the capture flow in one modality (voice throughout). Should be an enhancement to the cue picker, not a
   replacement — some users will always prefer tapping. Tier decided: Connected-only (see Open Questions).
-  Natural language location resolution — when a voice or text cue references a place name the app does not
   recognize, prompt the user to identify it: "I don't have a location called 'house'. Do you want to identify it by
   Wi-Fi, map, or NFC?" Once resolved, the name is saved as a named Location. Falls out of voice cue input but
   applies to text input as well.
-  Multi-signal Location model — the Location entity currently carries lat/lng/radius. It should also carry optional
   wifiSsid and nfcTagIds fields, all pointing to the same named place. Any signal sufficient to identify arrival or
   departure. This makes location identification redundant and reliable in a way no single method is. Requires data model
   change and cue evaluation logic update. Design before building cue types that reference Locations.

---

## Open Questions

These questions were deferred for later decision and must be resolved before the relevant features are built.

**Paywall copy.**
The draft copy in the Monetization section is a placeholder.
Finalize the exact wording before v1 launch.
The principle is fixed: fire at a moment of demonstrated value, name what just happened, ask for money.

**Advanced voice cue input and TTS — implementation sequencing.**
Both are decided (voice cue input is Connected-only; TTS is free).
Neither is designed or built yet.
Voice cue input requires NLP work and should be spiked before committing to a timeline.
TTS is straightforward and can be added to v1 if time allows or v1.1 otherwise.

**Connected Tier backend provider.**
Select infrastructure provider and define minimum viable backend before Connected Tier design begins.
See Open Items for full detail.

**Caregiver page.**
Content is defined in the Connected Tier section.
Write and design before Connected Tier launch.

**Site revision.**
Resolved June 2026: the multi-page site is built (Home, Cues, Pricing, Caregivers, Privacy, FAQ) and the pricing table
reflects the three-tier model.
Remaining sync obligations: keep the FAQ seed list in this spec and the live faq.html aligned as both grow, and implement
the search behavior the FAQ section of this spec requires — the page currently has no search.

---

## Monetization

### Free Tier

Captrieve is free to download with full local functionality.
The free tier allows 20 lifetime retrieves — a retrieve being a capture that has surfaced via a cue and been acted on.
20 retrieves is enough to experience the product across genuinely different contexts and cue types.
Ten retrieves might all happen in the same week with the same cue type before the user has explored what the product
can do.
Twenty gives them time to set a geofence, a Wi-Fi cue, an NFC tag, a datetime — to discover the breadth of the
product before hitting the paywall.

After the 20th retrieve, a paywall message is shown before the user can continue.
Exact copy to be finalized, but the principle: the message fires at a moment of demonstrated value and names what just
happened before asking for money.
Draft:

> Twenty retrieves in. You've captured a thought in the moment and found it waiting for you in exactly the right place.
> That's Captrieve working as promised. Keep going — everything, forever, for $7.99.

A single "Unlock everything — $7.99" button follows.

### Why Retrieves, Not Captures

Capping on retrieves rather than captures means the paywall appears at a moment of demonstrated value — the user has just
experienced the product working as promised, not merely used it.
Asking for payment after a successful retrieve is honest: the user knows what they are paying for because they have already
received it twenty times.

### Reinstall Circumvention

Deleting and reinstalling the app resets local storage, including retrieve count.
There is no server-side enforcement of the free tier limit.
This is an accepted tradeoff given the no-data-leaves-device architecture — introducing a server solely to enforce a trial
limit would compromise the privacy positioning.
Users who go to that effort are not the audience.
Users who find genuine value will pay.

### Paid Features by Version

- **v1** — one-time $7.99 purchase unlocks unlimited retrieves. Fully local, no backend, no account.
- **v2** — Connected Tier subscription introduced. Calendar integration and advanced voice cue input are
  subscription-only. Users who purchased v1 are unaffected — their one-time purchase and fully local experience does
  not change.

### Solo Tier — $7.99 One-Time

$7.99 is priced at the non-brainer threshold — the zone where the mental transaction cost of deciding exceeds the cost
of buying.
The 20-retrieve free tier already handles the "will it actually work" objection before the paywall appears.
By the time the user hits the paywall they are not deciding whether to trust the product.
They are deciding whether $7.99 is worth what they have already experienced twenty times.
It is.

### Connected Tier — $2.99/month or $24.99/year

The annual price is approximately 30% off monthly — a standard and psychologically effective discount.
The caregiver use case converts better on annual pricing: a family setting this up for an elderly parent is thinking
about the year, not the month.

**The fallback mechanic.**
If a Connected subscriber later decides the connected features are not for them, they do not lose what they paid for.
They keep unlimited retrieves and all local features — the Solo experience — permanently, regardless of whether they
continue the subscription.
After any 12 consecutive months of Connected subscription, Solo is explicitly and automatically unlocked for life,
even if the subscription is later canceled.
This should be stated plainly on the pricing page as a reassurance, not buried in terms.
Suggested copy: "If you ever stop your subscription, everything you've unlocked stays yours. Connected features pause.
Everything else continues."
The 12-month threshold means a Connected subscriber who cancels after one year has paid $24.99 — well above the $7.99
Solo price — which satisfies the paid-more-than-one-time condition cleanly.

**Why subscription for connected features.**
The backend introduced for the Connected Tier has real ongoing costs: push notification infrastructure, encrypted relay,
presence log storage.
A one-time purchase does not recover those costs at scale.
A subscription does, honestly and proportionally.
This should be explained briefly on the pricing page — users who understand why the subscription exists are more likely
to trust it.

### Connected Tier (v2, Subscription)

The Connected Tier is a subscription layer for users who want to share presence, captures, and context with people they
trust.
It requires a backend and has genuine ongoing infrastructure costs.
Subscription is therefore the honest pricing model for it — approximately $2.99/month or $24.99/year, exact pricing to be
validated against infrastructure costs before launch.

The Connected Tier is purely additive.
Solo users on the one-time purchase are unaffected.
Nothing about their experience, their data, or their pricing changes when the Connected Tier is introduced.

What the Connected Tier includes is described fully in the Connected Tier section of this spec.
The short version: presence events, shared captures, capture forwarding, presence log with history, and push notification
routing to connected devices.

**Why subscription and not a one-time purchase for connected features.**
The backend introduced for the Connected Tier has ongoing costs: push notification infrastructure, encrypted relay for
presence events and shared captures, server compute for routing.
A one-time purchase does not recover those costs at scale.
A subscription does, honestly and proportionally.
The prior conclusion — no subscription because no feature clears the bar — was correct for a purely local app.
The Connected Tier changes the economics by introducing real ongoing costs that justify real ongoing revenue.

**Caregiver sharing and the Connected Tier.**
The original spec included caregiver sharing as a v2 feature included in the one-time purchase.
That decision is revised.
Caregiver sharing requires the same backend infrastructure as the Connected Tier generally.
It is now a use case within the Connected Tier, not a separate feature at a separate price point.
A caregiver who subscribes to support someone they care for gets the full Connected Tier — presence events, shared captures,
presence log — not a stripped-down version.
The full app at a fair subscription price is a better offer and a cleaner product story than a feature-limited caregiver mode.
The viral marketing dynamic is preserved: giving the caregiver full functionality at a reasonable subscription price is
more compelling than a free read-only companion app.


---

## Calendar Integration (v2, Paid Tier)

Calendar integration allows a capture's cue to be driven by a calendar appointment rather than a manually set date and time.
The user specifies matching criteria — the attendee email address of a specific person, or a keyword in the appointment title —
and the integration resolves that to a concrete datetime cue by finding the next matching appointment.
When the calendar changes, the cue is updated to match.

### What It Is and Is Not

The calendar cue is not a new cue type in the data model.
It is a resolver that sits above the datetime cue — it finds the next matching appointment and writes a datetime cue,
then rewrites that cue if the appointment moves or is canceled.
The underlying cue mechanism is unchanged.

### Matching Criteria

The user specifies one or both of:

-  An attendee email address — "the next appointment that includes this person"
-  A title keyword — "the next appointment whose title contains this word or phrase"

Both criteria are ANDed if both are provided.
The integration scans forward from the current moment and uses the start time of the first matching appointment as the cue
datetime.

### Calendar Scanning

On setup of a calendar cue, the integration scans immediately to find the next matching appointment and sets the cue
datetime.
It then polls the calendar periodically to detect changes.

**On-device polling (v1 path, no backend).**
Polling runs via iOS/Android background task scheduling — default three times per day (morning, midday, evening),
user-configurable.
This is best-effort. iOS background task scheduling is subject to OS deferral and throttling and cannot be guaranteed to run
at exact times.
Calendar cue updates on this path are approximate, not to-the-minute reliable.

**Backend polling (Connected Tier path).**
When the user is on the Connected Tier subscription, calendar polling runs on the backend on a reliable server schedule
rather than depending on iOS background task delivery.
The backend polls the calendar API directly using stored OAuth credentials, detects changes, and pushes cue updates to
the device immediately.
This makes calendar cues genuinely dependable rather than best-effort.
The device does not need to be awake, foregrounded, or lucky with OS background scheduling.
This is a meaningful reliability improvement and a secondary benefit of the Connected Tier backend infrastructure.
No calendar data is retained on the server — the backend reads, computes the new cue datetime, pushes the update, and
discards the response.

### When an Appointment Moves

If a tracked appointment's time changes, the cue datetime is updated silently.
The user is not notified of routine reschedules.

### When an Appointment Disappears

If a tracked appointment is canceled or removed, the integration immediately searches for the next appointment matching the same
criteria.
Two outcomes:

-  **Replacement found** — cue datetime is updated to the new appointment's start time. User is notified: "Your
   'dermatologist' appointment moved to March 12 at 2:00 PM — cue updated."
-  **No replacement found** — the capture's cue is suspended. User is notified: "No upcoming appointment found matching
   'dermatologist' — this capture has no active cue." The user decides whether to set a manual cue, update the criteria,
   or dismiss the capture.

### Supported Calendars

Phased rollout:

-  **v2.0** — Google Calendar (OAuth2)
-  **v2.1** — Apple Calendar (EventKit, iOS only)
-  **v2.2** — Microsoft Outlook / Exchange (OAuth2, Microsoft Graph API)

Each integration requires the user to grant calendar read access via the appropriate OAuth or system permission flow.
Credentials are stored in the device keychain.
No calendar data is transmitted to any server — all resolution logic runs on-device against the calendar API.
This is consistent with the no-data-leaves-device principle.

### Subscription Cancellation

When a paid subscription is canceled, the app immediately scans for all captures with active calendar-connected cues.
The user is warned: "You have N captures connected to your calendar.
When your subscription ends they will be converted to fixed date and time cues and will no longer update if appointments
move or are canceled." On subscription end, all calendar-connected cues are converted to plain datetime cues set to the
last known appointment time.
The calendar resolver is disconnected and no further polling occurs.
The captures remain fully functional as datetime-triggered captures.

---

## Connected Tier (v2, Subscription)

The Connected Tier allows Captrieve users to share presence, captures, and context with people they trust and who also have
Captrieve.
It is not a social network, not a group chat, and not a collaboration tool.
It is a small, intentional network of people who have chosen to be connected — family, caregivers, partners, close colleagues.

The moment someone asks for group chat, threaded replies, read receipts, or typing indicators, they have left this product.
Those belong to iMessage and WhatsApp and Captrieve cannot and should not compete there.
The Connected Tier does one thing those tools do not: it makes captures, presence, and context flow between people in ways
that are triggered by the real world, not by someone typing.

### Design Principles for Connected Features

Connected features are opt-in at every layer.
A user must subscribe, must explicitly add a connection, and must explicitly choose what to share.
Nothing flows to another person without the user's deliberate action.

The connected layer is invisible in normal solo use.
A user who has connected a caregiver sees no added complexity in their capture flow.
The sharing layer does not intrude.

Consent and revocation are always in the sharing user's control.
Any connection can be severed at any time, immediately, with no residual data retained on the server.

### What Connected Users Can Do

**Presence events.**
A user can designate any NFC tag tap as a presence event — a signal that is routed to one or more connected people.
The canonical case: tap the NFC tag at the front door when leaving the house; tap again on return.
Connected people receive a push notification: "Left home at 10:47 AM." "Returned home at 1:23 PM."
This is especially meaningful for elderly users whose family or caregivers want to know they are moving through their day
normally, without the surveillance feel of continuous GPS tracking.
The tap is consensual, intentional, and motivated by the user's own check-in habit — the presence signal is a side effect
of a gesture that already serves them.

**Presence log.**
Connected people see a timestamped log of presence events, not just real-time notifications.
Today, yesterday, last week.
The log makes patterns visible — "usually leaves by 10, it's noon" — without requiring automated alerts.
Anomaly detection is human, not algorithmic.
Automated anomaly alerts are a future option, not in scope for v2.

**Shared captures.**
A user can send a capture to a connected person.
The recipient receives it as a capture in their own inbox, with whatever cue the sender set — or they can set their own
cue on receipt.
This is genuinely novel.
No messaging app does this.
"I captured something I want you to see when you get to the store" is not a text message — it is a context-cued
delivery that finds the recipient when it is actionable.

**Capture forwarding.**
"Remember to pick up the dry cleaning on your way home" — captured by one person, forwarded to another, cue set to fire
on the recipient's departure from their office.
The sender sets the cue on the recipient's behalf, or leaves it for the recipient to set.

**Push notification routing.**
All connected features — presence events, shared captures — are delivered as push notifications to the recipient's device
via the backend.
No SMS. No carrier dependency. No per-message cost to the user.
The backend routes the notification; the recipient's device delivers it locally.

### What Connected Messaging Is Not

There is no chat thread.
There is no reply within Captrieve.
A shared capture is a one-way delivery.
If the recipient wants to respond, they use whatever communication channel they already use with that person.
Captrieve does not try to own the conversation — it delivers the thought at the right moment and gets out of the way.

**Shared captures are text, not audio.**
Captures shared via the Connected Tier are delivered as transcribed text, not as audio files.
The sender records by voice as normal; the capture transcribes on-device as it always does.
What is shared is the transcription.
This is a deliberate decision, not a temporary limitation.
Audio files are large relative to the small-payload architecture the Connected Tier backend is designed around.
Transcription is already a first-class part of the capture flow and produces a faithful representation of the thought.
The warmth of speaking is preserved in the sender's capture experience; what arrives for the recipient is the text — which
is sufficient and keeps the backend scope clean.
Revisit if specific use cases emerge where audio is genuinely necessary and worth the infrastructure cost.

**The infrastructure discipline principle.**
The existence of the backend is not an invitation to build features that belong to other products.
Each Connected Tier feature must justify itself as an extension of capture and retrieval — not merely as something the
infrastructure could support.

A shared grocery list was considered and rejected on exactly this basis.
A persistent, multi-author, collaboratively maintained list is a different product with dominant competitors (AnyList,
OurGroceries, Apple and Google shared lists).
Building it well requires item deduplication, category organization, checked-off state, list clearing after a shop — a
whole product surface with nothing to do with capture and retrieval.
The correct Captrieve answer to "remind my spouse we're low on ketchup" is a transcribed voice capture forwarded to her
with a grocery store geofence cue.
That is Captrieve doing what Captrieve does — a thought that finds her at the right moment.
It is not a grocery list.
This distinction should be applied to every proposed Connected Tier feature before any design work begins.

### Caregiver Use Case

The caregiver relationship is the most fully developed use case within the Connected Tier and the primary driver of
subscription revenue.

**Two roles, one product.**
The caregiver is not a passive viewer of someone else's app.
They are a full Captrieve user in their own right, using the app for their own captures alongside their caregiving role.
This matters for the product story: the caregiver page and marketing should not present a stripped-down companion
experience.
Both people have the full app.
Both people benefit from it independently.
The caregiving layer is what connects them.

**Setting up captures for the care recipient.**
The caregiver can create captures on the care recipient's behalf, shared to the recipient's device with cues the
caregiver sets.
The care recipient does not need to capture anything, navigate any UI, or understand the cue model.
Their trained behavior may be as simple as a single physical gesture — tapping a named NFC tag — which surfaces whatever
the caregiver has prepared for that context.
Example: a nightstand NFC tag, tapped at bedtime, surfaces: medications taken, door locked, phone charging.
The caregiver set this up.
Granny taps the tag.

The caregiver page on the website (to be written) will illustrate this role in detail with concrete examples, for both
marketing purposes and user reference.

**The caregiver's own captures in the care recipient's space.**
NFC tags placed in the care recipient's home serve both people independently.
The same physical tag, read by different phones, surfaces different captures for each person — the tag is just an
identifier; what surfaces depends entirely on whose phone is reading it.
The caregiver walks into the care recipient's home and taps the kitchen tag — their own Captrieve surfaces whatever they
set for that context: "ask about the medication side effect she mentioned Tuesday," "she wanted to show you the photos from
the reunion," "check whether she has enough of the blood pressure medication."
They tap the front door on the way out — "call the visiting nurse service before end of day."
The caregiver does not have to remember what to ask, what to check, what to bring up.
Their own captures surface at exactly the moment they are standing in the relevant context.
This is the caregiver using Captrieve for themselves, inside a space they share with the person they care for.

**Presence events and the presence log.**
A care recipient connects a caregiver — a family member, a professional, a friend.
The caregiver sees:
-  The care recipient's pending and surfaced captures and cue status, in real time.
-  The presence event log — NFC door taps, arrival and departure times, the rhythm of a normal day.
-  Shared captures the care recipient has chosen to send them.

The care recipient sees no added complexity.
Their capture flow is unchanged.
The sharing layer is invisible to them in normal use.

The caregiver-facing view is richer and may eventually be web-accessible, since caregivers often want to check on a laptop
rather than a phone.
Web access to the caregiver view is a near-term backend priority once the Connected Tier is established.

This feature directly addresses the autonomy concerns of people navigating cognitive decline or memory impairment.
GPS-based tracking feels surveillant because it is passive — it watches without the person's ongoing participation.
The NFC tap model is different: the person is an active participant in their own monitoring.
The tap serves them (check-in habit, captures surfaced) and incidentally informs their caregiver.
That distinction matters to the population and to the families supporting them.

### Privacy Architecture

The Connected Tier is the intentional exception to the no-data-leaves-device principle.
The exception is narrow and explicit: data leaves the device only when the user has chosen to share it with a specific named
person.

All data in transit and at rest on the server is end-to-end encrypted.
The server holds ciphertext only and cannot read capture content or presence events.
The user controls who holds the decryption key.
Revoking a connection immediately invalidates the key for that connection.

The backend is scoped narrowly: push notification routing, encrypted relay for presence events and shared captures, presence
log storage for connected pairs.
It is not a general sync service, not a cloud backup, not a data platform.
That scope is a product decision, not a resource constraint, and should be enforced as the Connected Tier evolves.

### Subscription Cancellation

When a Connected Tier subscription is canceled:
-  All active connections are immediately suspended.
-  Connected people are notified that the sharing relationship has ended.
-  No further presence events or shared captures are routed.
-  The user's local captures and cues are fully unaffected — the Solo experience continues normally.
-  If the user has completed 12 consecutive months of Connected subscription, Solo is permanently unlocked at no further
   cost. This is automatic and requires no action from the user.
-  If the user has not yet reached 12 months, they revert to the free tier (20-retrieve cap) unless they have separately
   purchased Solo.
-  Presence log history held on the server is deleted within 30 days of cancellation.



---

## Deferred

-  Web companion interface — deferred for v1 and v2 solo tier, but a caregiver-facing web view is a near-term priority
   once the Connected Tier backend is established. The backend already exists at that point; the web view is an interface
   question, not an architecture question.
-  Video capture
-  General sharing or collaboration — not a priority
-  Cloud sync or backup
-  iCloud / Google Drive export
-  Recurring captures (e.g. "remind me every Monday morning")
-  Watch app
-  Proximity-to-person cue via Bluetooth LE — detecting that a connected Captrieve user is physically nearby and
   triggering a capture on that event. Technically feasible with mutual consent and both parties running the app and
   subscribed to the Connected Tier. The adoption dependency is solved once a subscriber network exists. Deferred to v3
   or later; the connected messaging infrastructure in v2 is the prerequisite.
-  Large-radius geofence as an en-route cue — using a generous geofence radius (e.g. 5–15 km around home) to fire a
   capture while the user is still in transit rather than on arrival. This is not a new cue type; it is a configuration
   pattern that should be documented explicitly with examples. Canonical example: "call home to let my spouse know I'll be
   there in 15 minutes" — set a geofence_arrival on home with a radius of several kilometers so it fires while still driving.
   This pattern should appear in the FAQ and onboarding as a named use case, not left for users to discover.
-  Automated anomaly detection for caregiver presence log — "usually leaves by 10, it's noon, notify caregiver." The
   presence log in v2 makes patterns humanly visible; algorithmic alerting is a natural next step but not in scope for v2.
-  AirTag integration — Apple's Find My network is intentionally closed and exposes no public API.
   There is no way for Captrieve to know when an AirTag-tagged item is nearby or at a location.
   The underlying use cases — bike, car, bag, pet — are served by Bluetooth device cues (bike computer,
   car audio) and NFC tags placed on the same objects.
   Revisit only if Apple opens a relevant API. — finding patterns, groupings, and relationships across a large
   capture history. Only meaningful at scale: the individual user needs hundreds of captures before the signal is useful,
   and the business needs a large enough cohort of creative users to justify AI inference costs and engineering investment.
   The right order of operations is core product first, user base second, this feature third if the cohort warrants it.
   Also carries a privacy tension: on-device inference is feasible but limited; server-side inference requires capture
   content to leave the device, which conflicts with the core privacy positioning and would require explicit opt-in.
   Not to be built speculatively.
