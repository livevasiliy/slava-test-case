<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Resources\DataResource;
use App\Models\ImportRow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DataControllerTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('dataProvider')]
    public function test_data_controller($data, $perPage, $page)
    {
        // Подготовка данных с использованием фабрик
        foreach ($data as $row) {
            ImportRow::factory()->create($row);
        }

        // Выполнение запроса
        $response = $this->getJson(route('import.data', ['per_page' => $perPage, 'page' => $page]));

        // Проверка ответа
        $response->assertStatus(JsonResponse::HTTP_OK);

        // Проверка структуры ответа
        $response->assertJsonStructure([
            '*' => [ // '*' означает, что мы ожидаем массив объектов
                'date',
                'items' => [
                    '*' => [ // Проверяем, что 'items' содержит массив объектов
                        'id',
                        'name',
                        'date',
                    ],
                ],
            ],
        ], $response->json());
    }

    public static function dataProvider(): array
    {
        return [
            'single page' => [
                [
                    ['id' => 1, 'name' => 'Item 1', 'date' => '2023-01-01'],
                    ['id' => 2, 'name' => 'Item 2', 'date' => '2023-01-01'],
                ],
                2, // Количество элементов на странице
                1, // Текущая страница
            ],
            'multiple pages' => [
                [
                    ['id' => 1, 'name' => 'Item 1', 'date' => '2023-01-01'],
                    ['id' => 2, 'name' => 'Item 2', 'date' => '2023-01-01'],
                    ['id' => 3, 'name' => 'Item 3', 'date' => '2023-01-02'],
                ],
                2, // Количество элементов на странице
                2, // Текущая страница
            ],
            'no data' => [
                [],
                10, // Количество элементов на странице
                1, // Текущая страница
            ],
        ];
    }
}
