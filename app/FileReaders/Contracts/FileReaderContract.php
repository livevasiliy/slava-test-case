<?php

declare(strict_types=1);

namespace App\FileReaders\Contracts;

interface FileReaderContract
{
    public function setFilePath(string $filePath): void;

    public function getFilePath(): ?string;

    public function read(): array;
}
