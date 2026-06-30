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

    // nav items -- rendered by nav.htm_ in the hamburger menu
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
        { href : "/manual",          label : "Manual"          },
        { href : "/privacy",         label : "Privacy"         }
    ],

    // CTA button rendered at the end of the nav bar
    navCta : {
        href  : "/index#download",
        label : "Get the app"
    },

    // page-specific scripts, keyed by page basename (no extension)
    // headScripts load in <head>. bodyScripts load at end of <body>
    // the pre-launch preview gate now lives server-side in .htaccess, not here
    pageScripts : { },

    // error page scripts, keyed by HTTP status code or "default"
    errorPageScripts : {
        404     : ["/js/errorPage{{min.js}}", "/js/notFound{{min.js}}"],
        default : ["/js/errorPage{{min.js}}"]
    }

}
