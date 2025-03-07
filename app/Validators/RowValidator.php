<?php

declare(strict_types=1);

namespace App\Validators;

use App\Validators\Contracts\RowValidatorContract;

abstract class RowValidator implements RowValidatorContract
{
    /**
     * Возвращает массив правил валидации.
     *
     * @return array
     */
    abstract protected function getRules(): array;

    /**
     * Валидирует строку данных.
     *
     * @param array $row
     * @return array
     */
    public function validate(array $row): array
    {
        $rules = $this->getRules();
        $errors = [];

        foreach ($rules as $field => $rule) {
            if (!isset($row[$field]) || !$this->validateField($row[$field], $rule)) {
                $errors[] = "Field '{$field}' is invalid";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Проверяет поле на соответствие правилу.
     *
     * @param mixed $value
     * @param callable|string $rule
     * @return bool
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
}
