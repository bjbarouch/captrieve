import { safeHtml } from "/js/safeHtml.min.mjs";

/**
 * Displays the requested URL in the 404 page body.
 * @returns {void}
 */
function showRequestedUrl() {
    const
        el = document.getElementById("request");
    if (el) {
        el.innerHTML = safeHtml(location.pathname);
    }
}

showRequestedUrl();
