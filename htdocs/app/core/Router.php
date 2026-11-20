<?php
/**
 * Router - turns a URL like /post/edit/my-first-post into
 * PostController->edit('my-first-post').
 *
 * Default route (empty URL) goes to PostController->index (the homepage feed).
 */
class Router
{
    public function dispatch(string $url): void
    {
        $parts = $url === '' ? [] : explode('/', $url);

        $controllerName = !empty($parts[0]) ? ucfirst(strtolower($parts[0])) : 'Post';
        $action          = $parts[1] ?? 'index';
        $params          = array_slice($parts, 2);

        $controllerFile  = __DIR__ . '/../controllers/' . $controllerName . 'Controller.php';
        $controllerClass = $controllerName . 'Controller';

        if (!file_exists($controllerFile)) {
            $this->notFound();
        }

        require_once $controllerFile;

        if (!class_exists($controllerClass)) {
            $this->notFound();
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->notFound();
        }

        call_user_func_array([$controller, $action], $params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 - Page not found';
        exit;
    }
}
