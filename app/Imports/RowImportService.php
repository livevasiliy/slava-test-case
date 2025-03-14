<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\ImportFile;
use App\Models\ImportRow;
use Illuminate\Support\Carbon;

class RowImportService extends AbstractImportService
{
    protected function processRow(array $row, int $fileId): void
    {
        // Создаем запись в БД
        ImportRow::create([
            'id' => $row['id'],
            'name' => $row['name'],
            'date' => Carbon::createFromFormat('d.m.Y', $row['date'])->format('Y-m-d'),
            'file_id' => $fileId,
        ]);
        ImportFile::find($fileId)->increment('processed_rows');
    }
}
