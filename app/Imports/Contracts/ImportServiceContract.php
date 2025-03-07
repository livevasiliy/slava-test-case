<?php

declare(strict_types=1);

namespace App\Imports\Contracts;

interface ImportServiceContract
{
    public function import(string $filePath): void;
}
