<?php
require __DIR__ . '/../smarty/smartyinclude.php';
require_once __DIR__ . '/../lib/site.php';
$smarty->setCaching(Smarty\Smarty::CACHING_OFF);

function show_form(Smarty\Smarty $smarty, array $form = [], array $errors = []): never
{
    $smarty->assign('recaptcha_site_key', eb_env('RECAPTCHA_SITE_KEY'));
    $smarty->assign('form', $form);
    $smarty->assign('errors', $errors);
    $smarty->display('addform.tpl');
    exit;
}

if (($_POST['submit'] ?? '') !== 'Add Blog') {
    show_form($smarty);
}

$form = [];
foreach (['blogtitle' => 200, 'author' => 200, 'url' => 255, 'feedurl' => 255, 'description' => 255] as $name => $max) {
    $form[$name] = mb_substr(trim(strip_tags((string) ($_POST[$name] ?? ''))), 0, $max);
}

$errors = [];
if ($form['blogtitle'] === '') {
    $errors[] = 'Please give the blog title.';
}
if ($form['author'] === '') {
    $errors[] = 'Please give the primary author.';
}
if (safe_url($form['feedurl']) === '' || !filter_var($form['feedurl'], FILTER_VALIDATE_URL)) {
    $errors[] = 'The feed URL needs to be a full http:// or https:// address.';
}
if ($form['url'] !== '' && (safe_url($form['url']) === '' || !filter_var($form['url'], FILTER_VALIDATE_URL))) {
    $errors[] = 'The site URL needs to be a full http:// or https:// address.';
}

$recaptcha = new \ReCaptcha\ReCaptcha(eb_env('RECAPTCHA_SECRET'));
if (!$recaptcha->verify($_POST['g-recaptcha-response'] ?? '', $_SERVER['REMOTE_ADDR'])->isSuccess()) {
    $errors[] = 'The captcha check failed; please try again.';
}

if ($errors) {
    show_form($smarty, $form, $errors);
}

try {
    eb_db()->prepare(
        'insert into blogs_real(name, url, feedurl, owner, lastupdate, description, approved)
         values (:name, :url, :feedurl, :owner, to_timestamp(0), :description, 0)'
    )->execute([
        ':name'        => $form['blogtitle'],
        ':url'         => $form['url'],
        ':feedurl'     => $form['feedurl'],
        ':owner'       => $form['author'],
        ':description' => $form['description'],
    ]);
} catch (PDOException $e) {
    error_log('add.php insert failed: ' . $e->getMessage());
    show_form($smarty, $form, ['Something went wrong saving that. You should let Steve know.']);
}

$smarty->display('addblog.tpl');

mail(
    'steve@evebloggers.com',
    'New blog Added',
    "A new blog has been submitted for approval:\n\n"
    . "Title: {$form['blogtitle']}\nAuthor: {$form['author']}\nSite: {$form['url']}\nFeed: {$form['feedurl']}\n"
);
