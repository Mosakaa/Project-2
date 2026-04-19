<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$entries = array_slice(leaderboard_entries(), 0, 10);

render_header('Leaderboard', [
    'active' => 'leaderboard',
    'subtitle' => 'Biggest payouts from the studio so far.',
]);
?>

<section class="leaderboard-layout">
    <article class="leaderboard-table-wrap">
        <h2>Top 10 Payouts</h2>
        <?php if ($entries === []): ?>
            <div class="empty-state">No completed games yet. Finish a run to seed the leaderboard.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
                    <th>Payout</th>
                    <th>Outcome</th>
                    <th>Completed</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $index => $entry): ?>
                    <tr>
                        <td>#<?= h((string) ($index + 1)) ?></td>
                        <td><?= h((string) $entry['username']) ?></td>
                        <td>$<?= h(money((float) $entry['amount'])) ?></td>
                        <td><?= h((string) $entry['outcome']) ?></td>
                        <td><?= h(date('Y-m-d H:i', strtotime((string) $entry['completed_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </article>

    <article class="card">
        <h2>Hall of Fame Notes</h2>
        <ul class="help-list">
            <li>Username and final payout</li>
            <li>Deal or No Deal outcome</li>
            <li>Highest offer seen during the run</li>
            <li>Largest value eliminated before the result</li>
            <li>Completion timestamp for tie-breaking</li>
        </ul>
        <p class="muted">Every finished run can earn a place here, even after the player signs out.</p>
        <div class="cta-row">
            <a class="button" href="<?= h(app_url('/game.php?new=1')) ?>">Play a New Game</a>
            <a class="button ghost" href="<?= h(app_url('/index.php')) ?>">Return Home</a>
        </div>
    </article>
</section>

<?php render_footer(); ?>
