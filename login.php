<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

if (current_user() !== null) {
    header('Location: index.php');
    exit;
}

$errors = [];
$username = '';
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'The form expired. Please try again.';
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Enter your username and password.';
    }

    if (!$errors) {
        $statement = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
        $statement->execute([$username]);
        $account = $statement->fetch();

        if (!$account || !password_verify($password, $account['password_hash'])) {
            $errors[] = 'That username and password do not match.';
        } else {
            log_in_user((int) $account['id'], $account['username']);
            header('Location: ' . $next);
            exit;
        }
    }
}

$pageTitle = 'Log in - Frame Codex';
$nav = 'login';
$loadCatalogJs = false;

require __DIR__ . '/includes/header.php';
?>

<section class="auth-panel">
    <h1>Log in</h1>
    <p>Use the username and password from your Frame Codex account.</p>

    <?php if ($errors): ?>
        <div class="alert alert-warning">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= e($next) ?>">

        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" value="<?= e($username) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" required>
        </div>
        <button class="btn btn-warning" type="submit">Log in</button>
        <a class="btn btn-link" href="register.php?next=<?= e(urlencode($next)) ?>">Create an account</a>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
