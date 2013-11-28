<?php require("/home/web/evebloggers/smarty/smartyinclude.php");
$smarty->setCacheLifetime(0);


if ($_POST['submit']=="Add Blog")
{

$addsql="insert into blogs_real(name,url,feedurl,owner,lastupdate,description,approved) values (:name,:url,:feedurl,:owner,to_timestamp(0),:description,0)";
require_once("dbpost.inc.php");

$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try{
$addstmt=$dbh->prepare($addsql);

$addstmt->execute(array(":name"=>substr(strip_tags($_POST['blogtitle']),0,255),":url"=>substr(strip_tags($_POST['url']),0,255),":feedurl"=>substr(strip_tags($_POST['feedurl']),0,255),":owner"=>substr(strip_tags($_POST['author']),0,255),":description"=>substr(strip_tags($_POST['description']),0,255)));
$smarty->display("addblog.tpl");

mail("steve@evebloggers.com","New blog Added","a new blog with a title of '".substr(strip_tags($_POST['blogtitle']),0,255)."' has been added");


} catch (PDOException $e){
echo "Something went wrong. You should let Steve know";
# . $e->getMessage();

}


exit;
}
else
{
$smarty->display("addform.tpl");
}
