<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportFile extends Model
{
    protected $table = 'import_files';

    public $timestamps = true;

    protected $fillable = [
        'file_name',
        'file_path',
        'total_rows',
        'processed_rows',
        'status',
        'file_content',
        'process_with_error',
    ];

    public function getRedisKey(): string
    {
        return 'import_file:'.$this->id;
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class, 'file_id');
    }

    public function validationErrors(): HasMany
    {
        return $this->hasMany(ValidationError::class, 'file_id');
    }
}
