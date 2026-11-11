#!/bin/bash
# Poll all blog feeds. Called from root's crontab at :15 and :45.
set -u

LOGDIR=/home/web/evebloggers/logs/loader
mkdir -p "$LOGDIR"
LOG="$LOGDIR/$(date +%F).log"

cd /home/web/evebloggers/utils || exit 1

# Skip this run if the previous one (slow feeds) is still going.
flock -n /run/lock/evebloggers-loader.lock /usr/bin/php loadblogentries.php >>"$LOG" 2>&1 \
    || echo "$(date -Is) loader skipped or failed (exit $?)" >>"$LOG"

find "$LOGDIR" -name '*.log' -mtime +30 -delete
