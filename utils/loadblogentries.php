<?php


require("dbpost.inc.php");
require_once('autoloader.php');

$sql="select id,feedurl,name,owner,extract(epoch from lastupdate) lastupdate from blogs";

$feedstmt=$dbh->prepare($sql);
$feedstmt->execute();


$entry_sql=<<<EOS
insert into blog_entries (title,guid,link,"pubDate",description,content,date_entered,num_comments,author,authoremail,blog) values (:title,:guid,:link,to_timestamp(:pubDate),:description,:content,now(),:comments,:author,:authoremail,:blog)
EOS;

$entrystmt=$dbh->prepare($entry_sql);

$updatesql="update blogs_real set lastupdate=to_timestamp(:timestamp) where id=:feed";
$updatestmt=$dbh->prepare($updatesql);

while ($feed=$feedstmt->fetchObject()){

    # Iterate through each feed, checking the last update against the lastBuildDate to see if there is anything new in this one. 


    $rss=new SimplePie();
    $rss->set_feed_url($feed->feedurl);
    $rss->enable_order_by_date(false);
    $rss->enable_cache(false);
    $rss->init();
echo $feed->name."\n";
    $lastupdate=$feed->lastupdate;
    foreach ($rss->get_items() as $item)
    {
        $pubtime=strtotime($item->get_date());
        if ($pubtime<=$feed->lastupdate)
        {
            continue;
        }
        if ($lastupdate<$pubtime)
        {
             $lastupdate=$pubtime;
        }
        $length=(strlen(strip_tags($item->get_description()))<255) ?  strlen(strip_tags($item->get_description())) : 255;
        list($description,$rest)=explode("\n",wordwrap(strip_tags($item->get_description()." f"),$length));
        $author=$item->get_author(0);
        if (!isset($author))
        {
            $author=$feed->owner;
            $authoremail="";
        }
        else
        {
            $author=$item->get_author(0)->get_name();
            $authoremail=$item->get_author(0)->get_email();
        }
        $entrystmt->execute(array(":title"=>$item->get_title(),":guid"=>$item->get_permalink(),":link"=>$item->get_permalink(),":pubDate"=>$pubtime,":description"=>$description,":content"=>$item->get_content(),":comments"=>0,":author"=>$author,":authoremail"=>$authoremail,":blog"=>$feed->id));
       if (isset($entrystmt->errorCode))
       {
            print_r($entrystmt->errorInfo());
        }

    }
    $updatestmt->execute(array(":feed"=>$feed->id,":timestamp"=>$lastupdate));


}


require('../smarty/smartyinclude.php');

$smarty = new Smarty;
$smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
$smarty->clearCache('post.tpl');


?>
