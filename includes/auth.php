<?php

declare(strict_types=1);

function current_user(): ?array
{
    return $_SESSION['auth_user'] ?? null;
}

function is_authenticated(): bool
{
    return current_user() !== null;
}

function require_auth(): void
{
    if (!is_authenticated()) {
        set_flash('error', 'Please log in to continue.');
        redirect('/login.php');
    }
}

function login_user(array $user): void
{
    $_SESSION['auth_user'] = [
        'username' => $user['username'],
        'logged_in_at' => date('c'),
    ];

    setcookie('remembered_username', $user['username'], [
        'expires' => time() + (60 * 60 * 24 * 30),
        'path' => '/',
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function remembered_username(): string
{
    return trim((string) ($_COOKIE['remembered_username'] ?? ''));
}
