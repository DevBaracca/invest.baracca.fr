<?php
/**
 * Baracca - Site investissement
 * Espace investisseurs : authentification par compte individuel (email + mot de passe,
 * session PHP + bcrypt), sur le même modèle que le back-office du blog.
 * Les comptes sont gérés depuis le back-office (data/users.json).
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/users.php';

// Cookie de session renforcé : inaccessible en JavaScript, envoyé uniquement sur ce site (SameSite=Lax)
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function invest_is_logged_in(): bool
{
    return !empty($_SESSION['invest_user_id']);
}

function invest_login(string $email, string $password): bool
{
    $user = invest_find_user_by_email($email);
    if ($user !== null && password_verify($password, (string) $user['hash'])) {
        // Nouvel identifiant de session à la connexion (protection contre la fixation de session)
        session_regenerate_id(true);
        $_SESSION['invest_user_id'] = (string) $user['id'];
        return true;
    }
    return false;
}

// Compte actuellement connecté (null si non connecté)
function invest_current_user(): ?array
{
    $id = (string) ($_SESSION['invest_user_id'] ?? '');
    if ($id === '') {
        return null;
    }
    return invest_find_user_by_id($id);
}

function invest_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// ============================================
// Limitation de débit des connexions (par IP)
// ============================================

function invest_rate_limit_file(): string
{
    return INVEST_DATA_DIR . '/rate-limit.json';
}

function invest_rate_limit_load(): array
{
    $file = invest_rate_limit_file();
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data)) {
        return [];
    }
    // Nettoyage des entrées dont la fenêtre et le blocage sont expirés
    $now = time();
    foreach ($data as $ip => $entry) {
        $windowExpired = (int) ($entry['window_start'] ?? 0) + INVEST_LOGIN_WINDOW_SECONDS <= $now;
        $blockExpired = (int) ($entry['blocked_until'] ?? 0) <= $now;
        if ($windowExpired && $blockExpired) {
            unset($data[$ip]);
        }
    }
    return $data;
}

function invest_rate_limit_save(array $data): void
{
    if (!is_dir(INVEST_DATA_DIR)) {
        mkdir(INVEST_DATA_DIR, 0775, true);
    }
    file_put_contents(invest_rate_limit_file(), json_encode($data), LOCK_EX);
}

function invest_rate_limit_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function invest_rate_limit_is_blocked(): bool
{
    $entry = invest_rate_limit_load()[invest_rate_limit_ip()] ?? null;
    return is_array($entry) && (int) ($entry['blocked_until'] ?? 0) > time();
}

function invest_rate_limit_blocked_seconds(): int
{
    $entry = invest_rate_limit_load()[invest_rate_limit_ip()] ?? null;
    if (!is_array($entry)) {
        return 0;
    }
    return max(0, (int) ($entry['blocked_until'] ?? 0) - time());
}

function invest_rate_limit_failure(): void
{
    $ip = invest_rate_limit_ip();
    $data = invest_rate_limit_load();
    $now = time();
    $entry = $data[$ip] ?? ['failures' => 0, 'window_start' => $now, 'blocked_until' => 0];
    if ((int) $entry['window_start'] + INVEST_LOGIN_WINDOW_SECONDS <= $now) {
        // La fenêtre est expirée : on repart à zéro
        $entry = ['failures' => 0, 'window_start' => $now, 'blocked_until' => 0];
    }
    $entry['failures']++;
    if ($entry['failures'] >= INVEST_LOGIN_MAX_ATTEMPTS) {
        $entry['failures'] = 0;
        $entry['window_start'] = $now;
        $entry['blocked_until'] = $now + INVEST_LOGIN_BLOCK_SECONDS;
    }
    $data[$ip] = $entry;
    invest_rate_limit_save($data);
}

function invest_rate_limit_success(): void
{
    $ip = invest_rate_limit_ip();
    $data = invest_rate_limit_load();
    if (isset($data[$ip])) {
        unset($data[$ip]);
        invest_rate_limit_save($data);
    }
}
