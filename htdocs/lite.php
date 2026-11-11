<?php
// Same as feed.php, but without full post content.
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

$category = requested_category();   // ?category=... narrows the feed to one tag

render_feed($smarty, 'lite' . ($category ? '-cat' . $category[0] : ''), [
    'title'       => 'evebloggers.com' . ($category ? " - {$category[1]}" : ''),
    'description' => 'A consolidated feed of posts from EVE bloggers (summaries only)',
    'self'        => SITE_URL . 'lite.php' . ($category ? '?category=' . rawurlencode($category[1]) : ''),
    'days'        => $category ? 90 : 7,
    'category_id' => $category[0] ?? null,
    'lite'        => true,
]);
