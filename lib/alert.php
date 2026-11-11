<?php
/**
 * Operational alerts, posted to Discord by the fuzzworkbot bot
 * (DISCORD_BOT_TOKEN / DISCORD_ALERT_CHANNEL in .env).
 */
require_once __DIR__ . '/bootstrap.php';

function eb_alert(string $message): bool
{
    $token = eb_env('DISCORD_BOT_TOKEN');
    $channel = eb_env('DISCORD_ALERT_CHANNEL');
    if (!$token || !$channel) {
        error_log("eb_alert (not configured): $message");
        return false;
    }

    $ch = curl_init('https://discord.com/api/v10/channels/' . rawurlencode($channel) . '/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bot ' . $token,
            'Content-Type: application/json',
            'User-Agent: DiscordBot (https://evebloggers.com, 1.0)',
        ],
        CURLOPT_POSTFIELDS     => json_encode([
            'content'          => mb_substr('**evebloggers** · ' . $message, 0, 1990),
            'allowed_mentions' => ['parse' => []],   // never ping, whatever a blog title contains
        ]),
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($code < 200 || $code >= 300) {
        error_log("eb_alert failed (HTTP $code): " . substr((string) $body, 0, 300));
        return false;
    }
    return true;
}
