<?php

declare(strict_types=1);

const CASE_VALUES = [
    0.01, 1, 5, 10, 25, 50, 75, 100, 200, 300, 400, 500, 750,
    1000, 5000, 10000, 25000, 50000, 75000, 100000, 200000,
    300000, 400000, 500000, 750000, 1000000,
];

const ROUND_SCHEDULE = [6, 5, 4, 3, 2, 1, 1, 1];

function initialize_game(string $username): array
{
    // Reset the adaptive prompt session every time a fresh game starts.
    $_SESSION['ai_diff'] = 2;
    $_SESSION['seen_ids'] = [];
    $_SESSION['recent'] = [];
    $_SESSION['answers'] = [];
    $_SESSION['current_ai_question'] = null;
    $_SESSION['current_ai_round'] = null;
    $_SESSION['answered_ai_rounds'] = [];
    $_SESSION['last_ai_feedback'] = null;
    $_SESSION['scores'] = $_SESSION['scores'] ?? [];

    $caseNumbers = range(1, 26);
    $values = CASE_VALUES;
    shuffle($values);

    $caseMap = [];
    foreach ($caseNumbers as $index => $caseNumber) {
        $caseMap[$caseNumber] = $values[$index];
    }

    return [
        'token' => bin2hex(random_bytes(8)),
        'player_name' => $username,
        'case_map' => $caseMap,
        'opened_cases' => [],
        'opened_history' => [],
        'offer_history' => [],
        'round_index' => 0,
        'remaining_to_open' => ROUND_SCHEDULE[0],
        'selected_case' => null,
        'current_offer' => null,
        'current_offer_context' => '',
        'stage' => 'select',
        'deal_taken' => false,
        'accepted_offer' => null,
        'final_amount' => null,
        'started_at' => date('c'),
        'completed_at' => null,
        'result_saved' => false,
        'stats' => [
            'highest_offer' => 0,
            'largest_value_eliminated' => 0,
            'rounds_completed' => 0,
            'offers_rejected' => 0,
        ],
    ];
}

function question_bank(): array
{
    return [
        [
            'id' => 'risk-ev-1',
            'difficulty' => 1,
            'category' => 'expected_value',
            'prompt' => 'If many high values are still alive, what usually happens to the expected value of the board?',
            'options' => [
                'a' => 'It usually rises',
                'b' => 'It always drops to zero',
                'c' => 'It stops changing',
                'd' => 'It becomes negative',
            ],
            'correct' => 'a',
            'explanation' => 'More high values still in play usually means a higher average board value.',
        ],
        [
            'id' => 'risk-floor-1',
            'difficulty' => 1,
            'category' => 'banker_logic',
            'prompt' => 'If the smallest amounts are mostly gone, what does that usually do to the banker offer?',
            'options' => [
                'a' => 'It usually helps the offer climb',
                'b' => 'It freezes the offer',
                'c' => 'It forces an automatic deal',
                'd' => 'It ends the game immediately',
            ],
            'correct' => 'a',
            'explanation' => 'Removing low values raises the board average, which usually improves the offer.',
        ],
        [
            'id' => 'variance-2',
            'difficulty' => 2,
            'category' => 'volatility',
            'prompt' => 'Why might the banker stay cautious when one million is still live but many tiny amounts are also still live?',
            'options' => [
                'a' => 'The board is volatile',
                'b' => 'The player is guaranteed to win big',
                'c' => 'The round schedule is over',
                'd' => 'The selected case is already revealed',
            ],
            'correct' => 'a',
            'explanation' => 'A wide spread between tiny and huge values makes the board volatile, which can suppress the offer.',
        ],
        [
            'id' => 'median-2',
            'difficulty' => 2,
            'category' => 'banker_logic',
            'prompt' => 'If your last few openings remove only middle-tier values while the top prizes survive, what is the likely banker reaction?',
            'options' => [
                'a' => 'The banker may improve the offer slightly',
                'b' => 'The banker must offer the top prize',
                'c' => 'The banker must cut the offer in half',
                'd' => 'Nothing can change until the final round',
            ],
            'correct' => 'a',
            'explanation' => 'Keeping top prizes alive while trimming middle values can improve the board, but not guarantee a massive offer.',
        ],
        [
            'id' => 'streak-3',
            'difficulty' => 3,
            'category' => 'strategy',
            'prompt' => 'Why can a player misread the board after only a couple of lucky openings?',
            'options' => [
                'a' => 'A short hot streak can create false confidence',
                'b' => 'The banker must stop calling',
                'c' => 'All low values automatically return',
                'd' => 'Your personal case changes value',
            ],
            'correct' => 'a',
            'explanation' => 'A brief run of good luck can make the board feel safer than it really is.',
        ],
        [
            'id' => 'flow-3',
            'difficulty' => 3,
            'category' => 'strategy',
            'prompt' => 'Which board usually creates the toughest Deal or No Deal decision?',
            'options' => [
                'a' => 'A board with one huge prize and several tiny amounts',
                'b' => 'A board where every amount is the same',
                'c' => 'A board with no cases left to open',
                'd' => 'A board after the final reveal',
            ],
            'correct' => 'a',
            'explanation' => 'A wide split between the ceiling and floor creates maximum tension because the upside and risk are both dramatic.',
        ],
    ];
}

function ai_difficulty_label(): string
{
    return match ((int) ($_SESSION['ai_diff'] ?? 2)) {
        1 => 'Easy',
        3 => 'Hard',
        default => 'Medium',
    };
}

function ai_question_for_round(int $roundIndex): array
{
    if (in_array($roundIndex, $_SESSION['answered_ai_rounds'] ?? [], true)) {
        return [];
    }

    if (($_SESSION['current_ai_round'] ?? null) === $roundIndex && is_array($_SESSION['current_ai_question'] ?? null)) {
        return $_SESSION['current_ai_question'];
    }

    $questions = select_ai_questions_by_difficulty();
    $question = $questions[array_rand($questions)];

    $_SESSION['current_ai_round'] = $roundIndex;
    $_SESSION['current_ai_question'] = $question;

    return $question;
}

function select_ai_questions_by_difficulty(): array
{
    $bank = question_bank();
    $targetDifficulty = (int) ($_SESSION['ai_diff'] ?? 2);
    $seenIds = $_SESSION['seen_ids'] ?? [];

    // First try to stay on the current difficulty tier, then gracefully widen if that tier is exhausted.
    $filtered = array_values(array_filter(
        $bank,
        static fn (array $question): bool =>
            $question['difficulty'] === $targetDifficulty && !in_array($question['id'], $seenIds, true)
    ));

    if ($filtered !== []) {
        return $filtered;
    }

    $fallback = array_values(array_filter(
        $bank,
        static fn (array $question): bool => !in_array($question['id'], $seenIds, true)
    ));

    if ($fallback !== []) {
        return $fallback;
    }

    $_SESSION['seen_ids'] = [];

    return array_values(array_filter(
        $bank,
        static fn (array $question): bool => $question['difficulty'] === $targetDifficulty
    ));
}

function submit_ai_answer(string $choice, int $roundIndex): array
{
    if (in_array($roundIndex, $_SESSION['answered_ai_rounds'] ?? [], true)) {
        return $_SESSION['last_ai_feedback'] ?? [
            'correct' => false,
            'message' => 'This round already has a saved strategy result.',
        ];
    }

    $question = ai_question_for_round($roundIndex);
    if ($question === []) {
        return [
            'correct' => false,
            'message' => 'No strategy prompt is available for this round.',
        ];
    }

    $isCorrect = $choice === $question['correct'];

    $_SESSION['seen_ids'][] = $question['id'];
    $_SESSION['answers'][] = [
        'id' => $question['id'],
        'category' => $question['category'],
        'difficulty' => $question['difficulty'],
        'correct' => $isCorrect,
    ];
    $_SESSION['recent'][] = $isCorrect;
    $_SESSION['recent'] = array_slice($_SESSION['recent'], -3);
    $_SESSION['current_ai_question'] = null;
    $_SESSION['current_ai_round'] = null;
    $_SESSION['answered_ai_rounds'][] = $roundIndex;

    recalibrate_ai_difficulty();

    $_SESSION['last_ai_feedback'] = [
        'correct' => $isCorrect,
        'message' => $isCorrect
            ? 'Correct. ' . $question['explanation']
            : 'Not quite. ' . $question['explanation'],
    ];

    return $_SESSION['last_ai_feedback'];
}

function recalibrate_ai_difficulty(): void
{
    $recent = $_SESSION['recent'] ?? [];
    $current = (int) ($_SESSION['ai_diff'] ?? 2);

    if (count($recent) < 3) {
        return;
    }

    $correctCount = count(array_filter($recent, static fn (bool $result): bool => $result));

    if ($correctCount >= 2) {
        $_SESSION['ai_diff'] = min(3, $current + 1);

        return;
    }

    if ($correctCount <= 1) {
        $_SESSION['ai_diff'] = max(1, $current - 1);
    }
}

function ai_recent_summary(): string
{
    $recent = $_SESSION['recent'] ?? [];
    if ($recent === []) {
        return 'No strategy prompts answered yet.';
    }

    $correct = count(array_filter($recent, static fn (bool $result): bool => $result));

    return sprintf('You got %d of your last %d strategy prompts right.', $correct, count($recent));
}

function active_game(): ?array
{
    return $_SESSION['game'] ?? null;
}

function save_game(array $game): void
{
    $_SESSION['game'] = $game;
}

function clear_game(): void
{
    unset($_SESSION['game']);
}

function remaining_cases(array $game): array
{
    $remaining = [];
    foreach ($game['case_map'] as $caseNumber => $value) {
        if (!in_array($caseNumber, $game['opened_cases'], true) && $caseNumber !== $game['selected_case']) {
            $remaining[$caseNumber] = $value;
        }
    }

    return $remaining;
}

function remaining_values(array $game): array
{
    $values = array_values(remaining_cases($game));
    if ($game['selected_case'] !== null) {
        $values[] = $game['case_map'][$game['selected_case']];
    }

    sort($values);

    return $values;
}

function visible_board_values(array $game): array
{
    $values = CASE_VALUES;
    sort($values);

    $openedValues = array_map(
        static fn (array $opened): float => (float) $opened['value'],
        $game['opened_history']
    );

    return array_map(static function (float $value) use ($openedValues): array {
        return [
            'amount' => $value,
            'opened' => in_array($value, $openedValues, true),
        ];
    }, $values);
}

function banker_round_label(array $game): string
{
    return 'Round ' . ($game['round_index'] + 1);
}

function choose_personal_case(array &$game, int $caseNumber): bool
{
    if (!isset($game['case_map'][$caseNumber])) {
        return false;
    }

    if ($game['selected_case'] !== null) {
        return false;
    }

    $game['selected_case'] = $caseNumber;
    $game['stage'] = 'play';

    return true;
}

function open_case(array &$game, int $caseNumber): array
{
    if ($game['stage'] !== 'play') {
        return ['ok' => false, 'message' => 'This round is not ready for another case.'];
    }

    if (!isset($game['case_map'][$caseNumber])) {
        return ['ok' => false, 'message' => 'That briefcase does not exist.'];
    }

    if ($caseNumber === $game['selected_case']) {
        return ['ok' => false, 'message' => 'That is your personal briefcase.'];
    }

    if (in_array($caseNumber, $game['opened_cases'], true)) {
        return ['ok' => false, 'message' => 'That briefcase was already opened.'];
    }

    $value = (float) $game['case_map'][$caseNumber];
    $game['opened_cases'][] = $caseNumber;
    $game['opened_history'][] = [
        'case' => $caseNumber,
        'value' => $value,
        'round' => $game['round_index'] + 1,
    ];
    $game['remaining_to_open']--;
    $game['stats']['largest_value_eliminated'] = max($game['stats']['largest_value_eliminated'], $value);

    if ($game['remaining_to_open'] <= 0) {
        if (count(remaining_cases($game)) === 1) {
            $game['stage'] = 'complete';
        } else {
            $game['stage'] = 'banker';
            $game['current_offer'] = calculate_banker_offer($game);
            $game['current_offer_context'] = banker_context_summary($game, $game['current_offer']);
            $game['offer_history'][] = [
                'round' => $game['round_index'] + 1,
                'offer' => $game['current_offer'],
            ];
            $game['stats']['highest_offer'] = max($game['stats']['highest_offer'], $game['current_offer']);
            $game['stats']['rounds_completed'] = $game['round_index'] + 1;
        }
    }

    return [
        'ok' => true,
        'message' => 'Briefcase ' . $caseNumber . ' revealed $' . money($value) . '.',
    ];
}

function calculate_banker_offer(array $game): float
{
    $values = remaining_values($game);
    $average = array_sum($values) / max(count($values), 1);
    $maxValue = max($values);
    $minValue = min($values);
    $spread = $maxValue - $minValue;
    $openedRatio = count($game['opened_cases']) / 25;

    // The banker rewards progress through the board, but discounts wide volatility.
    $roundFactor = 0.32 + ($openedRatio * 0.58);
    $volatilityPenalty = min(0.12, $spread / 10000000);
    $offer = $average * max(0.28, $roundFactor - $volatilityPenalty);

    return round($offer, 2);
}

function banker_context_summary(array $game, float $offer): string
{
    $values = remaining_values($game);
    $average = array_sum($values) / count($values);
    $topThree = array_slice(array_reverse($values), 0, 3);
    $lowThree = array_slice($values, 0, 3);
    $risk = risk_band($values);

    return sprintf(
        'Expected value: $%s. Top prizes left: $%s. Safety floor: $%s. Banker reads this board as %s risk.',
        money($average),
        implode(', ', array_map(static fn (float $value): string => '$' . money($value), $topThree)),
        implode(', ', array_map(static fn (float $value): string => '$' . money($value), $lowThree)),
        $risk
    );
}

function risk_band(array $values): string
{
    $count = count(array_filter($values, static fn (float $value): bool => $value >= 100000));
    if ($count >= 4) {
        return 'high-upside';
    }
    if ($count >= 2) {
        return 'balanced';
    }

    return 'defensive';
}

function reject_banker_offer(array &$game): void
{
    $game['stats']['offers_rejected']++;
    $game['round_index']++;
    $game['current_offer'] = null;
    $game['current_offer_context'] = '';

    if (count(remaining_cases($game)) === 1) {
        $game['stage'] = 'complete';

        return;
    }

    $nextSchedule = ROUND_SCHEDULE[$game['round_index']] ?? 1;
    $game['remaining_to_open'] = min($nextSchedule, count(remaining_cases($game)) - 1);
    $game['stage'] = 'play';
}

function accept_banker_offer(array &$game): void
{
    $game['deal_taken'] = true;
    $game['accepted_offer'] = $game['current_offer'];
    $game['final_amount'] = $game['current_offer'];
    $game['stage'] = 'complete';
    $game['completed_at'] = date('c');
}

function finalize_game(array &$game): array
{
    if ($game['deal_taken'] && $game['final_amount'] !== null) {
        $chosenValue = (float) $game['case_map'][$game['selected_case']];
    } else {
        $chosenValue = (float) $game['case_map'][$game['selected_case']];
        $game['final_amount'] = $chosenValue;
        $game['completed_at'] = date('c');
    }

    $otherCases = remaining_cases($game);
    arsort($otherCases);
    $finalOpponentCase = (int) array_key_first($otherCases);
    $finalOpponentValue = (float) array_values($otherCases)[0];

    $result = [
        'selected_case' => $game['selected_case'],
        'selected_value' => $chosenValue,
        'final_case' => $finalOpponentCase,
        'final_case_value' => $finalOpponentValue,
        'comparison_label' => $game['deal_taken'] ? 'Highest live case after your deal' : 'Final opposing case',
        'deal_taken' => $game['deal_taken'],
        'accepted_offer' => $game['accepted_offer'],
        'final_amount' => $game['final_amount'],
        'completed_at' => $game['completed_at'] ?? date('c'),
    ];

    if (!$game['result_saved']) {
        // Save the score for the current visit and the shared leaderboard.
        $_SESSION['scores'][] = [
            'username' => $game['player_name'],
            'amount' => $game['final_amount'],
            'completed_at' => $result['completed_at'],
        ];
        save_leaderboard_entry([
            'username' => $game['player_name'],
            'amount' => $game['final_amount'],
            'outcome' => $game['deal_taken'] ? 'Deal' : 'No Deal',
            'selected_case' => $game['selected_case'],
            'selected_value' => $chosenValue,
            'offers_rejected' => $game['stats']['offers_rejected'],
            'highest_offer' => $game['stats']['highest_offer'],
            'largest_value_eliminated' => $game['stats']['largest_value_eliminated'],
            'completed_at' => $result['completed_at'],
        ]);
        $game['result_saved'] = true;
    }

    return $result;
}

function money(float $amount): string
{
    if ($amount < 1) {
        return number_format($amount, 2);
    }

    return number_format($amount, 0);
}

function banker_decision_profile(array $game): string
{
    if ($game['deal_taken']) {
        return 'You locked in certainty and beat the volatility.';
    }

    if (($game['stats']['offers_rejected'] ?? 0) >= 3) {
        return 'You played for upside and trusted the board until the reveal.';
    }

    return 'You balanced caution with suspense and pushed the banker carefully.';
}

function longest_correct_streak(array $answers): int
{
    $best = 0;
    $current = 0;

    foreach ($answers as $answer) {
        if (!empty($answer['correct'])) {
            $current++;
            $best = max($best, $current);
            continue;
        }

        $current = 0;
    }

    return $best;
}

function strategy_prompt_summary(array $answers): array
{
    $total = count($answers);
    $correct = count(array_filter($answers, static fn (array $answer): bool => !empty($answer['correct'])));
    $accuracy = $total > 0 ? (int) round(($correct / $total) * 100) : 0;

    $categoryBuckets = [];
    foreach ($answers as $answer) {
        $category = (string) ($answer['category'] ?? 'general');
        $categoryBuckets[$category] = $categoryBuckets[$category] ?? ['total' => 0, 'correct' => 0];
        $categoryBuckets[$category]['total']++;
        if (!empty($answer['correct'])) {
            $categoryBuckets[$category]['correct']++;
        }
    }

    $bestCategory = 'N/A';
    $bestRate = -1;
    foreach ($categoryBuckets as $category => $bucket) {
        $rate = $bucket['total'] > 0 ? $bucket['correct'] / $bucket['total'] : 0;
        if ($rate > $bestRate) {
            $bestRate = $rate;
            $bestCategory = str_replace('_', ' ', ucfirst($category));
        }
    }

    return [
        'total' => $total,
        'correct' => $correct,
        'accuracy' => $accuracy,
        'best_category' => $bestCategory,
        'best_streak' => longest_correct_streak($answers),
    ];
}

function player_style_label(array $game, array $result): string
{
    $highestOffer = (float) ($game['stats']['highest_offer'] ?? 0);
    $selectedValue = (float) ($result['selected_value'] ?? 0);
    $finalAmount = (float) ($result['final_amount'] ?? 0);

    if ($game['deal_taken'] && $finalAmount >= $selectedValue) {
        return 'Took the Safe Money';
    }

    if (!$game['deal_taken'] && $finalAmount >= $highestOffer && $highestOffer > 0) {
        return 'Trusted the Big Case';
    }

    if (($game['stats']['offers_rejected'] ?? 0) >= 3) {
        return 'Played for the Swing';
    }

    return 'Balanced Run';
}

function banker_offer_growth(array $game): float
{
    $history = $game['offer_history'] ?? [];
    if (count($history) < 2) {
        return 0;
    }

    $first = (float) ($history[0]['offer'] ?? 0);
    $highest = (float) ($game['stats']['highest_offer'] ?? 0);

    return max(0, $highest - $first);
}

function leaderboard_snapshot(array $entries): array
{
    if ($entries === []) {
        return [
            'highest' => 0,
            'average' => 0,
            'deal_count' => 0,
            'no_deal_count' => 0,
        ];
    }

    $dealCount = count(array_filter(
        $entries,
        static fn (array $entry): bool => (($entry['outcome'] ?? '') === 'Deal')
    ));
    $total = array_sum(array_map(static fn (array $entry): float => (float) ($entry['amount'] ?? 0), $entries));

    return [
        'highest' => (float) ($entries[0]['amount'] ?? 0),
        'average' => $total / count($entries),
        'deal_count' => $dealCount,
        'no_deal_count' => count($entries) - $dealCount,
    ];
}
