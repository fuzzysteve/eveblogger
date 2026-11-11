<?php
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

header('Cache-Control: no-cache');
$smarty->setCaching(Smarty\Smarty::CACHING_OFF);

$query = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 200);
$smarty->assign('query', $query);
$smarty->assign('posts', $query === '' ? [] : search_posts(eb_db(), $query));
$smarty->display('search.tpl');
