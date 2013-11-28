{include file="header.tpl" title="Eve Bloggers"}
<div class=row>
<div class="col-md-12">
<p>all the Blog posts, in the last 7 days, in date order</p>
</div>
</div>
<div class="row">
    <div class="site col-md-12">
    <ul>
    {foreach $posts as $story}
        <li><a href="{$story.link}" target="_BLANK" title="{$story.description}">{$story.title}</a> -  {$story.author} - <span class="datespan">{$story.date}</span> - {$blognames[$story.blog].name}</li>
    {/foreach}
    </ul>
    </div>
</row>
{include file="footer.tpl"}
