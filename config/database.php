<?php

declare(strict_types=1);

$env = [];
$envFile = dirname(__DIR__) . '/.env';

if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && substr($value, -1) === $value[0]) {
            $value = substr($value, 1, -1);
        }

        $env[$key] = $value;
    }
}

$read = static function (string $key, string $default) use ($env): string {
    $system = getenv($key);

    if ($system !== false && $system !== '') {
        return $system;
    }

    return $env[$key] ?? $default;
};

return [
    'host' => $read('DB_HOST', '127.0.0.1'),
    'port' => $read('DB_PORT', '3306'),
    'database' => $read('DB_DATABASE', 'studyplanner'),
    'username' => $read('DB_USERNAME', 'root'),
    'password' => $read('DB_PASSWORD', ''),
];