<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_auth();

$game = active_game();

if ($game === null) {
    set_flash('error', 'Start a game before visiting the banker.');
    redirect('/game.php');
}

if ($game['stage'] === 'complete') {
    redirect('/result.php');
}

if ($game['stage'] !== 'banker') {
    set_flash('error', 'The banker is only available after a completed round.');
    redirect('/game.php');
}

if (is_post()) {
    $action = post_string('action', 30);
    $decision = post_string('decision', 20);

    if ($action === 'answer_ai') {
        $choice = post_string('ai_choice', 2);
        $feedback = submit_ai_answer($choice, (int) $game['round_index']);
        set_flash($feedback['correct'] ? 'success' : 'error', $feedback['message']);
        redirect('/banker.php');
    }

    if ($decision === 'deal') {
        accept_banker_offer($game);
        save_game($game);
        redirect('/result.php');
    }

    if ($decision === 'no_deal') {
        reject_banker_offer($game);
        save_game($game);
        set_flash('success', 'No Deal. Back to the board.');
        redirect('/game.php');
    }
}

$currentQuestion = ai_question_for_round((int) $game['round_index']);
$alreadyAnswered = in_array((int) $game['round_index'], $_SESSION['answered_ai_rounds'] ?? [], true);
$lastAiFeedback = $_SESSION['last_ai_feedback'] ?? null;

render_header('Banker Offer', [
    'active' => 'game',
    'subtitle' => 'The call is in. Hear the offer and decide whether to cash out or press on.',
]);
?>

<section class="result-grid">
    <article class="card offer-card">
        <h2>Current Offer</h2>
        <p class="eyebrow">Round Summary</p>
        <p class="offer-amount">$<?= h(money((float) $game['current_offer'])) ?></p>
        <p><?= h($game['current_offer_context']) ?></p>
        <div class="tag-row tag-row-spaced">
            <span class="tag">Highest offer: $<?= h(money((float) $game['stats']['highest_offer'])) ?></span>
            <span class="tag">Rejected offers: <?= h((string) $game['stats']['offers_rejected']) ?></span>
        </div>
    </article>

    <article class="card">
        <h2>Decision</h2>
        <p>Take the money now or send the banker away and keep opening cases.</p>
        <form method="post" action="<?= h(app_url('/banker.php')) ?>" class="stack-form">
            <input type="hidden" name="decision" value="deal">
            <input type="submit" value="Deal">
        </form>
        <form method="post" action="<?= h(app_url('/banker.php')) ?>" class="stack-form stack-form-spaced">
            <input type="hidden" name="decision" value="no_deal">
            <button class="secondary" type="submit">No Deal</button>
        </form>
    </article>
</section>

<section class="two-col">
    <article class="card">
        <h2>Strategy Pulse</h2>
        <p><span class="badge"><?= h(ai_difficulty_label()) ?></span></p>
        <p class="muted"><?= h(ai_recent_summary()) ?></p>
        <?php if ($alreadyAnswered && $lastAiFeedback !== null): ?>
            <p><?= h($lastAiFeedback['message']) ?></p>
        <?php elseif ($currentQuestion !== []): ?>
            <p>A short between-round prompt keeps the pressure on and tracks how sharp your reads are tonight.</p>
            <form method="post" action="<?= h(app_url('/banker.php')) ?>" class="stack-form">
                <input type="hidden" name="action" value="answer_ai">
                <p><strong><?= h($currentQuestion['prompt']) ?></strong></p>
                <?php foreach ($currentQuestion['options'] as $optionKey => $optionText): ?>
                    <label>
                        <input type="radio" name="ai_choice" value="<?= h($optionKey) ?>" required>
                        <?= h($optionText) ?>
                    </label>
                <?php endforeach; ?>
                <button class="ghost" type="submit">Submit Strategy Answer</button>
            </form>
        <?php endif; ?>
    </article>

    <article class="card">
        <h2>Remaining Live Values</h2>
        <div class="case-value-grid">
            <?php foreach (remaining_values($game) as $value): ?>
                <div class="case-value">
                    <span>$<?= h(money((float) $value)) ?></span>
                    <span>Still in play</span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="card">
        <h2>Offer History</h2>
        <?php if ($game['offer_history'] === []): ?>
            <div class="empty-state">No earlier offers yet.</div>
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
</section>

<?php render_footer(); ?>
