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

$promptSummary = strategy_prompt_summary($_SESSION['answers'] ?? []);
$playerStyle = player_style_label($game, $result);
$offerGrowth = banker_offer_growth($game);
$highestOffer = (float) ($game['stats']['highest_offer'] ?? 0);
$finalVsOffer = $highestOffer > 0 ? (float) $result['final_amount'] - $highestOffer : 0;

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
                <strong><?= h((string) $promptSummary['total']) ?></strong>
            </div>
            <div class="metric">
                <span>Correct Reads</span>
                <strong><?= h((string) $promptSummary['correct']) ?></strong>
            </div>
            <div class="metric">
                <span>Accuracy</span>
                <strong><?= h((string) $promptSummary['accuracy']) ?>%</strong>
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
        <h2>Performance Notes</h2>
        <div class="stat-grid">
            <div class="metric">
                <span>Play Style</span>
                <strong><?= h($playerStyle) ?></strong>
            </div>
            <div class="metric">
                <span>Best Category</span>
                <strong><?= h($promptSummary['best_category']) ?></strong>
            </div>
            <div class="metric">
                <span>Best Streak</span>
                <strong><?= h((string) $promptSummary['best_streak']) ?></strong>
            </div>
            <div class="metric">
                <span>Offer Growth</span>
                <strong>$<?= h(money($offerGrowth)) ?></strong>
            </div>
        </div>
        <ul class="help-list">
            <li><strong>Decision profile:</strong> <?= h(banker_decision_profile($game)) ?></li>
            <li><strong>Rounds completed:</strong> <?= h((string) ($game['stats']['rounds_completed'] ?? 0)) ?></li>
            <li><strong>Highest offer gap:</strong> <?= $highestOffer > 0 ? h(($finalVsOffer >= 0 ? '+' : '-') . '$' . money(abs($finalVsOffer))) : 'N/A' ?></li>
        </ul>
    </article>
</section>

<?php render_footer(); ?>
