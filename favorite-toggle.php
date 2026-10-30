<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$returnTo = $_POST['return_to'] ?? 'index.php';

if (!in_array($returnTo, ['index.php', 'favorites.php'], true)) {
    $returnTo = 'index.php';
}

$category = $_POST['category'] ?? '';
$search = trim($_POST['q'] ?? '');
$roleId = (int) ($_POST['role'] ?? 0);
$openSlug = $_POST['open'] ?? '';

if (preg_match('/^[a-z0-9-]+$/', $openSlug) !== 1) {
    $openSlug = '';
}

$next = catalog_url($returnTo, [
    'category' => $category,
    'q' => $search,
    'role' => $roleId,
    'open' => $openSlug,
]);

if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    header('Location: ' . $next);
    exit;
}

$user = current_user();

if ($user === null) {
    header('Location: login.php?next=' . urlencode($next));
    exit;
}

$warframeId = (int) ($_POST['warframe_id'] ?? 0);
$pdo = db();

$frameCheck = $pdo->prepare('SELECT id FROM warframes WHERE id = ?');
$frameCheck->execute([$warframeId]);

if (!$frameCheck->fetch()) {
    header('Location: ' . $next);
    exit;
}

$existing = $pdo->prepare('SELECT user_id FROM favorites WHERE user_id = ? AND warframe_id = ?');
$existing->execute([$user['id'], $warframeId]);

if ($existing->fetch()) {
    $delete = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND warframe_id = ?');
    $delete->execute([$user['id'], $warframeId]);
} else {
    $insert = $pdo->prepare('INSERT INTO favorites (user_id, warframe_id) VALUES (?, ?)');
    $insert->execute([$user['id'], $warframeId]);
}

header('Location: ' . $next);
exit;
