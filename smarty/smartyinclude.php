<?php // put full path to Smarty.class.php
require('/usr/local/lib/php/Smarty/Smarty.class.php');
$smarty = new Smarty();

$smarty->setTemplateDir('/home/web/evebloggers/smarty/templates');
$smarty->setCompileDir('/home/web/evebloggers/smarty/templates_c');
$smarty->setCacheDir('/home/web/evebloggers/smarty/cache');
$smarty->setConfigDir('/home/web/evebloggers/smarty/configs');

$smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

?>
