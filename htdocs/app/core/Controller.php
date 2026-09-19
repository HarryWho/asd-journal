<?php
/**
 * Controller - base class all controllers extend.
 * Handles rendering views inside the shared layout.
 */
class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data); // makes $data['title'] available as $title, etc.

        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            die("View not found: {$view}");
        }

        // Everything between here and require('layout') becomes $content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require __DIR__ . '/../views/layouts/main.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    protected function isAdmin(): bool
    {
        return $this->isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
    }

    protected function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('auth/login');
        }
    }

    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }
}
