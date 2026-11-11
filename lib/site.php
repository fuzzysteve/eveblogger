<?php
/**
 * Shared page logic: the home page blog listing and RSS feeds.
 * Page scripts in htdocs/ are thin wrappers around these.
 */
require_once __DIR__ . '/bootstrap.php';

const SITE_URL = 'https://evebloggers.com/';

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
          from (select id, link, title, author, blog, description, "pubDate",
                       row_number() over (partition by blog order by "pubDate" desc) as rn
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
 *   $opts: title, description, self (feed URL), days, lite (bool: omit full content),
 *          category_id (optional: only posts with that category)
 */
function render_feed(Smarty\Smarty $smarty, string $cacheId, array $opts): void
{
    header('Content-Type: application/rss+xml; charset=UTF-8');
    $smarty->setCacheLifetime(1800);
    if (!$smarty->isCached('feed.tpl', $cacheId)) {
        $content = !empty($opts['lite']) ? "''" : 'e.content';
        $categoryFilter = !empty($opts['category_id'])
            ? 'and exists (select 1 from post_category pc where pc.post_id = e.id and pc.category_id = :cat)' : '';
        $stmt = eb_db()->prepare(<<<SQL
            select e.link, e.title, e.author, e.description, $content as content,
                   to_char(e."pubDate" at time zone 'UTC', 'Dy, DD Mon YYYY HH24:MI:SS "+0000"') as pubdate
              from blog_entries e
              join blogs b on b.id = e.blog
             where e."pubDate" > now() - make_interval(days => :days)
               $categoryFilter
             order by e."pubDate" desc
            SQL);
        $params = [':days' => $opts['days'] ?? 7];
        if (!empty($opts['category_id'])) {
            $params[':cat'] = $opts['category_id'];
        }
        $stmt->execute($params);

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

/**
 * A ?category= value from the query string, resolved to an existing category.
 * Returns [id, name] or null. Only known categories are accepted, so arbitrary
 * input can't mint new page-cache entries.
 */
function requested_category(): ?array
{
    $name = mb_strtolower(trim((string) ($_GET['category'] ?? '')));
    if ($name === '' || mb_strlen($name) > 200) {
        return null;
    }
    $stmt = eb_db()->prepare('select category_id, category from categories where category = :c');
    $stmt->execute([':c' => $name]);
    $row = $stmt->fetch(PDO::FETCH_NUM);
    return $row ? [(int) $row[0], $row[1]] : null;
}

/** Up to $limit category names per post id, alphabetically. */
function post_categories(PDO $dbh, array $postIds, int $limit = 4): array
{
    if (!$postIds) {
        return [];
    }
    $ids = implode(',', array_map('intval', $postIds));
    $out = [];
    foreach ($dbh->query(<<<SQL
        select post_id, category from (
            select pc.post_id, c.category,
                   row_number() over (partition by pc.post_id order by c.category) as rn
              from post_category pc join categories c using (category_id)
             where pc.post_id in ($ids)) t
         where rn <= $limit
        SQL) as $row) {
        $out[$row['post_id']][] = $row['category'];
    }
    return $out;
}

/** Posts newest first, with blog name and categories; optionally one category only. */
function recent_posts(PDO $dbh, int $days, ?int $categoryId = null): array
{
    $categoryFilter = $categoryId !== null
        ? 'and exists (select 1 from post_category pc where pc.post_id = e.id and pc.category_id = :cat)' : '';
    $stmt = $dbh->prepare(<<<SQL
        select e.id, e.link, e.title, e.author, e.description, b.name,
               to_char(e."pubDate" at time zone 'UTC', 'HH24:MI DD-Mon-YY') as pubdate
          from blog_entries e
          join blogs b on b.id = e.blog
         where e."pubDate" > now() - make_interval(days => :days)
           $categoryFilter
         order by e."pubDate" desc
         limit 500
        SQL);
    $params = [':days' => $days];
    if ($categoryId !== null) {
        $params[':cat'] = $categoryId;
    }
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cats = post_categories($dbh, array_column($rows, 'id'));
    foreach ($rows as &$row) {
        $row['link'] = safe_url($row['link']);
        $row['categories'] = $cats[$row['id']] ?? [];
    }
    return $rows;
}

/** Full-text search over all posts; best matches first, newer first among equals. */
function search_posts(PDO $dbh, string $query, int $limit = 50): array
{
    $stmt = $dbh->prepare(<<<SQL
        select e.link, e.title, e.author, e.description, b.name,
               to_char(e."pubDate" at time zone 'UTC', 'DD-Mon-YY') as pubdate
          from blog_entries e
          join blogs b on b.id = e.blog,
               websearch_to_tsquery('english', :q) as q
         where e.search @@ q
         order by ts_rank(e.search, q) desc, e."pubDate" desc
         limit $limit
        SQL);
    $stmt->execute([':q' => $query]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$row) {
        $row['link'] = safe_url($row['link']);
    }
    return $rows;
}

/** Every blog shown on the site, most recently active first. */
function blog_directory(PDO $dbh): array
{
    $rows = $dbh->query(<<<SQL
        select b.id, b.name, b.url, b.owner, b.approved,
               count(e.id) as posts,
               max(e."pubDate") as last_post,
               to_char(max(e."pubDate") at time zone 'UTC', 'DD-Mon-YYYY') as last_post_text
          from blogs b
          left join blog_entries e on e.blog = b.id
         group by b.id, b.name, b.url, b.owner, b.approved
         order by max(e."pubDate") desc nulls last, b.name
        SQL)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$row) {
        $row['url'] = safe_url($row['url']);
        $row['active'] = (int) $row['approved'] === 1;
    }
    return $rows;
}
