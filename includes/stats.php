<?php
/**
 * Baracca - Site investissement
 * Chiffres clés affichés sur le site, modifiables depuis le back-office.
 */

require_once __DIR__ . '/../config.php';

function invest_stats_file(): string
{
    return INVEST_DATA_DIR . '/stats.json';
}

// Valeurs affichées si aucune sauvegarde n'a encore été faite
function invest_stats_defaults(): array
{
    return [
        'euros_collectes' => 250000,
        'objectif' => 300000,
        'investisseurs' => 42,
        'modules_production' => 12,
        'modules_deployes' => 12,
        'demandes_attente' => 50,
    ];
}

function invest_stats(): array
{
    $defaults = invest_stats_defaults();
    $file = invest_stats_file();
    if (!is_file($file)) {
        return $defaults;
    }
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data)) {
        return $defaults;
    }
    $stats = [];
    foreach ($defaults as $key => $default) {
        $value = $data[$key] ?? $default;
        $stats[$key] = is_numeric($value) ? (int) $value : $default;
    }
    return $stats;
}

function invest_save_stats(array $stats): bool
{
    if (!is_dir(INVEST_DATA_DIR)) {
        mkdir(INVEST_DATA_DIR, 0775, true);
    }
    $clean = [];
    foreach (invest_stats_defaults() as $key => $default) {
        $value = (int) ($stats[$key] ?? $default);
        $clean[$key] = max(0, $value);
    }
    return file_put_contents(invest_stats_file(), json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
}

// Formatage d'un montant : 250000 -> "250 000"
function invest_format_montant(int $montant): string
{
    return number_format($montant, 0, ',', ' ');
}

// Largeur de la barre de progression en % (limitée à 100)
function invest_progress_percent(array $stats): float
{
    if ($stats['objectif'] <= 0) {
        return 0.0;
    }
    return min(100.0, round($stats['euros_collectes'] / $stats['objectif'] * 100, 1));
}
