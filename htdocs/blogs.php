<?php
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

$smarty->setCacheLifetime(3600);
if (!$smarty->isCached('blogs.tpl')) {
    $smarty->assign('blogs', blog_directory(eb_db()));
}
$smarty->display('blogs.tpl');
