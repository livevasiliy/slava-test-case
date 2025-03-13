<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidationError extends Model
{
    protected $table = 'validation_errors';

    protected $fillable = [
        'file_id',
        'row_number',
        'error_message',
    ];

    public $timestamps = true;

    public function file(): BelongsTo
    {
        return $this->belongsTo(ImportFile::class, 'file_id');
    }
}
