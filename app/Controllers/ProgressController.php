<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Task;

final class ProgressController extends Controller
{
    public function index(): void
    {
        $user = $this->requireRole('user');
        $tasks = (new Task())->allForUser($user);
        $completed = array_values(array_filter($tasks, fn (array $task): bool => $task['status'] === 'completed'));
        $pending = array_values(array_filter($tasks, fn (array $task): bool => $task['status'] !== 'completed'));
        $this->render('progress/index', [
            'total' => count($tasks),
            'completed' => $completed,
            'pending' => $pending,
            'progress' => $tasks === [] ? 0 : (int) round(count($completed) / count($tasks) * 100),
        ]);
    }
}