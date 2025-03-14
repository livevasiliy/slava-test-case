<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DTO\ImportRowDTO;
use App\Imports\AbstractImportService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ImportRowDTO[] $chunk
     */
    public array $chunk;

    public int $importFileId;
    public int $totalRows;

    /**
     * Create a new job instance.
     */
    public function __construct(int $importFileId, array $chunk, int $totalRows)
    {
        $this->chunk = $chunk;
        $this->importFileId = $importFileId;
        $this->totalRows = $totalRows;
    }

    /**
     * Execute the job.
     */
    public function handle(AbstractImportService $service): void
    {
        $validRows = [];
        foreach ($this->chunk as $index => $row) {
            $result = $service->processRowWithValidation(
                $service->mapRowToObject($row), $index, $this->importFileId
            );

            if (!is_null($result)) {
                $validRows[] = $result->jsonSerialize();
            }
        }
        $service->processRows($validRows, $this->importFileId);
    }
}
