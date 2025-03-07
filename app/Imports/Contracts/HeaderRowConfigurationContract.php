<?php

declare(strict_types=1);


namespace App\Imports\Contracts;

interface HeaderRowConfigurationContract
{
    public function getHeaderRowNumber(): int;
}
