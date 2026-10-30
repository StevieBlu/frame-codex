<?php

$pageTitle = $pageTitle ?? 'Frame Codex';
$nav = $nav ?? '';
$loadCatalogJs = $loadCatalogJs ?? false;
$bodyClass = $bodyClass ?? '';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
    <header class="site-header">
        <nav class="navbar navbar-expand-md">
            <div class="container">
                <a class="navbar-brand" href="index.php">Frame Codex</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#site-nav" aria-controls="site-nav" aria-expanded="false" aria-label="Open menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="site-nav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link<?= $nav === 'catalog' ? ' active' : '' ?>" href="index.php">Catalog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= $nav === 'squad' ? ' active' : '' ?>" href="squad.php">Squad</a>
                        </li>
                        <?php if ($user): ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $nav === 'favorites' ? ' active' : '' ?>" href="favorites.php">Favorites</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav">
                        <?php if ($user): ?>
                            <li class="nav-item">
                                <span class="navbar-text me-md-3">Hello, <?= e($user['username']) ?></span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">Log out</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $nav === 'login' ? ' active' : '' ?>" href="login.php">Log in</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?= $nav === 'register' ? ' active' : '' ?>" href="register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container py-4">
