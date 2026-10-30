<?php

// Small helpers used by the pages.

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_is_valid(?string $token): bool
{
    if (!is_string($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

// Only allow a return link to this site's own pages.
function safe_next(?string $next): string
{
    $next = $next ?? '';

    if (preg_match('/^(index|favorites|squad)\.php(\?[A-Za-z0-9_\-=&%.+]*)?$/', $next) === 1) {
        return $next;
    }

    return 'index.php';
}

function catalog_url(string $page, array $params): string
{
    $clean = [];

    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            continue;
        }

        $clean[$key] = $value;
    }

    $query = http_build_query($clean);

    if ($query === '') {
        return $page;
    }

    return $page . '?' . $query;
}

function portrait_for_slug(string $slug): ?string
{
    if (preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
        return null;
    }

    $folder = dirname(__DIR__) . '/images/';

    foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
        if (is_file($folder . $slug . '.' . $extension)) {
            return 'images/' . $slug . '.' . $extension;
        }
    }

    return null;
}

function category_label(string $category): string
{
    if ($category === 'prime') {
        return 'Prime';
    }

    if ($category === 'protoframe') {
        return 'Protoframe';
    }

    return 'Warframe';
}
