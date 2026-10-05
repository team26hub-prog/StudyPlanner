<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Subject;
use App\Models\Task;

final class HomeController extends Controller
{
    public function index(): void
    {
        $user = $this->requireRole('user');
        $tasks = new Task();
        $this->render('dashboard/index', [
            'counts' => $tasks->countsForUser($user),
            'subjects' => (new Subject())->allForUser($user),
            'upcomingTasks' => array_slice($tasks->allForUser($user), 0, 5),
        ]);
    }
}