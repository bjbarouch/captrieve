# Captrieve Website Changes – Competitive Honesty Pass (June 2026)

Reason for this pass: Apple Reminders has offered location triggers (arrive/leave geofences, Siri creation, car triggers)
since IOS 5 in 2011, and IOS Shortcuts automations can fire on NFC, Wi-Fi, Bluetooth, and location.
Several lines of current site copy claim or imply that nothing on the user's phone is location-aware.
Any Apple-literate reviewer can refute those lines with a screenshot, and the product's credibility takes the hit at first
contact.
The fix is not to soften the pitch – it is to aim it at the true enemy: tools that make the user do all the work at the
wrong moment.
Bonus tailwind to exploit: Google removed location-based reminders from Android entirely in late 2025 (Keep-to-Tasks
migration).

Three changes below. Each shows exact current text and exact proposed replacement, ready to paste.

---

## Change 1 – index.html hero: fix the attackable paragraph

### Current (the "In desperation" paragraph)

```html
<p class="hero-sub reveal">
    In desperation, you dictate it to Siri or Alexa to put in a note, and forget to ask for it later, or you put it in a
    Google doc, or even a spreadsheet, and it sits there, passively not reminding you.
    It just sits there, unindexed, so it's hard to find if you do remember to go looking for it.
    You put it into a reminder and if you happen to know it will come in handy at 4 o'clock on Wednesday, you have the
    one scenario that's genuinely useful.
</p>
```

Problem: the last sentence asserts reminders are time-only.
Apple Reminders does locations, and has for fifteen years.

### Proposed

```html
<p class="hero-sub reveal">
    In desperation, you dictate it to Siri or Alexa to put in a note, and forget to ask for it later, or you put it in a
    Google doc, or even a spreadsheet, and it sits there, passively not reminding you.
    It just sits there, unindexed, so it's hard to find if you do remember to go looking for it.
    Or you stop what you're doing, open your reminders app, type it in as a task, pick its one trigger, and hope –
    its own users will tell you how often those actually fire.
    And none of that had room for the melody, the phrasing, the half-formed idea.
    A task list was never the right shape for a thought.
</p>
```

What this does: concedes reminder apps exist and have triggers, indicts the workflow (stop, type, configure, one trigger,
hope) and the delivery reliability instead, and lands on the capture-shaped gap no incumbent fills.
Every clause survives a hostile fact-check.

---

## Change 2 – index.html: new section "What about the apps you already have?"

Insert between the "How it works" section and the "Who is Captrieve for?" section.
This is the user-facing competitive landscape: name what they already own, say plainly what it does, and locate the added
value in the drudgery it removes – not in pretending the incumbent doesn't exist.

```html
<!-- ── What you already have ── -->
<section>
    <div class="container">
        <p class="section-label reveal">The apps you already have</p>
        <h2 class="reveal">
            Your phone already does some of this.<br>
            <em>That's not the problem.</em>
        </h2>
        <p class="section-lead reveal">
            Apple's Reminders app has location triggers – it has since 2011.
            Shortcuts can fire on an NFC tag, if you enjoy building automations.
            We'd rather tell you that ourselves than have you wonder.
            Here is what each of them asks of you, and where each of them stops.
        </p>
        <div class="cue-cards">
            <div class="use-case reveal">
                <h3>Notes, docs, voice memos</h3>
                <p>
                    Capture, yes – retrieval, never.
                    Everything you save waits for you to remember that it exists and go looking.
                    The thought is preserved and the moment is lost.
                </p>
            </div>
            <div class="use-case reveal">
                <h3>Siri and Alexa</h3>
                <p>
                    Fine for "remind me at 4 on Wednesday" – when you happen to know the when.
                    Most thoughts worth keeping don't come with an appointment attached.
                    They come with a <em>where</em>, a <em>situation</em>, a <em>next time I'm at the piano</em>.
                </p>
            </div>
            <div class="use-case reveal">
                <h3>Apple Reminders</h3>
                <p>
                    A good task list with a trigger bolted on.
                    You stop, type the task, configure its one trigger by hand, and hope – missed and late deliveries are
                    its longest-running complaint.
                    No combinations, no delays, no tags to tap, nothing for the person you care for.
                    And nowhere to put a thought that isn't a task.
                </p>
            </div>
            <div class="use-case reveal">
                <h3>IOS Shortcuts</h3>
                <p>
                    Genuinely capable, for the few who enjoy programming their phone.
                    One hand-built automation per behavior, maintained forever, by you.
                    If that's you – sincerely, enjoy.
                    Captrieve is for everyone else.
                </p>
            </div>
            <div class="use-case reveal">
                <h3>Android</h3>
                <p>
                    Google removed location reminders entirely in late 2025.
                    They cannot be created or received anymore.
                    On Android, this category is simply vacant.
                </p>
            </div>
            <div class="use-case reveal">
                <h3>Captrieve</h3>
                <p>
                    Built around two moments and nothing else: the instant a thought arrives, and the instant it's useful.
                    Speak first, file never.
                    Cue it by place, tag, network, device, time – or combinations, with conditions and delays.
                    The work happens once, at capture.
                    The remembering is no longer your job.
                </p>
            </div>
        </div>
    </div>
</section>
```

Tone rationale: respectful of the reader's existing tools (they chose them), concrete about each one's stopping point,
self-aware enough to volunteer the comparison.
Naming the incumbents' strengths first is what buys the credibility for the last card.

---

## Change 3 – faq.html: fix one entry, add three

### 3a. Revise the existing first entry

Current first sentence of "What's the difference between Captrieve and a reminder app?" is:
"A reminder app fires at a time you set."
Same factual problem as the hero.

```html
<div class="faq-item reveal">
    <p class="faq-q">What's the difference between Captrieve and a reminder app?</p>
    <p class="faq-a">
        A reminder app is a task list: you type a task and configure its trigger – in Apple's case a time, a place,
        or getting in the car.
        Captrieve starts a step earlier, at the thought itself: speak it before it escapes, then cue it by time, place,
        Wi-Fi network, NFC tag, or Bluetooth device – alone or in combination, with conditions and delays.
        More importantly, Captrieve's whole job is capture and retrieval.
        That singularity is what makes it worth developing a habit around.
        Siri and Alexa do reminders alongside a thousand other things.
        When a tool does one thing, you remember to use it.
    </p>
</div>
```

### 3b. New entry – the head-on question

Place directly after the entry above, in "The basics."

```html
<div class="faq-item reveal">
    <p class="faq-q">Doesn't Apple Reminders already do location reminders?</p>
    <p class="faq-a">
        Yes – arriving and leaving a place, since 2011, plus getting in or out of the car.
        If a plain location-triggered task list covers your needs, use it – it's free and already on your phone.
        Here is where it stops.
        It is a task list: you must stop, type the task, and configure its one trigger by hand – there is no capturing a
        voice note, a melody, a half-formed idea.
        One trigger per item: no "when I leave work, but only after 5 PM," no "20 minutes after leaving," no "the store
        or the fridge tag, whichever comes first."
        No NFC tags, no Wi-Fi networks, no Bluetooth devices beyond your car.
        Nothing for sharing a thought that arrives at someone else's right moment, and nothing for the person you care for.
        And its location triggers are famously hit-or-miss – its own users describe delivery as a coin flip.
        Captrieve exists for everything after "yes."
    </p>
</div>
```

### 3c. New entry – the tinkerer's question

```html
<div class="faq-item reveal">
    <p class="faq-q">Couldn't I build this myself with IOS Shortcuts?</p>
    <p class="faq-a">
        A surprising amount of it, yes.
        Shortcuts automations can fire on an NFC tag, a Wi-Fi network, a Bluetooth device, a location, a time.
        What you'd be building is one automation per behavior, by hand, maintained forever – with no capture flow,
        no inbox, no cue attached to a thought, and nothing for anyone but yourself.
        If you build it and it serves you: genuinely, well done.
        Captrieve is the product shape on top of the same phone hardware, for everyone who won't.
    </p>
</div>
```

### 3d. New entry – the Android question

```html
<div class="faq-item reveal">
    <p class="faq-q">I'm on Android – didn't Google have location reminders?</p>
    <p class="faq-a">
        It did.
        Google removed them when Keep's reminders migrated to Google Tasks in late 2025 – they can no longer be created
        or received.
        Captrieve is, in part, what replaces them.
    </p>
</div>
```

---

## Not changed, deliberately

-  cues.html – the datetime card already concedes "Siri and your calendar can do this too" and claims composition as the
   difference, which is correct and survives the fact-check.
   The geofence card claims combination and delay features Reminders lacks – also accurate.
-  The "Who is Captrieve for?" stories and testimonials – no factual claims about incumbents.
-  Pricing and privacy pages – no competitive claims.

## One open question

Whether the new homepage section (Change 2) belongs on index.html or on cues.html.
Argument for index: the objection arrives in the first thirty seconds of a skeptical visit, and burying the answer a click
away wastes the credibility it buys.
Argument for cues: index.html is already long, and the hero rewrite (Change 1) may defuse enough of the objection on its own.
Recommendation: index, but trimmed to four cards (merge Notes/Siri into one, drop the Captrieve card and let the section
flow into "Who is Captrieve for?") if length becomes a concern.
