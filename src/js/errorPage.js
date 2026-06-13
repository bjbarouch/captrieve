import { httpCodeMap } from "/js/httpCodes{{min.mjs}}";

const
    errCode   = Number(location.pathname.replace(/.*\//, "").replace(".html", "")),
    httpErr   = httpCodeMap.get(errCode),
    message   = httpErr ? httpErr.description : `HTTP error code ${errCode}`,
    codeEl    = document.getElementById("err-code"),
    messageEl = document.getElementById("err-message");

/**
 * Fills the error code and description into the page.
 * @returns {void}
 */
function showError() {
    if (codeEl) {
        codeEl.innerText = String(errCode);
    }
    if (messageEl) {
        messageEl.innerText = message;
    }
}

showError();
