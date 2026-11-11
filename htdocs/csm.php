<?php
require __DIR__ . '/header.php';
require __DIR__ . '/../smarty/smartyinclude.php';
require __DIR__ . '/../lib/site.php';

// Few CSM blogs are still active, so no recency cut-off here.
render_listing($smarty, 'csm', true,
    'The five most recent posts from blogs written by CSM members. Times are EVE time (UTC).', 36500);
