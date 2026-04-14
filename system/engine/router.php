<?php
declare(strict_types=1);

namespace AureaVertex\System\Engine;

class Router
{
    public function __construct(private readonly array $routes)
    {
    }

    public function dispatch(string $path): array|null
    {
        $normalized = trim($path, '/');

        foreach ($this->routes as $pattern => $action) {
            $regex = $this->patternToRegex($pattern);

            if (!preg_match($regex, $normalized, $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[] = $value;
                }
            }

            return [
                'action' => $action,
                'params' => $params,
            ];
        }

        return null;
    }

    private function patternToRegex(string $pattern): string
    {
        $trimmed = trim($pattern, '/');

        if ($trimmed === '') {
            return '#^$#';
        }

        $escaped = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $trimmed);

        return '#^' . $escaped . '$#';
    }
}
