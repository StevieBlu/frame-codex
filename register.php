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
$email = '';
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'The form expired. Please try again.';
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (preg_match('/^[A-Za-z0-9_]{3,20}$/', $username) !== 1) {
        $errors[] = 'Username must be 3 to 20 characters and use only letters, numbers, and underscores.';
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 255) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }

    if (!$errors) {
        $pdo = db();
        $taken = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
        $taken->execute([$username, $email]);
        $existing = $taken->fetch();

        if ($existing) {
            if (strcasecmp($existing['username'], $username) === 0) {
                $errors[] = 'That username is already taken.';
            } else {
                $errors[] = 'That email is already registered.';
            }
        } else {
            $insert = $pdo->prepare(
                'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)'
            );
            $insert->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
            ]);

            log_in_user((int) $pdo->lastInsertId(), $username);
            header('Location: ' . $next);
            exit;
        }
    }
}

$pageTitle = 'Register - Frame Codex';
$nav = 'register';
$loadCatalogJs = false;

require __DIR__ . '/includes/header.php';
?>

<section class="auth-panel">
    <h1>Create an account</h1>
    <p>An account lets you save favorite Warframes. The site does not send email.</p>

    <?php if ($errors): ?>
        <div class="alert alert-warning">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= e($next) ?>">

        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" id="username" name="username" value="<?= e($username) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" id="email" name="email" type="email" value="<?= e($email) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" required>
            <div class="form-text">At least 8 characters.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="confirm_password">Confirm password</label>
            <input class="form-control" id="confirm_password" name="confirm_password" type="password" required>
        </div>
        <button class="btn btn-warning" type="submit">Register</button>
        <a class="btn btn-link" href="login.php?next=<?= e(urlencode($next)) ?>">I already have an account</a>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
