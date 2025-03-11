<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\FileReaders\Contracts\FileReaderContract;
use App\Http\Requests\ImportRequest;
use App\Imports\AbstractImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function __invoke(
        ImportRequest $request,
        AbstractImportService $service,
        FileReaderContract $fileReader
    ): JsonResponse {
        try {
            /** @var UploadedFile $file */
            $file = $request->validated('file');
            $service->import($file->getRealPath());

            return new JsonResponse(['message' => 'Import component successfully imported.'], JsonResponse::HTTP_OK);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return new JsonResponse(['message' => $exception->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
