{include file="header.tpl" title="Eve Bloggers"}

{if $errors}
<div class="alert alert-danger"><ul>{foreach $errors as $error}<li>{$error}</li>{/foreach}</ul></div>
{/if}
<form action="add.php" method="POST">
<table>
<tr><td><label for="blogtitle">Blog Title</label></td><td><input name="blogtitle" id="blogtitle" type="text" required maxlength="200" value="{$form.blogtitle|default:''}" placeholder="The title of your blog" size="80"></td></tr>
<tr><td><label for="author">Primary Author</label></td><td><input name="author" id="author" type="text" required maxlength="200" value="{$form.author|default:''}" placeholder="This gets used if your RSS doesn't attribute posts." size="80"></td></tr>
<tr><td><label for="url">URL</label></td><td><input name="url" id="url" type="url" maxlength="255" value="{$form.url|default:''}" placeholder="The base address of your site. e.g. https://evebloggers.com/" size="80"></td></tr>
<tr><td><label for="feedurl">Feed URL</label></td><td><input name="feedurl" id="feedurl" type="url" required maxlength="255" value="{$form.feedurl|default:''}" placeholder="Where to find the RSS on your site. e.g. https://evebloggers.com/blog/feed/" size="80"></td></tr>
<tr><td><label for="description">Description</label></td><td><input name="description" id="description" type="text" maxlength="255" value="{$form.description|default:''}" placeholder="A line about what you write." size="80"></td></tr>
</table>
<div class="g-recaptcha" data-sitekey="{$recaptcha_site_key}"></div>
<input type="submit" name="submit" value="Add Blog">
</form>
<script src="https://www.google.com/recaptcha/api.js"></script>
{include file="footer.tpl"}
