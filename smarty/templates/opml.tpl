<?xml version="1.0" encoding="UTF-8"?>
<opml version="2.0">
  <head>
    <title>evebloggers OPML file</title>
    <dateCreated>Thu, 28 Nov 2013 13:12:00 GMT</dateCreated>
    <dateModified>{$updated}</dateModified>
    <ownerName>Fuzzysteve</ownerName>
    <ownerEmail>steve@evebloggers.com</ownerEmail>
  </head>
  <body>
{foreach $feeds as $feed}
    <outline text="{$feed.name}" title="{$feed.name}" type="rss" xmlUrl="{$feed.feedurl}"{if $feed.url} htmlUrl="{$feed.url}"{/if}/>
{/foreach}
  </body>
</opml>
