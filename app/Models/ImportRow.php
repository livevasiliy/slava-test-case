<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ImportRowObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ImportRowObserver::class)]
class ImportRow extends Model
{
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
