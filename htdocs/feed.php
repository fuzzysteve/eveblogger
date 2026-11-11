<?php
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

render_feed($smarty, 'all', [
    'title'       => 'evebloggers.com',
    'description' => 'A consolidated feed of posts from EVE bloggers',
    'self'        => SITE_URL . 'feed.php',
    'days'        => 7,
]);
