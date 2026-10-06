<?php

declare(strict_types=1);

$baseUrl = rtrim(getenv('FORM_TEST_BASE_URL') ?: 'http://localhost/studyplanner', '/');
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] . ';charset=utf8mb4',
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$tag = bin2hex(random_bytes(5));
$email = 'formcheck.' . $tag . '@example.test';
$adminEmail = 'formadmin.' . $tag . '@example.test';
$adminName = 'Form Check Admin ' . $tag;
$adminPassword = 'AdminCheck1!';
$tempCookies = [];
$passed = 0;

function check(bool $condition, string $message): void
{
    global $passed;
    if (!$condition) throw new RuntimeException($message);
    $passed++;
    echo "PASS: {$message}\n";
}

function requestPage(string $session, string $method, string $path, ?array $fields = null): array
{
    global $baseUrl, $tempCookies;
    $cookieFile = $tempCookies[$session] ??= tempnam(sys_get_temp_dir(), 'sp-form-');
    $curl = curl_init($baseUrl . $path);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_TIMEOUT => 10,
    ];
    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($fields ?? []);
    }
    curl_setopt_array($curl, $options);
    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if ($body === false) throw new RuntimeException('HTTP request failed: ' . $error);
    return [$status, $body];
}

function csrf(string $session, string $viewPath): string
{
    [, $body] = requestPage($session, 'GET', $viewPath);
    if (!preg_match('/name="_token" value="([a-f0-9]+)"/', $body, $matches)) {
        throw new RuntimeException('Could not find CSRF token on ' . $viewPath);
    }
    return $matches[1];
}

function submitForm(string $session, string $viewPath, string $actionPath, array $fields): array
{
    $fields['_token'] = csrf($session, $viewPath);
    return requestPage($session, 'POST', $actionPath, $fields);
}

function assertServerError(array $response, string $expected, string $case): void
{
    [$status, $body] = $response;
    check($status === 200 && str_contains($body, 'error-message') && str_contains($body, $expected), $case);
}

$stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "admin")');
$stmt->execute([$adminName, $adminEmail, password_hash($adminPassword, PASSWORD_DEFAULT)]);

try {
    // Registration and login: empty, malformed, policy-invalid, mismatched, and valid values.
    assertServerError(submitForm('signup', '/signup', '/signup', ['name' => '', 'email' => '', 'password' => '', 'password_confirmation' => '']), 'name between 2 and 100', 'empty registration rejected');
    assertServerError(submitForm('signup', '/signup', '/signup', ['name' => ['array'], 'email' => 'not-an-email', 'password' => 'weak', 'password_confirmation' => 'different']), 'name between 2 and 100', 'array registration input rejected');
    assertServerError(submitForm('signup', '/signup', '/signup', ['name' => 'Valid Person', 'email' => 'not-an-email', 'password' => 'WeakPass1!', 'password_confirmation' => 'WeakPass1!']), 'valid email', 'invalid registration email rejected');
    assertServerError(submitForm('signup', '/signup', '/signup', ['name' => 'Valid Person', 'email' => $email, 'password' => 'weak', 'password_confirmation' => 'weak']), '8-72 characters', 'weak registration password rejected');
    assertServerError(submitForm('signup', '/signup', '/signup', ['name' => 'Valid Person', 'email' => $email, 'password' => 'GoodPass1!', 'password_confirmation' => 'OtherPass2!']), 'passwords do not match', 'registration password mismatch rejected');
    [$status] = submitForm('signup', '/signup', '/signup', ['name' => 'Form Check User', 'email' => $email, 'password' => 'GoodPass1!', 'password_confirmation' => 'GoodPass1!']);
    check($status === 200, 'valid registration accepted');

    assertServerError(submitForm('bad-login', '/login', '/login', ['email' => '', 'password' => '']), 'valid email', 'empty sign-in rejected');
    assertServerError(submitForm('bad-login', '/login', '/login', ['email' => $email, 'password' => 'wrong']), 'not recognized', 'incorrect sign-in credentials rejected');
    [$status] = submitForm('good-login', '/login', '/login', ['email' => $email, 'password' => 'GoodPass1!']);
    check($status === 200, 'valid sign-in accepted');

    // Subject create/edit: required name, allow-listed color, XSS-like text sanitization.
    assertServerError(submitForm('good-login', '/subjects/create', '/subjects', ['name' => '', 'description' => '', 'color' => '']), 'subject name between 2 and 100', 'empty subject rejected');
    assertServerError(submitForm('good-login', '/subjects/create', '/subjects', ['name' => 'Biology', 'description' => '', 'color' => '']), 'available subject colors', 'empty subject color rejected');
    assertServerError(submitForm('good-login', '/subjects/create', '/subjects', ['name' => 'Biology', 'description' => '', 'color' => '<script>']), 'available subject colors', 'unexpected subject color rejected');
    [$status] = submitForm('good-login', '/subjects/create', '/subjects', ['name' => '<b>Biology</b>', 'description' => '<script>alert(1)</script>Notes', 'color' => '#26745c']);
    check($status === 200, 'valid sanitized subject accepted');
    $userId = (int) $pdo->query("SELECT id FROM users WHERE email = " . $pdo->quote($email))->fetchColumn();
    $subjectId = (int) $pdo->query('SELECT id FROM subjects WHERE user_id = ' . $userId . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    check($subjectId > 0, 'subject saved with sanitized text');
    $savedSubject = $pdo->query('SELECT name, description FROM subjects WHERE id = ' . $subjectId)->fetch(PDO::FETCH_ASSOC);
    check($savedSubject['name'] === 'Biology' && $savedSubject['description'] === 'alert(1)Notes', 'HTML tags removed from saved subject text');
    assertServerError(submitForm('good-login', '/subjects/' . $subjectId . '/edit', '/subjects/' . $subjectId . '/update', ['name' => ' ', 'description' => '', 'color' => '#26745c']), 'subject name between 2 and 100', 'empty subject edit rejected');
    [$status] = submitForm('good-login', '/subjects/' . $subjectId . '/edit', '/subjects/' . $subjectId . '/update', ['name' => 'Biology II', 'description' => 'edited', 'color' => '#d47458']);
    check($status === 200, 'valid subject edit accepted');

    // Task create/edit/status/filter.
    assertServerError(submitForm('good-login', '/tasks/create', '/tasks', ['title' => '', 'description' => '', 'subject_id' => '', 'due_date' => '', 'priority' => '', 'status' => '']), 'task name between 2 and 180', 'empty task rejected');
    assertServerError(submitForm('good-login', '/tasks/create', '/tasks', ['title' => ['array'], 'description' => '', 'subject_id' => '', 'due_date' => '', 'priority' => 'unexpected', 'status' => 'pending']), 'task name between 2 and 180', 'array task input rejected');
    assertServerError(submitForm('good-login', '/tasks/create', '/tasks', ['title' => 'Task', 'description' => '', 'subject_id' => '', 'due_date' => '2026-02-31', 'priority' => 'high', 'status' => 'pending']), 'valid deadline', 'invalid task date rejected');
    [$status] = submitForm('good-login', '/tasks/create', '/tasks', ['title' => 'Review unit', 'description' => 'Study', 'subject_id' => (string) $subjectId, 'due_date' => '2099-02-28', 'priority' => 'high', 'status' => 'pending']);
    check($status === 200, 'valid task accepted');
    $taskId = (int) $pdo->query('SELECT id FROM tasks WHERE user_id = ' . $userId . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    assertServerError(submitForm('good-login', '/tasks/' . $taskId . '/edit', '/tasks/' . $taskId . '/update', ['title' => 'X', 'description' => '', 'subject_id' => '', 'due_date' => '', 'priority' => 'low', 'status' => 'completed']), 'task name between 2 and 180', 'short task edit rejected');
    assertServerError(submitForm('good-login', '/tasks/' . $taskId . '/edit', '/tasks/' . $taskId . '/update', ['title' => 'Review unit', 'description' => '', 'subject_id' => '', 'due_date' => '', 'priority' => 'unexpected', 'status' => 'pending']), 'valid task priority', 'unexpected task priority rejected');
    [$status] = submitForm('good-login', '/tasks/' . $taskId . '/edit', '/tasks/' . $taskId . '/update', ['title' => 'Review final unit', 'description' => '', 'subject_id' => '', 'due_date' => '', 'priority' => 'medium', 'status' => 'completed']);
    check($status === 200, 'valid task edit accepted');
    assertServerError(submitForm('good-login', '/tasks', '/tasks/' . $taskId . '/status', ['status' => 'unexpected']), 'valid task status', 'unexpected task status rejected');
    [$status] = submitForm('good-login', '/tasks', '/tasks/' . $taskId . '/status', ['status' => 'pending']);
    check($status === 200, 'valid task status accepted');
    assertServerError(submitForm('good-login', '/tasks', '/tasks/' . $taskId . '/status', ['status' => '']), 'valid task status', 'empty task status rejected');
    [$status] = requestPage('good-login', 'GET', '/tasks?status%5B%5D=invalid&subject_id%5B%5D=x');
    check($status === 200, 'unexpected filter arrays safely ignored');

    // Exam create/edit/status.
    assertServerError(submitForm('good-login', '/exams/create', '/exams', ['title' => '', 'subject_id' => '', 'exam_at' => '', 'status' => '']), 'exam name between 2 and 120', 'empty exam rejected');
    assertServerError(submitForm('good-login', '/exams/create', '/exams', ['title' => ['array'], 'subject_id' => (string) $subjectId, 'exam_at' => '2099-01-01T10:00', 'status' => 'scheduled']), 'exam name between 2 and 120', 'array exam input rejected');
    assertServerError(submitForm('good-login', '/exams/create', '/exams', ['title' => 'Final', 'subject_id' => (string) $subjectId, 'exam_at' => '2099-02-31T10:00', 'status' => 'scheduled']), 'valid exam date', 'invalid exam date rejected');
    [$status] = submitForm('good-login', '/exams/create', '/exams', ['title' => 'Final exam', 'subject_id' => (string) $subjectId, 'exam_at' => '2099-02-28T10:00', 'status' => 'scheduled']);
    check($status === 200, 'valid exam accepted');
    $examId = (int) $pdo->query('SELECT id FROM exams WHERE user_id = ' . $userId . ' ORDER BY id DESC LIMIT 1')->fetchColumn();
    assertServerError(submitForm('good-login', '/exams/' . $examId . '/edit', '/exams/' . $examId . '/update', ['title' => '', 'subject_id' => (string) $subjectId, 'exam_at' => '2099-02-28T10:00', 'status' => 'scheduled']), 'exam name between 2 and 120', 'empty exam edit rejected');
    [$status] = submitForm('good-login', '/exams/' . $examId . '/edit', '/exams/' . $examId . '/update', ['title' => 'Updated final', 'subject_id' => (string) $subjectId, 'exam_at' => '2099-02-28T10:00', 'status' => 'completed']);
    check($status === 200, 'valid exam edit accepted');
    assertServerError(submitForm('good-login', '/exams/' . $examId . '/edit', '/exams/' . $examId . '/update', ['title' => 'Updated final', 'subject_id' => '999999999', 'exam_at' => '2099-02-28T10:00', 'status' => 'completed']), 'one of your subjects', 'unexpected exam subject rejected');
    assertServerError(submitForm('good-login', '/exams', '/exams/' . $examId . '/status', ['status' => 'unexpected']), 'valid exam status', 'unexpected exam status rejected');
    [$status] = submitForm('good-login', '/exams', '/exams/' . $examId . '/status', ['status' => 'missed']);
    check($status === 200, 'valid exam status accepted');
    assertServerError(submitForm('good-login', '/exams', '/exams/' . $examId . '/status', ['status' => '']), 'valid exam status', 'empty exam status rejected');

    // Admin account form: empty, malformed, duplicate, and valid values.
    [$status] = submitForm('admin', '/login', '/login', ['email' => $adminEmail, 'password' => $adminPassword]);
    check($status === 200, 'valid admin sign-in accepted');
    [$status, $body] = submitForm('admin', '/admin/manage-users', '/admin/users', ['name' => '', 'email' => '', 'password' => '', 'password_confirmation' => '']);
    check($status === 200 && str_contains($body, 'name between 2 and 100'), 'empty admin user rejected');
    [$status, $body] = submitForm('admin', '/admin/manage-users', '/admin/users', ['name' => ['array'], 'email' => 'bad', 'password' => 'Bad', 'password_confirmation' => 'Bad']);
    check($status === 200 && str_contains($body, 'name between 2 and 100'), 'array admin user input rejected');
    [$status, $body] = submitForm('admin', '/admin/manage-users', '/admin/users', ['name' => 'Other User', 'email' => $email, 'password' => 'ValidPass1!', 'password_confirmation' => 'ValidPass1!']);
    check($status === 200 && str_contains($body, 'already exists'), 'duplicate admin email rejected');
    [$status] = submitForm('admin', '/admin/manage-users', '/admin/users', ['name' => 'Admin Created', 'email' => 'created.' . $tag . '@example.test', 'password' => 'ValidPass1!', 'password_confirmation' => 'ValidPass1!']);
    check($status === 200, 'valid admin user accepted');

    $childId = (int) $pdo->query("SELECT id FROM users WHERE email = " . $pdo->quote('created.' . $tag . '@example.test'))->fetchColumn();
    [$status] = submitForm('admin', '/admin/manage-users', '/admin/users/' . $childId . '/delete', []);
    check($status === 200, 'admin delete form accepted for a removable user');

    [$status] = submitForm('good-login', '/tasks', '/tasks/' . $taskId . '/delete', []);
    check($status === 200, 'task delete form accepted');
    [$status] = submitForm('good-login', '/exams', '/exams/' . $examId . '/delete', []);
    check($status === 200, 'exam delete form accepted');
    [$status] = submitForm('good-login', '/subjects/' . $subjectId, '/subjects/' . $subjectId . '/delete', []);
    check($status === 200, 'subject delete form accepted');

    [$status] = submitForm('good-login', '/', '/logout', []);
    check($status === 200, 'valid logout accepted');
} finally {
    $cleanup = $pdo->prepare('DELETE FROM activity_logs WHERE actor_name IN (?, ?)');
    $cleanup->execute(['Form Check User', $adminName]);
    $cleanup = $pdo->prepare('DELETE FROM users WHERE email LIKE ? OR email = ?');
    $cleanup->execute(['%.' . $tag . '@example.test', $adminEmail]);
    foreach ($tempCookies as $cookieFile) {
        if (is_file($cookieFile)) unlink($cookieFile);
    }
}

echo "Completed {$passed} form validation checks. Test records cleaned up.\n";
