export default {

    outDir   : "./",
    baseUrl  : "https://captrieve.com",

    // directories under src/ that contain pages to build
    pageDirs : ["pages"],

    brand     : "Captrieve",
    copyright : "Copyright © 2026 – Captrieve – All Rights Reserved",

    email : {
        support : "support@captrieve.com"
    },

    verify : {
        google : "",   // fill in when Search Console is set up
        msft   : ""    // fill in when Bing Webmaster is set up
    },

    ogImage : {
        url    : "https://captrieve.com/images/captrieve-logo-wide.png",
        width  : "1200",
        height : "630"
    },

    // primary nav items -- rendered by nav.htm_ as <li><a> entries
    // NOTE: nav varies across current pages; the list below is the most complete version
    // (from compare.html). Confirm which items belong before converting pages.
    nav : [
        { href : "/index",           label : "Home"            },
        { href : "/cues",            label : "Cues"            },
        { href : "/inspirations",    label : "Inspirations"    },
        { href : "/pricing",         label : "Pricing"         },
        { href : "/adhd",            label : "ADHD"            },
        { href : "/caregivers",      label : "Caregivers"      },
        { href : "/recommendations", label : "Recommendations" },
        { href : "/science",         label : "Science"         },
        { href : "/compare",         label : "Compare"         },
        { href : "/faq",             label : "FAQ"             },
        { href : "/privacy",         label : "Privacy"         }
    ],

    // CTA button rendered at the end of the nav bar
    navCta : {
        href  : "/index#download",
        label : "Get the app"
    },

    // page-specific scripts, keyed by page basename (no extension)
    // headScripts load in <head>; bodyScripts load at end of <body>
    // preview-auth.js is a temporary dev gate -- remove all entries here at launch
    pageScripts : {
        index           : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        compare         : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        pricing         : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        cues            : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        inspirations    : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        adhd            : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        caregivers      : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        science         : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        recommendations : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        login           : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        faq             : { headScripts : ["/preview-auth.js"], bodyScripts : [] },
        privacy         : { headScripts : ["/preview-auth.js"], bodyScripts : [] }
    },

    // error page scripts, keyed by HTTP status code or "default"
    errorPageScripts : {
        404     : ["/js/errorPage{{min.js}}", "/js/notFound{{min.js}}"],
        default : ["/js/errorPage{{min.js}}"]
    }

}
