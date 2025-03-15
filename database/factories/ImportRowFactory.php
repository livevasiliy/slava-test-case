<?php

namespace Database\Factories;

use App\Models\ImportFile;
use App\Models\ImportRow;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportRowFactory extends Factory
{
    protected $model = ImportRow::class;

    public function definition()
    {
        return [
            'id' => $this->faker->unique()->randomNumber(), // Уникальный ID
            'name' => $this->faker->word, // Случайное имя
            'date' => $this->faker->date('Y-m-d'), // Случайная дата
            'file_id' => ImportFile::factory(), // Это будет установлено позже
        ];
    }
}
