<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$config = require __DIR__ . '/config/database.php';
$database = (string) $config['database'];

if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $database)) {
    fwrite(STDERR, "DB_DATABASE may contain only letters, numbers, and underscores.\n");
    exit(1);
}

$schemaPath = __DIR__ . '/db/schema.sql';
$schema = file_get_contents($schemaPath);
if ($schema === false) {
    fwrite(STDERR, "Could not read db/schema.sql.\n");
    exit(1);
}

$schema = str_replace(
    ['CREATE DATABASE IF NOT EXISTS studyplanner', 'USE studyplanner;'],
    ['CREATE DATABASE IF NOT EXISTS `' . $database . '`', 'USE `' . $database . '`;'],
    $schema
);

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;charset=utf8mb4',
        $config['host'],
        $config['port']
    );
    $connection = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
    $connection->exec($schema);
    fwrite(STDOUT, "StudyPlanner database setup completed for '{$database}'.\n");
} catch (Throwable $error) {
    fwrite(STDERR, "Database setup failed: {$error->getMessage()}\n");
    exit(1);
}