<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function allPost(): array
    {
        return $_POST;
    }

    public function allGet(): array
    {
        return $_GET;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $_SERVER[$key] ?? $default;
    }

    public function getPath(): string
    {
        $routeParam = $this->get('route');

        if (is_string($routeParam) && $routeParam !== '') {
            return trim($routeParam, '/');
        }

        $uri = parse_url((string) ($this->server('REQUEST_URI') ?? '/'), PHP_URL_PATH) ?: '/';
        $scriptDir = str_replace('\\', '/', dirname((string) ($this->server('SCRIPT_NAME') ?? '/')));

        if ($scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }

        $path = trim((string) $uri, '/');

        if ($path === 'index.php') {
            return '';
        }

        if (str_starts_with($path, 'index.php/')) {
            return trim(substr($path, strlen('index.php/')), '/');
        }

        return $path;
    }
}
