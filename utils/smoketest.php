<?php
/**
 * Smoke test for the live site. Run after any change:
 *
 *   php utils/smoketest.php            hit this server directly (https, origin certificate)
 *   php utils/smoketest.php --public   go through Cloudflare like a visitor
 *
 * Exits non-zero if anything fails. Read-only: it only makes GET requests.
 */
declare(strict_types=1);

const HOST = 'evebloggers.com';
const ERROR_LOG_DIR = __DIR__ . '/../logs';

$public = in_array('--public', $argv, true);
$failures = 0;

function fetch(string $path, bool $public): array
{
    $ch = curl_init('https://' . HOST . $path);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => 'evebloggers-smoketest',
    ];
    if (!$public) {
        $opts[CURLOPT_RESOLVE] = [HOST . ':443:127.0.0.1'];
    }
    curl_setopt_array($ch, $opts);
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $err = curl_error($ch);
    curl_close($ch);
    $headers = [];
    foreach (explode("\r\n", substr($raw, 0, $headerSize)) as $line) {
        if (str_contains($line, ':')) {
            [$k, $v] = explode(':', $line, 2);
            $headers[strtolower(trim($k))] = trim($v);
        }
    }
    return [$code, $headers, substr($raw, $headerSize), $err];
}

function check(string $label, bool $ok, string $detail = ''): void
{
    global $failures;
    if (!$ok) {
        $failures++;
    }
    printf("%-4s %-46s %s\n", $ok ? 'ok' : 'FAIL', $label, $detail);
}

function xml_entries(string $body): ?int
{
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($body);
    if ($xml === false) {
        return null;
    }
    return max(count($xml->channel->item ?? []), count($xml->body->outline ?? []));
}

// Error log size before, to spot anything this run triggers.
$errorLog = ERROR_LOG_DIR . '/' . date('Y/m/d') . '/error.log';
$errorLogStart = is_file($errorLog) ? filesize($errorLog) : 0;

echo 'Smoke test against ', $public ? 'Cloudflare (public)' : 'this server (origin)', "\n";

// [path, expected status, check: needle in body | 'xml' | 'xml-feed']
$pages = [
    ['/',                                   200, 'class="site col-md-6'],
    ['/recent.php',                         200, '<li><a href='],
    ['/recent.php?category=eve%20online',   200, 'Posts tagged'],
    ['/recent.php?category=zz-no-such-tag', 404, 'No such tag'],
    ['/blogs.php',                          200, '<td class="text-end">'],
    ['/search.php?q=wormhole',              200, 'matches for'],
    ['/search.php',                         200, 'name="q"'],
    ['/add.php',                            200, 'g-recaptcha'],
    ['/admin.php',                          200, 'type="password"'],
    ['/feed.php',                           200, 'xml-feed'],
    ['/lite.php',                           200, 'xml-feed'],
    ['/feed.php?category=eve%20online',     200, 'xml'],
    ['/opml.php',                           200, 'xml-feed'],
    ['/assets/theme.js',                    200, 'data-bs-theme'],
    ['/assets/site.css',                    200, '.datespan'],
    ['/test.php',                           404, ''],
    ['/csm.php',                            404, ''],
];

foreach ($pages as [$path, $want, $expect]) {
    [$code, $headers, $body, $err] = fetch($path, $public);
    $ok = $code === $want;
    $detail = "HTTP $code" . ($err ? " ($err)" : '');
    if ($ok && str_starts_with($expect, 'xml')) {
        $n = xml_entries($body);
        $ok = $n !== null && ($expect === 'xml' || $n > 0);
        $detail .= $n === null ? ', invalid XML' : ", valid XML, $n entries";
    } elseif ($ok && $expect !== '') {
        $ok = str_contains($body, $expect);
        $detail .= $ok ? '' : ", missing '$expect'";
    }
    check($path, $ok, $detail);
}

// Security headers on a normal page.
[, $headers] = fetch('/', $public);
check('header: Content-Security-Policy', str_contains($headers['content-security-policy'] ?? '', "default-src 'self'"));
check('header: Strict-Transport-Security', str_contains($headers['strict-transport-security'] ?? '', 'max-age='),
    $headers['strict-transport-security'] ?? 'missing');
check('header: X-Content-Type-Options', ($headers['x-content-type-options'] ?? '') === 'nosniff');

// Plain HTTP should redirect to HTTPS (only meaningful through Cloudflare / port 80).
$ch = curl_init('http://' . HOST . '/recent.php');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_TIMEOUT => 15]
    + ($public ? [] : [CURLOPT_RESOLVE => [HOST . ':80:127.0.0.1']]));
curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$location = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);
check('http -> https redirect', $code === 301 && str_starts_with($location, 'https://' . HOST . '/'), "HTTP $code $location");

// Loader health.
$lastDone = 0;
foreach (glob(__DIR__ . '/../logs/loader/*.log') ?: [] as $file) {
    foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
        if (preg_match('/^(\S+) DONE /', $line, $m)) {
            $lastDone = max($lastDone, (int) strtotime($m[1]));
        }
    }
}
check('loader finished a run in the last 2h', $lastDone > time() - 7200,
    $lastDone ? 'last ' . gmdate('H:i', $lastDone) . ' UTC' : 'no runs found');

// New PHP errors caused by this run?
clearstatcache();
$new = is_file($errorLog) ? (string) file_get_contents($errorLog, false, null, $errorLogStart) : '';
preg_match_all('/PHP (Fatal error|Warning|Parse error|Deprecated|Notice):\s+([^;\']{0,120})/', $new, $m, PREG_SET_ORDER);
check('no new PHP errors in the error log', !$m, $m ? count($m) . ' new, e.g. ' . $m[0][1] . ': ' . $m[0][2] : '');

echo $failures ? "\n$failures check(s) FAILED\n" : "\nAll checks passed\n";
exit($failures ? 1 : 0);
