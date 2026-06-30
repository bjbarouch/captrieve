#!/usr/bin/env bash

# =============================================================================
# mail-probe.sh -- autojax email-delivery probe
#
# autojax uses email for two things only -- account-confirmation links and
# password-reset links. if email cannot send, new users cannot register without
# your assistance, and established users cannot reset a forgotten password
# without your assistance.
#
# the default "sendmail" transport hands each message to the local mail system,
# which queues and delivers it out of process so no web worker is tied up on the
# send. a sendmail binary being present is necessary but NOT proof of delivery:
# on macOS and minimal Linux the binary exists while the mail daemon is dormant
# or has no relay, so handed-off mail is queued and silently never delivered.
# the only certain test is to send a real message and confirm a human received
# it. this script does exactly that, in a single run.
#
# install.sh runs this for you as the first step of a fresh install and reads
# the verdict from this script's exit status -- nothing is written to disk, so
# two projects on one host never confound each other. you can also run it on its
# own at any time to re-test (for example after the host's mail setup changes)
# and then set "emailTransport" in autojax.json to match.
#
# the verdict is the EXIT STATUS:
#     0  -- delivery through the local mail system (sendmail) is verified
#     2  -- use the smtp transport instead
#     1  -- aborted or unanswered; the caller must not proceed
#
# usage:  bash mail-probe.sh [--embedded]
# --embedded tailors the closing text for the case where install.sh invoked it
# and will continue automatically. informational output goes to stdout; only the
# questions are read from the terminal (/dev/tty), so it also works when piped.
#
# requires: bash 3.2+
# =============================================================================

set -euo pipefail

EMBEDDED=false
if [ "${1:-}" = "--embedded" ]; then
    EMBEDDED=true
fi

# print a prompt to the terminal and echo the typed answer on stdout, so the
# caller can capture it with $( ). reads from /dev/tty so it works when piped
ask() {
    local prompt="$1" value
    printf "\n%s\n    : " "$prompt" > /dev/tty
    IFS= read -r value < /dev/tty
    printf '%s' "$value"
}

# echo the first usable sendmail-compatible binary on stdout, or nothing
find_sendmail() {
    local cand
    for cand in "$(command -v sendmail 2>/dev/null)" /usr/sbin/sendmail /usr/lib/sendmail /usr/bin/sendmail; do
        if [ -n "$cand" ] && [ -x "$cand" ]; then
            printf '%s' "$cand"
            return 0
        fi
    done
    return 0
}

print_smtp_tradeoffs() {
    cat << 'ENDMSG'

You will use SMTP for email, which opens an authenticated SMTP connection
to a mail server you name. This can be problematic:

  - You must identify an SMTP server on which you have an account you can use
    for autojax purposes, plus the user name and password for that account.
    These three details (server, name, and password) will be stored in plaintext
    in a file on this computer.
    You will choose where that file resides so you can keep it safe.
    If at any point any of those three details change, you will have to update
    them in the config file.
  - Each email send is an authenticated round trip made inline, while a web
    worker waits.
    autojax runs on a small fixed pool of workers, limited by your system.
    A slow or hung SMTP server pins that worker for the whole exchange.
    This is not a problem during low traffic periods, but during a signup burst,
    a few slow sends can exhaust the worker pool and stall the entire site, not
    just email.
    That's how SMTP works. It's not a choice made by autojax.
  - Also, SMTP providers grey-list and rate-limit new senders, and may even
    block what they deem to be "excessive" senders, so you might have to
    negotiate with the SMTP provider you identify here so they don't assume
    you are sending spam.

The "sendmail" transport avoids all of this by handing mail to a local mail
system that delivers out of process. If you have access to sendmail on this
system, use that instead.
ENDMSG
}

# hand a test message to the local mail system. returns the binary's exit status
send_test() {
    local bin="$1" addr="$2" host
    host="$(hostname -f 2>/dev/null || hostname 2>/dev/null || echo localhost)"
    printf 'From: autojax mail probe <autojax-mail-probe@%s>\nTo: %s\nSubject: autojax mail probe -- delivery test\n\nIf you are reading this, this host delivered a message through its local mail\nsystem (sendmail). autojax uses that path for account-confirmation and\npassword-reset links.\n\nReturn to the terminal and tell mail-probe.sh that this arrived.\n' \
        "$host" "$addr" | "$bin" -i -t
}

# the host cannot (or will not) deliver locally: explain the smtp cost and exit
# with the "use smtp" verdict
use_smtp() {
    print_smtp_tradeoffs
    if $EMBEDDED; then
        printf '\nThe installer will use the smtp transport and prompt you for the server and credentials.\n'
    else
        printf '\nUse the smtp transport: set "emailTransport" to "smtp" in autojax.json.\n'
    fi
    exit 2
}

# delivery confirmed: exit with the "sendmail" verdict
use_sendmail() {
    printf '\nConfirmed. This host delivers mail through its local mail system.\n'
    if $EMBEDDED; then
        printf 'The installer will use the "sendmail" transport; no SMTP credentials are needed.\n'
    else
        printf 'Use the "sendmail" transport: set "emailTransport" to "sendmail" in autojax.json.\n'
    fi
    exit 0
}

# send a real message, then wait inline for the human to confirm it arrived.
# resends on request and loops until the answer is yes or no, so the whole probe
# completes in one run with no state left on disk
run_test_flow() {
    local bin addr answer
    bin="$(find_sendmail)"
    if [ -z "$bin" ]; then
        cat << 'ENDMSG'

No sendmail binary was found in the standard locations on this host
(/usr/sbin/sendmail, /usr/lib/sendmail, /usr/bin/sendmail, or on PATH).
This host has no local mail system to test, so the "sendmail" transport
cannot work here.
ENDMSG
        use_smtp
    fi
    printf '\nFound a local mail interface at %s (necessary, but not proof of delivery).\n' "$bin"
    addr="$(ask "Email address you can check right now (the test message goes there)")"
    while [ -z "$addr" ]; do
        addr="$(ask "(required) email address for the test message")"
    done
    if ! send_test "$bin" "$addr"; then
        cat << 'ENDMSG'

The send command failed. This host cannot deliver email via sendmail.
ENDMSG
        use_smtp
    fi
    cat << ENDMSG

Test message handed to the local mail system, addressed to ${addr}.
This does NOT mean it was delivered. It may sit in a queue that never drains,
or be rejected or junked by the receiving provider, or swallowed by a spam
filter, or land in your own spam folder instead of your inbox.

Check your inbox and spam folder now. Delivery can take a few minutes, so leave
this prompt open and check from a phone or another window if you like, then
answer here. Type "resend" to send another copy.
ENDMSG
    while true; do
        answer="$(ask "Did the test message arrive? [yes / no / resend]")"
        case "$answer" in
            y|Y|yes|Yes|YES)
                use_sendmail
                ;;
            n|N|no|No|NO)
                cat << 'ENDMSG'

The message did not arrive. This host cannot deliver mail as configured, so you
will have to use an SMTP server.
ENDMSG
                use_smtp
                ;;
            r|R|resend|Resend|RESEND)
                if send_test "$bin" "$addr"; then
                    printf '\nResent to %s. Check again.\n' "$addr"
                else
                    cat << 'ENDMSG'

The resend failed. This host cannot deliver email via sendmail.
ENDMSG
                    use_smtp
                fi
                ;;
            *)
                printf '\nPlease answer "yes", "no", or "resend".\n'
                ;;
        esac
    done
}

main() {
    cat << 'ENDMSG'

autojax email-delivery probe
============================

autojax uses email for two things only -- account-confirmation links for new
user self-registration, and password-reset links for forgotten passwords.
Without email, new users cannot self-register without your assistance, and
established users cannot reset a forgotten password without your assistance.

This probe finds out whether this host can directly deliver email or must make
use of an SMTP server you specify.
ENDMSG
    local choice
    choice="$(ask "Do you know whether this host can deliver mail?
    1) I know it CAN
    2) I know it CANNOT
    3) I don't know
Enter 1, 2, or 3")"
    case "$choice" in
        2)
            use_smtp
            ;;
        1|3)
            run_test_flow
            ;;
        *)
            printf '\nUnrecognized answer. Run the script again and enter 1, 2, or 3.\n'
            exit 1
            ;;
    esac
}

main
