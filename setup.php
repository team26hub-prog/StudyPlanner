<?php

declare(strict_types=1);

const SETUP_TOKEN = 'b3def3776841dc4f840cc2445a77667db0e23bdadd9425fa';

$lockFile   = __DIR__ . '/storage/installed.lock';
$schemaPath = __DIR__ . '/db/schema.sql';
$configPath = __DIR__ . '/config/database.php';

function not_found(): never
{
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not found.';
    exit;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function render(string $title, string $body): never
{
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="robots" content="noindex,nofollow">'
        . '<title>' . h($title) . '</title>'
        . '<style>body{font-family:system-ui,sans-serif;max-width:600px;margin:3rem auto;padding:0 1rem;color:#222}'
        . 'table{border-collapse:collapse;width:100%;margin:1rem 0}td{border:1px solid #ddd;padding:.5rem}'
        . 'button{padding:.6rem 1.2rem;font-size:1rem;cursor:pointer}button[disabled]{cursor:not-allowed;opacity:.5}'
        . '.ok{color:#0a6b2d}.err{color:#b00020}</style></head><body>'
        . '<h1>' . h($title) . '</h1>' . $body . '</body></html>';
    exit;
}

function split_sql(string $sql): array
{
    $statements = [];
    $buffer = '';
    $quote = null;
    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $c = $sql[$i];
        $next = $sql[$i + 1] ?? '';

        if ($quote !== null) {
            $buffer .= $c;
            if ($c === '\\' && $quote !== '`') {
                $buffer .= $next;
                $i++;
            } elseif ($c === $quote) {
                if ($next === $quote) {
                    $buffer .= $next;
                    $i++;
                } else {
                    $quote = null;
                }
            }
            continue;
        }

        if ($c === "'" || $c === '"' || $c === '`') {
            $quote = $c;
            $buffer .= $c;
            continue;
        }

        $isDashComment = $c === '-' && $next === '-'
            && in_array($sql[$i + 2] ?? "\n", [' ', "\t", "\n", "\r"], true);
        if ($c === '#' || $isDashComment) {
            $end = strpos($sql, "\n", $i);
            if ($end === false) {
                break;
            }
            $i = $end;
            continue;
        }

        if ($c === '/' && $next === '*' && ($sql[$i + 2] ?? '') !== '!') {
            $end = strpos($sql, '*/', $i + 2);
            if ($end === false) {
                break;
            }
            $i = $end + 1;
            continue;
        }

        if ($c === ';') {
            $statement = trim($buffer);
            if ($statement !== '') {
                $statements[] = $statement;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $c;
    }

    $statement = trim($buffer);
    if ($statement !== '') {
        $statements[] = $statement;
    }

    return $statements;
}

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

if (is_file($lockFile)) {
    not_found();
}

$expected = getenv('SETUP_TOKEN') ?: SETUP_TOKEN;
if ($expected === '' || $expected === 'CHANGE_ME_TO_A_LONG_RANDOM_STRING' || strlen($expected) < 24) {
    not_found();
}

$supplied = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
if (!hash_equals($expected, $supplied)) {
    not_found();
}

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'cookie_secure'   => !empty($_SERVER['HTTPS']),
]);

$storageDir = __DIR__ . '/storage';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0750, true);
}

$checks = [
    'PHP 8.1 or newer (found ' . PHP_VERSION . ')'  => version_compare(PHP_VERSION, '8.1.0', '>='),
    'PDO MySQL extension (pdo_mysql)'               => extension_loaded('pdo_mysql'),
    'config/database.php readable'                  => is_readable($configPath),
    'db/schema.sql readable'                        => is_readable($schemaPath),
    'storage/ directory writable'                   => is_dir($storageDir) && is_writable($storageDir),
];
$checksPass = !in_array(false, $checks, true);

if (!$checksPass) {
    $rows = '';
    foreach ($checks as $label => $passed) {
        $rows .= '<tr><td>' . h($label) . '</td><td class="' . ($passed ? 'ok' : 'err') . '">'
            . ($passed ? 'OK' : 'FAILED') . '</td></tr>';
    }
    render('Setup cannot run', '<table>' . $rows . '</table><p class="err">Fix the failed items and reload.</p>');
}

$config = require $configPath;
foreach (['host', 'port', 'database', 'username', 'password'] as $key) {
    if (!is_array($config) || !array_key_exists($key, $config)) {
        render('Setup error', '<p class="err">config/database.php must return host, port, database, username and password.</p>');
    }
}
$database = (string) $config['database'];

if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $database)) {
    render('Setup error', '<p class="err">The database name may contain only letters, numbers, and underscores.</p>');
}

$schema = (string) file_get_contents($schemaPath);
if (preg_match('/^\s*DELIMITER\s/im', $schema)) {
    render('Setup error', '<p class="err">db/schema.sql uses DELIMITER (triggers/procedures). Import it with phpMyAdmin instead.</p>');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));

    $body = '<p>This will create the StudyPlanner tables using your existing database settings.</p>'
        . '<table>'
        . '<tr><td>Host</td><td>' . h((string) $config['host']) . '</td></tr>'
        . '<tr><td>Port</td><td>' . h((string) $config['port']) . '</td></tr>'
        . '<tr><td>Database</td><td>' . h($database) . '</td></tr>'
        . '<tr><td>User</td><td>' . h((string) $config['username']) . '</td></tr>'
        . '</table>'
        . '<form method="post">'
        . '<input type="hidden" name="token" value="' . h($supplied) . '">'
        . '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">'
        . '<button type="submit">Run installation</button></form>';
    render('StudyPlanner Setup', $body);
}

if (!hash_equals((string) ($_SESSION['csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''))) {
    http_response_code(400);
    render('Setup error', '<p class="err">Invalid or expired form. Reload the page and try again.</p>');
}

$statements = [];
foreach (split_sql($schema) as $statement) {
    if (preg_match('/\A(CREATE\s+DATABASE|USE)\b/i', $statement)) {
        continue;
    }
    if (preg_match('/\ACREATE\s+TABLE\b/i', $statement)) {
        $statement = preg_replace('/\s+AUTO_INCREMENT\s*=\s*\d+/i', '', $statement);
    }
    $statements[] = $statement;
}

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $database
    );
    $pdo = new PDO($dsn, (string) $config['username'], (string) $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $before = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    try {
        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    $after    = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $created  = array_values(array_diff($after, $before));
    $existing = array_values(array_intersect($after, $before));
} catch (Throwable $error) {
    error_log('StudyPlanner setup failed: ' . $error->getMessage());
    http_response_code(500);
    render(
        'Setup failed',
        '<p class="err">Database setup failed: ' . h($error->getMessage()) . '</p>'
        . '<p>Nothing has been locked. Fix the problem and reload this page to retry.</p>'
    );
}

@file_put_contents($lockFile, 'installed ' . gmdate('c') . "\n");
unset($_SESSION['csrf']);

$deleted = @unlink(__FILE__);

render(
    'Setup complete',
    '<p class="ok">StudyPlanner database setup completed for <strong>' . h($database) . '</strong>.</p>'
    . '<p>Created: ' . ($created ? h(implode(', ', $created)) : 'none') . '<br>'
    . 'Already existed (left untouched): ' . ($existing ? h(implode(', ', $existing)) : 'none') . '</p>'
    . ($deleted
        ? '<p>The installer deleted itself.</p>'
        : '<p class="err">Could not delete setup.php automatically. Delete it now via cPanel File Manager.</p>')
);