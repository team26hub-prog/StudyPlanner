<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->add('GET', $path, $action);
    }

    public function post(string $path, array $action): void
    {
        $this->add('POST', $path, $action);
    }

    public function dispatch(string $method, string $requestPath, string $basePath = ''): void
    {
        $path = '/' . trim(substr($requestPath, strlen($basePath)), '/');
        $path = $path === '//' ? '/' : $path;

        foreach ($this->routes as [$routeMethod, $pattern, $action]) {
            if ($method !== $routeMethod || !preg_match($pattern, $path, $matches)) {
                continue;
            }

            $arguments = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            [$controllerClass, $controllerMethod] = $action;
            (new $controllerClass())->{$controllerMethod}(...array_values($arguments));
            return;
        }

        http_response_code(404);
        echo 'Page not found';
    }

    private function add(string $method, string $path, array $action): void
    {
        $quotedPath = preg_quote($path, '~');
        $pattern = preg_replace_callback(
            '/\\\\\{([a-zA-Z][a-zA-Z0-9_]*)\\\\\}/',
            static fn (array $match): string => '(?P<' . $match[1] . '>' . ($match[1] === 'id' ? '[1-9][0-9]*' : '[^/]+') . ')',
            $quotedPath
        );
        $this->routes[] = [$method, '~^' . $pattern . '$~', $action];
    }
}
