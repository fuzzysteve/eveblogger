{include file="header.tpl" title="Eve Bloggers"}
<div class="row">
  <div class="col-md-12">
    <p>{$toptext}</p>
  </div>
</div>
<div class="row">
{foreach $blogs as $blog}
  <div class="site col-md-6">
    <span class="title">{if $blog.url}<a href="{$blog.url}">{$blog.name}</a>{else}{$blog.name}{/if}</span>
    <div class="sitelinks">
      <ul>
      {foreach $blog.posts as $story}
        <li><a href="{$story.link}" target="_blank" rel="noopener" title="{$story.description}">{$story.title}</a> - {$story.author} - <span class="datespan">{$story.date}</span></li>
      {/foreach}
      </ul>
    </div>
  </div>
  {if $blog@iteration is even}</div><div class="row">{/if}
{foreachelse}
  <div class="col-md-12"><p>No posts yet.</p></div>
{/foreach}
</div>
{include file="footer.tpl"}
