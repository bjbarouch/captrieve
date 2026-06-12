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
