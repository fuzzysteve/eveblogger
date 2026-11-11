<?php
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

render_listing($smarty, 'home', false,
    'The five most recent posts from every EVE blog that has posted in the past year, most recently active first. Times are EVE time (UTC).');
