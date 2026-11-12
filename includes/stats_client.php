<?php
/**
 * Baracca - Site investissement
 * Envoi d'événements de métriques au collecteur central du configurateur
 * (https://configurateur.baracca.fr/php/stats_collect.php), en POST
 * serveur -> serveur avec le jeton partagé INVEST_STATS_COLLECT_TOKEN.
 * La page n'est jamais bloquée : timeout court (2 s) et erreurs ignorées
 * si le collecteur est indisponible.
 */

require_once __DIR__ . '/../config.php';

function invest_stats_send(string $event, ?string $userId = null, string $detail = ''): void
{
    if (!defined('INVEST_STATS_COLLECT_TOKEN') || INVEST_STATS_COLLECT_TOKEN === '') {
        return;
    }
    $payload = json_encode([
        'token' => INVEST_STATS_COLLECT_TOKEN,
        'site' => 'invest',
        'event' => $event,
        'user' => $userId ?? '',
        'detail' => $detail,
    ], JSON_UNESCAPED_UNICODE);
    $url = 'https://configurateur.baracca.fr/php/stats_collect.php';
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
        ]);
        @curl_exec($ch);
        curl_close($ch);
    } elseif (ini_get('allow_url_fopen')) {
        @file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 2,
            ],
        ]));
    }
}
