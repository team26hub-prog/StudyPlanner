<?php

declare(strict_types=1);

function sanitize_text_value(?string $value): string
{
    if ($value === null) {
        return '';
    }

    if (preg_match('//u', $value) !== 1) {
        $value = function_exists('iconv') ? (iconv('UTF-8', 'UTF-8//IGNORE', $value) ?: '') : '';
    }

    $value = trim($value);
    $value = preg_replace('/[\x00-\x1F\x7F]+/u', '', $value) ?? $value;
    $value = strip_tags($value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = strip_tags($value);

    return trim($value);
}

function text_length(string $value): int
{
    if (preg_match_all('/./us', $value, $matches) !== false) {
        return count($matches[0]);
    }

    return strlen($value);
}

function is_valid_person_name(string $value): bool
{
    $value = trim($value, ' ');

    return preg_match('/\A\p{L}+(?: +\p{L}+)*\z/u', $value) === 1;
}

function sanitize_email_value(?string $value): string
{
    return strtolower(trim((string) filter_var($value ?? '', FILTER_SANITIZE_EMAIL)));
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    return $basePath . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);

    return $value;
}

function input_string(array $source, string $key, string $default = ''): string
{
    if (!array_key_exists($key, $source)) {
        return $default;
    }

    $value = $source[$key];
    if (is_string($value)) {
        return trim($value);
    }

    if (is_scalar($value)) {
        return trim((string) $value);
    }

    // Force malformed array/object input to fail ordinary field length/enum rules.
    return str_repeat('x', 4096);
}

function input_password(array $source, string $key, string $default = ''): string
{
    if (!array_key_exists($key, $source)) {
        return $default;
    }

    $value = $source[$key];

    if (is_string($value)) {
        return $value;
    }

    if (is_scalar($value)) {
        return (string) $value;
    }

    return str_repeat('x', 1025);
}

function input_email(array $source, string $key): string
{
    $value = $source[$key] ?? null;

    return is_string($value) ? strtolower(trim($value)) : '';
}

function password_meets_policy(string $password): bool
{
    return strlen($password) >= 8
        && strlen($password) <= 72
        && preg_match('/[\x00-\x1F\x7F]/', $password) !== 1
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1
        && preg_match('/[^A-Za-z0-9]/', $password) === 1;
}
