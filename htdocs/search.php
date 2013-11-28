<?
require_once("dbpost.inc.php");
require_once("../utils/indexer/init.php");
require("/home/web/evebloggers/smarty/smartyinclude.php");



if (isset($_POST['query']))
{

$client = new Solarium_Client($config);
$query = $client->createSelect();

$querystring=substr($_POST['query'],0,255);

$query->setQuery($querystring);
$query->setFields(array('id'));
$query->addSort('last_modified', Solarium_Query_Select::SORT_DESC);

$resultset = $client->select($query);

$smarty->assign("resultnumber",$resultset->getNumFound());


foreach ($resultset as $document) {
    $results[]=$document['id'];
}

$sql=<<<EOS
select title,blog_entries.description,to_char("pubDate", 'Dy, DD Mon YYYY HH24:MI:SS +0100') "pubDated",link,author,name from blog_entries join blogs on (blog_entries.blog=blogs.id) where blog_entries.id in (
EOS;
$sql.=join(",",$results).') order by "pubDate" desc';

$resultstmt=$dbh->prepare($sql);
$entries=array();
$resultstmt->execute();

while ($row = $resultstmt->fetchObject()){
$entries[]=array("link"=>$row->link,"author"=>$row->author,"title"=>utf8_encode($row->title),"description"=>$row->description,"date"=>$row->pubDated,"name"=>$row->name);
}




$smarty->assign("posts",$entries);
$smarty->display("query.tpl");



}

?>

