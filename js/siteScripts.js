/**
 * Wires up the IntersectionObserver that drives .reveal scroll animations.
 * Runs on every page. Elements with class "reveal" fade/slide in when they
 * enter the viewport.
 */
function initRevealObserver() {
    const
        observer = new IntersectionObserver(
            function onIntersect(entries) {
                entries.forEach(function onEntry(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                    }
                });
            },
            { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
        );
    document.querySelectorAll(".reveal").forEach(function observeEl(el) {
        observer.observe(el);
    });
}

initRevealObserver();

/**
 * Wires up the collapsed-nav (hamburger) toggle. The button is shown below the
 * inline-fit breakpoint; clicking it opens or closes the nav-links panel and
 * keeps aria-expanded in sync. Closes on link activation, outside click, or Escape.
 */
function initNavToggle() {
    const
        toggle = document.querySelector(".nav-toggle"),
        links  = document.getElementById("nav-links");
    if ( ! toggle || ! links) {
        return;
    }
    /**
     * Opens or closes the nav panel and keeps the toggle's aria-expanded in sync.
     * @param {boolean} open
     * @returns {void}
     */
    function setOpen(open) {
        links.classList.toggle("open", open);
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
    }
    toggle.addEventListener("click", function onToggleClick(event) {
        event.stopPropagation();
        setOpen( ! links.classList.contains("open"));
    });
    links.addEventListener("click", function onLinksClick(event) {
        if (event.target.closest("a")) {
            setOpen(false);
        }
    });
    document.addEventListener("click", function onDocumentClick(event) {
        if (links.classList.contains("open") && ! event.target.closest("nav")) {
            setOpen(false);
        }
    });
    document.addEventListener("keydown", function onDocumentKeydown(event) {
        if (event.key === "Escape") {
            setOpen(false);
        }
    });
}

initNavToggle();
