# Captrieve – Product Specification

## Purpose and Philosophy

**Captrieve: Capture in the moment. Retrieve in context.**

This is the product in seven words.
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
-  **Captures stay on the device.** Captures are stored locally, not in the cloud – this is a feature, not a limitation, and
   privacy is a selling point. Data leaves the device only by the user's explicit choice: captures shared on the Connected
   Tier travel end-to-end encrypted, and optional anonymous diagnostics may be sent to fix crashes, carrying no identifier
   and no capture content. Nothing about a capture's contents leaves the device without the user's action.
-  **The user is in control.** Triggers are explicit. Conditions are evaluable. Nothing fires unexpectedly and nothing fails to
   fire silently.

---

## Marketing

### Two-Sentence Pitch

You just had a thought you cannot afford to lose and cannot act on right now.
Captrieve captures it before it's gone and delivers it back to you exactly when and where you need it –
future you will thank you.

### Two-Paragraph Pitch

Every person loses thoughts that mattered.
The idea that arrived in the shower, the question you meant to ask the doctor, the thing you needed to say before the
negotiation, the errand that only made sense on the way home – gone, because the moment of remembering and the moment of
usefulness never coincided.
Notes apps and voice memos solve the capture half and abandon the retrieval half entirely.
Reminder apps solve a sliver of retrieval – one trigger per task, configured by hand, for things you already knew were tasks –
and abandon the capture half: there is no place in them for a melody, a phrasing, a half-formed idea.

Captrieve solves both.
Capture is three steps: tap, speak, done – fast enough that the thought doesn't escape.
Then you tell Captrieve when and where you want it back – a time, a date, a place, or a combination.
When that moment arrives, it finds you.
Not in a pile of notes you have to dig through.
Not dependent on you remembering to look.
It surfaces when it is useful, because you told it to.
Your thoughts stay private – nothing leaves your phone.
No subscriptions, no cloud, no algorithm deciding when you are ready.
Future you will thank you for taking the moment to capture it.

### Investor Pitch

Every person loses thoughts that mattered.
The idea that arrived in the shower, the question to ask the doctor, the thing to say before the negotiation, the errand
that only made sense on the way home.
Notes apps solve capture and abandon retrieval.
Reminder apps solve a sliver of retrieval – one manually configured trigger per item, typed as a task, for things the
user already knew were tasks – and abandon capture entirely.
No existing product is built around both.
Captrieve is.

**The objection worth answering directly.**
Apple Reminders has had location triggers since 2011.
IOS Shortcuts can fire on an NFC tag, a Wi-Fi network, a Bluetooth device, a charger, a Focus mode, and a location.
A technically sophisticated reviewer will know this.
The honest answer is that these facts strengthen, not weaken, the case.

Apple Reminders is a task list with a trigger bolted on.
The workflow it requires – stop what you are doing, open the app, type the task as text, configure its one trigger by
hand – is the enemy Captrieve is designed against.
It has no capture flow, no inbox model, no composition, no AND conditions, no delays, no multi-cue OR.
Its location reliability is a long-running user complaint its own forums describe as a coin flip.
It does not run on Android.

IOS Shortcuts is a programming tool.
Every behavior requires a hand-built automation, maintained by the user indefinitely.
There is no capture model – no thought with a lifecycle, no inbox, no cue attached to a specific thing that needs
to be remembered.
The overlap with Captrieve's trigger taxonomy is real and is acknowledged in the spec.
The conclusion is also the competitive argument: Shortcuts proves the phone hardware can do this.
Captrieve is the product shape that makes it accessible to everyone who is not going to program their phone.
That population is large.

The Shortcuts objection is also thirteen years old in a different form.
Third-party geofence reminder apps have existed since 2012, differentiating on exactly the composition features
Reminders lacks.
Apple has not absorbed them.
The features are not technically difficult for Apple to build; Apple has simply chosen not to.
That pattern is a strong signal that Captrieve's composition and capture model are durable.

**The real moat, in order of strength.**
The trigger taxonomy alone is not the moat.
The moat is the combination of things no incumbent offers together:

1. The capture-first model with a thought inbox.
   A thought has a lifecycle in Captrieve – captured, pending, surfaced, acted on or snoozed.
   Nothing in any incumbent represents a thought this way.
   This is the structural difference Reminders cannot absorb without becoming a different product.

2. The Connected and caregiver layer.
   Presence events, shared captures with recipient-side cues, the presence log.
   Nothing in any incumbent resembles this.
   It requires a backend, which is why no solo-first app has built it.
   The caregiver use case within it – a population with high willingness to pay and strong word-of-mouth in exactly
   the communities where Captrieve's value is most legible – is the primary subscription driver and the most
   defensible long-term revenue stream.

3. NFC-as-product for normal humans.
   The deliberate tap ritual, packaged, named, and habit-framed, aimed at ADHD and older adult populations.
   Shortcuts can technically do NFC; Captrieve makes it approachable and meaningful.

4. Cue composition – AND conditions, delays, multi-cue OR – as a first-class design principle, not an afterthought.

5. Cross-platform, with Android freshly vacated by Google.
   Google removed location-based reminders from Android entirely in late 2025.
   On the platform where Apple's free incumbent does not exist, the category is now simply empty.

**Privacy as a targeted differentiator.**
The no-data-leaves-the-device principle is strongest against two specific competitors: the third-party geofence app
category (largely ad- and tracking-funded, with data practices that contradict what their users want) and Android
(where the vacuum left by Google makes a privacy-first option especially resonant).
It is not a strong differentiator against Apple Reminders, which is also locally stored by default.
The privacy claim is true and should be made, but it should be aimed at the right targets.

**Monetization.**
Free: fully on-device, no backend, no account, the whole capture-and-retrieve loop with no retrieve cap, the everyday cues
(date and time, app-open, an NFC tag tap up to five tags, and a Wi-Fi network up to two), single-condition only.
Solo: a one-time purchase of about $10 unlocks the power tier, which is the rest of the cues (geofence, charger, Focus, and
Bluetooth), the composition layer, and removal of the tag and network caps.
The upgrade prompt fires at a moment of demonstrated value, when the user reaches for a paid cue, a sixth tag or third network,
or any composition.

Connected: $2.99/month or $24.99/year, with Family Sharing on.
The backend introduced for the Connected Tier has real ongoing costs, so a subscription is the honest pricing model for it.
Connected includes the Solo power tier while active, and after 12 consecutive months the Solo power tier is permanently
unlocked.
A voluntary Supporter purchase exists as a topper for goodwill, and unlocks only a thank-you, never functionality.

### What to Tell Family

"You know how you think of something you need to remember, and then it's just gone by the time it matters?
I'm building an app where you speak the thought before it escapes, tell it when or where you want to be reminded – like when
you walk into the doctor's office, or when you land somewhere, or tomorrow morning – and it just shows up then.
No digging through notes. It finds you.
I want you to be the first people to try it."

### Privacy Promise (Consumer-Facing)

Four points, stated plainly, as they appear on the marketing page:

-  **No account required.** There is nothing to sign up for and nothing to log into – unless you choose the Connected Tier,
   which requires an account to identify you to the people you share with.
-  **No cloud.** Captures live in local storage on the device. Full stop – unless you choose to share them with a connected
   person, in which case only what you explicitly share leaves the device, end-to-end encrypted.
-  **No subscription.** One purchase. No recurring charges. No ongoing relationship to manage – unless you choose the
   Connected Tier, which has ongoing backend costs and is priced accordingly.
-  **No algorithm.** Nothing decides when the user is ready. They set the cue. The app honors it. This is unconditional
   and does not change at any tier.
-  **Anonymous diagnostics, off in one tap.** To find and fix crashes, the app may send anonymous diagnostic reports – the
   kind of error and where it happened, the app and OS version, the device model. They carry no account, no identifier, and
   nothing you captured. This is on by default and turns off with a single toggle in Settings.

The qualifications above are honest, not apologetic.
Every exception that moves your captures or personal data is your own choice, made explicitly, for a feature that requires it.
The one thing on by default – anonymous diagnostics – carries nothing personal and nothing you captured, and turns off in one
tap.
Nothing you capture leaves your phone without your action.

### Customer Quotes

These quotes are for the website and App Store page.
Each illustrates a specific human moment rather than describing a feature.

> "I was in the shower and a chord progression came to me – I just started humming it, different parts, the rhythm, the feel.
> I dried off just enough to handle my phone. Then tap, record my mouth doing its best to sound like a rock band, and said,
> "Give this to me when I get to the studio." Then I finished my shower.
> When I got to the studio, my phone vibrated, and presented the riff.
> I built the whole bridge from that two-minute voice memo, without having to worry about what part I might have forgotten."
> – Jordan K., singer-songwriter

> "I used to walk into meetings and remember, right as they started, that there was something I wanted to bring up, but damn
> me if I knew what it was.
> Now I capture it the second I think of it and it's there when I walk in the door.
> My manager has noticed. I've noticed.
> I assigned my phone's action button to Captrieve."
> – Priya S., project coordinator

> "Ten minutes before we sat down to negotiate the licensing terms, my phone surfaced a note I'd captured six weeks earlier –
> something their CEO had said offhand at a conference about their biggest pressure point.
> I walked in knowing exactly where to push and how to frame what we had to offer."
> – R. Whitfield, VP of Sales

> "My daughter used to worry that I'd forget my questions at the doctor and just say everything was fine.
> Now I capture them when I think of them, and they're there when I walk in.
> Last visit I actually remembered to ask about the medication interaction.
> Turns out it mattered."
> – Carolyn M., retired teacher

### Example Use Cases

The range of what Captrieve handles is intentionally broad.
A few examples across very different contexts – in every case, future you is glad present you captured it before it was gone:

-  The question you want to ask at your doctor's appointment next month, captured the moment you thought of it – surfaced when
   you arrive at the clinic.
-  The thing you want to pick up the next time you are in New York – surfaced when you land.
-  The idea that won't let you sleep – captured in the dark in thirty seconds, surfaced tomorrow morning at 8 am so you can
   evaluate it rested.
-  The medication side effect you noticed on Wednesday – surfaced at your Thursday appointment.
-  The weekly review you keep meaning to do – surfaced every Sunday at 7:30 am.
-  The errand you need to run on the way home – surfaced when you leave the office, only if it's after 5 pm.
-  The thing to tell the dog walker – surfaced when you arrive home and they are likely there.
-  The follow-up you meant to send – surfaced Tuesday at 3 pm when you said you would be at your desk.
-  The book title someone mentioned at dinner – surfaced the next time you open the app, when you are ready to look it up.
-  The observation about your parent's health – surfaced when you arrive at their next care appointment.
-  The ingredient you are out of – surfaced the next time you are at the grocery store.
-  The song you want to learn – surfaced when you get to the recording studio.
-  The errand that only makes sense on the way – surfaced 20 minutes after you leave work, when you are close enough to the
   store to stop.
-  The thing to remember when you sit down at the piano – surfaced when you tap the NFC tag.
-  The question for your colleague – surfaced when you join the office Wi-Fi network.
-  The grocery item you thought of at midnight – surfaced when you join the supermarket's Wi-Fi or enter its geofence.
-  The reminder to call ahead – surfaced when you are 15 minutes from home, set via a large-radius geofence on your home
   location.
-  The thing you need when you get in the car – surfaced when your phone connects to your car audio system.
-  The evening medication – surfaced every night at 8 pm, unless you already tapped the pillbox tag, in which case it stays
   silent for the night.
-  Feeding the dog – surfaced every day at 6 pm, unless the dog-food tag was tapped first, so it only nags on the days you
   actually forgot.
-  The plants – surfaced every Saturday morning, unless you watered them early and tapped the windowsill tag.
-  The course of antibiotics – surfaced twice a day for ten days starting tomorrow, then it ends on its own.
-  The monthly bill – surfaced on the first of every month, and on the second Wednesday if there is one you pay then too.


---

## Go-To-Market

No paid acquisition budget.
The launch strategy is community-first: direct engagement in the forums where the target users already discuss the
problems Captrieve solves.

**Primary channels:**

-  ADHD communities – r/ADHD, r/productivity, r/adhdwomen, and equivalents.
   These communities discuss working-memory failures, unreliable reminder systems, and the gap between knowing you
   should remember something and actually remembering it constantly and specifically.
   Captrieve is a direct answer to problems that come up in these threads every day.
   Showing up as a founder with a real solution to a problem the community knows well is more credible in these spaces
   than any ad.

-  Elder-care and caregiver communities – r/AgingParents, r/dementia, r/CaregiverSupport, and equivalents.
   The caregiver use case – setting up context-triggered reminders for someone who cannot reliably set them for
   themselves, while also receiving gentle presence signals without GPS surveillance – is genuinely novel and directly
   addresses things these communities talk about.
   Word-of-mouth within caregiver networks is high-trust and fast.

**What this means for the website.**
A visitor arriving from a community thread is a warm visitor: they already believe the problem is real because they live
with it, and they have a specific reason to trust the recommendation.
The site must work for that person.
They should not have to read about creative professionals before finding out why Captrieve is for them.

Each primary community gets a dedicated landing page.
Pages built: caregivers.html, adhd.html, inspirations.html (creative people / inspiration capture), compare.html
(side-by-side comparison of doing common tasks with and without Captrieve).
Each speaks entirely in the language of its audience's experience without requiring the visitor to read about other
segments first.

**The sequence: earn proof at retail, then borrow reach from organizations.**
The motion is staged, and the order matters.
First, build credibility-light at the retail level, person to person.
Free educational talks at independent-living and active-55+ communities, taught as a class on memory and habit by a retired
author and coach, with hands-on help installing the free app and setting up a few NFC tags on the spot.
In parallel, low-key presence in the ADHD forums and informal ADHD media, showing up as the maker who built the tool for his
own brain.
This stage is teaching, not selling, which is why it fits a founder who does not enjoy and will not sustain a hard-sell motion.
It also produces the three things the next stage needs: a relationship with the community's gatekeepers, real stories from real
users, and first-hand UX learning from watching older and ADHD users onboard.

Second, take that proof to organizations that already hold the audience, and offer them a genuinely useful free app as
editorial content rather than an ad.
A newsletter editor at an aging-services or ADHD organization is pitched constantly, and "please feature my app" is ignorable.
"A retired author built a free, privacy-respecting memory tool, taught it at local senior communities, and here is what a
resident and her daughter said about it" is a story they would want anyway.
The retail stage is what converts the org pitch from a request into a gift.

**Organizations to approach, once there is proof to show.**
Aging and caregiving: AARP and its state chapters, Area Agencies on Aging, the National Council on Aging, local senior-center
networks, and caregiver-focused groups such as the Alzheimer's Association and the Family Caregiver Alliance.
The caregiver-focused groups are the sweet spot, because their readers are the adult children, who are exactly who converts to
Connected.
ADHD: CHADD, ADDA (adult ADHD specifically, which matches the product better than child-focused channels), and the large
informal channels of ADHD newsletters, Substacks, podcasts, and subreddits, which are often more reachable and more
influential than the formal nonprofits.

**The parent-to-adult-child bridge.**
The funnel is deliberate and maps onto the tier model rather than fighting it.
The parent installs the free app, often with in-person setup, and lives happily in the free tier.
The paid conversion is the adult child subscribing to Connected to stay in the loop, which is exactly what Connected is for, and
Family Sharing means one subscription covers the pair.
To keep this from leaking, make the bridge explicit: a one-page handout the resident can hand to their child, and an in-app
"invite a family member to stay connected" flow that makes the Connected value obvious to the child.

**The ethical guardrail, stated as a hard constraint.**
The two target communities are exceptionally alert to exploitation, and rightly so.
The instant any of this reads as a funnel that softens up vulnerable people so their families can be sold a subscription, the
outreach fails and the standing in a small, talkative world is lost.
The protection is that the product is genuinely free and genuinely private, and the story stays honest to that.
Connected must be discoverable by anyone who wants it, but it is never the point of the outreach, never mentioned to a room of
residents, and never the thing an organization is implicitly helping sell.
The free app is the offer, and Connected is found, not pushed.
This is both the ethics and the strategy, and they point the same direction.

**The shape of the motion.**
This is slow compounding, not a launch.
Talks produce stories, stories make org pitches land, an org feature produces a burst of free installs, and a small fraction of
those families find Connected on their own.
Each loop is months, and none of it works until the app is live and solid in both stores.
It rewards patience, genuineness, and a real thing built well, and it does not require becoming a salesperson.

**What this does not change.**
The product does not change.
The pricing, the tiers, and the feature set are set by the Monetization section, not by the go-to-market strategy.
Community-first acquisition is a constraint on messaging and site structure, not on the product itself.

---

## Competitive Landscape

Added June 2026 after a late discovery: Apple Reminders has had location triggers since IOS 5 (2011), and earlier drafts of
this spec and the site copy claimed or implied otherwise.
This section exists so that every marketing claim is written against what incumbents actually do, and so the "doesn't my
phone already do this?" objection is answered before a reviewer asks it.
Standing rule: no claim of the form "your phone can't do X" ships without being checked against this section, and this
section gets re-verified each release.

### What incumbents actually do

**Apple Reminders (free, preinstalled, IOS/macOS/watchOS)**

-  Geofence triggers, arriving or leaving, with adjustable radius (~100m minimum to ~150 miles), since IOS 5.
-  Siri creation by voice, including "when I get home / leave work" via contact-card addresses.
-  Car triggers: getting in / out of the car, via CarPlay or car Bluetooth connect/disconnect.
-  "When Messaging" a chosen person.
-  Shared lists with assignment, subtasks, attachments, early reminders, iCloud sync.

What it does not do: capture-first flow (no voice-note-becomes-item gesture), one trigger per item, no AND conditions, no
time offsets ("20 minutes after leaving"), no multi-cue OR, no NFC, no Wi-Fi network triggers, no arbitrary Bluetooth
devices, no charger triggers, no Focus mode triggers, no caregiver layer, no presence events, no Android.
Reliability of its geofences is a long-running user complaint – community threads describe roughly coin-flip delivery.

**IOS Shortcuts personal automations (free, preinstalled)**

-  Triggers include: NFC tag, Wi-Fi network join, Bluetooth device connect, arrive/leave location, time of day, app opened,
   charger, Focus mode, CarPlay.
   This overlaps the entire Captrieve trigger taxonomy.

What it does not do: it is a programming tool, not a product.
No capture model, no inbox, no per-capture cue attachment – the user builds and maintains one automation per behavior,
indefinitely.
Accessing even a single trigger type – say, NFC – requires creating a Shortcut, naming it, writing its actions, attaching
it to the tag during a separate registration flow, and knowing where to find it if it needs to be changed later.
That is a meaningful engineering burden that the overwhelming majority of phone users will never undertake.
The overlap is real for power users and irrelevant for the target segments (creative flow, ADHD, older adults, caregivers).
The honest framing: Shortcuts proves the OS hardware can do this – Captrieve is the product that makes it available to
everyone who is not going to program their phone.
This argument is strengthened by Shortcuts' thirteen-year existence: the DIY option has always been there, and most people
have never used it.

**Google / Android**

-  Google migrated Keep reminders into Google Tasks (rollout late 2025) and removed location-based reminders entirely –
   they can no longer be created or received.
-  Displaced users are being pointed at Samsung Reminder (Samsung devices only), TickTick, and Tasks.org.

This is a tailwind: the platform without Apple's free incumbent just lost its first-party location reminders.

**Third-party geofence reminder apps (IOS App Store)**

-  A persistent small-app category since Checkmark (2012), whose differentiator against Reminders was exactly time offsets
   before/after location events.
   Current examples: GeoNudge, GeoReminder, Remind There – typically $1–$20 lifetime or ~$10/year, geofence-only,
   single-trigger, ad- or subscription-funded, several with data-collection practices that contradict Captrieve's privacy
   posture.

None observed offers the combination of NFC + Wi-Fi + BLE + charger + Focus mode + composition + capture-first +
caregiver layer + local-only privacy.

### Where the moat actually is

The trigger, taken alone, is commoditized.
The defensible territory, in order of strength:

1. The capture-first model with a thought inbox – a thought has a lifecycle.
   Nothing in any incumbent represents a thought this way.
   Reminders structurally cannot absorb this without becoming a different product.
2. The Connected/caregiver layer – presence events, shared captures with recipient-side cues, the log-not-a-map model.
   Nothing in any incumbent resembles it.
3. NFC-as-product for normal humans – the deliberate tap ritual, packaged, named, and habit-framed, vs Shortcuts DIY.
4. Cue composition – AND conditions, delays, multi-cue OR – as a design principle, not an afterthought.
5. Cross-platform, with Android freshly vacated by Google.
6. Local-only privacy as architecture, targeted against the third-party field (largely tracking-funded) and Android
   (where the vacuum creates demand for a privacy-first option).
   This argument is weak against Apple Reminders and should not be made in that comparison.

### The Shortcuts objection – extended treatment

The claim that IOS Shortcuts makes Captrieve redundant is the most technically sophisticated objection and deserves a
thorough answer that goes beyond "Shortcuts is hard."

First, the objection is thirteen years old in a different form.
Third-party geofence reminder apps have existed since 2012.
The power user has always had a DIY path.
That path has not prevented a persistent paid market for apps with better product shape.

Second, the burden per trigger type is not trivial.
To replicate a single Captrieve capture with an NFC cue in Shortcuts: open Shortcuts, create a new automation, choose
Personal Automation, choose NFC, hold the phone against the tag to register it, name the automation, add an action to
show a notification, type the thought text into the notification body, save.
That is eight to ten steps, requires knowing what an automation is and where to find it, and produces a notification with
no inbox, no lifecycle, no snooze, no dismiss, no way to browse all pending automations together.
To add an AND condition ("but only after 5 pm"), the user must add a conditional action block and wire it correctly.
To replicate OR logic across two triggers, the user must build two automations that both show the same notification.
To replicate a delay, the user must add a wait action and understand that Shortcuts automations can be killed by the OS
while waiting.
None of this is product thinking.
It is engineering work that users are not doing, and will not do.

Third, Shortcuts automations have no shared context.
If the user changes the thought – realizes they need to add something – they must find the automation, edit the
notification text, save.
In Captrieve they open the capture and edit the body.
The thought and its cue are coupled in Captrieve; in Shortcuts they are separate artifacts.

The Shortcuts comparison should be embraced, not deflected.
"Yes, you could build this yourself. Here is what that looks like. Captrieve is for everyone who won't." is a stronger
position than pretending Shortcuts doesn't exist.

### Positioning rules derived from the above

-  Never claim incumbents lack location triggers.
   Claim what they lack: the capture flow, the composition, the reliability engineering, the connected layer, the system.
-  The enemy in marketing copy is not "your phone can't do this" but "what you have makes you do all the work at the
   wrong moment, in the wrong modality, with one hand tied behind your back."
-  The Shortcuts comparison is an asset, not a liability.
   Name it, describe the work it requires, and let the contrast do the job.
-  The privacy argument should be scoped: strong against third-party apps and on Android, weak against Apple Reminders.
   Do not lead with privacy in the Apple comparison.
-  Lead marketing with NFC, composition, and the caregiver story – geofence is the most commoditized and least reliable
   trigger and should not be the headline.
-  Preempt the objection by asking it ourselves, in the FAQ and on the site, in plain words: "Doesn't Apple Reminders
   already do this?" and "Couldn't I just build this in Shortcuts?"
-  Apple-risk note: the composition and capture features are absorbable by Apple in principle; the caregiver layer,
   cross-platform presence, and privacy-architected sharing are the parts that survive an Apple sherlocking.
   Thirteen years of non-absorption is a strong signal, not a guarantee.

---

## FAQ Page (captrieve.com/faq)

A second page on the marketing site, distinct from the main landing page.
Searchable – a static list of eight entries becomes useless quickly; this page will grow.

### Purpose and Audience

The FAQ serves two distinct audiences with different needs.
Prospective users want reassurance and context – they are deciding whether to trust the product.
Existing users hitting a problem want a direct answer fast, in language that matches how they would describe the problem
("my reminder didn't go off," not "geofence cue failure").
Entries should be written with enough natural-language keywords that a frustrated user's search surfaces the right entry.

### Entry Structure

Each entry follows this structure consistently:

1. **What the behavior is** – stated plainly, without hedging.
2. **Why it happens** – with platform attribution where the constraint originates in IOS or Android, not Captrieve.
3. **What the user can do about it** – always included if there is anything actionable. Never a dead end.

### Known Entries (seed list)

Status codes: [LIVE] = on faq.html now. [SPEC ONLY] = defined here, not yet on site.

**Competitive / basics**

-  What's the difference between Captrieve and a reminder app? [LIVE]
-  Doesn't Apple Reminders already do location reminders? [LIVE]
-  Couldn't I build this myself with IOS Shortcuts? [LIVE]
   (Links to compare.html for side-by-side task breakdown.)
-  I'm on Android – didn't Google have location reminders? [LIVE]
-  What's a retrieve? [LIVE]
-  What is free, and what does Solo unlock? [LIVE]
-  What happens to my captures if I delete the app? [LIVE]

**Cues and reliability**

-  Why didn't my location cue fire? [SPEC ONLY]
   (Covers: phone off during boundary crossing, low-power mode deferral, region cap prioritization – all attributed to IOS/Android.)
-  Why is location triggering less reliable in some places than others? [LIVE]
-  My Wi-Fi cue didn't fire when I arrived. What happened? [LIVE]
-  Can Captrieve tell which floor of a building I'm on? [SPEC ONLY]
   (No. GPS does not resolve altitude at floor scale on any phone. IOS and Android do not expose this. Not a Captrieve limitation.)
-  Why did my cue fire twice right after I restarted my phone? [SPEC ONLY]
   (Known IOS behavior on reboot. One-line explanation, no action needed – Captrieve discards the duplicate automatically.)
-  What happens if I have a lot of location cues set? [SPEC ONLY]
   (IOS limits how many locations your phone monitors simultaneously. Captrieve prioritizes the ones nearest your current
   position. Mitigation: dismiss captures you've already acted on.)
-  What happens if my phone is off when a cue was supposed to fire? [SPEC ONLY]
   (Time-based cues are evaluated on next boot. Geofence cues cannot be recovered – the device wasn't present to
   detect the crossing. Recommendation: set a fallback time cue for anything critical.)
-  Can Captrieve tell the difference between my house and my neighbor's house? [SPEC ONLY]
   (Probably not reliably. Consumer GPS accuracy is typically 5–30 meters. Locations closer than 150–200 meters apart
   may not be consistently distinguished. Physics and platform limitation, not Captrieve. Mitigation: single geofence
   for the area, rely on capture label to clarify context.)
-  My location cue didn't fire and my phone is an older Android. What's wrong? [SPEC ONLY]
   (Battery optimization whitelisting. Device behavior, not Android's or Captrieve's. Path: Settings > Battery >
   Battery Optimization > Captrieve > Don't optimize.)
-  I have a tablet. Will location cues work? [SPEC ONLY]
   (Depends on hardware. WiFi-only tablets: no cellular, slower GPS, reduced reliability in motion. Generally works
   for typical use cases near WiFi.)
-  What is a good radius for an en-route cue? [SPEC ONLY]
   (5–15km on a geofence_arrival for the destination, so it fires while still in transit.)

**NFC**

-  How do I set up an NFC tag? [LIVE]
-  Does NFC work in the background, or do I have to open the app first? [LIVE]
-  Where do people put NFC tags? [LIVE]
-  What if I set up NFC tags and after a while I just... don't need them anymore? [SPEC ONLY]
   (That is the app working at its best. The habit outlasted the tool. Keep the tags. Tap them anyway.)

**Privacy and data**

-  Does Captrieve send my data anywhere? [SPEC ONLY – covered in FAQ Connected section but not as standalone]
   (No. All captures live on your device. No account, no cloud, no server.)
-  Can I get my data out? [SPEC ONLY]
   (Yes. Export everything as JSON from Settings at any time.)
-  Does Captrieve send my location to a server? [LIVE]
-  What does the Connected tier's backend actually store? [LIVE]

**Voice and transcription**

-  Why does voice transcription sometimes get words wrong? [SPEC ONLY]
   (On-device transcription quality varies by device, OS version, accent, and background noise. Platform limitation.
   Original audio is always retained.)

**Connected tier**

-  What is the Connected tier? [LIVE]
-  Why does Connected require a subscription when Solo is a one-time purchase? [LIVE]
-  Does the caregiver need a separate account from the person they care for? [LIVE]
-  Can Captrieve send a text message to my caregiver when I leave the house? [LIVE]

### Hardware Expectations

The FAQ should include a plain-language summary of what hardware works best, so users can calibrate expectations before
they encounter a problem.

Recommended device characteristics for reliable location triggering:

-  A current or recent smartphone (iPhone or Android) with cellular capability.
   Cellular triangulation significantly improves position accuracy and update frequency,
   especially indoors and when GPS signal is weak.
-  GPS hardware, which is present on all modern phones and most tablets.
-  A recent OS version. IOS 14+ and Android 8+ are the tested baseline.
   Older OS versions may have geofencing behavior that differs from current platform documentation.

Devices that will work with caveats:

-  WiFi-only tablets – location cues will function but with reduced reliability,
   particularly in motion or away from known WiFi networks.
-  Older Android devices – battery management whitelisting may be required (see above).
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
Open items in the spec that resolve during development – geofencing package behavior, transcription quality specifics –
generate FAQ entries when they do.
User support questions that recur become FAQ entries.

---

## Special Use Cases

### Creative People

Creatives already know the pain of a good idea evaporating before they could act on it.
A notes app fails them at the retrieval step – the idea gets buried in a pile of other captures and is never seen again at the
right moment.
Captrieve's retrieval guarantee changes that: the melodic idea surfaces when they arrive at the studio, the character
observation surfaces on Sunday morning when they write, the visual concept surfaces when they are standing in front of a canvas.
The tool fits naturally into a creative workflow without requiring the creative person to build or maintain a system – which
they will not.

### People with ADHD

This is the strongest fit, and the primary community-acquisition target.

The ADHD experience maps onto Captrieve's design almost point for point.
Thoughts arrive fast and leave faster – working memory is the unreliable link between having a thought and doing
something about it, and the gap between "I should do that" and "I forgot I was going to do that" can be seconds.
The capture flow must be fast enough that the thought does not escape before it is committed.
Any friction is a real barrier for this population, not a minor inconvenience.
This is why the capture flow is non-negotiable as a design constraint: one tap, speak, done.

Standard reminders fail this population in a specific, well-documented way: a reminder that fires at an arbitrary time
is easy to dismiss because the context makes it abstract.
"Call the pharmacy" at 2 pm on Tuesday means nothing if you are in the middle of something else.
"Call the pharmacy" when you walk past the pharmacy on the way home is a different thing entirely – the context
makes it immediately actionable and nearly impossible to rationalize away.
Context-triggered retrieval is not a convenience for people with ADHD.
It is an accommodation that addresses the mechanism of the failure, not just the symptom.

The NFC tap ritual is particularly well-suited here.
Passive cues ambush people at arbitrary moments.
An NFC check-in is active and intentional – I am here now, what did I need?
That shift from passive to active engagement matches the way many people with ADHD prefer to interact with their own
systems: deliberate, physical, clear.
The physicality of the tap is also a context cue in itself, which can reinforce habit formation over time.

The community language for this: "the thought was there and then it was just gone," "I wrote it down but never looked
at the note again," "reminders just train me to dismiss them," "I need to see it when I'm actually in the place where
it matters."
The site and any community outreach should mirror this language, not describe it from outside.

One of those complaints, "reminders just train me to dismiss them," has a direct structural answer in the satisfaction cue.
A reminder that stays silent whenever the thing was already done stops firing on the occasions that teach dismissal, so it
keeps the credibility that ordinary reminders spend.

There is significant overlap between creative people and people with ADHD – the hyperfocus-then-forget pattern, the
sensitivity to interruptions in the capture moment, the need for retrieval that fits the moment rather than a clock.
This is not a coincidence and the product serves both in the same motion.

### People Managing Their Health and Memory

A different and more serious use case: people with early-stage cognitive decline, post-concussion symptoms, chemo brain,
or other acquired memory impairments.
Also: older adults who are fully cognitively intact but find that the volume of things to manage – medications,
appointments, follow-up questions – has grown beyond what working memory handles comfortably.

For this population, a reliable external memory that surfaces things in the right context is not a convenience – it is
a meaningful quality-of-life tool.
The failure mode is not forgetting to look at a note.
It is walking into the doctor's office after waiting six weeks for the appointment and saying "everything is fine"
because the questions are gone.
Captrieve addresses that specific failure: the question is captured when it arrives, and it surfaces at the clinic door.

This population is also the reason satisfaction cues default to a soft, confirm-first suppression for anything health-related.
A missed medication reminder is not an acceptable failure mode, so when a done signal is only a heuristic the reminder still
appears, pre-marked, rather than vanishing on a guess.

The privacy positioning is especially important here: people navigating memory loss or cognitive change are often
already managing loss of autonomy, and may be resistant to tools that feel surveillant or that share their data without
explicit control.
Captrieve's no-data-leaves-the-device principle – with the explicit exception of caregiver sharing, which the user
controls – directly addresses that resistance.
The product does not require an account, does not connect to a cloud, and does not send anything anywhere unless the
user explicitly chooses to.
For a population that is already asked to trust a lot of systems, that matters.

The community language here runs through caregivers as much as through the people themselves.
"I wish I could get her to remember to ask the doctor about the medication."
"He forgets what he wanted to say by the time the appointment starts."
"I can't be there every time, but I want to know she's okay."
The caregiver page and community outreach should address the person setting Captrieve up for someone else as much as
the person using it for themselves.

**UI simplicity as a non-negotiable constraint.**
The simplicity enforced throughout this spec is not an aesthetic preference.
It is a functional requirement for the ADHD and memory-impairment use cases specifically.
Any added friction in the capture flow is a real barrier for these users – not a minor inconvenience.
This constraint applies to every future feature evaluation: if a proposed feature adds friction to capture or retrieval,
the burden of justification is on the feature, not on the constraint.

---

## Platform

Flutter/Dart, targeting IOS and Android from a single codebase.

The product is fundamentally a phone app.
Capture is impulse-driven and happens in the moment.
Notification response is immediate and happens wherever you are.
The phone is always present; other surfaces are not.

### Language Standard for OS-Imposed Constraints

Any user-facing text describing a limitation that originates in IOS or Android behavior – not a Captrieve product decision –
must attribute it accurately.

The standard: name the platform, not the app.

-  Correct: "IOS limits the number of locations your phone can monitor simultaneously."
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
A web interface added later talks to the same repository contract – it does not require rearchitecting storage or data models.
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
Transcription runs in the background – the user is not blocked waiting for it.
A "transcribing..." indicator appears on the capture in the inbox.
The review-and-confirm step is available when the user next opens that capture's detail view, not as a blocking step in the
capture flow.
By default audio is retained alongside the transcript as a backup, and the user may discard it to save storage.
When a capture is set to play its recording on retrieve, or is marked audio-primary, the audio is the content rather than a
backup, and it is protected from discard while that setting holds (see Audio playback on retrieve).

### Maps and Geofencing

`flutter_map` with OpenStreetMap tiles – no Google API key required, no third-party dependency.
Geocoding via a compatible OpenStreetMap geocoding package, supporting place name search, city search, airport codes, and street
addresses.

Background geofencing via platform-native APIs through a Flutter geofencing package.
Geofencing is a first-class feature within the paid tier in v1, not an add-on, and may migrate to the free tier in a later
version (see Monetization).
Because geofence is paid in v1, the free app ships with no always-on location permission, which is a deliberate
simplification.
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
| `audioPath` | String? | Local path to audio recording, if kept. Protected from discard when playAudioOnRetrieve or audioIsPrimary is set |
| `photoPath` | String? | Local path to photo, if attached |
| `playAudioOnRetrieve` | bool | If true, the original recording plays when the capture is retrieved, not only shown or read |
| `audioIsPrimary` | bool | If true, the recording is the content: transcription is skipped or hidden, the audio is protected from discard, and the inbox shows a voice-note row |
| `triggers` | List\<Trigger\> | One or more; OR logic across the list |
| `satisfiers` | List\<Satisfier\> | Optional. Cues that mark the capture done and suppress its pending triggers when one fires inside the satisfaction window. OR across the list. Empty for most captures |
| `status` | CaptureStatus | pending, surfaced, snoozed, satisfied, dismissed |
| `snoozedUntil` | DateTime? | Set when status is snoozed |
| `label` | String? | Optional user-defined name shown in inbox instead of auto-generated cue summary |
| `autoDismiss` | bool | If true, dismiss automatically after first notification is acted on |
| `surfaceCount` | int | Incremented each time a cue fires and a notification is delivered. Never reset. Used to detect stale captures. |
| `completions` | List\<Completion\> | Append-only log of completion events from Done, Already Done, or a satisfier firing: timestamp, source, and whether it was unaided. Drives the unaided count and the habit-graduation suggestion |
| `activeFrom` | DateTime? | If set, no cue fires before this. Arms a capture for a future date – "starting next Monday" |
| `activeUntil` | DateTime? | If set, no cue fires after this and the capture archives. Bounds event-based cues to a range; a recurring time cue usually expresses its range through the recurrence rule instead |

### Trigger

Each Capture has one or more Triggers.
Each Cue fires independently on its primary event, then evaluates any AND conditions at the moment of firing.
If all conditions pass, the notification is delivered.
If any condition fails, the cue does not fire – and does not retry.
There is no ongoing state between evaluation attempts.

OR logic across multiple cues is simply a matter of setting multiple cues on the same Capture.
Any cue that fires delivers the notification.

| Field | Type | Notes |
|---|---|---|
| `id` | UUID | |
| `type` | TriggerType | datetime, geofence_arrival, geofence_departure, wifi_join, wifi_leave, bluetooth_connect, bluetooth_disconnect, charger_connect, charger_disconnect, dnd_mode_enter, nfc_checkin, app_open |
| `fireAt` | DateTime? | For datetime triggers |
| `locationId` | UUID? | Reference to a saved Location |
| `inlineLat` | double? | For one-off geofences not saved as a Location |
| `inlineLng` | double? | |
| `inlineRadius` | double? | Meters |
| `wifiSsid` | String? | For wifi_join and wifi_leave triggers |
| `bluetoothDeviceName` | String? | For bluetooth_connect and bluetooth_disconnect triggers |
| `nfcTagId` | String? | For nfc_checkin triggers; the app-minted UUIDv4 written to the tag at registration |
| `delayMinutes` | int? | Delay after primary event before notification fires |
| `repeatAfterMinutes` | int? | If set, fires again N minutes after initial delivery. A re-nag within one firing, not a daily repeat |
| `recurrence` | Recurrence? | For datetime triggers only. If set, the trigger fires repeatedly on a rule – daily, weekly, monthly – rather than once. See Recurrence |
| `andConditions` | List\<Condition\> | All must pass at moment of firing |

### Condition

A point-in-time predicate evaluated at the moment a Trigger's primary event occurs.

| Field | Type | Notes |
|---|---|---|
| `type` | ConditionType | time_after, time_before, inside_location, outside_location |
| `time` | TimeOfDay? | For time_after and time_before |
| `locationId` | UUID? | For inside_location and outside_location |

**Examples of compound cue logic:**

-  "Remind me when I leave the office, but only if it's after 5 pm" – geofence_departure trigger on the office location, with a
   time_after 17:00 AND condition.
-  "Remind me at 5 pm, but only if I'm not at the office" – datetime cue at 17:00, with an outside_location AND condition on
   the office location.
-  "Either of the above, or remind me at 7 pm regardless" – the two cues above, plus a plain datetime cue at 19:00. OR
   across all three.
-  "Remind me when I leave the office, then again 20 minutes later" – geofence_departure trigger with repeatAfterMinutes set to
   20.
-  "Remind me when I leave the office, but wait 20 minutes first" – geofence_departure trigger with delayMinutes set to 20.
-  "Remind me when I land" – geofence_arrival trigger on the destination city or airport, with a generous radius (several
   kilometers). The user searches for the destination on the map rather than dragging a pin from their current location.

### Recurrence

A datetime Trigger fires once by default.
A Recurrence turns it into a repeating reminder: every day at 9, every weekday at 7, the second Wednesday of each month, the
first of the month at noon.
The model is a deliberate subset of the iCalendar recurrence rule (RRULE), chosen because it is the portable standard and maps
cleanly onto both platforms, without exposing RRULE's full and rarely-needed complexity.

| Field | Type | Notes |
|---|---|---|
| `frequency` | Frequency | daily, weekly, monthly, yearly |
| `interval` | int | Every N periods. 1 is every period, 2 is every other, and so on |
| `byWeekday` | List\<Weekday\>? | For weekly rules: which days fire, such as Mon, Wed, Fri |
| `bySetPosition` | int? | For "the Nth weekday of the month": 2 with byWeekday Wednesday is the second Wednesday; -1 is the last |
| `byMonthDay` | int? | For "the Nth of the month": 15 is the fifteenth, -1 is the last day |
| `timeOfDay` | TimeOfDay | The clock time the reminder fires on each occurrence |
| `starts` | Date | The first eligible date. "Beginning next Monday" sets this |
| `ends` | RecurrenceEnd | never, onDate (until a date), or afterCount (after N occurrences) |

**Range is recurrence plus an end.**
"Daily at 7 am for one week, beginning next Monday" is one rule: frequency daily, timeOfDay 07:00, starts next Monday, ends
afterCount 7.
A range is not a separate concept for time reminders; it is the recurrence's own start and end.
For an event cue with no clock – "every time I tap the gym tag, but only for the next month" – the same range is expressed by
the capture's activeFrom and activeUntil instead, since there is no datetime trigger to carry the rule.

**One occurrence at a time.**
A recurring trigger schedules only its next occurrence, not an open-ended series of pending notifications.
When an occurrence fires or is satisfied, the next one is computed and scheduled.
This keeps the notification queue bounded and survives reboot the same way a single datetime trigger does (see Phone Off or
Rebooted).

### Satisfier

A Satisfier is a cue that silences a reminder instead of delivering one.
It reuses the full Trigger event vocabulary – an NFC tap, a Wi-Fi join, a geofence arrival, a charger connect – but inverts
the effect: when a Satisfier fires inside its window, the capture is marked `satisfied`, its pending triggers for that window
are canceled, and the event is appended to the completion log.
A capture can carry more than one Satisfier, with OR across them: any one firing satisfies the capture.

This is the third cue primitive, distinct from the two above.
A Trigger surfaces the capture.
A Condition gates a Trigger at the instant it fires.
A Satisfier suppresses a Trigger that has not yet fired, on the strength of something the user already did.

A Satisfier therefore departs from the statelessness that Triggers and Conditions assume.
A Condition is a point-in-time predicate with no memory.
A Satisfier must remember that the thing was done at 7 am and carry that fact to the 8 pm trigger.
The carried state is small – a `satisfied` flag and a timestamp scoped to the current window – but it is real, and it is the
one place in the model where a cue's outcome depends on an earlier event rather than only on the present moment.

| Field | Type | Notes |
|---|---|---|
| `id` | UUID | |
| `type` | TriggerType | The same event vocabulary as Trigger: nfc_checkin, wifi_join, geofence_arrival, charger_connect, and the rest |
| event params | – | The same fields a Trigger of this type uses: nfcTagId, wifiSsid, locationId, and so on |
| `window` | SatisfactionWindow | The period inside which doing the thing counts as done for this reminder. For a one-shot capture, creation until the trigger. For a recurring capture, the current recurrence period |
| `mode` | SatisfyMode | hard or soft. hard suppresses the reminder silently. soft still fires, pre-marked "looks like you already did this – confirm?", so a wrong guess never silently drops a real need |

**Suppression mode and the cost of a wrong guess.**
A false trigger is a spurious nudge, which is merely annoying.
A false satisfier is silence when the user actually needed the nudge, which is worse, and for a medication reminder it is a
safety matter.
The two example signals sit in different reliability classes.
A dedicated NFC tag on the pillbox is trustworthy, because the user taps it only when the dose is actually taken.
A Wi-Fi join at the pharmacy standing in for "picked up the refill" is a heuristic, fine for "did I collect the prescription
this week" and wrong for "did I take today's dose", because the user might be there for shampoo.
hard mode suits the trustworthy signal.
soft mode is the default for anything health-related, and the always-visible inbox backstops both, since a suppressed capture
is quiet, never gone.

### Completion

One entry in a capture's completion log, written each time the capture is completed – by the user choosing Done or Already Done
on a surfaced capture, or by a Satisfier firing.

| Field | Type | Notes |
|---|---|---|
| `at` | DateTime | When the completion was logged |
| `source` | CompletionSource | already_done (manual, the user had handled it before the reminder), done (manual, the user acted when it surfaced), satisfier_auto (a satisfaction cue completed it) |
| `unaided` | bool | True for already_done and satisfier_auto – the user had it handled without the reminder's help. False for done. Feeds the unaided count and the habit-graduation suggestion |
| `satisfierId` | UUID? | Set only when source is satisfier_auto: which satisfier marked the capture |

---

## Cue Types

Retrieval is the core value of Captrieve.
The richness of available cue types is the direct implementation of that value.
Each type below is a first-class cue – it appears in the cue picker, can carry AND conditions, and participates in OR
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
Particularly useful with AND conditions – "when I leave the office, but only if it's after 5 pm."

Use cases: leave work, leave the house, leave a meeting venue.

### wifi_join

Fires when the device joins a specific Wi-Fi network by SSID.
This is a high-precision indoor location signal that GPS cannot provide.
It is passive – no user action required beyond having Wi-Fi enabled – and requires no additional hardware.

This cue type was not in the original spec and was added because it solves a real problem geofencing handles poorly:
distinguishing between nearby locations (your office vs. the coffee shop next door), or identifying indoor arrival at a
specific place (the dentist's office, the airport terminal, a friend's home) where GPS is unreliable.

Use cases: arrive at home network, arrive at office network, arrive at the airport, arrive at a specific venue.

Implementation note: Wi-Fi SSID scanning in the background is supported on both IOS and Android with the appropriate
permissions.
On IOS, the CNCopyCurrentNetworkInfo API requires the Access Wi-Fi Information entitlement.
This should be treated as a required entitlement, not optional – wifi_join is a core cue type.

### wifi_leave

Fires when the device disconnects from a specific Wi-Fi network.
The complement of wifi_join.

Use cases: leave home, leave the office.

### bluetooth_connect

Fires when the device enters sustained proximity to a specific Bluetooth device, identified by device name.
The most common real-world case is car audio – being in the car is a reliable proxy for "I am now in my car."
A home speaker or work headset can serve similar roles.

This cue type requires no additional hardware beyond devices the user already owns.
It is lower priority than geofence and Wi-Fi cues in the cue picker UI, but fully supported.

Use cases: get in the car, arrive at a location where a known device lives.

**BLE proximity state machine.**
Raw Bluetooth RSSI data is noisy – signal strength fluctuates even when neither the phone nor the device has moved.
A naive implementation that fires on first detection or on any RSSI threshold crossing produces false positives and rapid
oscillation at the boundary.
Captrieve converts the raw RSSI stream into a deterministic application-level event using a three-state machine.

States:

- OUTSIDE – device not considered in proximity. Default state.
- INSIDE – device considered in proximity. ENTER event has been emitted.
- LOCKED_OUT – temporary suppression after exit to prevent re-entry oscillation.

The machine maintains a per-device sliding RSSI buffer over a 3–5 second window.
Only windows with at least 3 samples are evaluated.
Two thresholds enforce hysteresis:

- ENTER_THRESHOLD (-60 dBm example): average RSSI must exceed this to consider entry.
- EXIT_THRESHOLD (-75 dBm example): average RSSI must fall below this to trigger exit.
  The exit threshold is lower than the entry threshold; the gap between them is the hysteresis band.

Entry requires stability: the RSSI window must exceed ENTER_THRESHOLD for 2–3 consecutive valid windows
(ENTER_STABILITY) before INSIDE is declared and a CONNECT event is emitted.
A single window that clears the threshold but is followed by one that does not resets the consecutive counter to zero.

Exit transitions immediately to LOCKED_OUT when the RSSI window falls below EXIT_THRESHOLD for a sustained period.
LOCKED_OUT ignores all ENTER conditions for 10–30 seconds (LOCKOUT_DURATION) after the exit time.
After that duration, the machine transitions to OUTSIDE and ENTER conditions are evaluated again normally.

The application layer only ever receives CONNECT (from OUTSIDE → INSIDE) and DISCONNECT (from INSIDE → LOCKED_OUT).
All RSSI noise handling, window averaging, stability counting, and lockout timing is internal.

**Implementation parameters (tunable).**
The values above are starting points.
ENTER_THRESHOLD, EXIT_THRESHOLD, WINDOW_SIZE, MIN_SAMPLES, ENTER_STABILITY, and LOCKOUT_DURATION are all
implementation constants that should be validated against real device behavior during the Bluetooth cue spike.
Car Bluetooth in particular may warrant different tuning than a desk speaker – entry and exit dynamics differ when the
user is walking toward a device versus driving into range.

### bluetooth_disconnect

Fires when the device exits sustained proximity to a specific Bluetooth device.
The complement of bluetooth_connect.
The exit event is emitted by the BLE state machine when the device transitions from INSIDE to LOCKED_OUT –
after RSSI has fallen below EXIT_THRESHOLD for a sustained period, not on the first low-signal scan.

Use cases: get out of the car, leave a location where a known device lives.

### charger_connect

Fires when the device is plugged into a charger.
No additional hardware required.

The canonical use cases are bedtime and morning.
Plugging in at night is a reliable proxy for "I am settling in for the evening"; unplugging in the morning is a reliable
proxy for "I am starting my day."
These use cases were originally served by a bedside NFC tag, and the NFC approach remains valid and preferred for users
who want the intentional tap ritual.
The charger cue serves users who want the same result without any physical setup – the act of plugging in is already part
of their routine.

The cue is most useful with a time_after AND condition to avoid firing on incidental mid-day charges.
"When I plug in, but only after 9 pm" isolates the bedtime scenario cleanly.
Without a time condition, the cue fires on every charger connection, which is likely too frequent.

Use cases: bedtime routine captures, end-of-day review, anything that should surface when the user is winding down.

### charger_disconnect

Fires when the device is unplugged from a charger.
The complement of charger_connect.

Same time-condition discipline applies.
"When I unplug, but only after 5 am" isolates the morning wake-up scenario and avoids firing if the phone is briefly
disconnected and reconnected at night.

Use cases: morning routine captures, start-of-day reminders, anything that should surface when the user is beginning
their day.

### dnd_mode_enter

Fires when the device enters Do Not Disturb or a Focus mode (IOS Focus, Android DND).
This is a device-state cue, not a physical context cue – the user has explicitly signaled they are entering a
different mode of attention.

This is distinct from Captrieve's own DND feature (which suppresses Captrieve notifications but does not fire a cue).
dnd_mode_enter responds to the OS-level state change, which can be set manually or on a schedule by the user outside
of Captrieve.

The most natural use case: a capture that should surface precisely at the transition into a focused state, not
interrupting during it.
Example: a preparation reminder that fires when Work Focus activates – "check your notes before the deep work session"
– rather than firing at a fixed time that may or may not align with when focus actually begins.

The cue fires once per DND/Focus mode activation event.
It does not fire repeatedly while the mode is active.
A time_after OR time_before AND condition can scope it to a specific window if the user's focus modes are set on a
regular schedule but not always at the same time.

Implementation note: IOS exposes Focus mode state via the Focus framework; reading current Focus status requires
appropriate entitlements.
Android exposes DND state via NotificationManager.
Evaluate package support and permission requirements during the trigger spike.

### nfc_checkin

Fires when the device reads a specific NFC tag.
NFC requires physical proximity – effectively contact – between the phone and the tag.
This is a feature, not a limitation.
The tap is a deliberate micro-ritual: I am here now, what do I need to know?

NFC tags are inexpensive (under $1 each), widely available, and can be placed anywhere: a desk, a piano, a car dashboard,
a medicine cabinet, a front door, an airplane seat armrest.
At first claim the app writes its own identifier to the tag and locks it (see Tag identity and registration below).
When the phone later reads that identifier, the associated capture surfaces.

**The check-in habit.**
For users with ADHD or age-related memory impairment, passive cues fire at them.
An NFC check-in puts the user in an active, intentional relationship with their own memory.
Six tags placed at consistent locations – desk, piano, kitchen, car, front door, bedside – build a physical routine that is
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
dosage questions, or side effects surface when the user is physically at the cabinet – the moment of highest relevance.
NFC tags are inexpensive enough to treat as disposable for one-off contexts.
Stick a tag on an airplane seat armrest: tap on boarding, tap again when gathering your things to depart – the tap fires
whatever capture was set for that tag.
This solves a use case that geofencing handles poorly: airport geofences are large and imprecise, firing on approach or
during layovers.
An NFC tap on departure is unambiguous.

**Nothing pending feedback.**
When the user taps an NFC tag and no captures are pending for that tag, the app must respond visibly.
Silence is ambiguous – the user took a deliberate action and needs to know it was received.
The response: a brief, non-alarming message – "Nothing pending for Piano" (using the tag's saved name) – displayed
for two to three seconds, then dismissed automatically.
This confirms the tap registered, the tag is recognized, and the absence of a retrieve is meaningful rather than a
malfunction.
The user does not need to tap again.
When the user selects nfc_checkin as a cue type, they are prompted to tap a tag to register it.
Registration mints and writes the tag's identity and locks the tag (see Tag identity and registration below), then stores
that identity as `nfcTagId` on the trigger.
The user can name the tag (e.g. "Piano", "Desk", "Car") – this name is a local mapping from the identity to a human label,
stored on the device and used in the cue summary.
Renaming changes only the label; the identity is unchanged, so every capture pointing at the tag is undisturbed.
Named tags are saved to a tag library analogous to the saved Locations list, any number of captures may point at one tag, and
a tag can be reused across captures.

**Signal abstraction.**
NFC follows the same abstraction principle as BLE proximity: the raw hardware event is converted to a single
deterministic application-level event before the cue layer ever sees it.
For NFC the raw event is already deterministic – the tag either reads successfully or it does not, with no ambiguity
about proximity or signal strength – so no state machine is required.
The tap is the event.
The cue fires on confirmed tag read.
Nothing below that is visible to the cue evaluation layer.
This parallelism is intentional: all context-sensing cue types in Captrieve expose a clean binary event (arrived,
departed, connected, disconnected, tapped, opened) and hide all hardware complexity below that abstraction boundary.

Implementation note: Flutter NFC packages support tag reading and writing on both IOS and Android.
Two NFC operations exist, and they are different.
Registration is a one-time foreground session (NFCReaderSession on IOS, reader mode on Android) that the app initiates – the
only moment a tag is read in the foreground or written.
Retrieval, after registration, uses background reading on both platforms: the written NDEF Universal Link is dispatched to the
app by the OS without the app open (see Tag identity and registration for the full model and its platform caveats).
The earlier framing – that IOS cannot read in the background, so the intentional foreground tap is the only model – is
superseded. Background reading is available on IOS for NDEF Universal Links and is the better retrieval path; the foreground
session survives only as the one-time registration step.

**Tag selection and purchasing guidance.**
Users supply their own tags, so the spec must define exactly which tags are compatible, and the website and manual must
relay that in plain language.
The recommended tags are the NXP NTAG21x family – NTAG213, NTAG215, or NTAG216 – which are inexpensive, ISO 14443 Type A,
universally readable by current iPhones and Android phones, and writable NDEF.
Any of the three suffices because Captrieve stores only a small identifier on the tag, not real content, so the least
expensive (NTAG213) is adequate.
Tags must be acquired rewritable and not pre-locked, because registration writes an NDEF Universal Link to the tag and then
locks it (see Tag identity and registration below).
A read-only or pre-locked tag cannot be registered.

Tags that must be excluded, with the reason in each case:

-  Privacy / anti-tracking / random-UID tags – emit a different identity on every read by design and resist stable NDEF use.
-  Magic / changeable-UID tags – sold for cloning, and the writable UID makes them untrustworthy as a fixed identifier.
-  MIFARE Classic – inconsistently supported or wholly unsupported on IOS. NTAG21x avoids this entirely.
-  Pre-locked / read-only tags – cannot accept the NDEF write that registration performs.

Metal placement requires anti-metal (ferrite-backed, "on-metal") tags.
A plain sticker tag detunes against metal and fails to read.
This is a placement caveat rather than a compatibility one, but it is the single most common field failure, so it belongs in
the user-facing guidance.
Per the Language Standard for hardware constraints, this is the tag's physics, not a Captrieve deficiency.

**Tag identity and registration (resolved model).**
This replaces the earlier read-vs-write fork. The model is: the app mints its own identifier, writes it to the tag once, and
locks the tag. The factory UID is not used.

Identity.
At first claim the app generates a UUIDv4 and writes a single NDEF record – the Universal Link https://captrieve.com/t/<uuid>
– as the only record on the tag, then applies the tag's one-way lock.
The UUID is the canonical identity, stored as `nfcTagId`.
A UUIDv4 is opaque: a stranger who reads the sticker learns a random token and nothing else – no hardware identifier, no
content, no user.
Two unrelated people could even use the same physical tag with entirely separate local mappings and never collide.

Registration flow (one foreground session per tag, at first claim). The session branches on what the tag already holds:

-  Blank tag – mint a UUIDv4, write the Universal Link as the sole record, lock the tag, save the identity. The normal path.
-  Tag already carrying a Captrieve Universal Link – adopt the existing UUID in place. No write, no new identity. This is what
   makes a second device, a fresh reinstall, or a found tag just work, and it is why locking does not strand a tag: the
   identity on the tag is reusable even when the phone that wrote it is gone.
-  Foreign NDEF, or a tag locked by something else – refuse gracefully with a plain explanation, rather than fail silently.

Why locking. An unlocked tag that someone later overwrites produces the worst failure mode: the user concludes the app
stopped working when in fact the tag changed under it. The one-way lock forecloses this. The cost is that the tag can never
be re-pointed, which is acceptable because re-pointing is not a use case – a tag marks a fixed physical place.

The re-adoption consequence, stated plainly. Re-adopting a tag restores the tag as a working slot, not its associations.
The name and the captures lived only in the phone's local store, never on the tag, so a tag recovered after its mapping is
lost comes back empty: a valid identity with no name and nothing attached.
The user re-labels and re-populates it.
For someone who has scattered tags across home, work, and car, re-adoption is still a large win over re-buying and re-placing
them – but it is recovery of the slots, not their contents, and the manual must say so rather than let "the sticker still
works" imply otherwise.

Naming. Naming is a local mapping uuid to a human label. Rename edits the label only; the identity is unchanged, so every
capture pointing at the tag is undisturbed. Any number of captures may point at one tag.

Retrieval path and platform mechanics. After registration a tap is handled by OS background reading of the written Universal
Link, with no app open and no button:

-  IOS – Background Tag Reading dispatches the link to the app as an NSUserActivity. Requirements: the Associated Domains
   entitlement (applinks:captrieve.com) and a valid apple-app-site-association file at
   https://captrieve.com/.well-known/apple-app-site-association. That file is fetched by Apple at app install, validated, and
   cached on the device. Tag taps thereafter resolve against the local cache with no network call. The Universal Link must be
   the first NDEF record. Limits: iPhone XS and later (effectively all current devices); the phone must have been unlocked
   once since boot, with the display on, and not mid Core-NFC session, Apple Pay, or camera use; iOS shows a banner the user
   taps, and a locked phone asks to unlock first. The app receives the URL and parses the UUID from the path – it never
   fetches the URL.
-  Android – an intent filter on the NDEF URI, with autoVerify against a Digital Asset Links file, lets the OS launch the app
   from the tap, generally without the extra confirmation IOS imposes. The live UID could also be read here, but is not
   needed; the written UUID keeps both platforms on one identity.

A failure mode to expect in testing: if the apple-app-site-association file (or the Android Digital Asset Links file) is
missing, malformed, or unreachable at install time, the OS silently falls back to treating the link as a plain web URL and
opens the browser. The domain and files must be live and correct before users install, not only before they tap.

Installed-app-versus-not. Because the identity is a real Universal Link on a domain Captrieve controls, a tap on a phone
without the app installed opens https://captrieve.com/t/<uuid> in the browser, which is a soft install-prompt opportunity
rather than a dead tap. Installed app gets the parsed UUID; no app gets the web page; never both.

Apple relationship. Writing NDEF, including the Universal Link, is plain Core NFC under the same "Near Field Communication
Tag Reading" capability used for reading. There is no separate Apple agreement or approval for writing. The only addition
versus pure reading is the Associated Domains entitlement, which is free and self-serve. The NFC & SE ("payments") path,
which does require Apple approval, is not used. The standing build prerequisite is unchanged: the paid Apple Developer Program
plus the Tag Reading capability, now plus Associated Domains.

**Backup and the tag mapping.**
Because the tag carries only an opaque UUID, and the uuid-to-name mapping plus the captures live only on the device, that
local store is the single copy of everything that gives a tag meaning.
It must be covered by the same backup path already specified for the Connected key material: the local store is backed up
through the platform's own mechanism (iCloud, Android backup), and a reinstall on the same Apple or Google account restores
it.
Without a backup, a lost or wiped phone loses the mappings permanently. The locked tags survive as reusable identities but
return empty on re-adoption.
This is mandatory hygiene, stated in the user manual as plainly as "back up your phone," not buried.

**Cross-device and sync (resolved).**
This was an open fork. It is now decided.
v1 ships JSON export and import of the tag library and the wider local store.
Import defaults to merge: the incoming library is unioned with the local one, keyed by tag UUID, so the same physical tag is
never duplicated.
On a name conflict for the same UUID the rule is last-write-wins by timestamp.
Replace is offered as an explicit alternative that discards the local library and takes the incoming one wholesale.
The transport is any plain-text channel the user already has: a copy and paste of the JSON, or the JSON exported as a small
file handed to the OS share sheet.
The share sheet is the deliberate choice for the "Bluetooth on demand" wish.
It already routes through AirDrop on IOS, Quick Share and Nearby on Android, and Bluetooth, email, and messages on both, so
device-to-device transfer within one ecosystem falls out for almost no code.
A custom app-managed Bluetooth path is explicitly rejected.
IOS exposes no classic-Bluetooth serial channel to third-party apps, BLE peripheral support in Flutter is limited and
platform-inconsistent, and the share sheet already delivers the same result.
The cost would be disproportionate for a feature that is secondary by design.

Live cloud sync stays deferred, but the deferral now has a decided shape rather than an open question.
Three cloud paths exist and only one is worth building.
Platform cloud on IOS (iCloud, CloudKit or key-value) is easy and privacy-clean but Apple-only.
It is worth adding only as an optional nicety when a user's secondary device is also Apple, and never as the general answer.
Google Drive or Firebase reaches both platforms but drags in a third-party backend and an account, which contradicts the
no-server, no-account promise of the local tiers, so it is rejected.
The Connected backend is the chosen path if and when live sync is built.
It is the only transport that is both cross-ecosystem and privacy-clean, because the relay is app-to-app rather than tied to
any vendor cloud and is already end-to-end encrypted.
The work it adds is incremental rather than trivial: a new synced data type, end-to-end encryption of that payload like the
rest, and multi-device conflict resolution, for which last-write-wins on timestamps is sufficient for a uuid-to-name map.
It is therefore Connected-tier-only and gated on the Connected backend existing at all, which is a later release.
The one fork that changes this calculus is the nature of the user's secondary device.
An Apple secondary makes the optional iCloud nicety cheap.
An Android secondary makes the Connected backend the only privacy-clean route, since no single consumer cloud spans iPhone
and Android.

### app_open

Fires the next time the user opens Captrieve.
This is "until I ask" – the user knows they will want this capture soon and trusts themselves to open the app when the moment
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

`pending` – cue has not yet fired. `surfaced` – cue fired, notification delivered, awaiting user action. `snoozed` –
user deferred; `snoozedUntil` set. `satisfied` – a satisfaction cue fired and suppressed the pending reminder for this
window; for a recurring capture the flag resets at the next period, for a one-shot it resolves like a completed capture.
`completed` – the user chose Done or Already Done, or a satisfier completed it; a Completion is logged and the capture moves to
the archive. `dismissed` – the user released the capture without doing it.
No further notifications.
For a recurring capture (see Recurring Reminders), status tracks the current occurrence and returns to `pending` for the next
one; the capture moves to the archive only when its recurrence rule ends.

---

## Recurring Reminders

Any reminder can recur.
A datetime cue can fire once, or every day at 9, or every weekday, or the second Wednesday of each month, or daily at 7 am for
one week beginning next Monday.
The rule and its bounds are described in the Recurrence data model; this section is what recurrence means for the rest of the
product.

**The capture stays alive across occurrences.**
A one-shot capture resolves after it is acted on.
A recurring capture does not.
Its status describes the current occurrence – pending, then surfaced, then acted on, snoozed, or satisfied – and then resets to
pending for the next occurrence.
When the rule runs out, the last occurrence behaves like a one-shot and the capture moves to the archive.
Editing the rule, or ending it early, is an ordinary edit on the capture.

**Recurrence is what makes a satisfaction cue repeat.**
A satisfaction cue needs a window inside which "already done" counts, and for a recurring reminder that window is exactly one
occurrence period.
Feed the dog every day at 8 pm, unless the dog-food tag was tapped today: each day is a fresh window, a tap before 8 pm
satisfies that day, and tomorrow the question returns.
The second Wednesday of the month, unless already handled: the window is that month's occurrence.
This is the pairing the two features were built for, and it is where the completion log accrues into habit graduation.

**Persistent silence and persistent nagging are both signals.**
A recurring capture that is satisfied early, day after day, is a habit forming, and the app offers to loosen or retire it.
A recurring capture that surfaces and is ignored, day after day, is the opposite: the same stale-capture machinery that flags a
one-shot gone cold (see Stale Capture Prompt) can ask whether a daily reminder the user keeps dismissing still earns its place.

**Tier.**
Basic recurrence is free, because a repeating date-and-time reminder is part of the everyday loop the free tier already
promises with "date and time, unlimited."
What is paid is composition, unchanged: the moment a recurring reminder also carries a satisfaction cue, an AND condition, a
delay, or a second OR'd cue, it crosses into Solo, on the same rule as every other composed capture.

---

## Satisfaction Cues ("skip if done")

A satisfaction cue answers a request the rest of the model cannot: remind me to do this, unless I have already done it.
Feed the dog at 8 pm, unless the dog-food tag was already tapped.
Take the evening dose, unless the pillbox tag was already tapped.
Pick up the refill, unless joining the pharmacy Wi-Fi already showed I was there.
The reminder is set as usual, and a second cue is attached as the done signal.
If the done signal fires first, the reminder never speaks.

**Why it belongs in Captrieve and not in a reminder app.**
A reminder app fires on a clock and has no idea whether the thing was done, so it nags whether or not the nagging is needed.
That is the precise mechanism by which reminders train people to dismiss them: a share of the time they fire when the work is
already finished, so the user learns the notification carries no information and swipes it away on reflex.
A satisfaction cue removes those false alarms.
The reminder that does appear is one the user genuinely had not acted on, so it keeps its signal and stays worth answering.

**It is composition, inverted.**
Mechanically a satisfaction cue is a second cue on the capture whose effect is to cancel rather than deliver.
It reuses every existing cue type, so the user is choosing from a vocabulary they already know.
Because it is composition, it lives in the Solo tier alongside AND conditions, delays, and multi-cue OR, and the upgrade
prompt fires the first time a user reaches for a done signal.

**Recurrence is the natural home.**
The headline cases – feed the dog, take the meds, water the plants – are daily.
A satisfaction cue is most valuable on a recurring capture, where each period resets the done flag and the cue asks the
question again the next day.
Recurring captures are the home this feature was built for: see Recurring Reminders for how a recurrence period defines the
satisfaction window.

**Habit graduation, made real.**
Because each satisfaction event is logged with whether it beat the reminder, the app can notice when the behavior has
outrun the cue – fourteen evenings running, the dog was fed before 8 pm – and offer to move the reminder later, loosen it,
or pause it.
This is the same idea the cues page already celebrates, the habit outlasting the tool, turned into a concrete prompt.
It stays a suggestion the user accepts, never an automatic change, because the standing rule holds: no algorithm decides when
the user is ready.

---

## Completion and Acknowledgment

Every surfaced capture is dismissed somehow, so the dismiss moment is where the app learns what actually happened, at no extra
cost in taps.
Instead of one "dismiss" that throws that outcome away, a surfaced capture offers two completion verbs and a release.

**The two verbs.**
Done (✓) means the user acted on the reminder when it surfaced.
Already Done (✓+) means the user had already handled it before the reminder spoke – called Bob at 2 for a 3:45 reminder, or
simply remembered the thing at the piano on their own.
Already Done is the more meaningful of the two, because it is unaided: the user remembered without the app's help, which is the
exact memory-self-efficacy proof the science page is built on.
The check-plus icon foreshadows that it is worth more.
Dismiss, separately, releases a capture the user no longer intends to act on, and logs nothing.
The two verbs are present on every surfaced capture, recurring or one-shot, task or thought; the user picks the one that is
true, and Already Done is simply ignored when it does not apply rather than hidden.

**Logging is always on and always distinct.**
Done and Already Done are recorded as separate Completion entries every time, whether or not the user has ever opened the score
utility below.
This costs nothing and means the history is already there if the user later wants to see it.
This is the manual twin of the satisfaction cue: a satisfaction cue is an automatic Already Done, detected by a real-world
signal instead of reported by hand, and all three paths write to the one completion log.

**Acknowledgment.**
Already Done earns a small celebration, a brief confetti burst, because it is the unaided win.
Done earns a quiet check.
The acknowledgment is non-verbal by default, with no "good job" text, because spoken praise can read as condescending to the
older and memory-impaired users the product serves; the feeling is carried by the motion, not by words.

**The score utility, which may never be clicked.**
There is a single, optional, private screen that the user reaches only by going looking for it.
It shows two things: how reliably the user gets things Done, and how many times they did them unaided.
It is a count that goes up, never a ratio and never a percentage, so a quiet week is a smaller happy number and never a failing
grade.
It carries no streaks, and nothing about it ever fires a notification – a reminder nagging the user to keep their numbers up
would re-create the very dismissal-training the rest of this design exists to remove.
It is local and personal, with no leaderboard and no sharing, consistent with the no-account, no-cloud posture.

**Graduation is the highest score.**
The completion log is what powers the habit-graduation suggestion in Recurring Reminders, and the framing is deliberate.
When a recurring reminder has been Already Done often enough that the habit clearly runs on its own, the app offers to retire
it, and retiring it is presented as the achievement, not a loss.
The score's pinnacle is needing the app less, which inverts the usual incentive of an app that profits from engagement.

**What it is not.**
Completion data never becomes a caregiver compliance feed.
"Did she take her pills today" is exactly the action-level monitoring the Connected Tier deliberately refuses (see Caregiver
Use Case): presence there is passive pattern-absence, never a watch on specific acts.
The completion log and the score stay personal to the user whose captures they are, and any caregiver visibility would be a
separate, explicit, user-controlled decision well outside this feature.

**Tier.**
Free, all tiers.
The dismiss verbs, the logging, and the acknowledgment are core retrieval UX, and the score utility is a local personal
feedback screen with no backend, so none of it is gated.

---

## Capture Modalities

### Voice (Primary)

One tap opens the record view.
Tap to record, tap to stop.
Audio is saved immediately.
Transcription runs in the background.
The cue picker opens right away – the user does not wait for transcription.
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

### Audio playback on retrieve

By default a retrieved capture shows its transcript, and optionally has that transcript read aloud by TTS.
For some captures that is not enough, because the recording itself is the point.
A per-capture setting, "play the recording when this comes back," makes the original pre-transcription audio part of the
retrieve rather than a backup behind the text.

Two distinct cases sit on this setting, and both are real.

-  Audio as emphasis. The transcript is still useful, but the voice carries something the text cannot. A daughter records
   "remember to take both the red pill and the blue one, and remember that I love you," sets it on her mother's
   medicine-cabinet tag, and what her mother hears at the cabinet is her daughter's actual voice. The "I love you" in a real
   voice is precisely what transcription strips out. Here playAudioOnRetrieve is set and audioIsPrimary is not, because the
   text still has value as a written reminder.
-  Audio as content. There is no useful transcript at all. A musician hums a riff to capture it and wants to hear the hum
   back at the guitar. Here audioIsPrimary is set. Transcription is skipped or hidden rather than shown as noise, the audio is
   protected from being discarded to save storage, and the inbox shows the capture as a voice note with a play control and a
   duration rather than a line of text.

**Playback behavior.**
The recording plays when the user opens the retrieved capture.
Whether it autoplays on open or waits for a play tap is a preference, with autoplay the sensible default for the accessibility
and caregiver cases.
Audio never autoplays during Do Not Disturb or quiet hours.
A capture held during a quiet period waits exactly as its notification does, and its audio plays when the user opens it after
the period ends.
This composes with the NFC check-in model cleanly: tap the guitar tag and the riff plays, tap the cabinet tag and the voice
plays, once the app has come forward.

**Interaction with adjacent features.**
Audio playback is the human recording, and TTS is the spoken transcript, so the two do not stack.
When audioIsPrimary is set, TTS has nothing to read and is not offered.
With the optional recall prompt, the revealed answer is the audio rather than text.

**Tier placement.**
Playing back your own recording on retrieve is a free-tier feature, because it is a natural extension of voice capture and
costs nothing on a single device.
Hearing someone else's recording, as in the daughter-to-mother case, requires the Connected Tier, because the audio has to
travel between two people's devices, which is sharing and carries the only real cost.
See the Connected Tier section for how shared audio is handled.

---

## Trigger Setting UI

After capture, the user is immediately presented with the cue picker.
This is not optional – a capture with no cue is a note, not a Captrieve.
The cue picker must be fast and opinionated.

### Quick Options (one tap)

The first option shown is the user's selected default cue.
The default default is "Tomorrow morning." The user can change their default in settings.
Other quick options:

-  Next time I open the app
-  Specific time – opens time/date picker
-  When I arrive somewhere – opens Location picker
-  When I leave somewhere – opens Location picker
-  When I join a Wi-Fi network – opens Wi-Fi picker (lists nearby networks; user can type an SSID manually)
-  When I connect a Bluetooth device – opens Bluetooth device picker (lists paired devices)
-  When I plug in my charger – optionally opens time condition picker ("but only after...")
-  When I unplug my charger – optionally opens time condition picker ("but only after...")
-  When Do Not Disturb / Focus starts – optionally opens time condition picker
-  When I tap an NFC tag – initiates tag registration flow

### Saved Locations

A list of named locations the user has previously saved.
Selecting one applies it immediately.
A "New location" option opens the map.

### Map View

For setting a one-off geofence or creating a new saved Location.

The map opens centered on the user's current position by default.
A search bar at the top supports place name, city, airport code, and street address lookup via geocoding – the map navigates to
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
The UI makes clear these are OR – any one firing delivers the notification.

### Recurrence and Range

For a date-and-time cue, a "Repeats" control offers the common rules as one tap each – every day, every weekday, weekly on
chosen days, monthly on a date, monthly on the Nth weekday – with a custom option for the rest.
A "Starts" date and an "Ends" choice – never, on a date, or after a number of times – set the range, defaulting to start today
and never end.
Most captures are one-shot and show none of this until the user asks for it.

### Satisfaction Cues ("But skip it if...")

Available after a primary cue is set, alongside the "But only if..." condition option, as a "But skip it if..." addition.
The user picks a done signal from the same cue picker – most often the tag or network that means the task is finished – and
chooses whether a match silences the reminder outright or surfaces it pre-marked for a quick confirm.
Not required, and absent on most captures.
A Solo-tier feature, since it is a second composed cue.

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

**Surfaced** – captures whose cue has fired but have not yet been acted on.
Shown at the top.
The app badge count reflects the number of surfaced captures.
On foreground, surfaced captures are displayed prominently.
They persist until the user explicitly snoozes or dismisses.

**Pending** – captures with cues that have not yet fired, ordered by next cue time.

### Inbox Row Layout

Each row is divided horizontally: one third for the label, two thirds for the body preview.
The default row height is one line.
If the label requires wrapping it drives the row taller, up to a maximum of three lines.
The body preview occupies the same vertical space as the label but never drives row height – it always truncates with ellipsis
at whatever space it is given.
A label that exceeds three wrapped lines is also truncated with ellipsis at that point.

The label column has two levels.
The label name occupies the top of the column in normal weight text, wrapping up to the 3-line maximum; if the name exceeds that
it is truncated with ellipsis.
Directly below it, always present, is the creation timestamp in smaller, lighter text – for example "2026-02-03 11:53 AM".
The timestamp is always shown in full and never truncated.
If no label name has been set, the timestamp appears at the top of the column in normal weight as the sole identifier.

The body preview shows the beginning of the capture text – enough to identify the thought without opening it.

The 1/3 – 2/3 horizontal split is a starting point to validate against a real device.
The label column needs enough width for the name to wrap readably; the body column needs enough width to show meaningful preview
text.
Adjust after first build.

This allows the user to scan the inbox and immediately recognize groupings – for example, three captures all labeled
"dermatologist" visible before any appointment, ready to review or consolidate.

The label can be set or changed from the inbox row or the detail view.
Once set it appears everywhere the capture is shown.
The auto-generated cue summary remains visible in the detail view regardless of whether a label is set.
The label is also the primary searchable and scannable identifier, making "add to existing" fast to navigate.

### Inbox Actions

From the inbox the user can:

-  Tap any capture to see full detail – text, photo, audio player, all cues and their status.
-  Snooze a surfaced capture – sets a new cue from a quick-option menu or free picker.
-  Complete a surfaced capture with one of two verbs – Done (✓), or Already Done (✓+) when the user had already handled it
   before the reminder. Both log a Completion and move the capture to the archive; Already Done also marks the completion
   unaided. See Completion and Acknowledgment.
-  Dismiss a capture without completing it – releases it to the archive with no completion logged, for when the user no longer
   intends to act on it.
-  Edit any capture's body, photo, or cues.
-  Delete a capture permanently.
-  Search all captures by text content.
-  Select multiple captures and merge them – combines their bodies in order, unifies their cues (OR across all), and deletes
   the originals. Useful when several captures have accumulated for the same upcoming context and the user wants a single
   consolidated notification. Each captured body becomes one top-level bullet in the merged result. Plain text bodies are
   wrapped in a single bullet. Bodies that already contain list structure are preserved intact and nested under a top-level
   bullet whose header is the capture's label name and creation timestamp, formatted inline as: "dermatologist, 2026-02-03 11:53
   AM". If no label is set, the timestamp alone is used. This format is always deterministic and unambiguous even when labels
   repeat. No existing structure is flattened. After merge the user is dropped into an edit view to review and adjust the
   combined body before committing.
-  Add a new capture to an existing one – at capture time, instead of creating a new capture, the user can select "Add to
   existing." A searchable list of existing captures opens, browsable by label or cue summary. Selecting one appends the new
   body as a bullet item to the existing capture's body. No new cue is created; the existing cues remain unchanged.

---

## Archive

Completed and dismissed captures move to the archive.
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
It does not appear for datetime cues – repeated datetime surfacing is expected for recurring-style captures and carries
less signal.
It applies to geofence, Wi-Fi, Bluetooth, charger, DND mode, and NFC cues – all context-based cue types where repeated surfacing without action is meaningful signal.
The threshold of 2 is a starting point; adjust after observing real usage patterns.

This prompt serves a secondary function: it naturally surfaces candidates for dismissal when the user is approaching the
20-region IOS geofence cap, without ever exposing the cap to the user as a technical constraint.

### Phone Off or Rebooted

Time-based cues whose fire time passed while the phone was off are evaluated on next boot.
Any whose time has passed are surfaced immediately on next app open.

Geofence cues that fired while the phone was off are unrecoverable – the device was not present to detect the boundary
crossing.
This is an honest limitation of on-device geofencing and is noted in onboarding.
The user should set a fallback datetime cue for any geofence capture where missing the cue would be a significant
problem.

---

## Do Not Disturb

A global, momentary notification gate.
Not the same as snooze, which operates on individual captures.
DND says: I am not available right now – hold everything.

The user was just pulled into an unplanned meeting.
A timer is about to go off.
They need one tap to suppress all incoming notifications until they are ready.

### What It Does

DND is a delivery gate, not a state change.
While active, cues continue to evaluate normally.
If a cue fires and all conditions pass, the capture moves to `surfaced` and the badge count increments –
but no notification is delivered to the lock screen or notification center.
The capture is waiting in the inbox when the user is ready.

DND does not affect cue scheduling, capture status, or the cue evaluation pipeline.
It is purely a notification delivery gate, applied at the moment of delivery.

### Activation

One tap from anywhere in the app.
DND is a persistent element in the navigation chrome – not buried in settings.
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
-  The selected duration expires – DND lifts automatically, no interaction required.
-  The user taps the DND indicator to turn it off manually.
-  The app is force-quit and relaunched – DND does not survive a full app termination.
   A session that ends is assumed to have ended for a reason.

### Visual Treatment

While DND is active, a persistent indicator is visible in the navigation chrome –
unambiguous, not alarming, dismissible with one tap.
The indicator shows the remaining duration if a duration was set: "DND – 47 min remaining."
Manual DND shows: "DND – tap to end."

The app badge continues to reflect surfaced captures during DND.
The user knows things are waiting; they chose when to look.

### Held Notifications on DND End

When DND lifts, no flood of notifications is delivered.
Captures that surfaced during DND are already in the surfaced section of the inbox.
The badge count already reflects them.
No additional notification is sent – the inbox is the retrieval surface, not the lock screen.

### Auto-Dismiss Interaction

A capture with `autoDismiss` set to true normally archives itself when the notification is tapped.
If that capture surfaces during DND, the notification is never delivered, so the tap never occurs.
The capture remains in `surfaced` state – it does not auto-dismiss.
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
This is consistent with the captures-stay-on-device philosophy – the user controls their data and can take it with them.

---

## Settings

-  Default cue – the quick option shown first in the cue picker (default: tomorrow morning)
-  Default "tomorrow morning" time (default: 8:00 am)
-  Auto-dismiss default – whether new captures default to auto-dismiss on or off
-  Completion acknowledgment – whether the Already Done confetti and Done check animations play (default: on)
-  Progress screen – show the optional, private completion and unaided-count screen (default: off; the underlying logging is
   always on regardless, see Completion and Acknowledgment)
-  Audio retention preference – keep all, keep none, ask each time
-  Saved Locations – manage, edit, delete
-  Saved Wi-Fi Networks – manage named networks used as cue SSIDs
-  Saved NFC Tags – manage named tags; rename, delete, reassign
-  Storage usage summary
-  Export all data as JSON
-  Do Not Disturb default duration – the duration pre-selected when DND is activated (default: 1 hour)
-  Diagnostics – send anonymous crash and error diagnostics (default: on); a single toggle turns it off. No identifier, no
   capture content (see Diagnostics and Telemetry)

---

## Diagnostics and Telemetry

Captrieve sends anonymous diagnostics to find and fix crashes and errors.
This is the only telemetry in the app, and it is deliberately minimal.

**What a diagnostic report contains.**
-  The error or crash type and the code path where it occurred (stack trace).
-  The app version and build.
-  The operating system and version.
-  The device model.

**What a diagnostic report never contains.**
-  No account, user ID, device ID, or any stable identifier that ties a report to a person or to that person's other reports.
-  No capture content – not the text, not the audio, not the transcription.
-  No cue content – no locations, geofences, Wi-Fi names, NFC tags, Bluetooth devices, or times.
-  No completion, progress, or score data – not what was done, and not the count of what the user handled unaided.
-  No presence events and no connection graph.
-  No advertising or analytics SDKs, and no third-party trackers.

**Default and control.**
Diagnostics are on by default and turn off with a single toggle in Settings.
The toggle takes effect immediately.
This is the one piece of data flow in the app that is on without an explicit per-use choice, and it is named here and on the
consumer-facing Privacy Promise precisely so that it is never a surprise.

**Disclosure.**
The diagnostics behavior is declared accurately in the App Store and Google Play data-collection labels: diagnostic data, not
linked to the user's identity, not used for tracking.
The store labels and this section must say the same thing.

**Why on by default.**
A crash stream that almost no one opts into is not worth building, and the early releases are when crash data matters most.
The reports carry nothing personal and nothing captured, so the cost to the user is negligible while the benefit to product
quality is real.
If this default is ever judged to sit uncomfortably against the Privacy Promise, the honest alternative is opt-in accepted at
first launch – a product decision, recorded here as the fallback.

---

## Open Items

-  Connected Tier backend – select infrastructure provider and define minimum viable backend: push notification routing
   (FCM for Android, APNs for IOS), end-to-end encrypted relay for presence events and shared captures, presence log
   storage. Scope must stay narrow – no general sync, no data platform. Define data retention and deletion policy before
   launch.
-  Connected Tier account model – define minimum account: email, device registration, connection graph. No profile, no
   social features. The account exists solely to identify the user to the routing layer.
-  Subscription pricing – validate $2.99/month and $24.99/year against projected backend infrastructure costs before
   committing. Confirm App Store and Google Play subscription mechanics. (Cancellation mechanics now specified: the stores
   control cancellation, it stops renewal only, and access runs to period end – see Disconnect Versus Cancellation. Pricing
   validation against infrastructure cost is still open.)
-  Which geofencing Flutter package – `native_geofence` is the current candidate and the reliability spike (background,
   terminated, low-power, reboot scenarios) is underway. IOS limits the number of simultaneously monitored regions and may
   defer delivery in low-power mode. Commit only after spike results are in; do not build cue UI before then.
-  Transcription – on-device only, or optional cloud transcription for accuracy? On-device preferred given the
   captures-stay-on-device principle; evaluate quality on both platforms first.
-  Snooze options – confirm quick-option menu: 1 hour, tonight, tomorrow morning, plus free datetime picker.
   For captures whose cue fired on a location event (geofence, Wi-Fi, NFC), the snooze menu should offer location-based
   options first – "snooze until I arrive here again," "snooze until I get to [another saved location]" – before time options.
   Offering time-only snooze on a location-triggered capture is a UX mismatch; the capture surfaced because of context, and
   the natural deferral is back to that context.
-  Wi-Fi cue – confirm CNCopyCurrentNetworkInfo entitlement approval process for App Store submission. Evaluate whether
   SSID is accessible in background on both platforms without foreground requirement.
-  Bluetooth cue – evaluate flutter_blue or equivalent for device name enumeration and RSSI scanning in background.
   The BLE proximity state machine is specified (see bluetooth_connect section); spike must validate the tuning
   parameters (ENTER_THRESHOLD, EXIT_THRESHOLD, WINDOW_SIZE, ENTER_STABILITY, LOCKOUT_DURATION) against real
   device behavior across car audio and stationary speaker scenarios.
   Confirm continuous RSSI scanning battery impact is acceptable; if not, evaluate scan interval strategies
   (e.g. coarse scanning in OUTSIDE state, finer scanning once in proximity) to reduce drain.
-  Charger cue – confirm charger connect/disconnect event availability in Flutter on both IOS and Android.
   On IOS, evaluate UIDevice.BatteryState notifications or equivalent.
   On Android, evaluate BatteryManager broadcast receiver.
   Confirm the time_after AND condition is surfaced prominently in the cue picker for charger triggers –
   without a time gate the cue is too noisy for most users.
-  DND/Focus mode cue – confirm Focus framework access on IOS (entitlement requirements, App Store review implications).
   On Android, confirm NotificationManager DND state change broadcast availability.
   Evaluate whether different named Focus modes (Work, Personal, Sleep) should be individually selectable in the
   cue picker or treated as a single "any Focus mode" event.
-  NFC cue – evaluate flutter_nfc_kit or equivalent. Confirm tag write/read flow on both platforms.
   The abstraction model is specified (see nfc_checkin section): the cue layer receives a single confirmed-read event;
   all hardware detail is below the abstraction boundary.
   On IOS, confirm the intentional tap model via NFCReaderSession is sufficient and background NFC scanning is not
   needed. On Android, confirm the more permissive background NFC behavior does not introduce spurious read events
   that would require debouncing.
-  NFC tag library UI – design the saved tag list analogous to saved Locations. Confirm naming and reuse flow before building.
-  Launch affordances – specify the fast-entry paths the manual now recommends, as the mitigation for the IOS foreground-scan
   constraint: an App Shortcut / App Intent so the iOS Action button (iPhone 15 Pro+) and the Apple Watch Ultra Action button
   can open Captrieve or begin a scan; an iOS Control Center control (Controls API, iOS 18+) and an Android Quick Settings
   tile (TileService – can startActivity over the lock screen, or unlockAndRun) for one-swipe access from the lock screen;
   and home-screen icon placement guidance for devices with no button. Design alongside the NFC identity-model decision,
   since the two together define the real-world tap experience.
-  Wearable scope – capture and retrieval on Apple Watch and Wear OS are wanted, but scope them realistically. Third-party
   NFC tag scanning is not available on either Apple Watch (Core NFC is iOS-only; watch NFC is reserved for Apple Pay) or
   Wear OS (reader-mode NFC is not exposed to apps), so a watch cannot be a tag-scanning surface. The watch role is fast
   voice capture and acting on surfaced retrievals. Model: the watch records a voice note and may set a cue on it; the
   capture and its cue migrate to the phone at the first opportunity, where transcription and storage happen. If the phone is
   not present, transcription is deferred until the watch reconnects to the phone. A tag cue set on the watch can only point
   at an already-registered tag, drawn from the saved tag library, since the watch cannot scan a new one – which is a further
   reason the tag library is a first-class object analogous to saved Locations. This also softens the current Platform line
   "the phone is always present; other surfaces are not" – if wearables become a target, that stance moves from phone-only to
   phone-first.
-  "Next time I open the app" cue – flag checked on foreground resume; confirm sufficient before building around it.
-  Geocoding package for flutter_map – evaluate options for place name, city, and airport search.
-  Radius slider UX – confirm range and step behavior feels natural across neighborhood, city, and airport-approach scales.
-  TTS playback – text-to-speech reading of capture body on notification or detail view open. Low implementation cost,
   high value for elderly users, drivers, and anyone in a hands-free context. Global preference with per-capture override.
   Auto-enable heuristic when a Bluetooth audio device is connected warrants evaluation. Tier decided: free, all tiers
   (see Open Questions). Distinct from audio playback on retrieve: TTS reads the transcript aloud, audio playback plays the
   original human recording, and the two do not stack (see Audio playback on retrieve).
-  Voice cue input – natural language voice input for cue setting ("the cue for this is when I leave the
   house") as an alternative to tapping the cue picker UI. Requires constrained NLP parsing against the cue model.
   Keeps the capture flow in one modality (voice throughout). Should be an enhancement to the cue picker, not a
   replacement – some users will always prefer tapping. Tier decided: Connected-only (see Open Questions).
-  Natural language location resolution – when a voice or text cue references a place name the app does not
   recognize, prompt the user to identify it: "I don't have a location called 'house'. Do you want to identify it by
   Wi-Fi, map, or NFC?" Once resolved, the name is saved as a named Location. Falls out of voice cue input but
   applies to text input as well.
-  Multi-signal Location model – the Location entity currently carries lat/lng/radius. It should also carry optional
   wifiSsid and nfcTagIds fields, all pointing to the same named place. Any signal sufficient to identify arrival or
   departure. This makes location identification redundant and reliable in a way no single method is. Requires data model
   change and cue evaluation logic update. Design before building cue types that reference Locations.
-  Recurrence and satisfaction windows – the recurrence model is now specified (see Recurrence and Recurring Reminders): any
   datetime cue can repeat on an RRULE subset with a start and an end, and a satisfaction window is one occurrence period.
   Remaining before build: confirm the RRULE subset is expressible with the chosen Flutter scheduling and notification
   packages across reboot and low-power deferral, since each occurrence is scheduled one at a time; decide how far the
   "Repeats" quick options go before falling to a custom rule; and confirm the per-occurrence satisfied-flag persistence
   introduces no regression in the otherwise point-in-time cue evaluation path.

---

## Open Questions

These questions were deferred for later decision and must be resolved before the relevant features are built.

**Upgrade prompt copy.**
The Solo upgrade prompt fires when a user reaches for a paid cue, tries to add a sixth NFC tag or a third Wi-Fi network, tries
to combine cues at all, or attaches a satisfaction cue for the first time.
The exact wording is to be finalized before launch.
The principle is fixed: fire at a moment of demonstrated value, name what just happened, then ask for money.

**Recurrence in the free tier.**
Decided: basic recurrence – one datetime cue repeating on a rule, with a range – is free, and composing a recurring reminder
with a satisfaction cue or any condition is Solo.
Confirm this reads as generous rather than as a downgrade of the old "date and time, unlimited" promise, and that the free
"Repeats" quick options are rich enough to feel complete on their own.

**Satisfaction cue default mode.**
Suppression mode is hard or soft per capture: hard silences the reminder, soft surfaces it pre-marked for a quick confirm.
The decided default is soft for anything health-related and hard available everywhere, but the exact rule for when the app
pre-selects soft – by keyword, by category, or only on explicit user choice – is open, and must avoid both silently dropping a
real medication need and nagging through a done dose. Resolve alongside the recurrence model (see Open Items).

**Advanced voice cue input and TTS – implementation sequencing.**
Both are decided (voice cue input is Connected-only; TTS is free).
Neither is designed or built yet.
Voice cue input requires NLP work and should be spiked before committing to a timeline.
TTS is straightforward and can be added to v1 if time allows or v1.1 otherwise.

**Connected Tier backend provider.**
Select infrastructure provider and define minimum viable backend before Connected Tier design begins.
See Open Items for full detail.

**Caregiver page.**
Built. Content defined in the Connected Tier section.
Review copy against Connected Tier spec before Connected Tier launch.

**Site revision.**
Resolved June 2026: the multi-page site is built.
Current pages: Home, Cues, Inspirations (creative people), ADHD, Compare (side-by-side task comparison),
Caregivers, Pricing, Privacy, FAQ.
The pricing table reflects the three-tier model.
Remaining sync obligations: keep the FAQ seed list in this spec and the live faq.html aligned as both grow,
and implement the search behavior the FAQ section of this spec requires – the page currently has no search.

---

## Monetization

The model has one rule behind it: never gate the core capture-and-retrieve loop, and never cut a user off from a thought
they already captured.
Payment gates power and breadth, not the basic act of remembering.
This keeps the free tier strong enough to spread, and strong enough to serve fragile users with dignity, while giving
committed users a fair upgrade at a moment of demonstrated value.

### Free Tier (forever, unlimited core)

Captrieve is free to download and free to use indefinitely, with no account and nothing leaving the device.
The free tier includes the entire capture-and-retrieve loop with no retrieve cap.
There is no lifetime limit on remembering, and a capture and its retrieval are never walled.
The free tier includes the cues a casual or older user actually reaches for, each on its own without composition: a date or
time, the next time you open the app, an NFC tag tap, and a Wi-Fi network.
A date-or-time cue may repeat on a schedule and run for a bounded range, since a recurring reminder is part of the everyday
loop, not a power feature.
Composing that recurring reminder with a skip-if-done cue or any other condition is where Solo begins.
Two of these carry a volume cap rather than a capability gate: up to five NFC tags and up to two Wi-Fi networks, with date,
time, and app-open unlimited.
The cap counts physical tags, not captures, so a single tag can carry many captures and a single Wi-Fi network many cues.
This is generous for everyday use.
A user with tags on the medicine cabinet and the kitchen, wake and bedtime reminders set by time, and the home and one regular
place on Wi-Fi lives entirely inside the free tier and never pays, which is the intended outcome.

### Solo Tier (one-time, about $10)

Solo is a one-time purchase that unlocks the power tier.
It adds three things:

-  The rest of the cues: geofence arrival and departure, Bluetooth connect and disconnect, charger connect and disconnect, and
   Focus or Do Not Disturb. Geofence sits here in v1 because keeping it out of the free tier means the free app needs no
   always-on location permission, which keeps it simpler, lighter on battery, and clear of the platform background-location
   review.
-  The composition layer: AND conditions, delays, satisfaction cues ("skip if done"), and more than one cue on a capture with
   OR across them.
-  Removal of the free volume caps: unlimited NFC tags and unlimited Wi-Fi networks.

These are the features a casual user rarely reaches for and a committed user reaches for constantly.
They are also the most expensive features to build and to keep reliable across devices, so charging for them is honest rather
than extractive.

The upgrade prompt fires at the moment of demonstrated value, which is the spec's standing monetization principle: fire when
the user has just felt the value, name what just happened, then ask for money.
Four moments trigger it.
The first is when a user reaches for a paid cue type, such as a geofence or a Focus cue.
The second is when a user tries to add a sixth NFC tag or a third Wi-Fi network.
The third is when a user tries to combine cues at all, with an AND condition, a delay, or a second cue OR'd onto a capture.
The fourth is the first time a user asks a reminder to skip itself when the thing is already done, by attaching a satisfaction
cue.
In every case the user has reached past the everyday product on their own, so the ask lands on someone already getting more out
of Captrieve than the average user, which is why this converts at a real rate rather than a rounding error.

Pricing is about $10, in the non-brainer zone where the cost of deciding exceeds the cost of buying.
The exact figure is to be finalized before launch.

### Connected Tier (subscription, $2.99/month or $24.99/year, Family Sharing on)

Connected is the opt-in sharing and caregiver layer, described in full in the Connected Tier section.
It is the only part of Captrieve with a real backend and therefore a real ongoing cost: push routing, the end-to-end encrypted
relay, and presence-log storage.
A subscription is the honest model for it, because it recovers an ongoing cost rather than charging rent on a local feature.
Cloud-backed sharing is also a sanctioned subscription category on both stores.

Connected includes the Solo power tier while the subscription is active, so a subscriber never has to buy Solo separately.
After any twelve consecutive months of Connected subscription, the Solo power tier is permanently unlocked for life, even if
the subscription is later canceled.
The reassurance is stated plainly on the pricing page rather than buried: if you ever stop your subscription, everything you
have unlocked stays yours, the connected features pause, and everything else continues.

Family Sharing is enabled on the Connected subscription.
This directly serves the core caregiver case and the author's own case: a single Connected subscription covers the subscriber
and up to five family members, so one payment connects a parent and an adult child with no second account and no second
charge.

### Optional Supporter purchase (a topper, not the plan)

A voluntary Supporter purchase exists for users who want to support development beyond what they have paid, or who are happy on
the free tier and want to give something back.
It unlocks nothing functional.
It grants only a thank-you: a supporter badge, an alternate app icon, and a line in the about screen.
It is offered at two or three fixed price points, for example about $2.99, $6.99, and $14.99, to approximate pay-what-you-want
within the stores' fixed-tier pricing.
It is a non-consumable with Family Sharing enabled, so it restores across devices and reads as owned rather than rented.
The App Store permits in-app purchases whose purpose is to tip the developer, so this is compliant.
The Supporter purchase catches goodwill on top of Solo and Connected.
It does not carry the model, and the model does not depend on it.

### Reinstall and the absence of server enforcement

There is no retrieve cap and no trial to circumvent, so the old reinstall concern is moot for the free tier.
The Solo unlock and the Supporter purchase are store purchases restored through the platform, so they survive a reinstall on
the same account without any server of ours.
This keeps the privacy posture intact, since no server is introduced to police entitlement.

### What this retires

The previous model gated the core loop with a twenty-retrieve lifetime cap and sold Solo as the removal of that cap.
That cap punished the core action and hit the most fragile users at the worst moment, so it is removed entirely.
Solo is redefined from "uncap retrieves" to "unlock the power tier and lift the volume caps."
The retrieves-not-captures rationale and the trial-circumvention reasoning that depended on the cap are retired with it.

The free-to-paid boundary was then revised once more.
An interim model put a uniform five-active-cue cap on the free tier, kept place and geofence in the free tier, and treated
Wi-Fi as a paid power cue.
The current model replaces the uniform cap with per-type volume caps that match how people actually set the product up, five
NFC tags and two Wi-Fi networks, moves Wi-Fi into the free tier where it is cheap and reliable, and moves geofence into the
paid tier.
The geofence move is the load-bearing one: it is what lets the free app ship with no always-on location permission, and it
also puts the paid wall on composition and breadth rather than on geofence, which competitors already give away.
Geofence may migrate to the free tier in a later version, since users expect location reminders to be free.
That change is deliberately deferred to after the Android launch, because a free tier with geofence re-adds the always-on
location permission and the platform background-location review that the v1 design was structured to avoid, and that cost is
worth taking on only once there is a proven product and momentum to carry it.


---

## Calendar Integration (Not Committed)

Calendar integration is not committed for any version.
It is recorded here to preserve the thinking and surface the reasons it was deferred.

The basic concept: a resolver sitting above the datetime cue that finds the next appointment matching a criterion –
attendee email, title keyword – and writes a datetime cue, then rewrites it if the appointment moves or is canceled.
The data model does not need to change; the resolver is a layer above it.

**Why it was deferred.**
The matching criteria that seem simple are not.
"The next appointment with Dr. Reyes" works cleanly until there are two people named Reyes, or the appointment is a
recurring series, or it is an all-day event, or the next match is six months out.
Edge cases around recurring events, cancellations with no replacement, and appointments matching multiple criteria
pile up quickly.
The on-device polling path (background task scheduling) is best-effort and cannot guarantee timely cue updates –
a cue that might silently be stale is a credibility problem for the product.
The backend polling path (Connected Tier) solves the reliability problem but scopes the feature to subscribers and
introduces OAuth credential management, calendar API integrations across three providers, and ongoing maintenance
surface.
The implementation cost is high relative to the use case, which is already partially served by setting a datetime
cue manually against a known appointment time.

**If this is ever revisited.**
The correct sequencing: define the matching model exhaustively first, including all edge cases, before writing any
integration code.
The resolver architecture (datetime cue as the output, calendar as the input source) is sound and does not require
rethinking.
The on-device vs. backend polling decision should be made once, not both paths built speculatively.

---

## Connected Tier (v2, Subscription)

The Connected Tier allows Captrieve users to share presence, captures, and context with people they trust and who also have
Captrieve.
It is not a social network, not a group chat, and not a collaboration tool.
It is a small, intentional network of people who have chosen to be connected – family, caregivers, partners, close colleagues.

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
Presence is built on passive signals first, so the feature works for the person it is meant to serve, someone who cannot be
relied on to remember a gesture, since not remembering is the reason they are connected in the first place.
The primary signals ask nothing of the care recipient.
A home geofence reports leaving and returning directionally and on its own.
A home Wi-Fi join or leave corroborates it cheaply and works indoors where GPS is weak.
First phone activity in the morning, or the overnight charger coming off, reads as up and about.
A scheduled reminder firing, such as the ten o'clock medication reminder, is itself a visible signal that the reminder went
out, which is distinct from any claim that it was acted on.
Connected people receive these as notifications and see them in the log: "Left home at 10:47 AM." "Home by 1:23 PM." "Up and
about this morning."
An NFC tag tap can also be designated as a presence event, an optional active check-in for users who keep that habit, but it
is never the backbone, because a tap is directionless and a missed tap means nothing.
That is the load-bearing honesty of the feature.
Every signal tracks the phone, not the person, and none of them asserts that the care recipient is fine.
What the system delivers is the rhythm of an ordinary day, and the actionable event is its absence.
A day that stays silent is the caregiver's cue to call and check in, which is the design working as intended rather than a gap
in it.
This serves the families and caregivers who want to know an elderly person is moving through their day, without the
surveillance feel of continuous GPS tracking, and without pretending to a certainty the technology cannot provide.

**Presence log.**
Connected people see a timestamped log of presence events, not just real-time notifications.
Today, yesterday, last week.
The log makes patterns visible – "usually leaves by 10, it's noon" – without requiring automated alerts.
Anomaly detection is human, not algorithmic.
Automated anomaly alerts are a future option, not in scope for v2.

**Shared captures.**
A user can send a capture to a connected person.
The recipient receives it as a capture in their own inbox, with whatever cue the sender set – or they can set their own
cue on receipt.
This is genuinely novel.
No messaging app does this.
"I captured something I want you to see when you get to the store" is not a text message – it is a context-cued
delivery that finds the recipient when it is actionable.

**Capture forwarding.**
"Remember to pick up the dry cleaning on your way home" – captured by one person, forwarded to another, cue set to fire
on the recipient's departure from their office.
The sender sets the cue on the recipient's behalf, or leaves it for the recipient to set.

**Push notification routing.**
All connected features – presence events, shared captures – are delivered as push notifications to the recipient's device
via the backend.
No SMS. No carrier dependency. No per-message cost to the user.
The backend routes the notification; the recipient's device delivers it locally.

### What Connected Messaging Is Not

There is no chat thread.
There is no reply within Captrieve.
A shared capture is a one-way delivery.
If the recipient wants to respond, they use whatever communication channel they already use with that person.
Captrieve does not try to own the conversation – it delivers the thought at the right moment and gets out of the way.

**Shared captures are text by default, and audio when the voice is the point.**
By default a shared capture is delivered as transcribed text, which keeps the common case lean for the small-payload backend.
The sender records by voice as normal, the capture transcribes on-device, and the text is what travels.
This spec previously declared text-only a permanent decision.
That decision is now revised, because a genuine use case has emerged of the exact kind the prior text invited a revisit for: a
caregiver recording in their own voice for someone who needs to hear it, where transcription strips the one thing that
matters.
A sender may therefore opt a shared capture into carrying its audio.
That audio travels end-to-end encrypted like all shared content, is bounded in length to respect the backend's payload
discipline, and remains the deliberate exception rather than the default.
The cost concern that motivated text-only still holds for the common case, which is why text stays the default and audio is a
per-capture choice the sender makes when the voice is the reason for the message.
If the recipient is offline, the audio is delivered when their device next syncs, the same deferral the rest of the connected
flow uses.

**The infrastructure discipline principle.**
The existence of the backend is not an invitation to build features that belong to other products.
Each Connected Tier feature must justify itself as an extension of capture and retrieval – not merely as something the
infrastructure could support.

A shared grocery list was considered and rejected on exactly this basis.
A persistent, multi-author, collaboratively maintained list is a different product with dominant competitors (AnyList,
OurGroceries, Apple and Google shared lists).
Building it well requires item deduplication, category organization, checked-off state, list clearing after a shop – a
whole product surface with nothing to do with capture and retrieval.
The correct Captrieve answer to "remind my spouse we're low on ketchup" is a transcribed voice capture forwarded to her
with a grocery store geofence cue.
That is Captrieve doing what Captrieve does – a thought that finds her at the right moment.
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
Their trained behavior may be as simple as a single physical gesture – tapping a named NFC tag – which surfaces whatever
the caregiver has prepared for that context.
Example: a nightstand NFC tag, tapped at bedtime, surfaces: medications taken, door locked, phone charging.
The caregiver set this up.
Granny taps the tag.

The caregiver page on the website (to be written) will illustrate this role in detail with concrete examples, for both
marketing purposes and user reference.

**The caregiver's own captures in the care recipient's space.**
NFC tags placed in the care recipient's home serve both people independently.
The same physical tag, read by different phones, surfaces different captures for each person – the tag is just an
identifier; what surfaces depends entirely on whose phone is reading it.
The caregiver walks into the care recipient's home and taps the kitchen tag – their own Captrieve surfaces whatever they
set for that context: "ask about the medication side effect she mentioned Tuesday," "she wanted to show you the photos from
the reunion," "check whether she has enough of the blood pressure medication."
They tap the front door on the way out – "call the visiting nurse service before end of day."
The caregiver does not have to remember what to ask, what to check, what to bring up.
Their own captures surface at exactly the moment they are standing in the relevant context.
This is the caregiver using Captrieve for themselves, inside a space they share with the person they care for.

**Presence events and the presence log.**
A care recipient connects a caregiver – a family member, a professional, a friend.
The caregiver sees:
-  The care recipient's pending and surfaced captures and cue status, in real time.
-  The presence log: arrivals and departures, morning activity, scheduled reminders firing, and any optional tag check-ins,
   the rhythm of a normal day where a silent day is the cue to call.
-  Shared captures the care recipient has chosen to send them.

The care recipient sees no added complexity.
Their capture flow is unchanged.
The sharing layer is invisible to them in normal use.

The caregiver-facing view is richer and may eventually be web-accessible, since caregivers often want to check on a laptop
rather than a phone.
Web access to the caregiver view is a near-term backend priority once the Connected Tier is established.

This feature directly addresses the autonomy concerns of people navigating cognitive decline or memory impairment.
GPS-based tracking feels surveillant because it is passive – it watches without the person's ongoing participation.
The NFC tap model is different: the person is an active participant in their own monitoring.
The tap serves them (check-in habit, captures surfaced) and incidentally informs their caregiver.
That distinction matters to the population and to the families supporting them.

### Privacy Architecture

The Connected Tier is the intentional exception to the captures-stay-on-device principle.
The exception is narrow and explicit: data leaves the device only when the user has chosen to share it with a specific named
person.

All data in transit and at rest on the server is end-to-end encrypted.
The server holds ciphertext only and cannot read capture content or presence events.
This is true end-to-end encryption, not encryption the server could undo – the claim that there is nothing for us to read is
literal.

**Keys are generated on the device.**
Each device generates its own encryption keypair.
The private key never leaves the device.
The server stores only public keys, as a versioned directory: to share with a connected person, the sender encrypts to that
person's current public key, looked up from the directory.
The server never holds, escrows, or issues a private key, and so has nothing that could decrypt a user's data.

**Key backup and recovery.**
The private key is backed up through the platform's secure keychain – iCloud Keychain on IOS, the Android Keystore with the
platform's encrypted backup.
Reinstalling on the same Apple or Google account restores the key, and shared data becomes readable again with no extra step.
This replaces any notion of a server-issued key that could be lost when an account is deleted, which was the earlier and
weaker model.

**Key change and re-sharing.**
If a key is lost beyond recovery, or deliberately reissued, the device generates a new keypair and publishes the new public
key to the directory, which bumps its version.
Anything that person previously received was encrypted to the old public key, so it cannot be read with the new private key –
those items show as unavailable, waiting to be re-shared.
The asymmetry that makes this tolerable: the sender of any shared item still holds the original in plaintext on their own
device, and the versioned directory lets senders detect a stale peer key and re-encrypt to the new one.
Re-sharing restores access, and nothing a user authored themselves is ever lost to a key change.

The backend is scoped narrowly: a versioned public-key directory, push notification routing, encrypted relay for presence
events and shared captures, and presence log storage for connected pairs.
It is not a general sync service, not a cloud backup, not a data platform.
That scope is a product decision, not a resource constraint, and should be enforced as the Connected Tier evolves.

### Disconnect Versus Cancellation

These are two different actions and the product must not conflate them.
Disconnect is immediate and about privacy.
Cancellation is about billing and, because the stores control it, is not immediate.

**Disconnect – immediate, in-app, per connection.**
A user can sever any connection at any time, from inside the app, with immediate effect.
On disconnect: presence sharing stops at once, the former connected person's access is revoked, and the data shared with them
is purged from the server.
This is the privacy kill-switch, and it is independent of billing – a user can disconnect a person while keeping the
subscription, and the subscription can lapse without anyone needing to disconnect first.
This realizes the Connected design principle that any connection can be severed at any time, immediately, with no residual
data on the server.

**Cancellation – billing, through the store, effective at period end.**
A Connected subscription is billed through the App Store or Google Play, and only the store can cancel it.
The app cannot cancel a subscription programmatically – the most it can do is deep-link the user to the system subscription
settings.
Canceling stops the renewal; it does not end access mid-period.
The subscriber keeps Connected features until the end of the period already paid for, and only then does access end.
Cancellation is therefore not a privacy mechanism – a user who wants sharing to stop now uses Disconnect, which is immediate.

**What happens when access actually ends, at period end, if not renewed.**
-  Connected features stop: presence events and shared captures are no longer routed, and connected people see that the
   sharing relationship has ended.
-  The user's local captures and cues are fully unaffected – the Solo experience continues normally.
-  If the user completed 12 consecutive months of Connected subscription, Solo is permanently unlocked at no further cost,
   automatically and with no action required.
-  If the user has not reached 12 months, they revert to the free tier (the everyday cues within the free volume caps, no
   retrieve cap) unless they separately purchased Solo.
-  Presence log history held on the server is deleted within 30 days of access ending.

Data held server-side for an active connection is purged immediately on an explicit Disconnect, as above.
The 30-day window covers presence log history in the case where a subscription simply lapses without an explicit disconnect.



---

## Deferred

-  Web companion interface – deferred for v1 and v2 solo tier, but a caregiver-facing web view is a near-term priority
   once the Connected Tier backend is established. The backend already exists at that point; the web view is an interface
   question, not an architecture question.
-  Video capture
-  General sharing or collaboration – not a priority
-  Cloud sync or backup
-  iCloud / Google Drive export
-  Recurring captures (e.g. "remind me every Monday morning")
-  Watch app
-  Proximity-to-person cue via Bluetooth LE – detecting that a connected Captrieve user is physically nearby and
   triggering a capture on that event. Technically feasible with mutual consent and both parties running the app and
   subscribed to the Connected Tier. The adoption dependency is solved once a subscriber network exists. Deferred to v3
   or later; the connected messaging infrastructure in v2 is the prerequisite.
-  Large-radius geofence as an en-route cue – using a generous geofence radius (e.g. 5–15 km around home) to fire a
   capture while the user is still in transit rather than on arrival. This is not a new cue type; it is a configuration
   pattern that should be documented explicitly with examples. Canonical example: "call home to let my spouse know I'll be
   there in 15 minutes" – set a geofence_arrival on home with a radius of several kilometers so it fires while still driving.
   This pattern should appear in the FAQ and onboarding as a named use case, not left for users to discover.
-  Automated anomaly detection for caregiver presence log – "usually leaves by 10, it's noon, notify caregiver." The
   presence log in v2 makes patterns humanly visible; algorithmic alerting is a natural next step but not in scope for v2.
-  AirTag integration – Apple's Find My network is intentionally closed and exposes no public API.
   There is no way for Captrieve to know when an AirTag-tagged item is nearby or at a location.
   The underlying use cases – bike, car, bag, pet – are served by Bluetooth device cues (bike computer,
   car audio) and NFC tags placed on the same objects.
   Revisit only if Apple opens a relevant API. – finding patterns, groupings, and relationships across a large
   capture history. Only meaningful at scale: the individual user needs hundreds of captures before the signal is useful,
   and the business needs a large enough cohort of creative users to justify AI inference costs and engineering investment.
   The right order of operations is core product first, user base second, this feature third if the cohort warrants it.
   Also carries a privacy tension: on-device inference is feasible but limited; server-side inference requires capture
   content to leave the device, which conflicts with the core privacy positioning and would require explicit opt-in.
   Not to be built speculatively.
