<?php

declare(strict_types=1);

function bootstrap_storage(): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0775, true);
    }

    if (!is_file(USERS_FILE)) {
        file_put_contents(USERS_FILE, json_encode([], JSON_PRETTY_PRINT));
    }

    if (!is_file(LEADERBOARD_FILE)) {
        file_put_contents(LEADERBOARD_FILE, json_encode([], JSON_PRETTY_PRINT));
    }
}

function read_json_file(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $contents = file_get_contents($path);
    if ($contents === false || $contents === '') {
        return [];
    }

    $decoded = json_decode($contents, true);

    return is_array($decoded) ? $decoded : [];
}

function write_json_file(string $path, array $payload): void
{
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Unable to open storage file: ' . $path);
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        throw new RuntimeException('Unable to lock storage file: ' . $path);
    }

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

function all_users(): array
{
    return read_json_file(USERS_FILE);
}

function find_user(string $username): ?array
{
    foreach (all_users() as $user) {
        if (($user['username'] ?? '') === $username) {
            return $user;
        }
    }

    return null;
}

function create_user(string $username, string $password): bool
{
    $users = all_users();
    foreach ($users as $user) {
        if (($user['username'] ?? '') === $username) {
            return false;
        }
    }

    $users[] = [
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('c'),
    ];

    write_json_file(USERS_FILE, $users);

    return true;
}

function leaderboard_entries(): array
{
    $entries = read_json_file(LEADERBOARD_FILE);

    usort($entries, static function (array $left, array $right): int {
        $amountCompare = ($right['amount'] ?? 0) <=> ($left['amount'] ?? 0);
        if ($amountCompare !== 0) {
            return $amountCompare;
        }

        return strcmp((string) ($right['completed_at'] ?? ''), (string) ($left['completed_at'] ?? ''));
    });

    return $entries;
}

function save_leaderboard_entry(array $entry): void
{
    $entries = read_json_file(LEADERBOARD_FILE);
    $entries[] = $entry;
    write_json_file(LEADERBOARD_FILE, $entries);
}
