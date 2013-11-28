<?php require("/home/web/evebloggers/smarty/smartyinclude.php");
$smarty->setCacheLifetime(300);



require_once("dbpost.inc.php");

$blogsql="select name,id,url from blogs order by id asc";

$stmt = $dbh->prepare($blogsql);

$stmt->execute();
$blog=array();
while ($row = $stmt->fetchObject()){
$blog[$row->id]=array("name"=>$row->name,"url"=>$row->url);
}



$sql=<<<EOS
select id,link,title,to_char("pubDate",'HH24:MI DD-Mon-YY') "pubDated",author,blog,description from (select id,link,title,"pubDate",author,blog,description from blog_entries where  "pubDate">current_date - interval '7 days') a order by "pubDate" desc 
EOS;

$stmt = $dbh->prepare($sql);

$stmt->execute();

$entries=array();
while ($row = $stmt->fetchObject()){
$entries[$row->id]=array("link"=>$row->link,"author"=>$row->author,"blog"=>$row->blog,"title"=>$row->title,"description"=>$row->description,"date"=>$row->pubDated);
}


$smarty->assign("posts",$entries);
$smarty->assign("blognames",$blog);




$smarty->display("recent.tpl");
