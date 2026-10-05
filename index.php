<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/Core/Database.php';
require __DIR__ . '/app/Core/Router.php';
require __DIR__ . '/app/Core/Controller.php';
require __DIR__ . '/app/Models/ActivityLog.php';
require __DIR__ . '/app/Models/User.php';
require __DIR__ . '/app/Models/Task.php';
require __DIR__ . '/app/Models/Subject.php';
require __DIR__ . '/app/Models/Exam.php';
require __DIR__ . '/app/Controllers/AuthController.php';
require __DIR__ . '/app/Controllers/AdminController.php';
require __DIR__ . '/app/Controllers/HomeController.php';
require __DIR__ . '/app/Controllers/TaskController.php';
require __DIR__ . '/app/Controllers/ExamController.php';
require __DIR__ . '/app/Controllers/SubjectController.php';
require __DIR__ . '/app/Controllers/ProgressController.php';

$router = new App\Core\Router();
require __DIR__ . '/routes/web.php';

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
    $basePath
);