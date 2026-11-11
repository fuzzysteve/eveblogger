<?php
/**
 * Hourly from cron: alert (Discord) when the site has quietly stopped working.
 * Each problem is announced once, repeated every REPEAT_HOURS while it lasts,
 * and followed by a "recovered" message when it clears.
 *
 *   php healthcheck.php            check and alert
 *   php healthcheck.php --dry-run  print what would be sent
 */
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/alert.php';

const LOADER_LOG_DIR = __DIR__ . '/../logs/loader';
const STATE_FILE = __DIR__ . '/../logs/healthcheck-state.json';
const LOADER_STALE_HOURS = 2;    // cron runs it every 30 minutes
const NO_POSTS_DAYS = 3;         // ~15 active blogs; 3 quiet days means something's wrong
const PENDING_DAYS = 3;          // submissions waiting for a decision
const REPEAT_HOURS = 24;

$dryRun = in_array('--dry-run', $argv, true);
$problems = [];   // key => message

// 1. Has the loader finished a run recently, and without fatals?
$lastDone = 0;
$fatal = '';
foreach (glob(LOADER_LOG_DIR . '/*.log') ?: [] as $file) {
    if (filemtime($file) < time() - 86400 * 2) {
        continue;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
        if (preg_match('/^(\S+) DONE /', $line, $m)) {
            $lastDone = max($lastDone, (int) strtotime($m[1]));
        } elseif (preg_match('/PHP (Fatal error|Parse error)/', $line)) {
            $fatal = mb_substr($line, 0, 300);
        }
    }
}
if ($lastDone < time() - LOADER_STALE_HOURS * 3600) {
    $problems['loader'] = $lastDone
        ? 'The feed loader has not finished a run since ' . gmdate('D H:i', $lastDone) . ' UTC.'
        : 'The feed loader has not finished a run in the last two days.';
    if ($fatal !== '') {
        $problems['loader'] .= " Last fatal error in its log: `$fatal`";
    }
}

// 2./3. Database checks (also proves the DB is reachable).
try {
    $dbh = eb_db();
    $newest = $dbh->query('select extract(epoch from max(date_entered))::bigint from blog_entries')->fetchColumn();
    if (!$newest || $newest < time() - NO_POSTS_DAYS * 86400) {
        $problems['fresh'] = sprintf('No new posts have been stored for %d days (last: %s UTC).',
            NO_POSTS_DAYS, $newest ? gmdate('d M H:i', (int) $newest) : 'never');
    }
    $pending = (int) $dbh->query('select count(*) from blogs_real where approved = 0')->fetchColumn();
    if ($pending > 0) {
        $oldest = $dbh->query('select min(id) from blogs_real where approved = 0')->fetchColumn();
        // No created_at column, so "waiting" is measured from when this check first saw it (below).
        $problems['pending'] = "$pending blog submission(s) waiting for approval (oldest #$oldest): https://evebloggers.com/admin.php";
    }
} catch (Throwable $e) {
    $problems['db'] = 'Cannot query the database: ' . $e->getMessage();
}

// ---- alert on changes ------------------------------------------------------
$state = is_readable(STATE_FILE) ? (json_decode((string) file_get_contents(STATE_FILE), true) ?: []) : [];
$now = time();
$messages = [];

foreach ($problems as $key => $message) {
    $first = $state[$key]['since'] ?? $now;
    $lastSent = $state[$key]['sent'] ?? 0;
    // Pending submissions are normal for a few days; only nag once they've waited a while.
    $due = $key === 'pending' ? ($now - $first >= PENDING_DAYS * 86400) : true;
    if ($due && $now - $lastSent >= REPEAT_HOURS * 3600) {
        $messages[] = ($lastSent ? 'Still: ' : '') . $message;
        $lastSent = $now;
    }
    $state[$key] = ['since' => $first, 'sent' => $lastSent];
}
foreach (array_diff_key($state, $problems) as $key => $info) {
    if (!empty($info['sent'])) {
        $messages[] = "Recovered: $key is OK again (problem since " . gmdate('d M H:i', (int) $info['since']) . ' UTC).';
    }
    unset($state[$key]);
}

foreach ($messages as $message) {
    if ($dryRun) {
        echo "WOULD SEND: $message\n";
    } else {
        eb_alert($message);
        echo date('c'), " sent: $message\n";
    }
}
if (!$dryRun) {
    file_put_contents(STATE_FILE, json_encode($state, JSON_PRETTY_PRINT));
}
if (!$messages) {
    echo date('c'), ' ok', $problems ? ' (known: ' . implode(', ', array_keys($problems)) . ')' : '', "\n";
}
