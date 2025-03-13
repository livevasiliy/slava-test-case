<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Redis;

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
    ];

    /**
     * Получить уникальный ключ для прогресса импорта.
     */
    public function getProgressKey(): string
    {
        return "import_progress:{$this->id}";
    }

    /**
     * Установить начальное значение прогресса.
     */
    public function initializeProgress(int $totalRows): void
    {
        $progressKey = $this->getProgressKey();
        Redis::set($progressKey, 0); // Начальное значение: 0 обработанных строк
        Redis::expire($progressKey, 86400); // Устанавливаем TTL (например, 24 часа)
    }

    /**
     * Получить текущий прогресс (количество обработанных строк).
     */
    public function getProgress(): int
    {
        $progressKey = $this->getProgressKey();

        return (int) Redis::get($progressKey);
    }

    /**
     * Получить процент выполнения.
     */
    public function getProgressPercentage(int $totalRows): float
    {
        $processedRows = $this->getProgress();

        return $totalRows > 0 ? round(($processedRows / $totalRows) * 100, 2) : 0;
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
