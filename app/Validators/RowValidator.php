<?php

declare(strict_types=1);

namespace App\Validators;

use App\Validators\Contracts\RowValidatorContract;
use Illuminate\Support\Facades\Validator;

abstract class RowValidator implements RowValidatorContract
{
    /**
     * Возвращает массив правил валидации.
     */
    abstract protected function getRules(): array;

    /**
     * Валидирует строку данных.
     */
    public function validate(array $row): array
    {
        $validator = Validator::make($row, $this->getRules(), $this->getMessages());

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(), // Все ошибки в виде массива
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
        ];
    }

    protected function getMessages(): array
    {
        return [];
    }
}
