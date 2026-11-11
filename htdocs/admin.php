<?php
/**
 * Moderation: held feeds, pending submissions, failing feeds, blog statuses.
 * Login: password checked against ADMIN_PASSWORD_HASH (password_hash()) in .env.
 */
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

header('Cache-Control: no-store');
header('X-Frame-Options: DENY');
$smarty->setCaching(Smarty\Smarty::CACHING_OFF);

session_name('ebadmin');
session_set_cookie_params(['lifetime' => 0, 'path' => '/admin.php', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
session_start();

const STATUSES = [1 => 'active', 2 => 'retired', 0 => 'pending', -1 => 'polled, hidden', -2 => 'rejected'];

function redirect_self(string $flash = ''): never
{
    if ($flash !== '') {
        $_SESSION['flash'] = $flash;
    }
    header('Location: /admin.php', true, 303);
    exit;
}

// ---- login / logout -------------------------------------------------------
if (isset($_POST['password'])) {
    $hash = (string) eb_env('ADMIN_PASSWORD_HASH');
    if ($hash !== '' && password_verify((string) $_POST['password'], $hash)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        redirect_self();
    }
    sleep(2);   // slow down guessing
    error_log('admin.php: failed login from ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
    $smarty->assign('error', 'Wrong password.');
}
if (empty($_SESSION['admin'])) {
    $smarty->assign('loggedin', false);
    $smarty->display('admin.tpl');
    exit;
}

// ---- actions (POST + CSRF) ------------------------------------------------
$dbh = eb_db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Bad CSRF token; reload the page.');
    }
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: /', true, 303);
        exit;
    }

    $name = $dbh->prepare('select name from blogs_real where id = :id');
    $name->execute([':id' => $id]);
    $blogName = (string) $name->fetchColumn();
    if ($blogName === '') {
        redirect_self("No blog #$id.");
    }

    switch ($action) {
        case 'status':
            $status = (int) ($_POST['status'] ?? 1);
            if (!array_key_exists($status, STATUSES)) {
                redirect_self('Unknown status.');
            }
            $dbh->prepare('update blogs_real set approved = :s, held_reason = null, held_since = null where id = :id')
                ->execute([':s' => $status, ':id' => $id]);
            $flash = "#$id $blogName is now " . STATUSES[$status] . '.';
            break;

        case 'trust':
            // Accept the hosts the loader complained about, clear the hold, poll again next run.
            $row = $dbh->prepare('select held_reason from blogs_real where id = :id');
            $row->execute([':id' => $id]);
            $hosts = preg_match('/link to (.+?); blog is known/', (string) $row->fetchColumn(), $m)
                ? array_map('trim', explode(',', $m[1])) : [];
            $dbh->prepare(
                'update blogs_real
                    set allowed_hosts = (select array(select distinct unnest(allowed_hosts || :hosts::text[]))),
                        held_reason = null, held_since = null
                  where id = :id'
            )->execute([':hosts' => '{' . implode(',', array_map(fn($h) => '"' . addcslashes($h, '"\\') . '"', $hosts)) . '}', ':id' => $id]);
            $flash = "#$id $blogName: trusted " . (implode(', ', $hosts) ?: 'nothing') . '; it will be polled on the next run.';
            break;

        case 'release':
            $dbh->prepare('update blogs_real set held_reason = null, held_since = null where id = :id')->execute([':id' => $id]);
            $flash = "#$id $blogName released; it will be re-checked on the next run.";
            break;

        default:
            redirect_self('Unknown action.');
    }
    $smarty->clearAllCache();   // listings, OPML and directory reflect the change at once
    redirect_self($flash);
}

// ---- page -----------------------------------------------------------------
$held = $dbh->query(
    "select id, name, feedurl, url, held_reason, to_char(held_since at time zone 'UTC', 'DD-Mon HH24:MI') as since
       from blogs_real where held_reason is not null order by held_since"
)->fetchAll(PDO::FETCH_ASSOC);

$pending = $dbh->query(
    'select id, name, url, feedurl, owner, description from blogs_real where approved = 0 order by id desc'
)->fetchAll(PDO::FETCH_ASSOC);

// Failures from the most recent loader run.
$failing = [];
$lastRun = '';
foreach (glob(__DIR__ . '/../logs/loader/*.log') ?: [] as $file) {
    $lastRun = $file;   // glob sorts, so this ends on the newest day
}
if ($lastRun) {
    $run = [];
    foreach (file($lastRun, FILE_IGNORE_NEW_LINES) as $line) {
        if (str_contains($line, ' DONE ')) {
            $failing = $run;
            $run = [];
        } elseif (preg_match('/ FAIL  #(\d+) (.*?): (.*)$/', $line, $m)) {
            $run[(int) $m[1]] = ['id' => (int) $m[1], 'name' => $m[2], 'error' => mb_substr($m[3], 0, 140)];
        }
    }
}

$blogs = $dbh->query(
    "select b.id, b.name, b.url, b.feedurl, b.approved,
            to_char(max(e.\"pubDate\") at time zone 'UTC', 'DD-Mon-YYYY') as last_post
       from blogs_real b left join blog_entries e on e.blog = b.id
      group by b.id order by b.approved = 1 desc, max(e.\"pubDate\") desc nulls last"
)->fetchAll(PDO::FETCH_ASSOC);
foreach ($blogs as &$b) {
    $b['url'] = safe_url($b['url']);
}
unset($b);

$smarty->assign([
    'loggedin' => true,
    'csrf'     => $_SESSION['csrf'],
    'flash'    => $_SESSION['flash'] ?? '',
    'held'     => $held,
    'pending'  => $pending,
    'failing'  => array_values($failing),
    'blogs'    => $blogs,
    'statuses' => STATUSES,
]);
unset($_SESSION['flash']);
$smarty->display('admin.tpl');
