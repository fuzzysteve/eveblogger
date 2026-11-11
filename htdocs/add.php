<?php
require __DIR__ . '/../smarty/smartyinclude.php';
require_once __DIR__ . '/../lib/bootstrap.php';
$smarty->setCacheLifetime(0);

if (($_POST['submit'] ?? '') !== 'Add Blog') {
    $smarty->assign('recaptcha_site_key', eb_env('RECAPTCHA_SITE_KEY'));
    $smarty->display('addform.tpl');
    exit;
}

$recaptcha = new \ReCaptcha\ReCaptcha(eb_env('RECAPTCHA_SECRET'));
$resp = $recaptcha->verify($_POST['g-recaptcha-response'] ?? '', $_SERVER['REMOTE_ADDR']);
if (!$resp->isSuccess()) {
    echo 'Bad captcha answer';
    exit;
}

$field = fn(string $name): string => substr(strip_tags($_POST[$name] ?? ''), 0, 255);

try {
    require_once 'dbpost.inc.php';
    $addstmt = $dbh->prepare(
        'insert into blogs_real(name,url,feedurl,owner,lastupdate,description,approved)
         values (:name,:url,:feedurl,:owner,to_timestamp(0),:description,0)'
    );
    $addstmt->execute([
        ':name'        => $field('blogtitle'),
        ':url'         => $field('url'),
        ':feedurl'     => $field('feedurl'),
        ':owner'       => $field('author'),
        ':description' => $field('description'),
    ]);
} catch (PDOException $e) {
    error_log('add.php insert failed: ' . $e->getMessage());
    echo 'Something went wrong. You should let Steve know';
    exit;
}

$smarty->display('addblog.tpl');

mail(
    'steve@evebloggers.com',
    'New blog Added',
    "a new blog with a title of '" . $field('blogtitle') . "' has been added"
);
