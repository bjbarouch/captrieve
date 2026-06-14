import nunjucks                                           from "nunjucks";
import * as sass                                          from "sass";
import { readFileSync, writeFileSync, statSync,
         existsSync, mkdirSync, copyFileSync,
         readdirSync, rmSync }                            from "fs";
import { createHash }                                     from "crypto";
import { globSync }                                       from "glob";
import path                                               from "path";
import { fileURLToPath }                                  from "url";
import site                                               from "./buildData.js";
import { minify }                                         from "terser";

const
    devAndTest = ! process.argv.includes("--prod");

const
    __dirname  = path.dirname(fileURLToPath(import.meta.url)),
    outDir     = path.resolve(__dirname, site.outDir),
    srcDir     = path.join(__dirname, "src"),
    partialDir = path.join(srcDir, "partials"),
    verbose    = process.argv.includes("--verbose");

const
    minMjs = devAndTest ? ".mjs"  : ".min.mjs",
    minJs  = devAndTest ? ".js"   : ".min.js";

function substituteMarkers(source) {
    return source
        .replaceAll("{{min.mjs}}", minMjs)
        .replaceAll("{{min.js}}",  minJs);
}

/**
 * Strips trailing whitespace from every line. Nunjucks leaves indented blank
 * lines where {% %} control tags were; this removes those leftover spaces.
 * @param {string} text
 * @returns {string}
 */
function stripLineTrailingSpace(text) {
    return text.replace(/[ \t]+$/gm, "");
}

/**
 * Guarantees the text ends with exactly one trailing newline.
 * @param {string} text
 * @returns {string}
 */
function endWithNewline(text) {
    return `${text.replace(/\n+$/, "")}\n`;
}

// ---------------------------------------------------------------------------
// Nunjucks environment
// ---------------------------------------------------------------------------

const
    njkEnv = nunjucks.configure(partialDir, { autoescape: true });

/**
 * Nunjucks global function: returns a cache-busting query string for a given
 * output-relative asset path. Returns empty string if the file does not yet exist.
 * @param {string} assetPath
 * @returns {string}
 */
function assetVersion(assetPath) {
    try {
        const
            full    = path.join(outDir, assetPath),
            content = readFileSync(full),
            hash    = createHash("sha256").update(content).digest("hex").slice(0, 6);
        return `?v=${hash}`;
    }
    catch {
        return "";
    }
}

njkEnv.addFilter("assetVersion", assetVersion);
njkEnv.addGlobal("assetVersion", assetVersion);
njkEnv.addGlobal("siteScriptsPath", substituteMarkers("/js/siteScripts{{min.js}}"));

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Returns true if the source file is newer than the output file, or the output does not exist.
 * @param {string} srcFile
 * @param {string} outFile
 * @returns {boolean}
 */

/**
 * Returns the most recent mtime (ms) across all partials and the SCSS source.
 * Used by needsRebuild to treat any dependency change as a trigger.
 * @returns {number}
 */
function depsLastModified() {
    const
        scssFile  = path.join(srcDir, "scss", "stylesheet.scss"),
        partials  = globSync(path.join(partialDir, "*.htm_")),
        files     = existsSync(scssFile) ? [...partials, scssFile] : partials;
    return files.reduce(function maxMtime(max, f) {
        try {
            return Math.max(max, statSync(f).mtimeMs);
        }
        catch {
            return max;
        }
    }, 0);
}

function needsRebuild(srcFile, outFile) {
    try {
        const
            outMtime = statSync(outFile).mtimeMs;
        return statSync(srcFile).mtimeMs > outMtime || depsLastModified() > outMtime;
    }
    catch {
        return true;
    }
}

/**
 * Extracts metadata from the JSON-LD block embedded in a source file.
 * @param {string} source
 * @param {string} filePath - used only for error messages
 * @returns {{ title: string, tabTitle: string, description: string, canonical: string, datePublished: string, dateModified: string }}
 */
function extractMeta(source, filePath) {
    const
        match = source.match(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/);
    if ( ! match) {
        throw new Error(`no JSON-LD found in ${filePath}`);
    }
    let graph;
    try {
        graph = JSON.parse(match[1])["@graph"];
    }
    catch (err) {
        throw new Error(`malformed JSON-LD in ${filePath}: ${err.message}`);
    }
    const
        article      = graph.find(function findArticle(n) { return n["@type"] === "Article"; }),
        pageNode     = graph.find(function findPage(n) { return n.url; }),
        tabTitleNode = graph.find(function findTabTitle(n) { return n.tabTitle; });
    if ( ! pageNode?.url) {
        throw new Error(`no url found in JSON-LD in ${filePath}`);
    }
    return {
        title         : article?.headline ?? pageNode?.name ?? "",
        tabTitle      : tabTitleNode?.tabTitle ?? "",
        description   : graph.find(function findDesc(n) { return n.description; })?.description ?? "",
        canonical     : pageNode.url,
        datePublished : article?.datePublished ?? "",
        dateModified  : article?.dateModified  ?? ""
    };
}

/**
 * Directories under src/ that contain source pages. All others are skipped.
 */
const
    PAGE_DIRS = new Set(site.pageDirs ?? ["pages", "articles"]);

/**
 * Renders a source file into a complete HTML page and writes it to the output path.
 * Skips the file if output is already up to date.
 * @param {string} srcFile
 * @param {string} outFile
 * @returns {boolean} true if the file was (re)built, false if skipped
 */
function buildPage(srcFile, outFile) {
    if ( ! needsRebuild(srcFile, outFile)) {
        return false;
    }
    const
        source      = readFileSync(srcFile, "utf8"),
        meta        = extractMeta(source, srcFile),
        basename    = path.basename(srcFile, ".htm_"),
        pageScripts = site.pageScripts?.[basename] ?? { },
        context     = {
            site,
            meta,
            headScripts : (pageScripts.headScripts ?? [ ]).map(substituteMarkers),
            bodyScripts : (pageScripts.bodyScripts ?? [ ]).map(substituteMarkers)
        },
        head    = njkEnv.render("head.htm_",    context),
        nav     = njkEnv.render("nav.htm_",     context),
        footer  = njkEnv.render("footer.htm_",  context),
        scripts = njkEnv.render("scripts.htm_", context),
        body    = njkEnv.renderString(source, context),
        html    = head + nav + body + footer + scripts;
    mkdirSync(path.dirname(outFile), { recursive: true });
    writeFileSync(outFile, endWithNewline(stripLineTrailingSpace(html)));
    return true;
}

// ---------------------------------------------------------------------------
// Build steps
// ---------------------------------------------------------------------------

/**
 * Compiles SCSS to minified CSS and writes it to the output css/ directory.
 * @returns {void}
 */
function buildCss() {
    const
        srcFile = path.join(srcDir, "scss", "stylesheet.scss"),
        outFile = path.join(outDir, "css", "stylesheet.min.css");
    if ( ! needsRebuild(srcFile, outFile)) {
        return;
    }
    const
        result = sass.compile(srcFile, { style: devAndTest ? "expanded" : "compressed", sourceMap: true });
    mkdirSync(path.join(outDir, "css"), { recursive: true });
    writeFileSync(outFile, endWithNewline(result.css));
    if (result.sourceMap) {
        writeFileSync(`${outFile}.map`, JSON.stringify(result.sourceMap));
    }
    if (verbose) {
        console.log("  built css/stylesheet.min.css");
    }
}

/**
 * Copies font files from src/fonts/ to the output fonts/ directory.
 * @param {string[]} built - accumulates filenames of copied files
 * @returns {void}
 */
function buildFonts(built) {
    const
        fontSrcDir = path.join(srcDir, "fonts"),
        fontOutDir = path.join(outDir, "fonts");
    mkdirSync(fontOutDir, { recursive: true });
    for (const file of readdirSync(fontSrcDir)) {
        const
            srcFile = path.join(fontSrcDir, file),
            outFile = path.join(fontOutDir, file);
        if ( ! needsRebuild(srcFile, outFile)) {
            continue;
        }
        copyFileSync(srcFile, outFile);
        built.push(file);
        if (verbose) {
            console.log(`  copied fonts/${file}`);
        }
    }
}

/**
 * Copies web-safe image files from src/images/ to the output images/ directory.
 * Skips non-web formats (.psd, .ai, .xcf, etc.).
 * @param {string[]} built - accumulates filenames of copied files
 * @returns {void}
 */
function buildImages(built) {
    const
        imgSrcDir  = path.join(srcDir, "images"),
        imgOutDir  = path.join(outDir, "images"),
        WEB_EXTS   = new Set([".jpg", ".jpeg", ".png", ".gif", ".webp", ".svg", ".ico", ".avif"]);
    mkdirSync(imgOutDir, { recursive: true });
    for (const file of readdirSync(imgSrcDir)) {
        if ( ! WEB_EXTS.has(path.extname(file).toLowerCase())) {
            continue;
        }
        const
            srcFile = path.join(imgSrcDir, file),
            outFile = path.join(imgOutDir, file);
        if ( ! needsRebuild(srcFile, outFile)) {
            continue;
        }
        copyFileSync(srcFile, outFile);
        built.push(file);
        if (verbose) {
            console.log(`  copied images/${file}`);
        }
    }
}

/**
 * Processes JS and MJS source files from src/js/ to the output js/ directory.
 * Applies marker substitution to all file contents.
 * In dev/test: writes verbatim under the source filename.
 * In production: minifies via terser and writes under a .min.-inserted filename.
 * @param {string[]} built - accumulates output filenames of written files
 * @returns {Promise<void>}
 */
async function buildJs(built) {
    const
        jsSrcDir = path.join(srcDir, "js"),
        jsOutDir = path.join(outDir, "js");
    mkdirSync(jsOutDir, { recursive: true });
    for (const file of readdirSync(jsSrcDir)) {
        const
            isMjs = file.endsWith(".mjs"),
            isJs  = file.endsWith(".js");
        if ( ! isMjs && ! isJs) {
            continue;
        }
        if (file.includes(".min.")) {
            continue;
        }
        const
            srcFile = path.join(jsSrcDir, file),
            outName = devAndTest ? file : file.replace(/\.(mjs|js)$/, ".min.$1"),
            outFile = path.join(jsOutDir, outName);
        if ( ! needsRebuild(srcFile, outFile)) {
            continue;
        }
        const
            source = substituteMarkers(readFileSync(srcFile, "utf8"));
        if (devAndTest) {
            writeFileSync(outFile, endWithNewline(source));
        }
        else {
            const result = await minify(source, { module: true, compress: true, mangle: true });
            writeFileSync(outFile, endWithNewline(result.code));
        }
        built.push(outName);
        if (verbose) {
            console.log(`  copied js/${outName}`);
        }
    }
}

/**
 * Builds all pages from directories listed in site.pageDirs (default: pages, articles).
 * Skips files with a leading underscore (drafts).
 * Output path mirrors source path relative to src/, with pages/ flattened to outDir root.
 * @returns {void}
 */
function buildPages() {
    for (const pageDir of PAGE_DIRS) {
        const
            pattern = path.join(srcDir, pageDir, "*.htm_"),
            built   = [ ];
        for (const srcFile of globSync(pattern)) {
            if (path.basename(srcFile).startsWith("_")) {
                continue;
            }
            const
                base    = path.basename(srcFile, ".htm_"),
                outFile = pageDir === "pages"
                    ? path.join(outDir, `${base}.html`)
                    : path.join(outDir, pageDir, `${base}.html`);
            if (buildPage(srcFile, outFile)) {
                built.push(base);
                if (verbose) {
                    console.log(`  built ${pageDir}/${base}.html`);
                }
            }
        }
        if ( ! verbose && built.length > 0) {
            console.log(`  built ${pageDir}/ (${built.length} page${built.length === 1 ? "" : "s"})`);
        }
    }
}

/**
 * Generates sitemap.xml from all built pages across site.pageDirs.
 * Uses datePublished and dateModified from JSON-LD for lastmod.
 * @returns {void}
 */
function buildSitemap() {
    const
        outFile = path.join(outDir, "sitemap.xml"),
        urls    = [ ];
    for (const pageDir of PAGE_DIRS) {
        for (const srcFile of globSync(path.join(srcDir, pageDir, "*.htm_"))) {
            if (path.basename(srcFile).startsWith("_")) {
                continue;
            }
            const
                source = readFileSync(srcFile, "utf8"),
                meta   = extractMeta(source, srcFile);
            if ( ! meta.canonical) {
                continue;
            }
            urls.push({ loc : meta.canonical, lastmod : meta.dateModified || meta.datePublished || "" });
        }
    }
    const
        entries = urls.map(function renderEntry(u) {
            const
                lastmod = u.lastmod ? `\n        <lastmod>${u.lastmod}</lastmod>` : "";
            return `    <url>\n        <loc>${u.loc}</loc>${lastmod}\n    </url>`;
        }),
        xml = `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${entries.join("\n")}\n</urlset>\n`;
    writeFileSync(outFile, xml);
    if (verbose) {
        console.log("  built sitemap.xml");
    }
}

const
    ERROR_CODES = [
        400, 401, 402, 403, 404, 405, 406, 407, 408, 409,
        410, 411, 412, 413, 414, 415, 416, 417, 418, 420,
        421, 422, 423, 424, 425, 426, 428, 429, 430,
        500, 501, 502, 503, 504, 505, 506, 507, 508, 510, 511
    ];

const
    ERROR_DESCRIPTIONS = new Map([
        [400, "Your request couldn't be processed because it was malformed or missing required information."],
        [401, "You'll need to sign in to access this page."],
        [402, "Payment is required to access this resource."],
        [403, "You don't have permission to view this page."],
        [404, "We couldn't find the page you're looking for."],
        [405, "That method isn't allowed for this resource."],
        [406, "The requested content isn't available in a format we can serve."],
        [407, "A proxy authentication step is required before proceeding."],
        [408, "The request took too long and timed out. Please try again."],
        [409, "There's a conflict with the current state of the resource."],
        [410, "This page used to exist but has been permanently removed."],
        [411, "A valid Content-Length is required for this request."],
        [412, "A required precondition for this request wasn't met."],
        [413, "The request payload is larger than the server will accept."],
        [414, "The requested URL is too long for the server to process."],
        [415, "The media type of your request isn't supported."],
        [416, "The requested range can't be satisfied for this resource."],
        [417, "A required expectation for this request wasn't met."],
        [418, "I'm a teapot. (Yes, really.)"],
        [420, "There was a rate/validation issue processing your request."],
        [421, "The request was directed at a server that can't produce a response."],
        [422, "We understood the request but couldn't process the contained instructions."],
        [423, "The resource is currently locked."],
        [424, "The request failed because a dependent action failed."],
        [425, "The server isn't ready to handle requests that might be replayed."],
        [426, "Please upgrade your client to use a different protocol."],
        [428, "A required precondition header is missing."],
        [429, "Too many requests -- please slow down and try again later."],
        [430, "There was a temporary issue with your request; please retry."],
        [500, "That's on us -- an unexpected error occurred."],
        [501, "The method you used isn't implemented on this server."],
        [502, "Upstream service returned a bad response. Please try again."],
        [503, "We're temporarily unavailable -- please try again shortly."],
        [504, "Upstream service took too long to respond."],
        [505, "The HTTP version used in the request isn't supported."],
        [506, "Variant negotiation resulted in a configuration the server can't handle."],
        [507, "The server is out of storage to complete the request."],
        [508, "The server encountered an infinite loop while processing the request."],
        [510, "Further extensions are required to fulfill this request."],
        [511, "Network authentication is required before accessing this resource."]
    ]);

/**
 * Returns a human-friendly description for a given HTTP status code.
 * @param {number} code
 * @returns {string}
 */
function defaultDesc(code) {
    return ERROR_DESCRIPTIONS.get(code) ?? "There was a problem with your request.";
}

/**
 * Builds all HTTP error pages from src/errorPages/ body templates.
 * Uses 400.htm_ as the fallback body for codes without their own template.
 * Skips individual pages that are already up to date.
 * @returns {void}
 */
function buildErrorPages() {
    const
        errorSrcDir = path.join(srcDir, "errorPages"),
        errorOutDir = path.join(outDir, "errorPages"),
        baseBody    = path.join(errorSrcDir, "400.htm_");
    mkdirSync(errorOutDir, { recursive: true });
    let built = 0;
    for (const code of ERROR_CODES) {
        const
            outFile  = path.join(errorOutDir, `${code}.html`),
            bodyFile = path.join(errorSrcDir, `${code}.htm_`),
            srcFile  = existsSync(bodyFile) ? bodyFile : baseBody;
        if ( ! needsRebuild(srcFile, outFile)) {
            continue;
        }
        const
            body        = readFileSync(srcFile, "utf8"),
            title       = `${site.brand}: ${code}`,
            canonical   = `${site.baseUrl}/errorPages/${code}`,
            meta        = {
                title,
                tabTitle      : title,
                description   : defaultDesc(code),
                canonical,
                datePublished : "",
                dateModified  : ""
            },
            bodyScripts = (site.errorPageScripts?.[code] ?? site.errorPageScripts?.default ?? []).map(substituteMarkers),
            context     = {
                site,
                meta,
                headScripts : [ ],
                bodyScripts
            },
            head    = njkEnv.render("head.htm_",    context),
            nav     = njkEnv.render("nav.htm_",     context),
            footer  = njkEnv.render("footer.htm_",  context),
            scripts = njkEnv.render("scripts.htm_", context),
            html    = head + nav + body + footer + scripts;
        writeFileSync(outFile, endWithNewline(stripLineTrailingSpace(html)));
        built++;
        if (verbose) {
            console.log(`  built errorPages/${code}.html`);
        }
    }
    if ( ! verbose && built > 0) {
        console.log(`  built errorPages/ (${built} page${built === 1 ? "" : "s"})`);
    }
}

/**
 * Checks that every published page in site.auditDir appears in site.auditIndex
 * as a link href (e.g. href="basename.html" or href="/articles/basename").
 * Both are optional config in buildData.js -- skipped if not set.
 * @returns {void}
 */
function auditIndex() {
    if ( ! site.auditIndex || ! site.auditDir) {
        return;
    }
    const
        indexFile = path.join(srcDir, site.auditIndex);
    let indexSource;
    try {
        indexSource = readFileSync(indexFile, "utf8");
    }
    catch {
        console.warn(`warning: ${site.auditIndex} not found -- skipping index audit`);
        return;
    }
    for (const srcFile of globSync(path.join(srcDir, site.auditDir, "*.htm_"))) {
        const
            base = path.basename(srcFile, ".htm_");
        if (base.startsWith("_") || base === path.basename(site.auditIndex, ".htm_")) {
            continue;
        }
        const
            hrefPattern = new RegExp(`href=["'][^"']*\\b${base}["']`);
        if ( ! hrefPattern.test(indexSource)) {
            console.warn(`warning: ${base} is published but not linked from ${site.auditIndex}`);
        }
    }
}

// ---------------------------------------------------------------------------
// Clean
// ---------------------------------------------------------------------------

/**
 * Removes all build output: asset directories, root-level HTML files, and sitemap.xml.
 * @returns {void}
 */
function clean() {
    const
        dirs = ["css", "fonts", "images", "js", "articles", "errorPages"];
    for (const dir of dirs) {
        rmSync(path.join(outDir, dir), { recursive: true, force: true });
        console.log(`  removed ${dir}/`);
    }
    for (const file of globSync(path.join(outDir, "*.html"))) {
        rmSync(file);
        if (verbose) {
            console.log(`  removed ${path.basename(file)}`);
        }
    }
    rmSync(path.join(outDir, "sitemap.xml"), { force: true });
    console.log("clean done\n");
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

/**
 * Runs the full build.
 * @returns {void}
 */
async function main() {
    if (process.argv.includes("--clean")) {
        console.log("\ncleaning...");
        clean();
        return;
    }
    if ( ! devAndTest) {
        console.log("\ncleaning...");
        clean();
    }
    console.log("\nbuilding...");
    const
        fontBuilt  = [ ],
        imageBuilt = [ ],
        jsBuilt    = [ ];
    buildCss();
    buildFonts(fontBuilt);
    buildImages(imageBuilt);
    await buildJs(jsBuilt);
    if ( ! verbose) {
        if (fontBuilt.length > 0) {
            console.log(`  built fonts/ (${fontBuilt.length} file${fontBuilt.length === 1 ? "" : "s"})`);
        }
        if (imageBuilt.length > 0) {
            console.log(`  built images/ (${imageBuilt.length} file${imageBuilt.length === 1 ? "" : "s"})`);
        }
        if (jsBuilt.length > 0) {
            console.log(`  built js/ (${jsBuilt.length} file${jsBuilt.length === 1 ? "" : "s"})`);
        }
    }
    buildPages();
    buildSitemap();
    buildErrorPages();
    auditIndex();
    console.log("done\n");
}

main();
