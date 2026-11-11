{include file="header.tpl" title="Eve Bloggers"}
<p>{$toptext}</p>
<div class="row">
{foreach $blogs as $blog}
  <div class="site col-md-6 mb-3">
    <div class="title">{if $blog.url}<a href="{$blog.url}">{$blog.name}</a>{else}{$blog.name}{/if}</div>
    <ul class="mb-0">
    {foreach $blog.posts as $story}
      <li><a href="{$story.link}" target="_blank" rel="noopener" title="{$story.description}">{$story.title}</a> - {$story.author} - <span class="datespan">{$story.date}</span></li>
    {/foreach}
    </ul>
  </div>
{foreachelse}
  <div class="col-12"><p>No posts yet.</p></div>
{/foreach}
</div>
{include file="footer.tpl"}
