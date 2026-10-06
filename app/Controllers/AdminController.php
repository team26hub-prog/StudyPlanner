<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\Subject;
use App\Models\Task;
use App\Models\User;

final class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');
        $users = new User();

        $this->render('admin/index', [
            'stats' => $users->adminStats(),
            'users' => $users->allWithSummaries(),
        ]);
    }

    public function showUser(string $id): void
    {
        $this->requireRole('admin');
        $users = new User();
        $user = $users->findWithSummary((int) $id);
        if ($user === null) {
            $this->notFound();
        }

        $this->render('admin/user', [
            'managedUser' => $user,
            'subjects' => (new Subject())->allForOwner((int) $id),
            'tasks' => (new Task())->allForOwner((int) $id),
        ]);
    }

    public function manageUsers(): void
    {
        $this->requireRole('admin');
        $this->render('admin/users', ['users' => (new User())->allWithSummaries()]);
    }

    public function storeUser(): void
    {
        $admin = $this->requireRole('admin');
        $this->requireCsrf();

        $name = sanitize_text_value(input_string($_POST, 'name'));
        $email = input_email($_POST, 'email');
        $password = input_password($_POST, 'password');
        $passwordConfirmation = input_password($_POST, 'password_confirmation');

        if (text_length($name) < 2 || text_length($name) > 100) {
            flash('error', 'Enter a name between 2 and 100 characters.');
            redirect('/admin/manage-users');
        }
        if ($email === '' || text_length($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid email address no longer than 190 characters.');
            redirect('/admin/manage-users');
        }
        if (!password_meets_policy($password)) {
            flash('error', 'Use 8-72 characters with uppercase and lowercase letters, a number, and a symbol.');
            redirect('/admin/manage-users');
        }
        if ($password !== $passwordConfirmation) {
            flash('error', 'The passwords do not match.');
            redirect('/admin/manage-users');
        }

        $users = new User();
        if ($users->findByEmail($email) !== null) {
            flash('error', 'An account with that email already exists.');
            redirect('/admin/manage-users');
        }

        $users->create($name, $email, $password);
        (new ActivityLog())->record($admin, 'user.created', "Created user account {$name} ({$email}).");
        flash('notice', 'User account created.');
        redirect('/admin/manage-users');
    }

    public function deleteUser(string $id): void
    {
        $admin = $this->requireRole('admin');
        $this->requireCsrf();

        $users = new User();
        $target = $users->findWithSummary((int) $id);
        if ($target === null || $target['role'] !== 'user' || (int) $target['id'] === (int) $admin['id']) {
            flash('error', 'That account cannot be deleted.');
            redirect('/admin/manage-users');
        }

        $users->deleteUser((int) $target['id']);
        (new ActivityLog())->record($admin, 'user.deleted', "Deleted user account {$target['name']} ({$target['email']}).");
        flash('notice', 'User account and its study data deleted.');
        redirect('/admin/manage-users');
    }

    public function activity(): void
    {
        $this->requireRole('admin');
        $this->render('admin/activity', ['activities' => (new ActivityLog())->latest()]);
    }
}
