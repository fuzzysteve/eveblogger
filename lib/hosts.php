<?php
/** Link-host helpers for the feed loader's hijack check. */

/** Lower-cased host of a URL without a leading "www.", or '' if there isn't one. */
function link_host(?string $url): string
{
    $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));
    return preg_replace('/^www\./', '', $host) ?? '';
}

/**
 * Blogging platforms' own sites. A lapsed blog on the platform redirects its feed to these, so
 * they never count as a blog's home, even if past (hijacked) posts already linked there.
 */
const PLATFORM_HOSTS = [
    'wordpress.com', 'blog.wordpress.com', 'en.blog.wordpress.com', 'wordpress.org',
    'blogger.com', 'blogspot.com', 'tumblr.com', 'medium.com', 'substack.com',
];

/**
 * Does $host belong to the blog? Exact match with a known host, or a subdomain of one
 * (blog.example.com under example.com). Not the reverse: wordpress.com is NOT accepted for
 * someblog.wordpress.com, which is exactly what a lapsed WordPress.com blog redirects to.
 * Blogspot country domains (.de, .co.uk ...) are treated as blogspot.com.
 */
function host_is_known(string $host, array $known): bool
{
    $norm = fn(string $h) => preg_replace('/\.blogspot\.[a-z.]+$/', '.blogspot.com', $h);
    $host = $norm($host);
    if (in_array($host, PLATFORM_HOSTS, true)) {
        return false;
    }
    foreach ($known as $k) {
        $k = $norm($k);
        if ($k !== '' && ($host === $k || str_ends_with($host, '.' . $k))) {
            return true;
        }
    }
    return false;
}
