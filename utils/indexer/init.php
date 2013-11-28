<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require('autoload.php');

if (file_exists('config.php')) {
    require('config.php');
} else {
    require('config.dist.php');
}



?>
