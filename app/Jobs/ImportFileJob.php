<?php

namespace App\Jobs;

use App\Imports\AbstractImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportFileJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $file)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AbstractImportService $service): void
    {
        $service->import($this->file);
    }
}
