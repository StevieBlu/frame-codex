<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/squads.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    header('Location: squad.php');
    exit;
}

$user = current_user();

if ($user === null) {
    header('Location: login.php?next=' . urlencode('squad.php'));
    exit;
}

delete_squad($user['id'], (int) ($_POST['squad_id'] ?? 0));

header('Location: squad.php');
exit;
