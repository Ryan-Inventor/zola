<?php

/**
 * INF-02/INF-04 — Crée la base MySQL zola si elle n'existe pas.
 * Lit les variables DB_* depuis zola-api/.env
 */

$root = dirname(__DIR__);
$envFile = $root . '/zola-api/.env';

if (! is_file($envFile)) {
    fwrite(STDERR, "Fichier .env introuvable : {$envFile}\n");
    exit(1);
}

$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
        continue;
    }
    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value, " \t\"'");
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$database = $env['DB_DATABASE'] ?? 'zola';

$dsn = "mysql:host={$host};port={$port};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec(
        "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );
    echo "Base '{$database}' prête.\n";
} catch (PDOException $e) {
    fwrite(STDERR, 'Erreur MySQL : ' . $e->getMessage() . "\n");
    exit(1);
}
