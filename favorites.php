<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/frames.php';

$allowedCategories = ['warframe', 'prime', 'protoframe'];
$category = $_GET['category'] ?? '';

if (!in_array($category, $allowedCategories, true)) {
    $category = '';
}

$search = trim($_GET['q'] ?? '');
$roleId = (int) ($_GET['role'] ?? 0);
$openSlug = $_GET['open'] ?? '';

if (preg_match('/^[a-z0-9-]+$/', $openSlug) !== 1) {
    $openSlug = '';
}

$morning = $_GET['morning'] ?? '';

if ($morning !== 'ash') {
    $morning = '';
}

$returnTo = 'favorites.php';
$showAllTab = true;
$showMorning = true;
$page = 'favorites.php';

$next = catalog_url('favorites.php', [
    'category' => $category,
    'q' => $search,
    'role' => $roleId,
    'open' => $openSlug,
]);
require_login($next);

$user = current_user();
$dbError = '';
$frames = [];
$roles = [];
$morningFrame = null;

try {
    $roles = fetch_roles();
    $frames = fetch_frames([
        'category' => $category,
        'q' => $search,
        'role_id' => $roleId,
        'user_id' => $user['id'],
        'favorites_only' => true,
    ]);

    if ($morning === 'ash') {
        foreach ($frames as $candidate) {
            if ($candidate['slug'] === 'ash') {
                $morningFrame = $candidate;
                break;
            }
        }

        $filtersHideAsh = $morningFrame === null && ($category !== '' || $search !== '' || $roleId > 0);

        if ($filtersHideAsh) {
            $allFavorites = fetch_frames([
                'category' => '',
                'q' => '',
                'role_id' => 0,
                'user_id' => $user['id'],
                'favorites_only' => true,
            ]);

            foreach ($allFavorites as $candidate) {
                if ($candidate['slug'] === 'ash') {
                    $morningFrame = $candidate;
                    break;
                }
            }
        }
    }
} catch (PDOException $exception) {
    $dbError = 'The database is connected, but the catalog tables are missing or out of date. Import sql/schema.sql and then sql/seed.sql in phpMyAdmin.';
    $morningFrame = null;
}

$pageTitle = $morningFrame ? 'Ash Upgrade - Frame Codex' : 'Favorites - Frame Codex';
$nav = 'favorites';
$bodyClass = $morningFrame ? 'is-morning' : '';
$loadCatalogJs = true;

require __DIR__ . '/includes/header.php';
?>

<?php if (!$morningFrame): ?>
<section class="page-intro">
    <h1>Your favorites</h1>
    <p>Cards you saved stay in this list. Open one to read it again, or remove it.</p>
</section>
<?php endif; ?>

<?php if ($dbError !== ''): ?>
    <div class="alert alert-warning"><?= e($dbError) ?></div>
<?php elseif ($morningFrame): ?>
    <?php
    $frame = $morningFrame;
    require __DIR__ . '/includes/morning.php';
    ?>
<?php else: ?>
    <?php require __DIR__ . '/includes/filters.php'; ?>

    <p class="result-count"><?= count($frames) ?> shown</p>

    <?php if (!$frames): ?>
        <div class="empty-state">
            <h2>No favorites yet</h2>
            <p>Open the catalog and choose Save favorite on a card.</p>
            <a class="btn btn-warning" href="index.php">Back to the catalog</a>
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
