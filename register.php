<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_authenticated()) {
    redirect('/game.php');
}

$errors = [];

if (is_post()) {
    $username = preg_replace('/[^a-zA-Z0-9_]/', '', post_string('username', 20));
    $password = post_string('password', 120);
    $confirm = post_string('confirm_password', 120);

    store_old_input(['username' => $username]);

    if ($username === '' || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters and use letters, numbers, or underscores only.';
    }

    if ($password === '' || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Password confirmation does not match.';
    }

    if ($errors === [] && !create_user($username, $password)) {
        $errors[] = 'That username is already taken.';
    }

    if ($errors === []) {
        clear_old_input();
        set_flash('success', 'Registration complete. Log in to start your game.');
        redirect('/login.php');
    }
}

render_header('Create Your Contestant Account', [
    'active' => 'register',
    'subtitle' => 'Create a player name and step onto the stage.',
]);
?>

<section class="auth-grid">
    <article class="auth-card">
        <h2>Register</h2>
        <?php if ($errors !== []): ?>
            <div class="flash flash-error"><?= h(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= h(app_url('/register.php')) ?>" novalidate>
            <label>
                Username
                <input type="text" name="username" maxlength="20" value="<?= h(old('username')) ?>" required>
            </label>
            <label>
                Password
                <input type="password" name="password" required>
            </label>
            <label>
                Confirm Password
                <input type="password" name="confirm_password" required>
            </label>
            <input type="submit" value="Create Account">
        </form>
    </article>

    <article class="auth-card">
        <h2>Before You Start</h2>
        <ul class="help-list">
            <li>Usernames use letters, numbers, and underscores only.</li>
            <li>Passwords must be at least six characters long.</li>
            <li>If something needs fixing, your username stays filled in.</li>
        </ul>
        <p><a class="button ghost" href="<?= h(app_url('/login.php')) ?>">Already registered? Log in</a></p>
    </article>
</section>

<?php render_footer(); ?>
