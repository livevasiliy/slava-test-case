<?php

declare(strict_types=1);

namespace App\Validators;

class ExcelRowValidator extends RowValidator
{
    protected function getRules(): array
    {
        return [
            'id' => ['required', 'integer', 'gt:0'],
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'date_format:d.m.Y'],
        ];
    }

    protected function getMessages(): array
    {
        return [
        ];
    }
}
