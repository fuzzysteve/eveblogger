<?xml version="1.0" encoding="ISO-8859-1" ?>
<rss version="2.0"
  	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
    <channel>
      <title>evebloggers.com</title>
      <atom:link href="http://evebloggers.com/feed.php" rel="self" type="application/rss+xml" />
      <link>http://evebloggers.com/feed.php</link>
      <description>A consolidated feed of posts from eve bloggers</description>
      <language>en-us</language>
  {foreach $posts as $post}
       <item>
           <title>{$post.title}</title>
           <link>{$post.link}</link>
           <guid>{$post.link}</guid>
           <description><![CDATA[{$post.description}]]></description>
           <content:encoded><![CDATA[{$post.content}]]></content:encoded>
           <pubDate>{$post.date}</pubDate>
           <dc:creator><![CDATA[{$post.author}]]></dc:creator>
       </item>
  {/foreach}
    </channel>
</rss>
