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
use App\Validators\Contracts\RowValidatorContract;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

abstract class AbstractImportService implements ImportServiceContract
{
    public function __construct(
        protected FileReader $fileReader,
        protected RowValidatorContract $validator,
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
        $batchSize = $this->batchSizeConfig->getBatchSize();

        $chunks = array_chunk($rows, $batchSize);

        if ($this->queueConfig && $this->queueConfig->shouldUseQueue()) {
            $this->processImport($importedFile->id, $chunks, $countRows);
        }
    }

    public function processRowWithValidation(ImportRowDTO $data, int $index, int $fileId): ?ImportRowDTO
    {
        $validationResult = $this->validator->validate($data->jsonSerialize());

        if ($validationResult['valid']) {
            return $data;
        } else {
            $lineNumber = $index + 1;
            foreach ($validationResult['errors'] as $error) {
                $validationError = ValidationError::create([
                    'file_id' => $fileId,
                    'row_number' => $lineNumber,
                    'error_message' => $error,
                ]);
                $validationError->file->increment('process_with_error');
            }
            $errorMessage = ($lineNumber).' - '.implode(', ', $validationResult['errors']);
            Log::channel('import_errors')->error($errorMessage);

            return null;
        }
    }

    public function mapRowToObject(array $row): ImportRowDTO
    {
        return new ImportRowDTO(
            (int) $row[0],
            (string) $row[1],
            (string) $row[2],
        );
    }

    public function processImport(int $fileId, array $chunks, int $totalRows): void
    {
        $jobs = collect($chunks)->map(fn ($chunk) => new ProcessImportChunkJob($fileId, $chunk, $totalRows))->toArray();

        Bus::batch($jobs)
            ->before(function (Batch $batch) use ($fileId) {
                Log::channel('import_errors')->info('Start import');
                $redisKey = "import_progress:$fileId";
                Redis::set($redisKey, 0);
                Log::channel('import_errors')->info('setup value in redis for '.$redisKey.'= '.Redis::get($redisKey));
            })
            ->progress(function (Batch $batch) use ($fileId) {
                ImportFile::where('id', $fileId)->update(['status' => 'processing']);
                Log::channel('import_errors')->info('Current progress: '.$batch->progress());
            })
            ->then(function (Batch $batch) use ($fileId) {
                ImportFile::where('id', $fileId)->update(['status' => 'completed']);
                Log::channel('import_errors')->info('Import finished');
            })
            ->catch(function (Batch $batch, Throwable $e) use ($fileId) {
                ImportFile::where('id', '=', $fileId)->update(['status' => 'failed']);
                Log::channel('import_errors')->error('Import error: '.$e->getMessage());
            })
            ->dispatch();
    }

    protected function storeFileOnDisk(string $filePath): string
    {
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        Storage::disk('imports')->put(
            $fileName.'.'.pathinfo($filePath, PATHINFO_EXTENSION),
            file_get_contents($filePath)
        );

        return Storage::disk('imports')->path($fileName.'.'.pathinfo($filePath, PATHINFO_EXTENSION));
    }

    protected function createImportedFileRecord(
        string $originalFilePath,
        string $storedFilePath,
        int $countRows
    ): ImportFile {
        $fileContent = file_get_contents($originalFilePath);

        return ImportFile::create([
            'file_name' => basename($originalFilePath),
            'file_path' => $storedFilePath,
            'file_content' => base64_encode($fileContent),
            'total_rows' => $countRows,
            'processed_rows' => 0,
            'status' => 'pending',
        ]);
    }

    abstract public function processRows(array $rows, int $fileId): void;
}
