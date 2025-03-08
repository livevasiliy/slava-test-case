<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\FileReaders\Contracts\FileReaderContract;
use App\Http\Requests\ImportRequest;
use App\Imports\AbstractImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function __invoke(
        ImportRequest $request,
        AbstractImportService $service,
        FileReaderContract $fileReader
    ): JsonResponse {
        $service->import($request->validated('file')->getRealPath());

        return new JsonResponse(['message' => 'Import component successfully imported.'], JsonResponse::HTTP_OK);
    }
}
