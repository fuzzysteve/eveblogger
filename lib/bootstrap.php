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
        $file = dirname(__DIR__) . '/.env';
        $env = is_readable($file) ? (parse_ini_file($file, false, INI_SCANNER_RAW) ?: []) : [];
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
