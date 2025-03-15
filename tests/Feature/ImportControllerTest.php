<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ImportFileJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImportControllerTest extends TestCase
{
    #[DataProvider('importDataProvider')]
    public function test_import_controller($file, $expectedStatus, $expectedMessage)
    {
        Bus::fake(); // Подменяем очередь

        $response = $this->postJson(route('import'), [
            'file' => $file,
        ]);

        $response->assertStatus($expectedStatus);

        if ($expectedStatus === JsonResponse::HTTP_OK) {
            Bus::assertDispatched(ImportFileJob::class);
        } else {
            $response->assertJson($expectedMessage, true);
        }
    }

    public static function importDataProvider(): array
    {
        return [
            'valid file xls' => [
                UploadedFile::fake()->create('valid_file.xls', 100), // Создаем фиктивный файл
                JsonResponse::HTTP_OK,
                null
            ],
            'valid file xlsx' => [
                UploadedFile::fake()->create('valid_file.xlsx', 100), // Создаем фиктивный файл
                JsonResponse::HTTP_OK,
                null
            ],
            'invalid file' => [
                UploadedFile::fake()->create('invalid_file.txt', 100), // Создаем фиктивный файл с неправильным расширением
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                [
                    'message' => 'The file field must be a file of type: xls, xlsx.',
                    'errors' => [
                        'file' => [
                            'The file field must be a file of type: xls, xlsx.'
                        ]
                    ]
                ]
            ],
        ];
    }
}
