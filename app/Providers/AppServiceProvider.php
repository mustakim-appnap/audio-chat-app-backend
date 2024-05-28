<?php

namespace App\Providers;

use App\Contracts\FileUploadContract;
use App\Services\v1\FileUploadServiceV1;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(FileUploadContract::class, function ($app) {
            return new FileUploadServiceV1();
        });
    }
}
