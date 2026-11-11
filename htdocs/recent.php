<?php
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

$category = requested_category();
if (isset($_GET['category']) && !$category) {
    // Unknown tag: small uncached 404, so it can never land in the shared 'all' cache entry.
    http_response_code(404);
    $smarty->setCaching(Smarty\Smarty::CACHING_OFF);
    $smarty->assign(['category' => null, 'unknown' => true, 'days' => 7, 'posts' => []]);
    $smarty->display('recent.tpl');
    exit;
}
$cacheId = $category ? 'cat' . $category[0] : 'all';

$smarty->setCacheLifetime(300);
if (!$smarty->isCached('recent.tpl', $cacheId)) {
    $smarty->assign('category', $category[1] ?? null);
    $smarty->assign('days', $category ? 90 : 7);
    $smarty->assign('posts', recent_posts(eb_db(), $category ? 90 : 7, $category[0] ?? null));
}
$smarty->display('recent.tpl', $cacheId);
