{include file="header.tpl" title="Eve Bloggers"}
<div class=row>
<div class="col-md-12">
<p>This is the very first draft, with the 5 most recent posts of the blogs being polled. No categorization built in, no sorting or ordering of blogs. Those should be features which come later.</p>
</div>
</div>
<div class="row">
{foreach $blogs as $site}
    <div class="site col-md-6">
    <span class=title><a href="{$blognames[$site].url}">{$blognames[$site].name}</a></span>
    <div class="sitelinks" id="blog-{$site}">
    <ul>
    {foreach $posts[$site] as $story}
        <li><a href="{$story.link}" target="_BLANK" title="{$story.description}">{$story.title}</a> -  {$story.author} - <span class="datespan">{$story.date}</span></li>
    {/foreach}
    </ul>
    </div>
    </div>
{/foreach}
</row>
{include file="footer.tpl"}
