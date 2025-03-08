<?php

declare(strict_types=1);

namespace App\Imports\Contracts;

interface BatchSizeConfigurationContract
{
    public function getBatchSize(): int;
}
