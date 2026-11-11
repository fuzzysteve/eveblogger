<?php
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

header('Content-Type: text/x-opml; charset=UTF-8');
$smarty->setCacheLifetime(300);
if (!$smarty->isCached('opml.tpl')) {
    $feeds = [];
    foreach (eb_db()->query('select name, feedurl, url from blogs where approved in (1, 3) order by name') as $row) {
        $feedurl = safe_url($row['feedurl']);
        if ($feedurl !== '') {
            $feeds[] = ['name' => xml_clean($row['name']), 'feedurl' => $feedurl, 'url' => safe_url($row['url'])];
        }
    }
    $smarty->assign('feeds', $feeds);
    $smarty->assign('updated', gmdate(DATE_RFC2822));
}
$smarty->display('opml.tpl');
