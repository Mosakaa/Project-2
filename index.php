<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = current_user();
$game = active_game();

render_header('Take the Banker or Trust the Case', [
    'active' => 'home',
    'subtitle' => 'Choose a case, play through the rounds, and decide whether to take the banker offer.',
]);
?>

<section class="hero-grid">
    <article class="panel">
        <h2>Game Overview</h2>
        <p>Create an account, choose your case, open the board round by round, and decide when to stop.</p>
        <div class="metric-list">
            <div class="metric">
                <span>Board Size</span>
                <strong>26 Briefcases</strong>
                <span class="muted">One personal case, 25 to open</span>
            </div>
            <div class="metric">
                <span>Banker Calls</span>
                <strong>8 Rounds</strong>
                <span class="muted">The board tightens as the offers rise</span>
            </div>
            <div class="metric">
                <span>Top Prize</span>
                <strong>$1,000,000</strong>
                <span class="muted">From one cent all the way to the big one</span>
            </div>
        </div>
        <div class="cta-row">
            <?php if ($user === null): ?>
                <a class="button" href="<?= h(app_url('/register.php')) ?>">Create Account</a>
                <a class="button secondary" href="<?= h(app_url('/login.php')) ?>">Login</a>
            <?php else: ?>
                <a class="button" href="<?= h(app_url('/game.php')) ?>"><?= $game !== null ? 'Resume Game' : 'Start Game' ?></a>
            <?php endif; ?>
            <a class="button ghost" href="<?= h(app_url('/leaderboard.php')) ?>">View Leaderboard</a>
        </div>
    </article>

    <article class="panel">
        <h2>Before You Play</h2>
        <ul class="check-list">
            <li>Register once, then log in to start a run</li>
            <li>Choose one personal briefcase and keep it sealed</li>
            <li>Open the required number of cases each round</li>
            <li>Review the banker offer after every completed round</li>
            <li>Finish the run to place your result on the leaderboard</li>
        </ul>
        <div class="badge-row">
            <span class="badge">One player</span>
            <span class="badge">26 cases</span>
            <span class="badge">Deal or No Deal</span>
        </div>
    </article>
</section>

<section class="two-col">
    <article class="card">
        <h2>How the Game Works</h2>
        <div class="history-grid">
            <div class="timeline-card">
                <h3>1. Lock Your Case</h3>
                <p>Pick one briefcase to keep sealed until the finish. Everything else stays live on the board.</p>
            </div>
            <div class="timeline-card">
                <h3>2. Open Cases by Round</h3>
                <p>Each round tells you exactly how many cases to open before the banker calls again.</p>
            </div>
            <div class="timeline-card">
                <h3>3. Hear the Banker</h3>
                <p>After every round, the banker tries to buy you out before the board gets even more dramatic.</p>
            </div>
            <div class="timeline-card">
                <h3>4. Deal or No Deal</h3>
                <p>Take the safe money or press on and trust the case you chose at the start.</p>
            </div>
        </div>
    </article>

    <article class="card">
        <h2>Returning Players</h2>
        <?php if (remembered_username() !== ''): ?>
            <p>Welcome back, <strong><?= h(remembered_username()) ?></strong>. Your last username is already filled in on the login page.</p>
        <?php else: ?>
            <p>The login page can remember the last username used on this browser.</p>
        <?php endif; ?>
        <p class="muted">The leaderboard stays available from the home page at any time.</p>
    </article>
</section>

<?php render_footer(); ?>
