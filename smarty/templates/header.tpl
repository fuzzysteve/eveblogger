<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title|default:"EveBloggers.com"}</title>
    <link rel="alternate" type="application/rss+xml" title="evebloggers.com: 7 day feed" href="/feed.php">
    <script>
      // Follow the OS light/dark preference (Bootstrap 5.3 colour modes).
      (function () {
        var dark = window.matchMedia('(prefers-color-scheme: dark)');
        var apply = function () { document.documentElement.setAttribute('data-bs-theme', dark.matches ? 'dark' : 'light'); };
        apply();
        dark.addEventListener('change', apply);
      })();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
      .datespan { font-size: .75rem; color: var(--bs-secondary-color); white-space: nowrap; }
      .site .title { font-weight: 600; }
      .site ul { padding-left: 1.1rem; }
      .legal { font-size: .7rem; }
    </style>
  </head>
  <body class="d-flex flex-column min-vh-100 bg-body-tertiary">
{include file="nav.tpl"}
