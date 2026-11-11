<?php

declare(strict_types=1);

namespace App\Core;

use ReflectionMethod;

final class Router
{
    public function dispatch(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath = rtrim($basePath, '/.');

        if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
            $path = substr($path, strlen($basePath));
        }

        $path = trim($path, '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if (count($segments) > 3) {
            $this->notFound();
        }

        $module = isset($segments[0]) ? rawurldecode($segments[0]) : 'dashboard';
        $action = isset($segments[1]) ? rawurldecode($segments[1]) : 'index';
        $id = isset($segments[2]) ? rawurldecode($segments[2]) : null;

        if (
            !preg_match('/\A[a-zA-Z][a-zA-Z0-9_-]*\z/', $module)
            || !preg_match('/\A[a-zA-Z][a-zA-Z0-9_-]*\z/', $action)
            || ($id !== null && ($id === '' || preg_match('/[\x00-\x1F\/\\\\]/', $id)))
        ) {
            $this->notFound();
        }

        $controllerName = $this->toStudlyCase($module) . 'Controller';
        $actionName = $this->toCamelCase($action);
        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass) || !is_subclass_of($controllerClass, Controller::class)) {
            $this->notFound();
        }

        if (!method_exists($controllerClass, $actionName)) {
            $this->notFound();
        }

        $method = new ReflectionMethod($controllerClass, $actionName);
        if (
            !$method->isPublic()
            || $method->isStatic()
            || $method->isConstructor()
            || $method->getDeclaringClass()->getName() === Controller::class
            || $method->getNumberOfRequiredParameters() > ($id === null ? 0 : 1)
            || ($id !== null && $method->getNumberOfParameters() === 0 && !$method->isVariadic())
        ) {
            $this->notFound();
        }

        $controller = new $controllerClass();
        if ($id === null) {
            $method->invoke($controller);
            return;
        }

        $method->invoke($controller, $id);
    }

    private function toStudlyCase(string $value): string
    {
        $parts = preg_split('/[-_]+/', $value) ?: [];

        return implode('', array_map(
            static fn (string $part): string => ucfirst(strtolower($part)),
            $parts
        ));
    }

    private function toCamelCase(string $value): string
    {
        return lcfirst($this->toStudlyCase($value));
    }

    private function notFound(): void
    {
        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="vi"><meta charset="utf-8">'
            . '<title>404 - Không tìm thấy</title>'
            . '<h1>404 - Không tìm thấy trang</h1></html>';
        exit;
    }
}
