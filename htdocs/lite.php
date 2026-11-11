<?php
// Same as feed.php, but without full post content.
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

render_feed($smarty, 'lite', [
    'title'       => 'evebloggers.com',
    'description' => 'A consolidated feed of posts from EVE bloggers (summaries only)',
    'self'        => SITE_URL . 'lite.php',
    'days'        => 7,
    'lite'        => true,
]);
