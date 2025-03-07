<?php

declare(strict_types=1);

namespace App\FileReaders;

use App\FileReaders\Contracts\FileReaderContract;

abstract class FileReader implements FileReaderContract
{
    protected ?string $filePath = null;

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    abstract public function read(): array;
}
