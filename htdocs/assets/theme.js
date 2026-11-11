// Follow the OS light/dark preference (Bootstrap 5.3 colour modes). Loaded early in <head>
// so the page never flashes the wrong theme.
(function () {
  var dark = window.matchMedia('(prefers-color-scheme: dark)');
  var apply = function () { document.documentElement.setAttribute('data-bs-theme', dark.matches ? 'dark' : 'light'); };
  apply();
  dark.addEventListener('change', apply);
})();
