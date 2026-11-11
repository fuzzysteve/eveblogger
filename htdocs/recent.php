<?php
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

$smarty->setCacheLifetime(300);
if (!$smarty->isCached('recent.tpl')) {
    $stmt = eb_db()->query(<<<SQL
        select e.link, e.title, e.author, e.description, b.name,
               to_char(e."pubDate" at time zone 'UTC', 'HH24:MI DD-Mon-YY') as pubdate
          from blog_entries e
          join blogs b on b.id = e.blog
         where e."pubDate" > now() - interval '7 days'
         order by e."pubDate" desc
        SQL);
    $posts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $row['link'] = safe_url($row['link']);
        $posts[] = $row;
    }
    $smarty->assign('posts', $posts);
}
$smarty->display('recent.tpl');
