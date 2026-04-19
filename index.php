<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = current_user();
$game = active_game();

render_header('Take the Banker or Trust the Case', [
    'active' => 'home',
    'subtitle' => 'Pick a case, ride the offers, and see where your luck lands on the board.',
]);
?>

<section class="hero-grid">
    <article class="panel">
        <h2>Tonight's Lineup</h2>
        <p>Create a player profile, lock in your case, survive the banker calls, and see where your run lands on the payout table.</p>
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
        <h2>What Stands Out</h2>
        <ul class="check-list">
            <li>Create a player name and jump straight into the studio</li>
            <li>Keep one personal case sealed until the very end</li>
            <li>Watch banker offers react to the live board</li>
            <li>Answer short between-round strategy prompts</li>
            <li>Chase a spot on the biggest-win leaderboard</li>
        </ul>
        <div class="badge-row">
            <span class="badge">Board watch</span>
            <span class="badge">Fast sign-in</span>
            <span class="badge">Live prompt leveling</span>
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
        <h2>Welcome Back</h2>
        <?php if (remembered_username() !== ''): ?>
            <p>Welcome back, <strong><?= h(remembered_username()) ?></strong>. Your last player name is ready so you can get back to the board faster.</p>
        <?php else: ?>
            <p>This browser can remember the last player name used here, which makes the next sign-in quicker.</p>
        <?php endif; ?>
        <p class="muted">The payout table stays open to everyone, so past runs are always easy to check.</p>
    </article>
</section>

<?php render_footer(); ?>
