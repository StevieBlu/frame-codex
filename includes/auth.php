<?php

// Remembers who is logged in. Every page includes this file first.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['user_id'],
        'username' => (string) ($_SESSION['username'] ?? ''),
    ];
}

function require_login(string $next): void
{
    if (current_user() !== null) {
        return;
    }

    header('Location: login.php?next=' . urlencode($next));
    exit;
}

function log_in_user(int $id, string $username): void
{
    // Gives the user a new session id so an old id cannot be reused.
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    $_SESSION['username'] = $username;
}
