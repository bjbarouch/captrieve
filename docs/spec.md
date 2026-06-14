# Captrieve Spec

Decisions captured here are the source of truth for behavior described loosely on the website.
This file covers the areas settled so far: Connected encryption, debug telemetry, and Connected cancellation.

## Connected: encryption and key management

The goal is genuine end-to-end encryption, so that the claim "neither we nor an attacker can read your data" is literally
true.
The server must be structurally incapable of decrypting shared content.

### Key generation

- The keypair is generated on the device.
  The private key never leaves the device in plaintext.
- The server never generates or receives a private key.
  A server-generated key, even transiently, means the server has seen it, which defeats the guarantee.
- Only the public key is published to the server.

### The server as a public-key directory

- The server holds a directory mapping each Connected account to its current public key and a version number.
- To share with a participant, an instance fetches that participant's current public key from the directory and encrypts to
  it.
- Instances may cache public keys, but the directory is the source of truth, so a key change propagates instead of leaving
  peers stranded on a stale key.

### Backup and recovery

- The private key is stored in the platform secure keychain with cloud backup enabled: IOS Keychain with iCloud Keychain,
  Android via Keystore plus the encrypted cloud backup path.
- Reinstalling on the same Apple or Google account restores the private key automatically.
  The server is never involved, so end-to-end encryption holds.
- If no backup is available (the user disabled it, or switched ecosystems), the key is treated as lost and the re-key path
  below applies.

### Re-key and re-share protocol

A key change happens on deliberate reissue or on loss without an available backup.

- Asymmetry to be precise about: data you sent was encrypted to your recipients' keys and is unaffected by a change to your
  own key.
  Only data you received (encrypted to your old public key) becomes unreadable.
- On key change: the device generates a new keypair, publishes the new public key to the directory with an incremented
  version, and the server notifies the participants who had shared to this user.
- Those senders re-encrypt their shared items to the new public key.
  They can do this because they still hold the plaintext (it is their own captures).
- Until a sender re-shares, the affected received items display as "unavailable, waiting to be re-shared" rather than
  silently breaking.
- Staleness is solved by the same versioning: peers fetch or are pushed the current version rather than trusting a cached
  key.

### Website claim alignment

- Because the server never holds a private key, "while stored on our server it is encrypted, with nothing to see by us" is
  true.
- This is the reason to generate keys on the device rather than on the server.

## Debug telemetry (diagnostics)

Quality control requires that we learn about bugs users hit, without surrendering the privacy posture.

### What it does

- On an unhandled error or crash, the app sends a diagnostic report.
- Contents: error type and stack trace, app version, OS version, device model, and the code path involved.
- Explicitly omitted: the user identifier, and the contents of any capture, cue, transcript, or location.
- It is diagnostics only, not usage analytics, and not behavioral tracking.

### Defaults and control

- On by default, because the bugs we never hear about are the ones that quietly degrade the experience.
- A single toggle turns it off, with no change to how the app works for the user.
- Disclosed in the App Store and Google Play privacy labels as anonymous diagnostics.

### Claim reconciliation

- The previous absolute claim ("the app sends nothing at all") is replaced by the qualified claim.
- The qualified claim: your captures and personal data never leave the phone, and the only thing the app may send is
  anonymous diagnostics, which carry no identifier and no content, and which you can switch off.
- The login and privacy pages now state this.

## Connected: cancellation and disconnect

These are two different actions and must not be conflated, because conflating them creates a patient-privacy hole.

### Disconnect (immediate, privacy action)

- Disconnecting from a specific person is a single in-app action.
- It takes effect immediately: presence events stop, the other person's access is revoked, and the data shared with them is
  purged from the server.
- It is independent of billing and exists regardless of subscription state.
- This is the correct way to cut off a caregiver or any peer right now, for example for patient privacy.

### Cancellation (billing action)

- Connected is an auto-renewable subscription billed through the App Store or Google Play.
- Cancellation is performed through the store (Apple Account subscriptions on IOS, Google Play subscriptions on Android), or
  via a deep link the app provides to that screen.
  The app cannot cancel a store subscription programmatically.
- Cancellation stops the renewal only.
  Access, and therefore any active connection, continues until the end of the paid period.
- Therefore cancellation is not a privacy mechanism.
  To stop sharing now, use Disconnect.

### Website message

- State the two actions separately and consistently across pricing, FAQ, and privacy.
- Make clear that Disconnect is immediate and Cancel only stops renewal.
