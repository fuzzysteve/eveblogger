<?php
/**
 * Shared configuration and database access.
 *
 * Secrets live in /home/web/evebloggers/.env (gitignored, outside the web root).
 * See .env.example for the expected keys.
 */

function eb_env(string $key, ?string $default = null): ?string
{
    static $env = null;
    if ($env === null) {
        // Plain KEY=value lines; blank lines and # comments ignored; optional surrounding quotes.
        // (Not parse_ini_file(): it treats '#' lines as syntax, and one bad line empties the lot.)
        $env = [];
        $file = dirname(__DIR__) . '/.env';
        foreach (is_readable($file) ? file($file, FILE_IGNORE_NEW_LINES) : [] as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$name, $value] = array_map('trim', explode('=', $line, 2));
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0]) {
                $value = substr($value, 1, -1);
            }
            $env[$name] = $value;
        }
    }
    if (isset($env[$key])) {
        return $env[$key];
    }
    $fromProcess = getenv($key);
    return $fromProcess === false ? $default : $fromProcess;
}

function eb_db(): PDO
{
    static $dbh = null;
    if ($dbh === null) {
        $dsn = sprintf(
            'pgsql:host=%s;dbname=%s',
            eb_env('DB_HOST', 'localhost'),
            eb_env('DB_NAME', 'eveblogger')
        );
        $dbh = new PDO($dsn, eb_env('DB_USER'), eb_env('DB_PASS'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }
    return $dbh;
}
