<?php
/**
 * Baracca - Site investissement
 * Back-office : authentification administrateur (session + hash bcrypt),
 * limitation de débit des tentatives de connexion et protection CSRF.
 */

require_once __DIR__ . '/../config.php';

// Cookie de session renforcé (identique à l'espace investisseurs)
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function invest_admin_is_logged_in(): bool
{
    return !empty($_SESSION['invest_admin']);
}

function invest_admin_login(string $username, string $password): bool
{
    if (hash_equals(INVEST_ADMIN_USERNAME, $username) && password_verify($password, INVEST_ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['invest_admin'] = true;
        return true;
    }
    return false;
}

function invest_admin_require_auth(): void
{
    if (!invest_admin_is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function invest_admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// ---------------------------------------------------------------------------
// Limitation de débit des connexions admin (par IP)
// Mêmes constantes que l'espace investisseurs : 5 échecs / 15 min de blocage
// ---------------------------------------------------------------------------

function invest_admin_rate_limit_file(): string
{
    return INVEST_DATA_DIR . '/admin-rate-limit.json';
}

function invest_admin_rate_limit_load(): array
{
    $file = invest_admin_rate_limit_file();
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function invest_admin_rate_limit_save(array $data): void
{
    if (!is_dir(INVEST_DATA_DIR)) {
        mkdir(INVEST_DATA_DIR, 0775, true);
    }
    file_put_contents(invest_admin_rate_limit_file(), json_encode($data), LOCK_EX);
}

function invest_admin_rate_limit_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function invest_admin_rate_limit_is_blocked(): bool
{
    $data = invest_admin_rate_limit_load();
    $ip = invest_admin_rate_limit_ip();
    if (empty($data[$ip]['blocked_until'])) {
        return false;
    }
    if (time() >= (int) $data[$ip]['blocked_until']) {
        unset($data[$ip]);
        invest_admin_rate_limit_save($data);
        return false;
    }
    return true;
}

function invest_admin_rate_limit_blocked_seconds(): int
{
    $data = invest_admin_rate_limit_load();
    $ip = invest_admin_rate_limit_ip();
    return max(0, (int) ($data[$ip]['blocked_until'] ?? 0) - time());
}

function invest_admin_rate_limit_failure(): void
{
    $data = invest_admin_rate_limit_load();
    $ip = invest_admin_rate_limit_ip();
    $data[$ip]['failures'] = (int) ($data[$ip]['failures'] ?? 0) + 1;
    if ($data[$ip]['failures'] >= INVEST_LOGIN_MAX_ATTEMPTS) {
        $data[$ip]['blocked_until'] = time() + INVEST_LOGIN_BLOCK_SECONDS;
        $data[$ip]['failures'] = 0;
    }
    invest_admin_rate_limit_save($data);
}

function invest_admin_rate_limit_success(): void
{
    $data = invest_admin_rate_limit_load();
    unset($data[invest_admin_rate_limit_ip()]);
    invest_admin_rate_limit_save($data);
}

// ---------------------------------------------------------------------------
// Protection CSRF des formulaires du back-office
// ---------------------------------------------------------------------------

function invest_admin_csrf_token(): string
{
    if (empty($_SESSION['invest_admin_csrf'])) {
        $_SESSION['invest_admin_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['invest_admin_csrf'];
}

function invest_admin_csrf_verify(?string $token): bool
{
    return $token !== null && !empty($_SESSION['invest_admin_csrf'])
        && hash_equals((string) $_SESSION['invest_admin_csrf'], $token);
}
