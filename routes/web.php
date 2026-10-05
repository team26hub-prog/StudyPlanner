<?php

use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\ExamController;
use App\Controllers\HomeController;
use App\Controllers\ProgressController;
use App\Controllers\SubjectController;
use App\Controllers\TaskController;

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/signup', [AuthController::class, 'registerForm']);
$router->post('/signup', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/manage-users', [AdminController::class, 'manageUsers']);
$router->post('/admin/users', [AdminController::class, 'storeUser']);
$router->post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser']);
$router->get('/admin/activity', [AdminController::class, 'activity']);
$router->get('/admin/users/{id}', [AdminController::class, 'showUser']);

$router->get('/', [HomeController::class, 'index']);
$router->get('/subjects', [SubjectController::class, 'index']);
$router->get('/subjects/create', [SubjectController::class, 'createForm']);
$router->post('/subjects', [SubjectController::class, 'store']);
$router->get('/subjects/{id}', [SubjectController::class, 'show']);
$router->get('/subjects/{id}/edit', [SubjectController::class, 'editForm']);
$router->post('/subjects/{id}/update', [SubjectController::class, 'update']);
$router->post('/subjects/{id}/delete', [SubjectController::class, 'delete']);

$router->get('/tasks', [TaskController::class, 'index']);
$router->get('/tasks/create', [TaskController::class, 'createForm']);
$router->post('/tasks', [TaskController::class, 'store']);
$router->get('/tasks/{id}/edit', [TaskController::class, 'editForm']);
$router->post('/tasks/{id}/update', [TaskController::class, 'update']);
$router->post('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
$router->post('/tasks/{id}/delete', [TaskController::class, 'delete']);

$router->get('/exams', [ExamController::class, 'index']);
$router->get('/exams/create', [ExamController::class, 'createForm']);
$router->post('/exams', [ExamController::class, 'store']);
$router->get('/exams/{id}/edit', [ExamController::class, 'editForm']);
$router->post('/exams/{id}/update', [ExamController::class, 'update']);
$router->post('/exams/{id}/status', [ExamController::class, 'updateStatus']);
$router->post('/exams/{id}/delete', [ExamController::class, 'delete']);

$router->get('/progress', [ProgressController::class, 'index']);