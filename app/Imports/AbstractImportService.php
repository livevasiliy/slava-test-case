<?php

declare(strict_types=1);

namespace App\Imports;

use App\FileReaders\FileReader;
use App\Imports\Contracts\BatchSizeConfigurationContract;
use App\Imports\Contracts\HeaderRowConfigurationContract;
use App\Imports\Contracts\ImportServiceContract;
use App\Imports\Contracts\QueueConfigurationContract;
use App\Jobs\ProcessImportChunkJob;
use App\Validators\RowValidator;
use Psr\Log\LoggerInterface;

abstract class AbstractImportService implements ImportServiceContract
{
    public function __construct(
        protected FileReader $fileReader,
        protected RowValidator $validator,
        protected LoggerInterface $logger,
        protected BatchSizeConfigurationContract $batchSizeConfig,
        protected ?QueueConfigurationContract $queueConfig = null,
        protected ?HeaderRowConfigurationContract $headerConfig = null
    )
    {
    }

    public function import(string $filePath): void
    {
        $this->fileReader->setFilePath($filePath);
        $rows = $this->fileReader->read();
        foreach ($rows as $index => $row) {
            if ($this->headerConfig && $this->headerConfig->getHeaderRowNumber() >= 0) {
                $headerRowNumber = $this->headerConfig->getHeaderRowNumber();
                unset($rows[$headerRowNumber]);
            }

            $batchSize = $this->batchSizeConfig->getBatchSize();
            $chunks = array_chunk($rows, $batchSize);

            foreach ($chunks as $chunk) {
                if ($this->queueConfig && $this->queueConfig->shouldUseQueue()) {
                    dispatch(new ProcessImportChunkJob($chunk));
                } else {
                    $this->processChunk($chunk);
                }
            }
        }
    }

    protected function processChunk(array $chunk): void
    {
        foreach ($chunk as $index => $row) {
            $this->processRowWithValidation($row, $index);
        }
    }

    public function processRowWithValidation(array $row, int $index): void
    {
        $validationResult = $this->validator->validate($row);

        if ($validationResult['valid']) {
            $this->processRow($row);
        } else {
            $errorMessage = ($index + 1) . ' - ' . implode(', ', $validationResult['errors']);
            $this->logger->error($errorMessage);
        }
    }

    abstract protected function processRow(array $row): void;
}
