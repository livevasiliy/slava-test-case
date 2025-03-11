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
        // 1. Сохраняем файл на диск
        $storedFilePath = $this->storeFileOnDisk($filePath);

        // 2. Читаем строки из файла
        $this->fileReader->setFilePath($filePath);
        $rows = $this->fileReader->read();

        // 5. Удаляем строку заголовка, если она есть
        if ($this->headerConfig && $this->headerConfig->getHeaderRowNumber() >= 0) {
            $headerRowNumber = $this->headerConfig->getHeaderRowNumber();
            unset($rows[$headerRowNumber]);
        }

        $countRows = count($rows);

        // 3. Создаем запись в таблице import_files
        $importedFile = $this->createImportedFileRecord($filePath, $storedFilePath, $countRows);

        // 6. Разбиваем строки на пакеты
        $batchSize = $this->batchSizeConfig->getBatchSize();
        $chunks = array_chunk($rows, $batchSize);

        foreach ($chunks as $chunk) {
            if ($this->queueConfig && $this->queueConfig->shouldUseQueue()) {
                dispatch(new ProcessImportChunkJob($chunk, $importedFile));
            } else {
                $this->processChunk($chunk, $importedFile);
            }
        }
    }

    protected function processChunk(array $chunk, ImportFile $importFile): void
    {
        foreach ($chunk as $index => $row) {
            $this->processRowWithValidation($row, $index, $importFile->id);
        }
    }

    public function processRowWithValidation(array $row, int $index, int $fileId): void
    {
        $data = (new ImportRowDTO(
            (int) $row[0],
            (string) $row[1],
            (string) $row[2],
        ))->jsonSerialize();

        $validationResult = $this->validator->validate($data);

        if ($validationResult['valid']) {
            $this->processRow($data, $fileId);
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
        $disk = 'local'; // Используем диск по умолчанию (можно изменить на 's3' или другой)
        Storage::disk($disk)->put(
            'imports/'.$fileName.pathinfo($filePath, PATHINFO_EXTENSION),
            file_get_contents($filePath)
        );

        return Storage::disk($disk)->path($fileName);
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
            'file_content' => base64_encode($fileContent), // Кодируем содержимое в Base64
            'total_rows' => $countRows, // Пока неизвестно количество строк
            'processed_rows' => 0,
            'status' => 'pending',
        ]);
    }

    abstract protected function processRow(array $row, int $fileId): void;
}
