<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Imports\AbstractImportService;
use App\Models\ImportFile;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessImportChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $chunk;

    public ImportFile $importFile;

    /**
     * Create a new job instance.
     */
    public function __construct(array $chunk, ImportFile $importFile)
    {
        $this->chunk = $chunk;
        $this->importFile = $importFile;
    }

    /**
     * Execute the job.
     */
    public function handle(AbstractImportService $service): void
    {
        foreach ($this->chunk as $index => $row) {
            $service->processRowWithValidation($row, $index, $this->importFile->id);
        }
    }
}
