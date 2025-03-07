<?php

declare(strict_types=1);


namespace App\Validators\Contracts;

interface RowValidatorContract
{
    public function validate(array $row): array;
}
