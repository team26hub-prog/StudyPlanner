<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        $data['currentUser'] = current_user();
        $data['notice'] = flash('notice');
        $data['error'] = $data['error'] ?? flash('error');
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__) . '/Views/' . $view . '.php';
        $content = ob_get_clean();
        require dirname(__DIR__) . '/Views/layout.php';
    }

    protected function requireAuth(): array
    {
        $user = current_user();
        if ($user === null) {
            flash('notice', 'Sign in to continue.');
            redirect('/login');
        }

        return $user;
    }

    protected function requireRole(string $role): array
    {
        $user = $this->requireAuth();
        if ($user['role'] !== $role) {
            $this->notFound();
        }

        return $user;
    }

    protected function requireGuest(): void
    {
        $user = current_user();
        if ($user !== null) {
            redirect($user['role'] === 'admin' ? '/admin' : '/');
        }
    }

    protected function requireCsrf(): void
    {
        $token = $_POST['_token'] ?? '';
        if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
            http_response_code(419);
            exit('Your form expired. Go back, refresh the page, and try again.');
        }
    }

    protected function notFound(): never
    {
        http_response_code(404);
        echo 'Page not found';
        exit;
    }
}
