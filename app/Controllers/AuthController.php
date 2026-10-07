<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\User;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->requireGuest();
        $this->render('auth/login');
    }

    public function login(): void
    {
        $this->requireGuest();
        $this->requireCsrf();

        $email = input_email($_POST, 'email');
        $password = input_password($_POST, 'password');

        if ($email === '' || text_length($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/login', ['error' => 'Enter a valid email address.']);
            return;
        }
        if ($password === '') {
            $this->render('auth/login', ['error' => 'Enter your password.']);
            return;
        }
        if (strlen($password) > 1024) {
            $this->render('auth/login', ['error' => 'Password must be 1024 characters or fewer.']);
            return;
        }

        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? (new User())->findByEmail($email) : null;

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $this->render('auth/login', ['error' => 'That email and password combination was not recognized.']);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        (new ActivityLog())->record($_SESSION['user'], 'auth.login', 'Signed in.');
        flash('success', 'You have signed in successfully.');
        redirect($user['role'] === 'admin' ? '/admin' : '/');
    }

    public function registerForm(): void
    {
        $this->requireGuest();
        $this->render('auth/register');
    }

    public function register(): void
    {
        $this->requireGuest();
        $this->requireCsrf();

        $nameInput = input_string($_POST, 'name');
        $name = sanitize_text_value($nameInput);
        $email = input_email($_POST, 'email');
        $password = input_password($_POST, 'password');
        $passwordConfirmation = input_password($_POST, 'password_confirmation');

        if (text_length($name) < 2 || text_length($name) > 100) {
            $this->render('auth/register', ['error' => 'Enter a name between 2 and 100 characters.']);
            return;
        }
        if (!is_valid_person_name($nameInput)) {
            $this->render('auth/register', ['error' => 'Name must contain letters only, with spaces between names.']);
            return;
        }
        if ($email === '' || text_length($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/register', ['error' => 'Enter a valid email address no longer than 190 characters.']);
            return;
        }
        if (!password_meets_policy($password)) {
            $this->render('auth/register', ['error' => 'Use 8-72 characters with uppercase and lowercase letters, a number, and a symbol.']);
            return;
        }
        if ($password !== $passwordConfirmation) {
            $this->render('auth/register', ['error' => 'The passwords do not match.']);
            return;
        }

        $users = new User();
        if ($users->findByEmail($email) !== null) {
            $this->render('auth/register', ['error' => 'An account with that email already exists.']);
            return;
        }

        $id = $users->create($name, $email, $password);
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $id,
            'name' => $name,
            'email' => strtolower($email),
            'role' => 'user',
        ];
        (new ActivityLog())->record($_SESSION['user'], 'auth.registered', 'Registered a new account.');
        flash('success', 'Your account has been created successfully.');
        redirect('/');
    }

    public function logout(): void
    {
        $user = $this->requireAuth();
        $this->requireCsrf();
        (new ActivityLog())->record($user, 'auth.logout', 'Signed out.');
        $_SESSION = [];
        session_regenerate_id(true);
        flash('success', 'You have signed out successfully.');
        redirect('/login');
    }
}
