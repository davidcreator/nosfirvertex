<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class Validator
{
    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function required(string|null $value): bool
    {
        return trim((string) $value) !== '';
    }

    public static function minLength(string $value, int $length): bool
    {
        return mb_strlen(trim($value)) >= $length;
    }
}
