<?php
declare(strict_types=1);

namespace AureaVertex\System\Engine;

class Registry
{
    private array $data = [];

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->data)) {
            throw new \RuntimeException('Registry key not found: ' . $key);
        }

        return $this->data[$key];
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }
}
