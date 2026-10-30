<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/frames.php';

$allowedCategories = ['warframe', 'prime', 'protoframe'];
$category = $_GET['category'] ?? 'warframe';

if (!in_array($category, $allowedCategories, true)) {
    $category = 'warframe';
}

$search = trim($_GET['q'] ?? '');
$roleId = (int) ($_GET['role'] ?? 0);
$openSlug = $_GET['open'] ?? '';

if (preg_match('/^[a-z0-9-]+$/', $openSlug) !== 1) {
    $openSlug = '';
}

$returnTo = 'index.php';
$showAllTab = false;
$page = 'index.php';
$user = current_user();
$dbError = '';
$frames = [];
$roles = [];

try {
    $roles = fetch_roles();
    $frames = fetch_frames([
        'category' => $category,
        'q' => $search,
        'role_id' => $roleId,
        'user_id' => $user['id'] ?? 0,
        'favorites_only' => false,
    ]);
} catch (PDOException $exception) {
    $dbError = 'The database is connected, but the catalog tables are missing or out of date. Import sql/schema.sql and then sql/seed.sql in phpMyAdmin.';
}

$pageTitle = 'Frame Codex';
$nav = 'catalog';
$loadCatalogJs = true;

require __DIR__ . '/includes/header.php';
?>

<section class="page-intro">
    <h1>Warframe catalog</h1>
    <p>Browse base Warframes, Primes, and Protoframes. Select a card to read its passive and abilities.</p>
</section>

<?php if ($dbError !== ''): ?>
    <div class="alert alert-warning"><?= e($dbError) ?></div>
<?php else: ?>
    <?php require __DIR__ . '/includes/filters.php'; ?>

    <p class="result-count"><?= count($frames) ?> shown</p>

    <?php if (!$frames): ?>
        <div class="empty-state">
            <h2>No matches</h2>
            <p>Try another name, role, or tab.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($frames as $frame): ?>
                <?php require __DIR__ . '/includes/card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
