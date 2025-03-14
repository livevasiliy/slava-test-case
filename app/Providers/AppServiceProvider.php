<?php

namespace App\Providers;

use App\Imports\AbstractImportService;
use App\Jobs\ImportFileJob;
use App\Jobs\ProcessImportChunkJob;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->bindMethod([ProcessImportChunkJob::class, 'handle'], function (ProcessImportChunkJob $job, Application $app) {
            $job->handle($this->app->make(AbstractImportService::class));
        });

        $this->app->bindMethod([ImportFileJob::class, 'handle'], function (ImportFileJob $job, Application $app) {
            $job->handle($this->app->make(AbstractImportService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
