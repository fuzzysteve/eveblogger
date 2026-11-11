{include file="header.tpl" title="Eve Bloggers"}
<p>All blog posts from the last 7 days, newest first. Times are EVE time (UTC).</p>
<div class="row">
  <div class="site col-12">
    <ul>
    {foreach $posts as $story}
      <li><a href="{$story.link}" target="_blank" rel="noopener" title="{$story.description}">{$story.title}</a> - {$story.author} - <span class="datespan">{$story.pubdate}</span> - {$story.name}</li>
    {foreachelse}
      <li>Nothing posted in the last 7 days.</li>
    {/foreach}
    </ul>
  </div>
</div>
{include file="footer.tpl"}
