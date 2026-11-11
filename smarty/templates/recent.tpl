{include file="header.tpl" title="{if $category}{$category} - {/if}Eve Bloggers"}
{if $category}
<h1 class="h4">Posts tagged “{$category}”</h1>
<p>From the last {$days} days, newest first. Times are EVE time (UTC).
  <a href="/feed.php?category={$category|escape:'url'}">RSS feed for this tag</a> · <a href="/recent.php">All recent posts</a></p>
{elseif $unknown|default:false}
<h1 class="h4">No such tag</h1>
<p><a href="/recent.php">All recent posts</a></p>
{else}
<p>All blog posts from the last {$days} days, newest first. Times are EVE time (UTC).</p>
{/if}
<ul class="site">
{if !($unknown|default:false)}
{foreach $posts as $story}
  <li><a href="{$story.link}" target="_blank" rel="noopener" title="{$story.description}">{$story.title}</a> - {$story.author} - <span class="datespan">{$story.pubdate}</span> - {$story.name}
  {foreach $story.categories as $cat}<a class="badge rounded-pill text-bg-light border text-decoration-none fw-normal ms-1" href="/recent.php?category={$cat|escape:'url'}">{$cat}</a>{/foreach}
  </li>
{foreachelse}
  <li>Nothing posted in this period.</li>
{/foreach}
{/if}
</ul>
{include file="footer.tpl"}
