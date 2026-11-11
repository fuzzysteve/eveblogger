<?php
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

render_feed($smarty, 'csm', [
    'title'       => 'evebloggers.com CSM feed',
    'description' => 'Posts from the blogs of CSM members',
    'self'        => SITE_URL . 'csmfeed.php',
    'days'        => 7,
    'csm'         => true,
]);
