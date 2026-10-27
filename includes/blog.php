<?php
/**
 * Baracca - Site investissement
 * Récupération des actualités publiées sur le blog (blog.baracca.fr).
 */

// URL de base du blog pour les liens (href) : relative en local, absolue en ligne
function invest_blog_url(string $path = ''): string
{
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    $isLocal = str_starts_with($host, 'localhost') || str_starts_with($host, '127.0.0.1');
    if ($isLocal) {
        return '../blog.baracca.fr' . $path;
    }
    return 'https://blog.baracca.fr' . $path;
}

// URL absolue du flux JSON du blog (appel serveur -> serveur, pas depuis le navigateur)
function invest_blog_api_url(): string
{
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    $isLocal = str_starts_with($host, 'localhost') || str_starts_with($host, '127.0.0.1');
    if ($isLocal) {
        return 'http://' . $host . '/blog.baracca.fr/api-articles.php';
    }
    return 'https://blog.baracca.fr/api-articles.php';
}

/**
 * Derniers articles publiés sur le blog (slug, titre, catégorie, date, extrait).
 * Retourne un tableau vide si le blog est injoignable ou répond mal.
 */
function invest_blog_articles(int $limit = 3): array
{
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true,
        ],
    ]);
    $json = @file_get_contents(invest_blog_api_url(), false, $context);
    if ($json === false) {
        return [];
    }
    $data = json_decode($json, true);
    if (!is_array($data) || !isset($data['articles']) || !is_array($data['articles'])) {
        return [];
    }
    return array_slice($data['articles'], 0, $limit);
}

// Formatage d'une date ISO (2026-10-20) en JJ/MM/AAAA
function invest_format_date(string $date): string
{
    $timestamp = strtotime($date);
    return $timestamp === false ? '' : date('d/m/Y', $timestamp);
}
