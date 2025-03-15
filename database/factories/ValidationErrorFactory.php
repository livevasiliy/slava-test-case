<?php

namespace Database\Factories;

use App\Models\ImportFile;
use App\Models\ValidationError;
use Illuminate\Database\Eloquent\Factories\Factory;

class ValidationErrorFactory extends Factory
{
    protected $model = ValidationError::class;

    public function definition()
    {
        return [
            'file_id' => ImportFile::factory()->id, // Это будет установлено позже
            'row_number' => $this->faker->numberBetween(1, 100), // Случайный номер строки
            'error_message' => $this->faker->sentence(), // Случайное сообщение об ошибке
        ];
    }
}
