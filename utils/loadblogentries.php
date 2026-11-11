<?php
/**
 * Polls every approved feed and stores posts newer than the blog's lastupdate.
 * Run from cron via run.sh; progress and errors go to stdout for the log file.
 */
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../vendor/autoload.php';

const FEED_TIMEOUT = 10;      // seconds per feed fetch
const SUMMARY_LENGTH = 255;   // characters kept from the post description

function logline(string $msg): void
{
    echo date('c'), ' ', $msg, "\n";
}

/**
 * HTML fragment -> plain text. Titles and descriptions are stored as plain text and
 * escaped by Smarty on output; only `content` keeps its HTML.
 */
function plain_text(string $html): string
{
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $text) ?? '');
}

/** First $max characters of the description as plain text, cut on a word boundary. */
function summary(string $html, int $max = SUMMARY_LENGTH): string
{
    $text = plain_text($html);
    if (mb_strlen($text) <= $max) {
        return $text;
    }
    $cut = mb_substr($text, 0, $max);
    $space = mb_strrpos($cut, ' ');
    return ($space !== false && $space > $max / 2) ? mb_substr($cut, 0, $space) : $cut;
}

$dbh = eb_db();

$feeds = $dbh->query(
    'select id, feedurl, name, owner, extract(epoch from lastupdate)::bigint as lastupdate
       from blogs_real where approved in (1, -1) order by id'
)->fetchAll(PDO::FETCH_OBJ);

$entryStmt = $dbh->prepare(
    'insert into blog_entries
        (title, guid, link, "pubDate", description, content, date_entered, num_comments, author, authoremail, blog)
     values
        (:title, :guid, :link, to_timestamp(:pubDate), :description, :content, now(), 0, :author, :authoremail, :blog)
     returning id'
);
$categoryStmt = $dbh->prepare(
    'insert into post_category (post_id, category_id) values (:post_id, category_id(:category_text))'
);
$updateStmt = $dbh->prepare('update blogs_real set lastupdate = to_timestamp(:timestamp) where id = :feed');

// The pre-2026 loader stored lastupdate truncated to the minute, and many blogs have since
// moved http -> https or to a new domain, so a blog's latest known post can reappear with a
// different link. Treat same link-ignoring-scheme, or same title within a minute, as seen.
$seenStmt = $dbh->prepare(
    'select 1 from blog_entries
      where blog = :blog
        and (regexp_replace(link, \'^https?://\', \'\') = :bare_link
             or (title = :title and abs(extract(epoch from "pubDate") - :pubtime) < 60))
      limit 1'
);

$totalAdded = 0;
$failedFeeds = 0;

foreach ($feeds as $feed) {
    try {
        $rss = new \SimplePie\SimplePie();
        $rss->set_feed_url($feed->feedurl);
        $rss->enable_order_by_date(false);
        $rss->enable_cache(false);
        $rss->set_timeout(FEED_TIMEOUT);

        if (!$rss->init()) {
            $failedFeeds++;
            logline("FAIL  #{$feed->id} {$feed->name}: " . trim((string) $rss->error()));
            continue;
        }

        $lastupdate = (int) $feed->lastupdate;
        $newest = $lastupdate;
        $added = 0;

        foreach ($rss->get_items() as $item) {
            $pubtime = (int) $item->get_date('U');
            $link = $item->get_permalink();
            if ($pubtime <= $lastupdate || !$link) {
                continue;  // already seen, undated, or no link to point at
            }

            $title = plain_text((string) $item->get_title());
            $seenStmt->execute([
                ':blog'      => $feed->id,
                ':bare_link' => preg_replace('#^https?://#', '', $link),
                ':title'     => $title,
                ':pubtime'   => $pubtime,
            ]);
            $seen = $seenStmt->fetchColumn();
            $seenStmt->closeCursor();
            if ($seen) {
                $newest = max($newest, $pubtime);
                continue;
            }

            $author = $item->get_author(0);
            try {
                $entryStmt->execute([
                    ':title'       => $title,
                    ':guid'        => $link,
                    ':link'        => $link,
                    ':pubDate'     => $pubtime,
                    ':description' => summary((string) $item->get_description()),
                    ':content'     => (string) $item->get_content(),
                    ':author'      => plain_text((string) ($author ? $author->get_name() : '')) ?: $feed->owner,
                    ':authoremail' => $author ? $author->get_email() : null,
                    ':blog'        => $feed->id,
                ]);
                $entryId = (int) $entryStmt->fetchColumn();
                $entryStmt->closeCursor();
            } catch (PDOException $e) {
                if ($e->getCode() === '23505') {   // unique_violation on guid: already stored
                    $newest = max($newest, $pubtime);
                    continue;
                }
                logline("ERROR #{$feed->id} insert {$link}: " . $e->getMessage());
                continue;
            }

            $newest = max($newest, $pubtime);
            $added++;

            foreach ($item->get_categories() ?? [] as $category) {
                $term = trim((string) $category->get_term());
                if ($term === '') {
                    continue;
                }
                try {
                    $categoryStmt->execute([':post_id' => $entryId, ':category_text' => $term]);
                } catch (PDOException $e) {
                    logline("WARN  #{$feed->id} category '{$term}' on post {$entryId}: " . $e->getMessage());
                }
            }
        }

        // Never move lastupdate into the future: a mis-dated post would hide everything until then.
        $newest = min($newest, time());
        if ($newest > $lastupdate) {
            $updateStmt->execute([':feed' => $feed->id, ':timestamp' => $newest]);
        }
        if ($added > 0) {
            logline("OK    #{$feed->id} {$feed->name}: {$added} new");
        }
        $totalAdded += $added;
    } catch (Throwable $e) {
        $failedFeeds++;
        logline("FAIL  #{$feed->id} {$feed->name}: " . get_class($e) . ': ' . $e->getMessage());
    }
}

if ($totalAdded > 0) {
    require __DIR__ . '/../smarty/smartyinclude.php';
    $smarty->clearAllCache();
}

logline(sprintf('DONE  %d feeds, %d new posts, %d feeds failed', count($feeds), $totalAdded, $failedFeeds));
