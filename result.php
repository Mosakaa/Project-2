<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_auth();

$game = active_game();

if ($game === null || $game['selected_case'] === null) {
    set_flash('error', 'There is no finished game to reveal yet.');
    redirect('/game.php');
}

if ($game['stage'] !== 'complete' && $game['stage'] !== 'banker') {
    set_flash('error', 'Finish the board before viewing the result.');
    redirect('/game.php');
}

$result = finalize_game($game);
save_game($game);

render_header('Final Result', [
    'active' => 'game',
    'subtitle' => 'Final payout, case reveal, and game summary.',
]);
?>

<section class="result-grid">
    <article class="card">
        <h2><?= $result['deal_taken'] ? 'You Took the Deal' : 'You Played to the End' ?></h2>
        <p class="eyebrow">Final Payout</p>
        <p class="result-amount">$<?= h(money((float) $result['final_amount'])) ?></p>
        <ul class="help-list">
            <li><strong>Your case:</strong> #<?= h((string) $result['selected_case']) ?> held $<?= h(money((float) $result['selected_value'])) ?></li>
            <li><strong><?= h((string) $result['comparison_label']) ?>:</strong> #<?= h((string) $result['final_case']) ?> held $<?= h(money((float) $result['final_case_value'])) ?></li>
            <?php if ($result['deal_taken']): ?>
                <li><strong>Accepted banker offer:</strong> $<?= h(money((float) $result['accepted_offer'])) ?></li>
            <?php endif; ?>
        </ul>
        <div class="cta-row">
            <a class="button" href="<?= h(app_url('/leaderboard.php')) ?>">View Leaderboard</a>
            <a class="button ghost" href="<?= h(app_url('/game.php?new=1')) ?>">Play Again</a>
        </div>
    </article>

    <article class="card">
        <h2>Round Summary</h2>
        <p><strong>Final Prompt Level:</strong> <?= h(ai_difficulty_label()) ?></p>
        <p><?= h(ai_recent_summary()) ?></p>
        <div class="metric-list">
            <div class="metric">
                <span>Prompts Answered</span>
                <strong><?= h((string) count($_SESSION['answers'] ?? [])) ?></strong>
            </div>
            <div class="metric">
                <span>Correct Reads</span>
                <strong><?= h((string) count(array_filter($_SESSION['answers'] ?? [], static fn (array $answer): bool => (bool) $answer['correct']))) ?></strong>
            </div>
            <div class="metric">
                <span>Main Themes</span>
                <strong><?= h(implode(', ', array_slice(array_values(array_unique(array_map(static fn (array $answer): string => (string) $answer['category'], $_SESSION['answers'] ?? []))), 0, 3)) ?: 'N/A') ?></strong>
            </div>
        </div>
    </article>
</section>

<section class="two-col">
    <article class="card">
        <h2>Offer Timeline</h2>
        <?php if ($game['offer_history'] === []): ?>
            <div class="empty-state">No banker offers were generated before the final reveal.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                <tr>
                    <th>Round</th>
                    <th>Offer</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($game['offer_history'] as $offer): ?>
                    <tr>
                        <td><?= h((string) $offer['round']) ?></td>
                        <td>$<?= h(money((float) $offer['offer'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </article>

    <article class="card">
        <h2>Play Again</h2>
        <ul class="help-list">
            <li>Each finished run records a single final score on the leaderboard.</li>
            <li>Starting again reshuffles every case and clears the banker history.</li>
            <li>The prompt level returns to Medium at the start of a fresh game.</li>
        </ul>
    </article>
</section>

<?php render_footer(); ?>
