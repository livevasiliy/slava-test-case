<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Row;
use Illuminate\Support\Carbon;

class RowImportService extends AbstractImportService
{

    protected function processRow(array $row): void
    {
        // Создаем запись в БД
        Row::create([
            'name' => $row[1],
            'date' => Carbon::createFromFormat('d.m.Y', $row[2])->format('Y-m-d'),
        ]);
    }
}
