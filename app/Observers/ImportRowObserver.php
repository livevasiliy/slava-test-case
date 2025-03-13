<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\RowCreatedEvent;
use App\Models\ImportRow;

class ImportRowObserver
{
    /**
     * Handle the ImportRow "created" event.
     */
    public function created(ImportRow $importRow): void
    {
        event(new RowCreatedEvent($importRow));
    }

    /**
     * Handle the ImportRow "updated" event.
     */
    public function updated(ImportRow $importRow): void
    {
        //
    }

    /**
     * Handle the ImportRow "deleted" event.
     */
    public function deleted(ImportRow $importRow): void
    {
        //
    }

    /**
     * Handle the ImportRow "restored" event.
     */
    public function restored(ImportRow $importRow): void
    {
        //
    }

    /**
     * Handle the ImportRow "force deleted" event.
     */
    public function forceDeleted(ImportRow $importRow): void
    {
        //
    }
}
