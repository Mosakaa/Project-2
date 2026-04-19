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

    store_old_input(['username' => $username]);
    $user = find_user($username);

    if ($user === null || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Invalid username or password.';
    }

    if ($errors === []) {
        clear_old_input();
        login_user($user);
        set_flash('success', 'Login successful. Choose your personal briefcase to begin.');
        redirect('/game.php');
    }
}

render_header('Log In and Enter the Studio', [
    'active' => 'login',
    'subtitle' => 'Sign in, claim your case, and head back to the studio floor.',
]);
?>

<section class="auth-grid">
    <article class="auth-card">
        <h2>Login</h2>
        <?php if ($errors !== []): ?>
            <div class="flash flash-error"><?= h(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= h(app_url('/login.php')) ?>" novalidate>
            <label>
                Username
                <input type="text" name="username" maxlength="20" value="<?= h(old('username', remembered_username())) ?>" required>
            </label>
            <label>
                Password
                <input type="password" name="password" required>
            </label>
            <input type="submit" value="Login">
        </form>
    </article>

    <article class="auth-card">
        <h2>Quick Notes</h2>
        <ul class="help-list">
            <li>This browser can remember your last player name for faster sign-in.</li>
            <li>Signed-in players can move straight to the main board.</li>
            <li>Logout ends the current visit and returns you to the home page.</li>
        </ul>
        <p><a class="button ghost" href="<?= h(app_url('/register.php')) ?>">Need an account? Register</a></p>
    </article>
</section>

<?php render_footer(); ?>
