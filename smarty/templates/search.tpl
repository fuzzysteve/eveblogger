{include file="header.tpl" title="{if $query}{$query} - {/if}Search - Eve Bloggers"}
<h1 class="h4">Search</h1>
<form action="/search.php" method="get" class="mb-3 col-lg-8" role="search">
  <div class="input-group">
    <input type="search" name="q" class="form-control" value="{$query}" placeholder="e.g. wormhole, &quot;alliance tournament&quot;, titan -doomsday" aria-label="Search posts" autofocus>
    <button class="btn btn-primary" type="submit">Search</button>
  </div>
  <div class="form-text">Searches every post's title, summary and text. Quote phrases; put - before a word to exclude it.</div>
</form>
{if $query}
  <p>{if $posts}Top {$posts|count} matches{else}No matches{/if} for “{$query}”.</p>
  <ul class="site">
  {foreach $posts as $story}
    <li><a href="{$story.link}" target="_blank" rel="noopener">{$story.title}</a> - {$story.name} - <span class="datespan">{$story.pubdate}</span>
      {if $story.description}<div class="small text-body-secondary mb-2">{$story.description|truncate:200}</div>{/if}</li>
  {/foreach}
  </ul>
{/if}
{include file="footer.tpl"}
