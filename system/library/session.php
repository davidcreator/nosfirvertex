<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class Session
{
    public function __construct(private readonly string $savePath)
    {
        $this->start();
    }

    private function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0775, true);
        }

        session_save_path($this->savePath);
        session_name('nosfirvertex_session');
        session_start();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function all(): array
    {
        return $_SESSION;
    }

    public function destroy(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
