<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRow extends Model
{
    use HasFactory;

    protected $table = 'import_rows';

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'id',
        'name',
        'date',
        'file_id',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(ImportFile::class, 'file_id');
    }
}
