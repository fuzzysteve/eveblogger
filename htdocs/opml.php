<?php
header('Content-Type: text/xml;charset=UTF-8');
require("/home/web/evebloggers/smarty/smartyinclude.php");
$smarty->setCacheLifetime(300);
require("dbpost.inc.php");


$sql=<<<EOS
select feedurl,name from blogs
EOS;

$stmt = $dbh->prepare($sql);

$stmt->execute();

$feeds=array();
while ($row = $stmt->fetchObject()){
$feeds[]=array("feedurl"=>$row->feedurl,"name"=>$row->name);
}


$smarty->assign("feeds",$feeds);


$smarty->display("opml.tpl");?>


