<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>{$title}</title>
    <atom:link href="{$selfurl}" rel="self" type="application/rss+xml" />
    <link>{$siteurl}</link>
    <description>{$description}</description>
    <language>en-us</language>
{foreach $posts as $post}
    <item>
      <title>{$post.title}</title>
      <link>{$post.link}</link>
      <guid isPermaLink="true">{$post.link}</guid>
      <description>{$post.description}</description>
{if !$lite}
      <content:encoded>{$post.content nofilter}</content:encoded>
{/if}
      <pubDate>{$post.date}</pubDate>
      <dc:creator>{$post.author}</dc:creator>
    </item>
{/foreach}
  </channel>
</rss>
