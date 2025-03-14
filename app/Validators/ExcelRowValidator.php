<?php

declare(strict_types=1);

namespace App\Validators;

use DateTime;

class ExcelRowValidator extends RowValidator
{
    protected function getRules(): array
    {
        return [
            'id' => ['required', 'integer', 'gt:0', 'unique:import_rows,id'],
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'date_format:d.m.Y'],
        ];
    }

    protected function getMessages(): array
    {
        return [];
    }

    public function validate(array $row): array
    {
        $errors = [];

        // Валидация ID
        if (empty($row['id']) || ! is_int($row['id']) || $row['id'] <= 0) {
            $errors[] = 'ID обязателен и должен быть больше 0.';
        }

        // Валидация имени
        if (empty($row['name']) || ! is_string($row['name']) || strlen($row['name']) > 255) {
            $errors[] = 'Имя обязательно и не должно превышать 255 символов.';
        }

        // Валидация даты
        $dateFormat = 'd.m.Y';
        $d = DateTime::createFromFormat($dateFormat, $row['date']);
        if (! $d || $d->format($dateFormat) !== $row['date']) {
            $errors[] = 'Дата обязательна и должна быть в формате дд.мм.гггг.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
