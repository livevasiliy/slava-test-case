<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Imports\AbstractImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $chunk)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AbstractImportService $importService): void
    {
        foreach ($this->chunk as $index => $row) {
            $importService->processRowWithValidation($row, $index);
        }
    }
}
