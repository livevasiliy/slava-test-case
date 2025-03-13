<?php

declare(strict_types=1);

namespace App\Imports;

use App\DTO\ImportRowDTO;
use App\FileReaders\FileReader;
use App\Imports\Contracts\BatchSizeConfigurationContract;
use App\Imports\Contracts\HeaderRowConfigurationContract;
use App\Imports\Contracts\ImportServiceContract;
use App\Imports\Contracts\QueueConfigurationContract;
use App\Jobs\ProcessImportChunkJob;
use App\Models\ImportFile;
use App\Models\ValidationError;
use App\Validators\RowValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;

abstract class AbstractImportService implements ImportServiceContract
{
    public function __construct(
        protected FileReader $fileReader,
        protected RowValidator $validator,
        protected BatchSizeConfigurationContract $batchSizeConfig,
        protected ?QueueConfigurationContract $queueConfig = null,
        protected ?HeaderRowConfigurationContract $headerConfig = null
    ) {}

    public function import(string $filePath): void
    {
        $storedFilePath = $this->storeFileOnDisk($filePath);

        $this->fileReader->setFilePath($filePath);
        $rows = $this->fileReader->read();

        if ($this->headerConfig && $this->headerConfig->getHeaderRowNumber() >= 0) {
            $headerRowNumber = $this->headerConfig->getHeaderRowNumber();
            unset($rows[$headerRowNumber]);
        }

        $countRows = count($rows);

        $importedFile = $this->createImportedFileRecord($filePath, $storedFilePath, $countRows);

        $this->initializeProgress($importedFile->id);

        $batchSize = $this->batchSizeConfig->getBatchSize();
        $chunks = array_chunk($rows, $batchSize);

        foreach ($chunks as $chunk) {
            /** @var ImportRowDTO[] $mappedChunk */
            $mappedChunk = array_map(fn(array $row) => $this->mapRowToObject($row), $chunk);
            if ($this->queueConfig && $this->queueConfig->shouldUseQueue()) {
                dispatch(new ProcessImportChunkJob($mappedChunk, $importedFile));
            } else {
                $this->processChunk($mappedChunk, $importedFile);
            }
        }
    }

    /**
     * @param ImportRowDTO[] $chunk
     * @param ImportFile $importFile
     * @return void
     */
    protected function processChunk(array $chunk, ImportFile $importFile): void
    {
        foreach ($chunk as $index => $row) {
            $this->processRowWithValidation($row, $index, $importFile->id);
        }
    }

    private function mapRowToObject(array $row): ImportRowDTO
    {
        return new ImportRowDTO(
            (int) $row[0],
            (string) $row[1],
            (string) $row[2],
        );
    }

    public function processRowWithValidation(ImportRowDTO $data, int $index, int $fileId): void
    {
        $validationResult = $this->validator->validate($data->jsonSerialize());

        // Получаем уникальный ключ для прогресса
        $progressKey = "import_progress:{$fileId}";

        if ($validationResult['valid']) {
            $this->processRow($data->jsonSerialize(), $fileId);

            // Увеличиваем количество обработанных строк в Redis
            $processedRows = (int) Redis::get($progressKey) + 1;
            Redis::set($progressKey, $processedRows);
        } else {
            $lineNumber = $index + 1;
            foreach ($validationResult['errors'] as $error) {
                ValidationError::create([
                    'file_id' => $fileId,
                    'row_number' => $lineNumber,
                    'error_message' => $error,
                ]);
            }
            $errorMessage = ($lineNumber).' - '.implode(', ', $validationResult['errors']);
            Log::channel('import_errors')->error($errorMessage);
        }
    }

    protected function storeFileOnDisk(string $filePath): string
    {
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        Storage::disk('imports')->put(
            $fileName . '.' . pathinfo($filePath, PATHINFO_EXTENSION),
            file_get_contents($filePath)
        );

        return Storage::disk('imports')->path($fileName . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
    }

    protected function createImportedFileRecord(
        string $originalFilePath,
        string $storedFilePath,
        int $countRows
    ): ImportFile {
        $fileContent = file_get_contents($originalFilePath); // Читаем содержимое файла

        return ImportFile::create([
            'file_name' => basename($originalFilePath),
            'file_path' => $storedFilePath,
            'file_content' => base64_encode($fileContent),
            'total_rows' => $countRows,
            'processed_rows' => 0,
            'status' => 'pending',
        ]);
    }

    protected function initializeProgress(int $fileId): void
    {
        $progressKey = "import_progress:{$fileId}";
        Redis::set($progressKey, 0); // Начальное значение: 0 обработанных строк
        Redis::expire($progressKey, 86400); // Устанавливаем TTL (например, 24 часа)
    }

    abstract protected function processRow(array $row, int $fileId): void;
}
