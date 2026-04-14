<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(string|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $scriptName = rtrim($scriptName, '/');

        $base = preg_replace('#/(admin|catalog|install)$#', '', $scriptName) ?: '';
        $base = rtrim((string) $base, '/');

        if ($path === '') {
            return $base === '' ? '/' : $base . '/';
        }

        return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return base_url($path);
    }
}
