<?php
/**
 * Baracca - Site investissement
 * Comptes ayant accès à l'espace investisseurs (email + mot de passe),
 * gérés depuis le back-office.
 */

require_once __DIR__ . '/../config.php';

function invest_users_file(): string
{
    return INVEST_DATA_DIR . '/users.json';
}

function invest_load_users(): array
{
    $file = invest_users_file();
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data) || !isset($data['users']) || !is_array($data['users'])) {
        return [];
    }
    return $data['users'];
}

function invest_save_users(array $users): bool
{
    if (!is_dir(INVEST_DATA_DIR)) {
        mkdir(INVEST_DATA_DIR, 0775, true);
    }
    return file_put_contents(invest_users_file(), json_encode(['users' => array_values($users)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
}

function invest_find_user_by_email(string $email): ?array
{
    foreach (invest_load_users() as $user) {
        if (hash_equals(strtolower((string) ($user['email'] ?? '')), strtolower($email))) {
            return $user;
        }
    }
    return null;
}

function invest_find_user_by_id(string $id): ?array
{
    foreach (invest_load_users() as $user) {
        if (($user['id'] ?? '') === $id) {
            return $user;
        }
    }
    return null;
}

function invest_add_user(string $email, string $name, string $password): bool
{
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || invest_find_user_by_email($email) !== null) {
        return false;
    }
    $users = invest_load_users();
    $users[] = [
        'id' => bin2hex(random_bytes(8)),
        'email' => $email,
        'name' => trim($name),
        'hash' => password_hash($password, PASSWORD_DEFAULT),
        'created' => date('Y-m-d'),
    ];
    return invest_save_users($users);
}

function invest_delete_user(string $id): bool
{
    $users = invest_load_users();
    foreach ($users as $i => $user) {
        if (($user['id'] ?? '') === $id) {
            unset($users[$i]);
            return invest_save_users($users);
        }
    }
    return false;
}

function invest_set_user_password(string $id, string $password): bool
{
    $users = invest_load_users();
    foreach ($users as $i => $user) {
        if (($user['id'] ?? '') === $id) {
            $users[$i]['hash'] = password_hash($password, PASSWORD_DEFAULT);
            return invest_save_users($users);
        }
    }
    return false;
}
