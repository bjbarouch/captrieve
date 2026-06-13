function safeHtml(str) {
    if ( ! (/[&<>"'\\]/).test(str)) {
        return str; // nothing to be done -- typical case, so let's not waste time
    }
    // there are of course many more html entities aside from these, but without
    // these, no intentional or accidental html injections can occur
    return str
        .replace(/&/g,  "&#38;")  // this one must be first since the others will be inserting &'s
        .replace(/</g,  "&#60;")  // the most obvious thing to escape, so an html element cannot be started
        .replace(/>/g,  "&#62;")  // so an html element cannot be prematurely ended
        .replace(/"/g,  "&#34")   // string hygiene
        .replace(/'/g,  "&#39;")  // string hygiene
        .replace(/\\/g, "&#92;"); // escape hygiene
}

export { safeHtml };
