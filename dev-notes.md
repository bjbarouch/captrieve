# captrieve dev notes

The working notes for the captrieve.com marketing website.
Planning and operational facts for the site live here.
The over-arching product docs live under docs/, starting from docs/captrieve.md.

## Project map (one project, two repos)

Captrieve is one product built across two repositories.
Treat them as a single project, and keep the cross-repo split in mind whichever one you are working in.

-  ~/Sites/captrieve is the captrieve.com marketing website (this repo).
-  ~/Sites/captrieve-app is the Flutter app.

Where the docs live:

-  Over-arching docs (product purpose, vision, plan, spanning both halves) live in this repo under docs/, starting
   from docs/captrieve.md (the top-level umbrella). The detailed ones are docs/captrieve-spec.md,
   docs/captrieve-the-thinking.md, and docs/devplan.md. The launch checklist is the "Before launching" section of
   docs/captrieve.md.
-  App-specific docs live in the app repo: README.md, FEATURES.md, dev-notes.md, and flutter-setup-macos.md.
-  Website-specific notes live here in this file.

Routing rule for a new doc: over-arching goes to captrieve/docs (linked from captrieve.md), app-specific goes to
captrieve-app, website-specific goes here.

## Website build

The site is a custom Nunjucks + Sass build.
Source lives under src/ (pages, partials, scss, and assets) and compiles to the static .html files at the repo root
via build.js and buildData.js.
Edit the source under src/, never the generated .html at the root.

## Before launch (website tasks)

The full cross-project launch checklist is the "Before launching" section of docs/captrieve.md.
The website-side launch mechanics it names: remove the preview-auth gate (preview-auth.js plus login.html) so the
site is public, and make the Digital Asset Links and apple-app-site-association files live before anyone installs
the app.
</content>
