{include file="header.tpl" title="Add a blog - Eve Bloggers"}
<h1 class="h3 mb-3">Add a blog</h1>
{if $errors}
<div class="alert alert-danger"><ul class="mb-0">{foreach $errors as $error}<li>{$error}</li>{/foreach}</ul></div>
{/if}
<form action="add.php" method="POST" class="col-lg-8">
  <div class="mb-3">
    <label for="blogtitle" class="form-label">Blog title</label>
    <input name="blogtitle" id="blogtitle" type="text" class="form-control" required maxlength="200" value="{$form.blogtitle|default:''}" placeholder="The title of your blog">
  </div>
  <div class="mb-3">
    <label for="author" class="form-label">Primary author</label>
    <input name="author" id="author" type="text" class="form-control" required maxlength="200" value="{$form.author|default:''}">
    <div class="form-text">Used when your feed doesn't name a post's author.</div>
  </div>
  <div class="mb-3">
    <label for="url" class="form-label">Site URL</label>
    <input name="url" id="url" type="url" class="form-control" maxlength="255" value="{$form.url|default:''}" placeholder="https://example.com/">
  </div>
  <div class="mb-3">
    <label for="feedurl" class="form-label">Feed URL</label>
    <input name="feedurl" id="feedurl" type="url" class="form-control" required maxlength="255" value="{$form.feedurl|default:''}" placeholder="https://example.com/feed/">
    <div class="form-text">The RSS or Atom feed for your posts.</div>
  </div>
  <div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <input name="description" id="description" type="text" class="form-control" maxlength="255" value="{$form.description|default:''}" placeholder="A line about what you write">
  </div>
  <div class="g-recaptcha mb-3" data-sitekey="{$recaptcha_site_key}"></div>
  <button type="submit" name="submit" value="Add Blog" class="btn btn-primary">Add blog</button>
</form>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
{include file="footer.tpl"}
