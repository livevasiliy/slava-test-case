<?php

declare(strict_types=1);

namespace App\Validators;

class ExcelRowValidator extends RowValidator
{
    protected function getRules(): array
    {
        return [
            0 => function ($value) {
                return ! empty($value); // ID не должен быть пустым
            },
            1 => function ($value) {
                return is_string($value) && ! empty($value); // Name должен быть непустой строкой
            },
            2 => function ($value) {
                return \DateTime::createFromFormat('d.m.Y', $value) !== false; // Date должен соответствовать формату d.m.Y
            },
        ];
    }
}
