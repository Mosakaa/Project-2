<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_auth();

$user = current_user();
$game = active_game();

if ($game === null || (isset($_GET['new']) && $_GET['new'] === '1') || (isset($_POST['action']) && $_POST['action'] === 'new_game')) {
    $game = initialize_game($user['username']);
    save_game($game);
}

if ($game['stage'] === 'banker') {
    redirect('/banker.php');
}

if ($game['stage'] === 'complete') {
    redirect('/result.php');
}

if (is_post()) {
    $action = post_string('action', 40);

    if ($action === 'choose_case') {
        $caseNumber = post_int('case_number');
        if (choose_personal_case($game, $caseNumber)) {
            set_flash('success', 'Briefcase ' . $caseNumber . ' is yours. Start opening the board.');
        } else {
            set_flash('error', 'Choose a valid briefcase that has not already been assigned.');
        }
    }

    if ($action === 'open_case') {
        $caseNumber = post_int('case_number');
        $result = open_case($game, $caseNumber);
        set_flash($result['ok'] ? 'success' : 'error', $result['message']);
    }

    save_game($game);

    if ($game['stage'] === 'banker') {
        redirect('/banker.php');
    }

    if ($game['stage'] === 'complete') {
        redirect('/result.php');
    }

    redirect('/game.php');
}

$game = active_game();
$remaining = remaining_cases($game);
$boardValues = visible_board_values($game);
$casesLeft = count($remaining);
$aiLabel = ai_difficulty_label();

render_header('Main Game Board', [
    'active' => 'game',
    'subtitle' => 'Choose your case, clear the round, and wait for the banker’s next call.',
]);
?>

<section class="board-layout">
    <aside class="card">
        <h2>Round Status</h2>
        <div class="metric-list">
            <div class="metric">
                <span>Round</span>
                <strong><?= h(banker_round_label($game)) ?></strong>
            </div>
            <div class="metric">
                <span>Cases Left This Round</span>
                <strong><?= h((string) $game['remaining_to_open']) ?></strong>
            </div>
            <div class="metric">
                <span>Cases Remaining</span>
                <strong><?= h((string) $casesLeft) ?></strong>
            </div>
        </div>
        <p><strong>Your case:</strong> <?= $game['selected_case'] !== null ? '#' . h((string) $game['selected_case']) : 'Choose one below' ?></p>
        <p><strong>Prompt Level:</strong> <span class="badge"><?= h($aiLabel) ?></span></p>
        <p class="muted"><?= h(ai_recent_summary()) ?></p>
        <form method="post" action="<?= h(app_url('/game.php')) ?>">
            <input type="hidden" name="action" value="new_game">
            <button class="ghost" type="submit">Reset Current Game</button>
        </form>
    </aside>

    <section class="card">
        <h2><?= $game['selected_case'] === null ? 'Choose Your Personal Briefcase' : 'Open the Remaining Cases' ?></h2>
        <div class="briefcase-grid">
            <?php foreach ($game['case_map'] as $caseNumber => $value): ?>
                <?php
                $isOpened = in_array($caseNumber, $game['opened_cases'], true);
                $isSelected = $game['selected_case'] === $caseNumber;
                $stateClass = $isOpened ? 'opened' : ($isSelected ? 'selected' : '');
                ?>
                <div class="briefcase <?= h($stateClass) ?>">
                    <?php if ($game['selected_case'] === null): ?>
                        <form method="post" action="<?= h(app_url('/game.php')) ?>">
                            <input type="hidden" name="action" value="choose_case">
                            <input type="hidden" name="case_number" value="<?= h((string) $caseNumber) ?>">
                            <button type="submit">Case <?= h((string) $caseNumber) ?><small>Keep this one</small></button>
                        </form>
                    <?php elseif ($isOpened): ?>
                        <button type="button" disabled>Case <?= h((string) $caseNumber) ?><small>$<?= h(money((float) $value)) ?></small></button>
                    <?php elseif ($isSelected): ?>
                        <button type="button" disabled>Case <?= h((string) $caseNumber) ?><small>Your case</small></button>
                    <?php else: ?>
                        <form method="post" action="<?= h(app_url('/game.php')) ?>">
                            <input type="hidden" name="action" value="open_case">
                            <input type="hidden" name="case_number" value="<?= h((string) $caseNumber) ?>">
                            <button type="submit">Case <?= h((string) $caseNumber) ?><small>Open now</small></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <aside class="card">
        <h2>Value Board</h2>
        <div class="case-value-grid">
            <?php foreach ($boardValues as $valueState): ?>
                <div class="case-value <?= $valueState['opened'] ? 'opened' : '' ?>">
                    <span>$<?= h(money((float) $valueState['amount'])) ?></span>
                    <span><?= $valueState['opened'] ? 'Off board' : 'Alive' ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </aside>
</section>

<section class="two-col">
    <article class="card">
        <h2>Opened Case History</h2>
        <?php if ($game['opened_history'] === []): ?>
            <div class="empty-state">No cases opened yet. Once you start revealing cases, each round is logged here.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                <tr>
                    <th>Round</th>
                    <th>Case</th>
                    <th>Value</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (array_reverse($game['opened_history']) as $opened): ?>
                    <tr>
                        <td><?= h((string) $opened['round']) ?></td>
                        <td>#<?= h((string) $opened['case']) ?></td>
                        <td>$<?= h(money((float) $opened['value'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </article>

    <article class="card">
        <h2>Board Watch</h2>
        <p>A quick read on how friendly or dangerous the board looks right now.</p>
        <ul class="help-list">
            <li><strong>Highest offer so far:</strong> $<?= h(money((float) $game['stats']['highest_offer'])) ?></li>
            <li><strong>Largest value eliminated:</strong> $<?= h(money((float) $game['stats']['largest_value_eliminated'])) ?></li>
            <li><strong>Risk band:</strong> <?= h(risk_band(remaining_values($game))) ?></li>
            <li><strong>Offers rejected:</strong> <?= h((string) $game['stats']['offers_rejected']) ?></li>
        </ul>
    </article>
</section>

<?php render_footer(); ?>
