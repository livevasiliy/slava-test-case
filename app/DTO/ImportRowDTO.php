<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

class ImportRowDTO implements JsonSerializable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $date,
    ) {}

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
