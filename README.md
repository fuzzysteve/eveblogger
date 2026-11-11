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
