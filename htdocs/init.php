<?php
error_reporting(E_ALL);
ini_set('display_errors', true);


require __DIR__ . '/vendor/autoload.php';

$config = array(
    'endpoint' => array(
        'localhost' => array (
            'host' => '127.0.0.1',
            'port' => 8983,
            'path' => '/',
            'core' => 'evebloggers'
        )
    )
);


?>
