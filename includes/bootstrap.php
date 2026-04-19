<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('APP_ROOT', dirname(__DIR__));
define('DATA_DIR', APP_ROOT . '/data');
define('USERS_FILE', DATA_DIR . '/users.json');
define('LEADERBOARD_FILE', DATA_DIR . '/leaderboard.json');

require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/game.php';
require_once __DIR__ . '/layout.php';

bootstrap_storage();

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function request_path(): string
{
    return basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function app_base_path(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = str_replace('\\', '/', dirname($scriptName));
    if ($base === '/' || $base === '\\' || $base === '.') {
        return '';
    }

    return rtrim($base, '/');
}

function app_url(string $path = ''): string
{
    $normalized = '/' . ltrim($path, '/');

    return app_base_path() . $normalized;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function store_old_input(array $input): void
{
    $_SESSION['old_input'] = $input;
}

function old(string $key, string $default = ''): string
{
    return $_SESSION['old_input'][$key] ?? $default;
}

function clear_old_input(): void
{
    unset($_SESSION['old_input']);
}

function post_string(string $key, int $maxLength = 255): string
{
    $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);
    if ($value === null || $value === false) {
        return '';
    }

    $value = trim((string) $value);
    if (mb_strlen($value) > $maxLength) {
        $value = mb_substr($value, 0, $maxLength);
    }

    return $value;
}

function post_int(string $key): int
{
    $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);

    return $value === false || $value === null ? 0 : (int) $value;
}
