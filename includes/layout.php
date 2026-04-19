<?php

declare(strict_types=1);

function render_header(string $title, array $options = []): void
{
    $active = $options['active'] ?? '';
    $user = current_user();
    $flash = get_flash();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($title) ?> | Deal or No Deal</title>
        <link rel="stylesheet" href="<?= h(app_url('/styles/main.css')) ?>">
    </head>
    <body>
    <header class="site-header">
        <div class="wrap nav-bar">
            <a class="brand" href="<?= h(app_url('/index.php')) ?>">Deal or No Deal</a>
            <nav class="site-nav">
                <a class="<?= $active === 'home' ? 'active' : '' ?>" href="<?= h(app_url('/index.php')) ?>">Home</a>
                <a class="<?= $active === 'leaderboard' ? 'active' : '' ?>" href="<?= h(app_url('/leaderboard.php')) ?>">Leaderboard</a>
                <?php if ($user !== null): ?>
                    <a class="<?= $active === 'game' ? 'active' : '' ?>" href="<?= h(app_url('/game.php')) ?>">Game</a>
                    <a href="<?= h(app_url('/logout.php')) ?>">Logout</a>
                <?php else: ?>
                    <a class="<?= $active === 'login' ? 'active' : '' ?>" href="<?= h(app_url('/login.php')) ?>">Login</a>
                    <a class="<?= $active === 'register' ? 'active' : '' ?>" href="<?= h(app_url('/register.php')) ?>">Register</a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="wrap hero">
            <div>
                <p class="eyebrow">Studio Edition</p>
                <h1><?= h($title) ?></h1>
                <?php if (!empty($options['subtitle'])): ?>
                    <p class="subtitle"><?= h($options['subtitle']) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($user !== null): ?>
                <div class="user-pill">
                    <span>Signed in as</span>
                    <strong><?= h($user['username']) ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="wrap page-shell">
        <?php if ($flash !== null): ?>
            <div class="flash flash-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
        <?php endif; ?>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <footer class="site-footer">
        <div class="wrap footer-grid">
            <div>
                <strong>Game Flow</strong>
                <p>Choose a case, open the board, and review banker offers after each round.</p>
            </div>
            <div>
                <strong>Player Tools</strong>
                <p>Use the leaderboard, remembered username, and round prompts while you play.</p>
            </div>
        </div>
    </footer>
    </body>
    </html>
    <?php
}
