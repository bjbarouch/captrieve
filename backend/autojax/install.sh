#!/usr/bin/env bash

# =============================================================================
# install.sh -- autojax installer
#
# usage:
#   first install:  bash dist/install.sh [destination]
#   reinstall:      bash dist/install.sh --reinstall [destination]
#
# a fresh install begins by running mail-probe.sh to verify how this host sends mail, because autojax relies on
# email for account-confirmation and password-reset links. the probe runs inline and reports its verdict through
# its exit status; nothing is written to disk, so installs of different projects on one host never confound each
# other. you can also run mail-probe.sh on its own to re-test after the host's mail setup changes.
#
# run from a built dist/ (or any deployed copy of it). the installer locates
# itself via BASH_SOURCE, then rsyncs itself to <destination> (the autojax/
# directory). it then:
#   writes two files that survive reinstalls:
#     autojax.json         -- non-secret config (in autojax/'s parent directory); you complete the ajaxApi section
#     autojax-secrets.php  -- secrets only; must live outside DOCUMENT_ROOT
#   and copies these editable files into autojax/'s parent directory:
#     autojax-forgotPassword.html
#     autojax-resetPassword.html
#     autojax-admin.html
#     autojax-confirmEmail.html
#     autojax-admin.css
#
# reinstall re-copies autojax/ over <destination> and leaves your files untouched.
# <destination> may be given as an argument; otherwise it is prompted for.
#
# requires: bash 3.2+, openssl, rsync
# =============================================================================

set -euo pipefail

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ "$(basename "$SOURCE_DIR")" == "src" ]]; then
    echo "ERROR: run install.sh from a built dist/, not the source repo (run 'npm run build' first)" >&2
    exit 1
fi

# =============================================================================
# utilities
# =============================================================================

expand_path() {
    local p="$1"
    [[ "$p" == "~"* ]] && p="${HOME}${p#\~}"
    printf '%s' "${p%/}"
}

# resolve a (possibly relative, possibly nonexistent) path to a normalized
# absolute path -- relative answers like "." or "../foo" are resolved against
# the current working directory; "." and ".." segments are collapsed
abs_path() {
    local p="$1" seg joined=""
    [[ "$p" == "~" || "$p" == "~/"* ]] && p="${HOME}${p#\~}"
    [[ "$p" != /* ]] && p="${PWD}/${p}"
    local parts=() out=()
    IFS='/' read -r -a parts <<< "$p"
    for seg in ${parts[@]+"${parts[@]}"}; do
        case "$seg" in
            ''|'.') ;;
            '..')   [ ${#out[@]} -gt 0 ] && unset "out[$(( ${#out[@]} - 1 ))]" ;;
            *)      out+=("$seg") ;;
        esac
    done
    for seg in ${out[@]+"${out[@]}"}; do
        joined+="/$seg"
    done
    printf '%s' "${joined:-/}"
}

# like prompt_with_default, but resolves the answer to a normalized absolute path
prompt_path() {
    abs_path "$(prompt_with_default "$1" "$2")"
}

# ensure AUTOJAX_DIR names the autojax/ package directory itself. if the requested path already ends in
# "autojax", it IS that directory. otherwise the requested path must be an existing directory (your app
# dir) and autojax/ is placed inside it. this keeps the package always at <app>/autojax -- next to the
# editable files, alphabetically adjacent to them, and a clean "rm -rf" target -- and removes the footgun
# where pointing the installer at an app directory let "rsync --delete" overwrite the whole app. operates
# on the AUTOJAX_DIR global and is called directly (not in a subshell) so its exit aborts the install.
normalize_autojax_dir() {
    if [ "$(basename "$AUTOJAX_DIR")" = "autojax" ]; then
        return 0
    fi
    if [ ! -d "$AUTOJAX_DIR" ]; then
        echo "ERROR: install path '${AUTOJAX_DIR}' does not end in 'autojax' and is not an existing directory." >&2
        echo "  give an absolute path ending in /autojax, or an existing app directory to install autojax into." >&2
        exit 1
    fi
    AUTOJAX_DIR="${AUTOJAX_DIR%/}/autojax"
    echo "install path is an existing directory -- autojax/ will be created inside it: ${AUTOJAX_DIR}"
}

# true when DIR already holds an installed autojax package. used to refuse a fresh install aimed at an existing
# one -- which would re-prompt for every setting and rewrite autojax.json -- and point the integrator at --reinstall
looks_like_autojax_install() {
    [ -f "${1}/ajaxApi/ajaxPort.php" ]
}

# refuse a fresh install that targets an existing install, naming the exact --reinstall command to use instead.
# operates at top level via exit, like normalize_autojax_dir, so it aborts the install
refuse_fresh_over_existing() {
    echo "" >&2
    echo "ERROR: ${1} already contains an autojax install." >&2
    echo "  a fresh install re-prompts for every setting and rewrites autojax.json." >&2
    echo "  to update the core in place, leaving your config and secrets untouched, run:" >&2
    echo "      install.sh --reinstall ${2}" >&2
    exit 1
}

prompt_with_default() {
    local prompt="$1" default="$2" value
    if [ -n "$default" ]; then
        printf "\n%s\n    [%s]: " "$prompt" "$default" > /dev/tty
        IFS= read -r value < /dev/tty
        value="${value:-$default}"
    else
        printf "\n%s\n    : " "$prompt" > /dev/tty
        IFS= read -r value < /dev/tty
        while [ -z "$value" ]; do
            printf "    (required)\n    : " > /dev/tty
            IFS= read -r value < /dev/tty
        done
    fi
    expand_path "$value"
}

# echo the emailTransport value already configured in an installed autojax.json, or nothing.
# used on reinstall, where the existing config is preserved and not re-derived
read_configured_transport() {
    local json="${AUTOJAX_PARENT_DIR}/autojax.json"
    [ -f "$json" ] || return 0
    sed -n 's/.*"emailTransport"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' "$json" | head -1
}

# echo "true" when emailTurnOff is set true in an installed autojax.json, otherwise nothing. used on reinstall
# to skip the transport requirement for a deployment that sends no mail at all
read_configured_turnoff() {
    local json="${AUTOJAX_PARENT_DIR}/autojax.json"
    [ -f "$json" ] || return 0
    sed -n 's/.*"emailTurnOff"[[:space:]]*:[[:space:]]*\(true\|false\).*/\1/p' "$json" | head -1 | grep -x true
}

# escape a value so it is safe as the replacement text of a sed "s" command using | as the delimiter
# (backslash, the | delimiter, and & -- which means "the matched text" in a replacement)
sed_escape() {
    local s="$1"
    s="${s//\\/\\\\}"
    s="${s//|/\\|}"
    s="${s//&/\\&}"
    printf '%s' "$s"
}

# escape a value so it is safe inside a PHP double-quoted string literal
# (backslash first, then the closing quote and $ which PHP would otherwise interpolate)
php_escape() {
    local s="$1"
    s="${s//\\/\\\\}"
    s="${s//\"/\\\"}"
    s="${s//\$/\\\$}"
    printf '%s' "$s"
}

substitute() {
    local src="$1" dest="$2"
    sed \
        -e "s|%%SECRETS_FILE%%|$(sed_escape "$AUTOJAX_SECRETS_FILE")|g" \
        -e "s|%%IMPORTMAP%%|$(sed_escape "$IMPORTMAP_REPLACEMENT")|g" \
        -e "s|%%CORE_URL%%|$(sed_escape "$AUTOJAX_CORE_URL")|g" \
        -e "s|%%CORE_PARENT_URL%%|$(sed_escape "$AUTOJAX_CORE_PARENT_URL")|g" \
        -e "s|%%LOGIN_URL%%|$(sed_escape "$AUTOJAX_LOGIN_URL")|g" \
        -e "s|%%LOG_DIR%%|$(sed_escape "$AUTOJAX_LOG_DIR")|g" \
        -e "s|%%TIMEZONE%%|$(sed_escape "$AUTOJAX_TIMEZONE")|g" \
        -e "s|%%EMAIL_TRANSPORT%%|$(sed_escape "$AUTOJAX_EMAIL_TRANSPORT")|g" \
        -e "s|%%EMAIL_SUPPORT_TEST%%|$(sed_escape "$AUTOJAX_EMAIL_SUPPORT_TEST")|g" \
        -e "s|%%EMAIL_SUPPORT_PROD%%|$(sed_escape "$AUTOJAX_EMAIL_SUPPORT_PROD")|g" \
        "$src" > "$dest"
}

write_secrets() {
    local dest="$1"
    # escape every secret for safe embedding in a PHP double-quoted string -- a value containing
    # " $ or \ would otherwise corrupt the generated file (db and email passwords are arbitrary)
    local dbHost dbName dbUser dbPassword emailServer emailUser emailPassword encryptionKey jwtSecret
    dbHost="$(php_escape "$AUTOJAX_DB_HOST")"
    dbName="$(php_escape "$AUTOJAX_DB_NAME")"
    dbUser="$(php_escape "$AUTOJAX_DB_USER")"
    dbPassword="$(php_escape "$AUTOJAX_DB_PASSWORD")"
    emailServer="$(php_escape "$AUTOJAX_EMAIL_SERVER")"
    emailUser="$(php_escape "$AUTOJAX_EMAIL_USER")"
    emailPassword="$(php_escape "$AUTOJAX_EMAIL_PASSWORD")"
    encryptionKey="$(php_escape "$AUTOJAX_ENCRYPTION_KEY")"
    jwtSecret="$(php_escape "$AUTOJAX_JWT_SECRET")"
    cat > "$dest" << ENDPHP
<?php

/**
 * @file autojax-secrets.php
 *
 * secrets-only configuration for autojax.
 * must live outside DOCUMENT_ROOT.
 * all other configuration is in autojax.json.
 *
 * exposes \$autoJaxSecrets with db credentials, email password, encryption key, and jwt secret.
 * ajaxPort.php reads autojax.json for all non-secret config and assembles \$autoJaxConfig.
 */

\$autoJaxSecrets = (object)[
    "dbHost"        => "${dbHost}",
    "dbName"        => "${dbName}",
    "dbUser"        => "${dbUser}",
    "dbPassword"    => "${dbPassword}",
    "emailServer"   => "${emailServer}",
    "emailUser"     => "${emailUser}",
    "emailPassword" => "${emailPassword}",
    "encryptionKey"       => "${encryptionKey}",
    "encryptionKeyOld"    => null,
    "encKeyLastUpdate"    => 0,
    "jwtSecret"           => "${jwtSecret}",
    "jwtSecretOld"        => null,
    "jwtSecretLastUpdate" => 0
];
ENDPHP
}

# write a deny-all .htaccess into the log directory (in case it sits inside DOCUMENT_ROOT) and
# keep the directory itself non-world-readable. ajaxPort.php also does this at runtime
write_log_htaccess() {
    local dir="$1"
    cat > "${dir}/.htaccess" << 'ENDHT'
Options -Indexes
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Deny from all
</IfModule>
ENDHT
    chmod 0750 "$dir"
}

# write security headers for the auth pages autojax ships into the parent dir. scoped via FilesMatch to only
# those four files so the integrator's own pages are untouched. needs Apache mod_headers; nginx users replicate
# these (see README). the CSP pins the inline import map by its sha256 hash and otherwise blocks inline scripts,
# so an injected <script> would not execute even though the shipped page code has no such sink today
write_pages_htaccess() {
    local dest="$1" hash="$2"
    cat > "$dest" << ENDHT
# autojax -- security headers for the auth pages it ships (the hash pins the inline import map; do not edit it)
<IfModule mod_headers.c>
    <FilesMatch "^autojax-(forgotPassword|resetPassword|admin|confirmEmail)\.html\$">
        Header always set Content-Security-Policy "default-src 'self'; script-src 'self' '${hash}'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'"
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-Frame-Options "DENY"
        Header always set Referrer-Policy "no-referrer"
    </FilesMatch>
</IfModule>
ENDHT
}

# =============================================================================
# collect params
# =============================================================================

AUTOJAX_DIR=""
AUTOJAX_PARENT_DIR=""
AUTOJAX_CORE_URL=""
AUTOJAX_CORE_PARENT_URL=""
AUTOJAX_LOGIN_URL=""
AUTOJAX_LOG_DIR=""
AUTOJAX_TIMEZONE=""
AUTOJAX_DB_HOST=""
AUTOJAX_DB_NAME=""
AUTOJAX_DB_USER=""
AUTOJAX_DB_PASSWORD=""
AUTOJAX_EMAIL_TRANSPORT=""
AUTOJAX_EMAIL_SERVER=""
AUTOJAX_EMAIL_USER=""
AUTOJAX_EMAIL_PASSWORD=""
AUTOJAX_EMAIL_SUPPORT_TEST=""
AUTOJAX_EMAIL_SUPPORT_PROD=""
AUTOJAX_ENCRYPTION_KEY=""
AUTOJAX_JWT_SECRET=""
AUTOJAX_SECRETS_DIR=""
AUTOJAX_SECRETS_FILE=""
IMPORTMAP_REPLACEMENT=""
IMPORTMAP_HASH=""

REINSTALL=false
DEST_ARG=""
HTACCESS_MERGE=false
for _arg in "$@"; do
    case "$_arg" in
        --reinstall)
            REINSTALL=true
            ;;
        -*)
            # an unrecognized option -- for example a typo like "--reninstall" -- must never be silently treated as
            # a destination path. doing so drops a meant-as-reinstall into the destructive fresh-install path, which
            # then demands mail-probe and re-prompts for everything. fail loudly instead.
            echo "ERROR: unknown option '${_arg}'." >&2
            if [ "$_arg" != "${_arg#--re}" ]; then
                echo "  did you mean '--reinstall'?" >&2
            fi
            echo "usage: install.sh [destination]              (first install)" >&2
            echo "       install.sh --reinstall [destination]  (update an existing install)" >&2
            exit 1
            ;;
        *)
            DEST_ARG="$(abs_path "$_arg")"
            ;;
    esac
done

if $REINSTALL; then
    echo "reinstalling -- autojax/ will be re-copied, your files left untouched"
    if [ -n "$DEST_ARG" ]; then
        AUTOJAX_DIR="$DEST_ARG"
    else
        AUTOJAX_DIR="$(prompt_path \
            "Absolute path of the installed autojax/ to update" \
            "")"
    fi
    normalize_autojax_dir
    AUTOJAX_PARENT_DIR="$(dirname "$AUTOJAX_DIR")"

    # reinstall preserves your autojax.json, but an existing config can predate the transport setting (or carry
    # a stale value). autojax refuses to start unless emailTransport is exactly "smtp" or "sendmail", so require
    # that here too rather than re-copy the core over a config that cannot run. skipped when email is turned off
    if [ -z "$(read_configured_turnoff)" ]; then
        _configured_transport="$(read_configured_transport)"
        if [ "$_configured_transport" != "smtp" ] && [ "$_configured_transport" != "sendmail" ]; then
            echo "" >&2
            echo "NOT READY:" >&2
            echo "  ${AUTOJAX_PARENT_DIR}/autojax.json has no valid \"emailTransport\" (it must be exactly" >&2
            echo "  \"smtp\" or \"sendmail\"); autojax would refuse to start. Run ${SOURCE_DIR}/mail-probe.sh to" >&2
            echo "  determine which this host can use and set it in autojax.json (or set \"emailTurnOff\" to true if" >&2
            echo "  this deployment sends no mail), then reinstall." >&2
            exit 1
        fi
    fi
else
    echo ""
    echo "autojax installer"
    echo "=================="
    echo ""

    # a fresh install aimed at a directory that already holds autojax is almost always a forgotten --reinstall
    # (for example "install.sh ." in an app that already has autojax/). catch it here, before the mail-probe gate
    # and the prompts, so the integrator is not marched through setup only to overwrite a working install
    if [ -n "$DEST_ARG" ]; then
        _fresh_target="$DEST_ARG"
        if [ "$(basename "$_fresh_target")" != "autojax" ] && [ -d "$_fresh_target" ]; then
            _fresh_target="${_fresh_target%/}/autojax"
        fi
        if looks_like_autojax_install "$_fresh_target"; then
            refuse_fresh_over_existing "$_fresh_target" "$DEST_ARG"
        fi
    fi

    # --- Email-delivery gate ----------------------------------------------
    # autojax uses email for account-confirmation and password-reset links, so a fresh install cannot proceed
    # until this host's mail delivery is known. run mail-probe.sh inline: it sends a real test message and waits
    # for the operator to confirm it arrived, then reports the transport through its exit status (0 sendmail,
    # 2 smtp, anything else aborted). nothing is written to disk. this runs first, before any prompts, so the
    # operator is not made to fill in everything only to be turned away
    _probe_rc=0
    bash "${SOURCE_DIR}/mail-probe.sh" --embedded || _probe_rc=$?
    case "$_probe_rc" in
        0)
            AUTOJAX_EMAIL_TRANSPORT="sendmail"
            ;;
        2)
            AUTOJAX_EMAIL_TRANSPORT="smtp"
            ;;
        *)
            echo "" >&2
            echo "NOT READY:" >&2
            echo "  The email-delivery probe did not complete, so installation cannot continue." >&2
            echo "" >&2
            echo "  Without email, new users cannot register without your assistance, and" >&2
            echo "  established users cannot reset a forgotten password without your assistance." >&2
            echo "" >&2
            echo "  Run ${SOURCE_DIR}/mail-probe.sh on its own to work through it, then run install again." >&2
            exit 1
            ;;
    esac

    echo "Press enter to accept the default shown in brackets."

    # --- Paths and URLs ---------------------------------------------------
    echo ""
    echo "╔════════════════╗"
    echo "║ Paths and URLs ║"
    echo "╚════════════════╝"

    local_document_root="$(prompt_path \
        "Absolute filesystem path of DOCUMENT_ROOT" \
        "$PWD")"

    AUTOJAX_DIR="$(prompt_path \
        "Install autojax/ to (absolute path)" \
        "${DEST_ARG:-${local_document_root}/autojax}")"
    normalize_autojax_dir
    AUTOJAX_PARENT_DIR="$(dirname "$AUTOJAX_DIR")"
    # the same guard as the DEST_ARG check above, for a path typed at the prompt rather than passed as an argument
    if looks_like_autojax_install "$AUTOJAX_DIR"; then
        refuse_fresh_over_existing "$AUTOJAX_DIR" "$AUTOJAX_DIR"
    fi

    # web URL of autojax/ is its install path relative to the document root. derived, not asked, because
    # autojax/ is served from under the document root in the normal case. only the importmap and ajaxUrl
    # need it, and both are easy to correct in autojax.json afterward if a non-standard web root requires it
    autojax_rel="${AUTOJAX_DIR#"$local_document_root"}"
    AUTOJAX_CORE_URL="$autojax_rel"

    AUTOJAX_CORE_PARENT_URL="$(dirname "$AUTOJAX_CORE_URL")"

    project_name="$(basename "$local_document_root")"

    AUTOJAX_LOGIN_URL="$(prompt_with_default \
        "Web URL of your login page (navigated to on session logout or timeout)" \
        "/index.html")"

    _log_abs="$(prompt_path \
        "Absolute filesystem path of log directory" \
        "${AUTOJAX_PARENT_DIR}/autojax-log")"
    # store relative to parent dir if it falls inside parent dir, otherwise absolute
    if [[ "$_log_abs" == "${AUTOJAX_PARENT_DIR}/"* ]]; then
        AUTOJAX_LOG_DIR="${_log_abs#"${AUTOJAX_PARENT_DIR}/"}"
    else
        AUTOJAX_LOG_DIR="$_log_abs"
    fi
    unset _log_abs

    echo ""
    echo "╔════════════════╗"
    echo "║ Time zone      ║"
    echo "╚════════════════╝"
    _tz_default="$(realpath /etc/localtime 2>/dev/null | sed 's#.*zoneinfo/##')"
    _tz_default="${_tz_default:-America/Los_Angeles}"
    AUTOJAX_TIMEZONE="$(prompt_with_default \
        "Local time zone" \
        "$_tz_default")"

    # --- Secrets file location --------------------------------------------
    echo ""
    echo "╔════════════════╗"
    echo "║ Secrets file   ║"
    echo "╚════════════════╝"
    echo ">> MUST BE OUTSIDE OF DOCUMENT_ROOT << contains secrets such as credentials and encryption keys"

    AUTOJAX_SECRETS_DIR="$(prompt_path \
        "Directory for autojax-secrets.php" \
        "${HOME}/${project_name}-autojax-secrets")"

    while [[ "$AUTOJAX_SECRETS_DIR" == "$local_document_root"* ]]; do
        echo "  ERROR: that path is inside DOCUMENT_ROOT"
        AUTOJAX_SECRETS_DIR="$(prompt_path \
            "Directory for autojax-secrets.php" \
            "${HOME}/${project_name}-autojax-secrets")"
    done

    AUTOJAX_SECRETS_FILE="${AUTOJAX_SECRETS_DIR}/autojax-secrets.php"

    # --- Database ---------------------------------------------------------
    echo ""
    echo "╔════════════════╗"
    echo "║ Database       ║"
    echo "╚════════════════╝"

    AUTOJAX_DB_HOST="$(prompt_with_default "Database host"     "localhost")"
    AUTOJAX_DB_NAME="$(prompt_with_default "Database name"     "$project_name")"
    AUTOJAX_DB_USER="$(prompt_with_default "Database user"     "")"
    AUTOJAX_DB_PASSWORD="$(prompt_with_default "Database password" "")"

    # --- Email ------------------------------------------------------------
    echo ""
    echo "╔════════════════╗"
    echo "║ Email          ║"
    echo "╚════════════════╝"

    # transport was decided by the probe gate above. only prompt for SMTP credentials when the smtp transport
    # is in use -- the sendmail transport needs none, so asking for them would be noise
    if [ "$AUTOJAX_EMAIL_TRANSPORT" = "sendmail" ]; then
        echo "The email probe verified local mail delivery -- using the \"sendmail\" transport."
        echo "No SMTP server or credentials are needed."
    else
        echo "The email probe did not confirm local mail delivery -- using the \"smtp\" transport."
        echo "Enter the SMTP relay server and the credentials autojax will authenticate with."
        AUTOJAX_EMAIL_SERVER="$(prompt_with_default "SMTP host"     "")"
        AUTOJAX_EMAIL_USER="$(prompt_with_default   "SMTP login"    "")"
        AUTOJAX_EMAIL_PASSWORD="$(prompt_with_default "SMTP password" "")"
    fi

    echo ""
    echo "Support address (also the dev/test reroute target -- all mail goes here in dev and test)"

    AUTOJAX_EMAIL_SUPPORT_TEST="$(prompt_with_default "Support address (dev/test)"  "")"
    AUTOJAX_EMAIL_SUPPORT_PROD="$(prompt_with_default "Support address (production)" "")"

    # --- Keys -------------------------------------------------------------
    echo ""
    echo "Generating encryption key and JWT secret..."
    AUTOJAX_ENCRYPTION_KEY="$(openssl rand -hex 32)"
    AUTOJAX_JWT_SECRET="$(openssl rand -hex 32)"
    echo "Done."

    # build the import map exactly as it will appear in the auth pages, and hash it for the page CSP. computing
    # the hash from the same string we inline guarantees the CSP 'sha256-...' matches the script element, so
    # script-src stays 'self' plus this one hash with no 'unsafe-inline'
    IMPORTMAP_CONTENT="{\"imports\":{\"autojax/\":\"${AUTOJAX_CORE_URL}/\"}}"
    IMPORTMAP_REPLACEMENT="<script type=\"importmap\">${IMPORTMAP_CONTENT}</script>"
    IMPORTMAP_HASH="sha256-$(printf '%s' "$IMPORTMAP_CONTENT" | openssl dgst -sha256 -binary | openssl base64)"
fi

# =============================================================================
# install
# =============================================================================

if [ -z "$AUTOJAX_DIR" ] || [ "$AUTOJAX_DIR" = "/" ] || [ "$AUTOJAX_DIR" = "$HOME" ]; then
    echo "ERROR: refusing to install to '${AUTOJAX_DIR}'" >&2
    exit 1
fi
if [ "$SOURCE_DIR" = "$AUTOJAX_DIR" ]; then
    echo "ERROR: source and destination are the same directory (${SOURCE_DIR})" >&2
    exit 1
fi
# the copy below is "rsync --delete", which makes AUTOJAX_DIR exactly match the new dist -- deleting any
# file already there that is not part of autojax. that is correct for an empty target or a prior autojax
# install, but catastrophic if AUTOJAX_DIR turns out to hold something else. refuse that case rather than
# wipe it. the sentinel files are stamped into every build, so a real install always has them
if [ -d "$AUTOJAX_DIR" ] && [ -n "$(ls -A "$AUTOJAX_DIR" 2>/dev/null)" ]; then
    if [ ! -f "${AUTOJAX_DIR}/autojax-version.json" ] && [ ! -f "${AUTOJAX_DIR}/ajaxApi/ajaxPort.php" ]; then
        echo "ERROR: ${AUTOJAX_DIR} is not empty and does not look like an autojax install." >&2
        echo "  refusing to overwrite it. remove it, or choose an empty path ending in /autojax." >&2
        exit 1
    fi
fi

echo ""
echo "copying autojax/ to ${AUTOJAX_DIR}/ (existing contents, if any, are replaced)"
mkdir -p "$AUTOJAX_DIR"
rsync -a --delete "${SOURCE_DIR}/" "${AUTOJAX_DIR}/"

if ! $REINSTALL; then
    echo ""
    echo "writing customizable files to ${AUTOJAX_PARENT_DIR}/ (existing ones are kept, not overwritten)"
    # these are yours to edit, so never clobber a copy that is already here. an adopter who copied the
    # example app (its filled-in autojax.json and custom auth pages) into place then ran install must keep
    # exactly those, so write each only when it is absent
    if [ -f "${AUTOJAX_PARENT_DIR}/autojax.json" ]; then
        echo "  autojax.json (kept existing)"
    else
        substitute "${AUTOJAX_DIR}/autojax.json" "${AUTOJAX_PARENT_DIR}/autojax.json"
        echo "  autojax.json"
    fi

    for page in autojax-forgotPassword autojax-resetPassword autojax-admin autojax-confirmEmail; do
        if [ -f "${AUTOJAX_PARENT_DIR}/${page}.html" ]; then
            echo "  ${page}.html (kept existing)"
        else
            substitute "${AUTOJAX_DIR}/templates/${page}.html" "${AUTOJAX_PARENT_DIR}/${page}.html"
            echo "  ${page}.html"
        fi
    done

    if [ -f "${AUTOJAX_PARENT_DIR}/autojax-admin.css" ]; then
        echo "  autojax-admin.css (kept existing)"
    else
        cp "${AUTOJAX_DIR}/templates/autojax-admin.css" "${AUTOJAX_PARENT_DIR}/autojax-admin.css"
        echo "  autojax-admin.css"
    fi

    echo ""
    # NEVER overwrite an existing secrets file. write_secrets generates fresh random encryption keys and a
    # jwt secret, so clobbering a live autojax-secrets.php would make every encrypted column and every
    # session token permanently unreadable. write one only when none exists
    if [ -f "$AUTOJAX_SECRETS_FILE" ]; then
        echo "secrets file ${AUTOJAX_SECRETS_FILE} already exists -- keeping it (NOT regenerating keys)"
    else
        echo "writing secrets file to ${AUTOJAX_SECRETS_FILE}"
        mkdir -p "$AUTOJAX_SECRETS_DIR"
        write_secrets "$AUTOJAX_SECRETS_FILE"
        chmod 600 "$AUTOJAX_SECRETS_FILE"
    fi

    # security headers for the auth pages. never clobber an existing .htaccess -- if one is present, drop the
    # block beside it for the integrator to merge
    if [ -e "${AUTOJAX_PARENT_DIR}/.htaccess" ]; then
        write_pages_htaccess "${AUTOJAX_PARENT_DIR}/autojax-headers.htaccess" "$IMPORTMAP_HASH"
        HTACCESS_MERGE=true
        echo "  autojax-headers.htaccess  (an .htaccess already exists -- merge this block into it)"
    else
        write_pages_htaccess "${AUTOJAX_PARENT_DIR}/.htaccess" "$IMPORTMAP_HASH"
        HTACCESS_MERGE=false
        echo "  .htaccess  (security headers for the auth pages)"
    fi

    if [[ "$AUTOJAX_LOG_DIR" == /* ]]; then
        mkdir -p "$AUTOJAX_LOG_DIR"
        write_log_htaccess "$AUTOJAX_LOG_DIR"
    else
        mkdir -p "${AUTOJAX_PARENT_DIR}/${AUTOJAX_LOG_DIR}"
        write_log_htaccess "${AUTOJAX_PARENT_DIR}/${AUTOJAX_LOG_DIR}"
    fi
fi

# =============================================================================
# summary
# =============================================================================

echo ""
echo "========================================================"
echo "autojax installation complete"
echo "========================================================"
echo ""

# email transport report. on a fresh install the transport was decided by mail-probe.sh and applied above.
# on a reinstall the existing autojax.json is preserved, so read and report whatever it already holds, and
# remind the integrator that a changed mail setup means re-running the probe
if $REINSTALL; then
    if [ -n "$(read_configured_turnoff)" ]; then
        echo "email transport: not required -- emailTurnOff is true, so this deployment sends no mail."
    else
        _transport="$(read_configured_transport)"
        echo "email transport (unchanged): \"${_transport}\""
        echo "       If your mail setup has changed, run ${SOURCE_DIR}/mail-probe.sh again and edit emailTransport in"
        echo "       ${AUTOJAX_PARENT_DIR}/autojax.json to match."
    fi
else
    if [ "$AUTOJAX_EMAIL_TRANSPORT" = "sendmail" ]; then
        echo "email transport: \"sendmail\" (delivery verified by mail-probe.sh)."
        echo "       Re-run ${SOURCE_DIR}/mail-probe.sh any time to re-test, for example after changing the host's mail setup."
    else
        echo "email transport: \"smtp\", using the server and credentials in your secrets file."
    fi
fi
echo ""

if $REINSTALL; then
    echo "autojax/ at ${AUTOJAX_DIR} has been re-copied. your possibly customized files were not touched."
    echo ""
else
    echo "REQUIRED before autojax will function:"
    echo ""
    echo "  1. complete the ajaxApi section of ${AUTOJAX_PARENT_DIR}/autojax.json"
    echo "        declare each of your app's services according to the instructions in that file"
    echo ""
    echo "  2. initialize the database"
    echo "        create the database named in your config, and a db user with access to it"
    echo "        (those credentials live in ${AUTOJAX_SECRETS_FILE})"
    echo "        then load the autojax schema, which creates the autoJaxUser and autoJaxThrottle tables:"
    echo ""
    echo "          mysql -u <user> -p <database> < ${AUTOJAX_DIR}/db/dbInit.sql"
    echo ""
    echo "        set up your own app schema next, if not already present or if it needs updating"
    echo "        do NOT add columns to autoJaxUser -- keep app data in your own tables and link them by"
    echo "        foreign key to autoJaxUser:"
    echo ""
    echo "          -- CASCADE: delete that user's rows along with the account (typical for user-owned data)"
    echo '          ALTER TABLE `YourTable`'
    echo '              ADD CONSTRAINT `fk_YourTable_user`'
    echo '              FOREIGN KEY (`authUser`) REFERENCES `autoJaxUser` (`autoJaxUserId`)'
    echo '              ON DELETE CASCADE ON UPDATE CASCADE;'
    echo ""
    echo "          -- SET NULL: keep the rows but clear the owner (the authUser column must be nullable)"
    echo '          ALTER TABLE `YourTable`'
    echo '              ADD CONSTRAINT `fk_YourTable_user`'
    echo '              FOREIGN KEY (`authUser`) REFERENCES `autoJaxUser` (`autoJaxUserId`)'
    echo '              ON DELETE SET NULL ON UPDATE CASCADE;'
    echo ""
    echo "  3. create the first admin account"
    echo "        there is no admin to promote yet, so create one from the command line. this prompts for a"
    echo "        display name, email, and password, and writes an active admin account directly:"
    echo ""
    echo "          php ${AUTOJAX_DIR}/util/autoJaxAdmin.php --secrets ${AUTOJAX_SECRETS_FILE} --create-admin"
    echo ""
    echo "        the same tool lists and manages accounts later (--list, --grant-admin WHO, --revoke-admin"
    echo "        WHO, --activate WHO, --set-password WHO; WHO is an autoJaxUserId or an email address)."
    echo ""
    echo "OPTIONAL:"
    echo ""
    echo "  - brand the auth pages in ${AUTOJAX_PARENT_DIR}/ with css and images as desired:"
    echo "      autojax-*.html, autojax-admin.css"
    echo ""
    if $HTACCESS_MERGE; then
        echo "  - an .htaccess already existed, so the auth pages' strict Content-Security-Policy was written to"
        echo "      ${AUTOJAX_PARENT_DIR}/autojax-headers.htaccess -- merge that block into your existing .htaccess"
        echo "      do NOT overwrite your .htaccess with it. the headers need Apache mod_headers (or replicate in nginx)"
    else
        echo "  - the auth pages' strict Content-Security-Policy was written to ${AUTOJAX_PARENT_DIR}/.htaccess and is active"
        echo "      it needs Apache mod_headers enabled, or replicate the headers in nginx -- see the autojax README"
    fi
    echo "      if you serve autojax/ from a different origin than the pages, add that origin to script-src"
    echo "      if you add your own inline <script> to a page, move it to a file or the CSP will block it"
    echo ""
    echo "To upgrade later: rebuild, then run the new dist's installer with this destination:"
    echo "      ${SOURCE_DIR}/install.sh --reinstall ${AUTOJAX_DIR}"
    echo "This re-copies autojax/ completely but leaves your possibly modified files untouched:"
    echo "      autojax-*.html, autojax-admin.css, autojax.json, autojax-headers.htaccess / .htaccess"
    echo ""
fi
