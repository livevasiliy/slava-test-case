<?php

declare(strict_types=1);

namespace App\Imports;

use App\Events\RowCreatedEvent;
use App\Models\ImportFile;
use App\Models\ImportRow;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

class RowImportService extends AbstractImportService
{
    public function processRows(array $rows, int $fileId): void
    {
        $insertData = [];

        foreach ($rows as $row) {
            $insertData[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'date' => Carbon::createFromFormat('d.m.Y', $row['date'])->format('Y-m-d'),
                'file_id' => $fileId,
                'created_at' => Carbon::now(),
            ];
        }

        // Массовая вставка
        try {
            if (count($insertData) > 0) {
                ImportRow::insert($insertData);
                $importFile = ImportFile::find($fileId);
                $importFile->increment('processed_rows', count($insertData));
                Redis::set($importFile->getRedisKey(), count($insertData));
                event(new RowCreatedEvent($insertData));
            }
        } catch (Exception $exception) {
            throw new $exception;
        }

    }
}
