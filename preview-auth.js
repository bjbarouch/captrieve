const
    PASSWORD  = "pregomartyblah",
    STORE_KEY = "captrieve_access";

function checkAccess() {
    if (localStorage.getItem(STORE_KEY) === "granted") {
        return;
    }
    const
        input = prompt("Password");
    if (input === PASSWORD) {
        localStorage.setItem(STORE_KEY, "granted");
    }
    else {
        document.body.innerHTML = "";
        checkAccess();
    }
}

checkAccess();
