<?php
header('Content-Type: application/rss+xml;charset=UTF-8');
require("/home/web/evebloggers/smarty/smartyinclude.php");
$smarty->setCacheLifetime(300);
require("dbpost.inc.php");


$sql=<<<EOS
select link,title,to_char("pubDate", 'Dy, DD Mon YYYY HH24:MI:SS +0100') "pubDated",author,description,content from blog_entries  where "pubDate">current_date - interval '7 days' order by "pubDate" desc
EOS;

$stmt = $dbh->prepare($sql);

$stmt->execute();

$entries=array();
while ($row = $stmt->fetchObject()){
$entries[]=array("link"=>$row->link,"author"=>$row->author,"title"=>utf8_encode($row->title),"description"=>$row->description,"content"=>$row->content,"date"=>$row->pubDated);
}


$smarty->assign("posts",$entries);


$smarty->display("feed.tpl");?>


