<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/Core/Database.php';
require dirname(__DIR__) . '/app/helpers.php';

$name = trim(getenv('ADMIN_NAME') ?: '');
$email = trim(getenv('ADMIN_EMAIL') ?: '');
$password = getenv('ADMIN_PASSWORD') ?: '';

if (strlen($name) < 2 || strlen($name) > 100 || strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12 || !password_meets_policy($password)) {
    fwrite(STDERR, "Set ADMIN_NAME, a valid ADMIN_EMAIL, and an ADMIN_PASSWORD with 12-72 characters, uppercase and lowercase letters, a number, and a symbol.\n");
    exit(1);
}

$statement = App\Core\Database::connection()->prepare(
    'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, "admin")'
);
$statement->execute([
    'name' => $name,
    'email' => strtolower($email),
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
]);

fwrite(STDOUT, "Admin account created for {$email}.\n");