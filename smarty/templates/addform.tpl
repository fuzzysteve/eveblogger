{include file="header.tpl" title="Eve Bloggers"}

<form action="add.php" method="POST">
<table>
<tr><td><label for="blogtitle">Blog Title</label></td><td><input name="blogtitle" id="blogtitle" type=text placeholder="The title of your blog" size=80></td></tr>
<tr><td><label for="author">Primary Author</label></td><td><input name="author" id="author" type=text placeholder="This gets used if your RSS doesn't attribute posts." size=80></td></tr>
<tr><td><label for="url">URL</label></td><td><input name="url" id="url" type=text placeholder="The base address of your site. e.g.  http://evebloggers.com/" size=80></td></tr>
<tr><td><label for="feedurl">Feed URL</label></td><td><input name="feedurl" id="feedurl" type=text placeholder="Where to find the rss on your site. e.g. http://evebloggers.com/blog/feed/" size=80></td></tr>
<tr><td><label for="description">Description</label></td><td><input name="description" id="description" type=text placeholder="Currently unused. but it seemed a good idea to ask." size=80 ></td></tr>
</table>
<input type=submit name=submit value="Add Blog">
</form>
{include file="footer.tpl"}
