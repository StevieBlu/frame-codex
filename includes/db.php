<?php

// Opens one database connection and reuses it for the rest of the page.

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $configPath = dirname(__DIR__) . '/config/db.local.php';

    if (!is_file($configPath)) {
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Database connection</title></head><body>';
        echo '<h1>Database config is missing</h1>';
        echo '<p>Copy <code>config/db.local.example.php</code> to <code>config/db.local.php</code> and set the host, port, database name, user, and password for this machine.</p>';
        echo '</body></html>';
        exit;
    }

    $config = require $configPath;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['db_host'],
        $config['db_port'],
        $config['db_name']
    );

    try {
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        error_log('DB connection failed: ' . $exception->getMessage());
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Database connection</title></head><body>';
        echo '<h1>Could not connect to the database</h1>';
        echo '<p>Start MySQL, create a database named <strong>frame_codex</strong>, then import <code>sql/schema.sql</code> and <code>sql/seed.sql</code>.</p>';
        echo '<p>If the password or port is different on your computer, edit <code>config/db.local.php</code>.</p>';
        echo '</body></html>';
        exit;
    }

    return $pdo;
}
