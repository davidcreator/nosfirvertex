<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class Config
{
    private array $data = [];

    public function load(string $file): void
    {
        if (!is_file($file)) {
            return;
        }

        $loaded = require $file;

        if (is_array($loaded)) {
            $this->data = array_replace_recursive($this->data, $loaded);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->data;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $target = &$this->data;

        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }

        $target = $value;
    }

    public function all(): array
    {
        return $this->data;
    }
}
