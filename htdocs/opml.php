<?php
header('Content-Type: text/xml;charset=UTF-8');
require("header.php");
require("/home/web/evebloggers/smarty/smartyinclude.php");
$smarty->setCacheLifetime(300);
require("dbpost.inc.php");


$sql=<<<EOS
select feedurl,name from blogs where approved=1
EOS;

$stmt = $dbh->prepare($sql);

$stmt->execute();

$feeds=array();
while ($row = $stmt->fetchObject()) {
    $feeds[]=array("feedurl"=>$row->feedurl, "name"=>filter_var($row->name, FILTER_SANITIZE_SPECIAL_CHARS, array('flags'=>FILTER_FLAG_STRIP_LOW)));
}


$smarty->assign("feeds", $feeds);


$smarty->display("opml.tpl");
