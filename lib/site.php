<?php
/**
 * Shared page logic: blog listings (home / CSM pages) and RSS feeds.
 * Page scripts in htdocs/ are thin wrappers around these.
 */
require_once __DIR__ . '/bootstrap.php';

const SITE_URL = 'http://evebloggers.com/';

/** Only http(s) links reach a page or feed; anything else (javascript:, data:, junk) becomes ''. */
function safe_url(?string $url): string
{
    $url = trim((string) $url);
    return preg_match('#^https?://#i', $url) ? $url : '';
}

/** Strip characters that are illegal in XML 1.0 (they make a feed unparseable). */
function xml_clean(?string $s): string
{
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $s) ?? '';
}

/** Wrap HTML for an RSS element, escaping any "]]>" inside it. */
function cdata(?string $html): string
{
    return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', xml_clean($html)) . ']]>';
}

/**
 * Blogs that have posted in the last $days, most recently active first,
 * each with its latest $perBlog posts. Dates are shown in UTC (EVE time).
 */
function blog_listing(PDO $dbh, int $days = 365, int $perBlog = 5): array
{
    $stmt = $dbh->prepare(<<<SQL
        select e.link, e.title, e.author, e.blog, e.description, b.name, b.url,
               to_char(e."pubDate" at time zone 'UTC', 'HH24:MI DD-Mon-YY') as pubdate,
               max(e."pubDate") over (partition by e.blog) as latest
          from (select *, row_number() over (partition by blog order by "pubDate" desc) as rn
                  from blog_entries
                 where "pubDate" > now() - make_interval(days => :days)) e
          join blogs b on b.id = e.blog
         where e.rn <= :per
         order by latest desc, e.blog, e.rn
        SQL);
    $stmt->execute([':days' => $days, ':per' => $perBlog]);

    $blogs = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $blogs[$row['blog']] ??= ['name' => $row['name'], 'url' => safe_url($row['url']), 'posts' => []];
        $blogs[$row['blog']]['posts'][] = [
            'link'        => safe_url($row['link']),
            'title'       => $row['title'],
            'author'      => $row['author'],
            'description' => $row['description'],
            'date'        => $row['pubdate'],
        ];
    }
    return array_values($blogs);
}

/** Render a cached posts.tpl listing page. */
function render_listing(Smarty\Smarty $smarty, string $cacheId, string $intro, int $days = 365): void
{
    $smarty->setCacheLifetime(300);
    if (!$smarty->isCached('posts.tpl', $cacheId)) {
        $smarty->assign('toptext', $intro);
        $smarty->assign('blogs', blog_listing(eb_db(), $days));
    }
    $smarty->display('posts.tpl', $cacheId);
}

/**
 * Render a cached RSS 2.0 feed.
 *   $opts: title, description, self (feed URL), days, csm (bool), lite (bool: omit full content)
 */
function render_feed(Smarty\Smarty $smarty, string $cacheId, array $opts): void
{
    header('Content-Type: application/rss+xml; charset=UTF-8');
    $smarty->setCacheLifetime(1800);
    if (!$smarty->isCached('feed.tpl', $cacheId)) {
        $csmJoin = !empty($opts['csm']) ? 'join csmblog c on c.blogid = e.blog' : '';
        $content = !empty($opts['lite']) ? "''" : 'e.content';
        $stmt = eb_db()->prepare(<<<SQL
            select e.link, e.title, e.author, e.description, $content as content,
                   to_char(e."pubDate" at time zone 'UTC', 'Dy, DD Mon YYYY HH24:MI:SS "+0000"') as pubdate
              from blog_entries e
              join blogs b on b.id = e.blog
              $csmJoin
             where e."pubDate" > now() - make_interval(days => :days)
             order by e."pubDate" desc
            SQL);
        $stmt->execute([':days' => $opts['days'] ?? 7]);

        $posts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $link = safe_url($row['link']);
            if ($link === '') {
                continue;
            }
            $posts[] = [
                'link'        => $link,
                'title'       => xml_clean($row['title']),
                'author'      => xml_clean($row['author']),
                'description' => xml_clean($row['description']),
                'content'     => cdata($row['content']),
                'date'        => $row['pubdate'],
            ];
        }
        $smarty->assign('title', $opts['title']);
        $smarty->assign('description', $opts['description']);
        $smarty->assign('selfurl', $opts['self']);
        $smarty->assign('siteurl', SITE_URL);
        $smarty->assign('lite', !empty($opts['lite']));
        $smarty->assign('posts', $posts);
    }
    $smarty->display('feed.tpl', $cacheId);
}
