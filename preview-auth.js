
// Using a hash here is pretending, since anyone reading this scan see how to bypass the password in a second anyway.
// But this is really only intended to indicate that the site is not actually open to the public yet, while allowing
// a few selected people to see a preview and provide feedback -- if someone else sees it, it's not actually a big deal.
// Why not jut use Basic Auth to make this real, you ask.
// Shared host problem? I don't know why, but I couldn't get it to work.
async function sha256Hash(message) {
    const
        encoder    = new TextEncoder(),
        data       = encoder.encode(message),
        hashBuffer = await crypto.subtle.digest("SHA-256", data),
        hashArray  = Array.from(new Uint8Array(hashBuffer)),
        hashHex    = hashArray.map(b => b.toString(16).padStart(2, "0")).join("");
    return hashHex;
}

const
    PASSWORD  = "5e21faf19a41d2e035a191b79a09aa8b99f71abc465ac26a1a22ab4cfc813602",
    STORE_KEY = "captrieve_access";

async function checkAccess() {
    if (localStorage.getItem(STORE_KEY) === "granted") {
        return;
    }
    const
        input = await sha256Hash(prompt("Password"));
    if (input === PASSWORD) {
        localStorage.setItem(STORE_KEY, "granted");
    }
    else {
        document.body.innerHTML = "Incorrect password. Refresh the page to try again.";
    }
}

checkAccess();
