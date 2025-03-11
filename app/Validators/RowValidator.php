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

    /**
     * Проверяет поле на соответствие правилу.
     *
     * @param  mixed  $value
     * @param  callable|string  $rule
     */
    protected function validateField($value, $rule): bool
    {
        if (is_callable($rule)) {
            return $rule($value);
        }

        if (is_string($rule) && function_exists($rule)) {
            return $rule($value);
        }

        return false;
    }

    protected function getMessages(): array
    {
        return [];
    }
}
