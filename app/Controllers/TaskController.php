<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\Subject;
use App\Models\Task;

final class TaskController extends Controller
{
    public function index(): void
    {
        $user = $this->requireRole('user');
        $status = sanitize_text_value(input_string($_GET, 'status'));
        $subjectFilter = sanitize_text_value(input_string($_GET, 'subject_id'));
        $filters = [
            'status' => in_array($status, ['pending', 'in_progress', 'completed'], true) ? $status : '',
            'subject_id' => ctype_digit($subjectFilter) ? (int) $subjectFilter : null,
        ];
        $this->render('tasks/index', [
            'tasks' => (new Task())->allForUser($user, $filters),
            'subjects' => (new Subject())->allForUser($user),
            'filters' => $filters,
        ]);
    }

    public function createForm(): void
    {
        $user = $this->requireRole('user');
        $this->render('tasks/form', [
            'task' => null,
            'subjects' => (new Subject())->allForUser($user),
            'formAction' => url('/tasks'),
            'formTitle' => 'Add a study task',
        ]);
    }

    public function store(): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $attributes = $this->validatedAttributes($user);
        if ($attributes === null) {
            redirect('/tasks/create');
        }
        $attributes['user_id'] = $user['id'];
        (new Task())->create($attributes);
        (new ActivityLog())->record($user, 'task.created', "Created task: {$attributes['title']}.");
        flash('notice', 'Task added to your plan.');
        redirect('/tasks');
    }

    public function editForm(string $id): void
    {
        $user = $this->requireRole('user');
        $task = (new Task())->findForUser((int) $id, $user);
        if ($task === null) {
            $this->notFound();
        }
        $this->render('tasks/form', [
            'task' => $task,
            'subjects' => (new Subject())->allForUser($user),
            'formAction' => url('/tasks/' . $id . '/update'),
            'formTitle' => 'Edit study task',
        ]);
    }

    public function update(string $id): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $tasks = new Task();
        if ($tasks->findForUser((int) $id, $user) === null) {
            $this->notFound();
        }
        $attributes = $this->validatedAttributes($user);
        if ($attributes === null) {
            redirect('/tasks/' . $id . '/edit');
        }
        $tasks->updateForUser((int) $id, $user, $attributes);
        (new ActivityLog())->record($user, 'task.updated', "Updated task: {$attributes['title']}.");
        flash('notice', 'Task details updated.');
        redirect('/tasks');
    }

    public function updateStatus(string $id): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $tasks = new Task();
        $task = $tasks->findForUser((int) $id, $user);
        if ($task === null) {
            $this->notFound();
        }
        $status = sanitize_text_value(input_string($_POST, 'status'));
        if (!in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            flash('error', 'Choose a valid task status.');
            redirect('/tasks');
        }
        $tasks->updateStatusForUser((int) $id, $user, $status);
        (new ActivityLog())->record($user, 'task.status_changed', "Changed task status: {$task['title']} to " . str_replace('_', ' ', $status) . '.');
        flash('notice', 'Task status updated.');
        redirect('/tasks');
    }

    public function delete(string $id): void
    {
        $user = $this->requireRole('user');
        $this->requireCsrf();
        $tasks = new Task();
        $task = $tasks->findForUser((int) $id, $user);
        if ($task === null) {
            $this->notFound();
        }
        $tasks->deleteForUser((int) $id, $user);
        (new ActivityLog())->record($user, 'task.deleted', "Deleted task: {$task['title']}.");
        flash('notice', 'Task deleted.');
        redirect('/tasks');
    }

    private function validatedAttributes(array $user): ?array
    {
        $title = sanitize_text_value(input_string($_POST, 'title'));
        $description = sanitize_text_value(input_string($_POST, 'description'));
        $priority = sanitize_text_value(input_string($_POST, 'priority', 'medium'));
        $status = sanitize_text_value(input_string($_POST, 'status', 'pending'));
        $subjectId = sanitize_text_value(input_string($_POST, 'subject_id'));
        $dueDate = sanitize_text_value(input_string($_POST, 'due_date'));

        if ($title === '' || strlen($title) > 180 || strlen($description) > 2000) {
            flash('error', 'Enter a task title up to 180 characters and a description under 2,000 characters.');
            return null;
        }
        if (!in_array($priority, ['low', 'medium', 'high'], true) || !in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            flash('error', 'Choose a valid priority and status.');
            return null;
        }
        if ($dueDate !== '') {
            $parsedDate = \DateTime::createFromFormat('!Y-m-d', $dueDate);
            if ($parsedDate === false || $parsedDate->format('Y-m-d') !== $dueDate) {
                flash('error', 'Enter a valid deadline.');
                return null;
            }
        }

        $subjectId = $subjectId === '' ? null : (ctype_digit($subjectId) ? (int) $subjectId : -1);
        if ($subjectId === -1 || ($subjectId !== null && (new Subject())->findForUser($subjectId, $user) === null)) {
            flash('error', 'Choose one of your available subjects.');
            return null;
        }

        return [
            'subject_id' => $subjectId,
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate === '' ? null : $dueDate,
            'priority' => $priority,
            'status' => $status,
        ];
    }
}