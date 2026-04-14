<?php
declare(strict_types=1);

namespace AureaVertex\System\Engine;

abstract class Model
{
    public function __construct(protected readonly Registry $registry)
    {
    }

    public function __get(string $name): mixed
    {
        return $this->registry->get($name);
    }
}
