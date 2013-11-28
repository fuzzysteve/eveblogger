<?xml version="1.0" encoding="ISO-8859-1"?>
<opml version="1.0">
	<head>
		<title>evebloggers OPML file</title>
                <dateCreated>Thu, 28 Nov 2013 13:12:00 GMT</dateCreated>
		<dateModified>{$updated}</dateModified>
		<ownerName>Fuzzysteve</ownerName>
		<ownerEmail>steve@evebloggers.com</ownerEmail>
	</head>
<body>
  {foreach $feeds as $feed}
       <outline text="{$feed.name}" type="link" url="{$feed.feedurl}"/>
  {/foreach}
</body>
</opml>
