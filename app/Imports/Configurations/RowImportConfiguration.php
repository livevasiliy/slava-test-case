<?php

declare(strict_types=1);


namespace App\Imports\Configurations;

use App\Imports\Contracts\BatchSizeConfigurationContract;
use App\Imports\Contracts\HeaderRowConfigurationContract;
use App\Imports\Contracts\QueueConfigurationContract;

class RowImportConfiguration implements
    BatchSizeConfigurationContract,
    QueueConfigurationContract,
    HeaderRowConfigurationContract
{

    public function getHeaderRowNumber(): int
    {
        return 0;
    }

    public function getBatchSize(): int
    {
        return 1000;
    }

    public function shouldUseQueue(): bool
    {
        return true;
    }
}
