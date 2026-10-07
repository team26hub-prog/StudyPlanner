<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        if (preg_match('#\A[a-zA-Z0-9_-]+(?:/[a-zA-Z0-9_-]+)*\z#', $view) !== 1) {
            http_response_code(500);
            echo 'Unable to render page.';
            return;
        }

        $viewsDirectory = dirname(__DIR__) . '/Views';
        $viewFile = $viewsDirectory . '/' . $view . '.php';
        $layoutFile = $viewsDirectory . '/layout.php';
        if (!is_file($viewFile) || !is_file($layoutFile)) {
            error_log('Page rendering failed: view or layout file is missing.');
            http_response_code(500);
            echo 'Unable to render page.';
            return;
        }

        $data['currentUser'] = current_user();
        $flashError = flash('error');
        $flashedMessages = [
            'notice' => flash('notice'),
            'success' => flash('success'),
            'error' => $flashError,
        ];
        $data['flashError'] = $data['flashError'] ?? $flashError;
        foreach ($flashedMessages as $key => $message) {
            $data[$key] = $data[$key] ?? $message;
        }

        extract($data, EXTR_SKIP);
        $bufferLevel = ob_get_level();
        ob_start();
        try {
            require $viewFile;
            $content = (string) ob_get_clean();
        } catch (\Throwable $exception) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            throw $exception;
        }

        require $layoutFile;
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
