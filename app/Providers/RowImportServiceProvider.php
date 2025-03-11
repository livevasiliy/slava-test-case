<?php

declare(strict_types=1);

namespace App\Providers;

use App\FileReaders\Contracts\FileReaderContract;
use App\FileReaders\XlsxFileReader;
use App\Imports\AbstractImportService;
use App\Imports\Configurations\RowImportConfiguration;
use App\Imports\Contracts\BatchSizeConfigurationContract;
use App\Imports\Contracts\HeaderRowConfigurationContract;
use App\Imports\Contracts\QueueConfigurationContract;
use App\Imports\RowImportService;
use App\Validators\Contracts\RowValidatorContract;
use App\Validators\ExcelRowValidator;
use Illuminate\Support\ServiceProvider;

class RowImportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрация FileReader
        $this->app->bind(FileReaderContract::class, function ($app) {
            return new XlsxFileReader;
        });

        // Регистрация Validator
        $this->app->singleton(RowValidatorContract::class, function ($app) {
            return new ExcelRowValidator;
        });

        $this->app->singleton(BatchSizeConfigurationContract::class, function ($app) {
            return new RowImportConfiguration;
        });

        $this->app->singleton(QueueConfigurationContract::class, function ($app) {
            return new RowImportConfiguration;
        });

        $this->app->singleton(HeaderRowConfigurationContract::class, function ($app) {
            return new RowImportConfiguration;
        });

        // Регистрация RowImportService
        $this->app->singleton(AbstractImportService::class, function ($app) {
            return new RowImportService(
                $app->make(FileReaderContract::class),
                $app->make(RowValidatorContract::class),
                $app->make(BatchSizeConfigurationContract::class),
                $app->make(QueueConfigurationContract::class),
                $app->make(HeaderRowConfigurationContract::class),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
