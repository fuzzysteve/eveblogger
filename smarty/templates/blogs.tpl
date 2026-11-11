{include file="header.tpl" title="All blogs - Eve Bloggers"}
<h1 class="h4">All blogs</h1>
<p>Every blog evebloggers.com has followed, most recently active first. Retired blogs' feeds have gone away, but their old posts are kept.
  The <a href="/opml.php">OPML file</a> has the active feeds for your reader.</p>
<div class="table-responsive">
<table class="table table-sm table-hover align-middle">
  <thead><tr><th>Blog</th><th>Author</th><th class="text-end">Posts</th><th>Last post</th><th>Status</th></tr></thead>
  <tbody>
  {foreach $blogs as $blog}
    <tr>
      <td>{if $blog.url}<a href="{$blog.url}" rel="noopener">{$blog.name}</a>{else}{$blog.name}{/if}</td>
      <td>{$blog.owner}</td>
      <td class="text-end">{$blog.posts}</td>
      <td class="text-nowrap">{$blog.last_post_text|default:'-'}</td>
      <td>{if $blog.active}<span class="badge text-bg-success">active</span>{else}<span class="badge text-bg-secondary">retired</span>{/if}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
</div>
{include file="footer.tpl"}
