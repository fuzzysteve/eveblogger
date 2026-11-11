# evebloggers.com

The code behind evebloggers.com: an aggregator that polls EVE Online blogs' RSS/Atom
feeds and shows their latest posts, plus combined RSS feeds and an OPML file.

## Layout

| Path | What |
| --- | --- |
| `htdocs/` | Web root. Each page is a thin wrapper around `lib/site.php`. |
| `lib/bootstrap.php` | Reads `.env`, opens the PostgreSQL connection (`eb_db()`). |
| `lib/site.php` | Blog listing and RSS feed rendering shared by the pages. |
| `smarty/templates/` | Smarty templates. Output is HTML-escaped by default (`nofilter` opts out). |
| `utils/loadblogentries.php` | Feed poller. `utils/run.sh` runs it from cron and logs to `logs/loader/`. |
| `eveblogger.sql` | Schema (`pg_dump --schema-only`). |
| `conf/apache/` | Copies of the live vhost (`/etc/apache2/sites-available/evebloggers.conf` + `evebloggers-site.inc`). |

## Setup

Requires PHP 8.1+ (with `pdo_pgsql`, `curl`, `mbstring`, `xml`) and PostgreSQL. It uses
PostgreSQL window functions.

```sh
composer install                 # Smarty, SimplePie, google/recaptcha into vendor/
cp .env.example .env             # then fill in DB and reCAPTCHA (v2) keys
chown root:www-data .env && chmod 640 .env
psql -d eveblogger -f eveblogger.sql
```

The web server's document root is `htdocs/`. `smarty/templates_c/` and `smarty/cache/`
must be writable by the web server user.

The site is served through Cloudflare. Apache needs `mod_remoteip` (real visitor IPs from
`CF-Connecting-IP`), `mod_headers` and `mod_ssl`; see `conf/apache/`. The HTTPS vhost turns on
once the origin certificate exists. It is issued and auto-renewed by acme.sh (root's daily
cron); to set it up from scratch:

```sh
/root/.acme.sh/acme.sh --issue --server https://acme-v02.api.letsencrypt.org/directory -d evebloggers.com -d www.evebloggers.com \
    -w /home/web/evebloggers/htdocs
/root/.acme.sh/acme.sh --install-cert -d evebloggers.com \
    --key-file       /home/web/conf/ssl/acmesh/evebloggers.com.key \
    --fullchain-file /home/web/conf/ssl/acmesh/evebloggers.com.fullchain.cer \
    --reloadcmd      "apache2ctl configtest && apache2ctl graceful"
```

then set Cloudflare's SSL/TLS mode to **Full (strict)**.

Cron, e.g. twice an hour:

```
15,45 * * * * /home/web/evebloggers/utils/run.sh
```

## Blog approval

Submissions from `add.php` land in `blogs_real` with `approved = 0`.

| `approved` | Polled by the loader | Shown on the site |
| --- | --- | --- |
| `0` | no | no (pending) |
| `1` | yes | yes |
| `2` | no | yes (retired: dead feed, old posts kept) |
| `-1` | yes | no |
