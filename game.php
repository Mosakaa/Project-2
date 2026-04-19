<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal or No Deal - Game</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="board-wrapper">
        <div class="board-grid">
            <?php for ($cell = 1; $cell <= 24; $cell++): ?>
                <div class="cell"><?php echo $cell; ?></div>
            <?php endfor; ?>
        </div>

        <div class="extra-row">
            <?php for ($cell = 25; $cell <= 26; $cell++): ?>
                <div class="cell"><?php echo $cell; ?></div>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>
