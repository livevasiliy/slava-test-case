<?php

namespace Database\Factories;

use App\Models\ImportFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportFileFactory extends Factory
{
    protected $model = ImportFile::class;

    public function definition()
    {
        return [
            'file_name' => $this->faker->word . '.csv', // Случайное имя файла
            'file_path' => $this->faker->filePath(), // Случайный путь к файлу
            'total_rows' => $this->faker->numberBetween(1, 100), // Случайное количество строк
            'processed_rows' => 0, // Начальное количество обработанных строк
            'status' => 'pending', // Начальный статус
            'file_content' => $this->faker->text(), // Случайное содержимое файла
            'process_with_error' => 0, // Начальное количество ошибок
        ];
    }
} 