{include file="header.tpl" title="Eve Bloggers"}
{nocache}
<div class=row>
<div class="col-md-12">
<p>Search Results</p>
</div>
</div>
<div class="row">
    <div class="site col-md-12">
    <ul>
    {foreach $posts as $story}
        <li><a href="{$story.link}" target="_BLANK" title="{$story.description}">{$story.title}</a> -  {$story.author} - <span class="datespan">{$story.date}</span> - {$story.name}</li>
    {/foreach}
    </ul>
    </div>
</row>
{/nocache}
{include file="footer.tpl"}
