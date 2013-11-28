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
select id,link,title,to_char("pubDate",'HH24:MI DD-Mon-YY') "pubDate",author,blog,description from (select id,link,title,"pubDate",author,blog,description,row_number() over(partition by blog order by "pubDate" desc) as row from blog_entries) a where a.row<=5 order by blog,row
EOS;

$stmt = $dbh->prepare($sql);

$stmt->execute();

$entries=array();
while ($row = $stmt->fetchObject()){
$entries[$row->blog][$row->id]=array("link"=>$row->link,"author"=>$row->author,"blog"=>$row->blog,"title"=>$row->title,"description"=>$row->description,"date"=>$row->pubDate);
}


$smarty->assign("posts",$entries);
$smarty->assign("blognames",$blog);
$smarty->assign("blogs",array_keys($blog));




$smarty->display("posts.tpl");
