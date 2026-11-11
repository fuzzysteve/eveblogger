<?php
header('Content-Type: application/rss+xml;charset=UTF-8');
require("header.php");
require("/home/web/evebloggers/smarty/smartyinclude.php");
$smarty->setCacheLifetime(1800);
require("dbpost.inc.php");

if(!$smarty->isCached('feed.tpl',1)) {

$sql=<<<EOS
select link,title,to_char("pubDate", 'Dy, DD Mon YYYY HH24:MI:SS +0100') "pubDated",author,blog_entries.description from blog_entries join blogs on (blog_entries.blog=blogs.id) where "pubDate">current_date - interval '7 days' order by "pubDate" desc
EOS;

$stmt = $dbh->prepare($sql);

$stmt->execute();

$entries=array();
while ($row = $stmt->fetchObject()){
$entries[]=array("link"=>$row->link,"author"=>$row->author,"title"=>filter_var(utf8_encode($row->title),FILTER_SANITIZE_SPECIAL_CHARS),"description"=>$row->description,"date"=>$row->pubDated);
}
$smarty->assign("description","A consolidated feed of posts from eve bloggers");
$smarty->assign("linkurl","http://evebloggers.com/feed.php");
$smarty->assign("title","evebloggers.com");


$smarty->assign("posts",$entries);
$smarty->assign("lite",1);
}

$smarty->display("feed.tpl",1);?>


