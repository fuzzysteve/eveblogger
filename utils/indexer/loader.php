<?php
require_once("dbpost.inc.php");



require('init.php');


// create a client instance
$client = new Solarium\Client($config);



$sql=<<<EOS
select id,link,title,to_char("pubDate",'YYYY-MM-DD"T"HH24:MI:SSZ') date,description,content,author from blog_entries where indexed=0
EOS;

$entriesstmt=$dbh->prepare($sql);
$entriesstmt->execute();

$updatesql="update blog_entries set indexed=1 where id=:id";

$updatestmt=$dbh->prepare($updatesql);

while ($row=$entriesstmt->fetchObject()){
print $row->id."\n";
$update = $client->createUpdate();
$doc =  $update->createDocument();
$doc->id=$row->id;
$doc->title=$row->title;
$doc->last_modified=$row->date;
$doc->description=$row->description;
$doc->text=$row->content;
$doc->links=$row->link;
$doc->author=$row->author;

$update->addDocuments(array($doc));
$update->addCommit();

$result = $client->update($update);

$updatestmt->execute(array(":id"=>$row->id));


}

?>
