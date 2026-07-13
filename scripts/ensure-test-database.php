<?php

/**
 * INF-05/AUTH-01 — Crée la base MySQL zola_test pour la suite Pest.
 * Lit les variables DB_* depuis zola-api/.env
 */

$root = dirname(__DIR__);
$envFile = $root . '/zola-api/.env';

$env = [];
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value, " \t\"'");
    }
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec(
        'CREATE DATABASE IF NOT EXISTS `zola_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    echo "Base 'zola_test' prête.\n";
} catch (PDOException $e) {
    fwrite(STDERR, 'Erreur MySQL : ' . $e->getMessage() . "\n");
    exit(1);
}
