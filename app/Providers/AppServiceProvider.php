<?php

namespace App\Providers;

use App\Services\Contracts\IGovBrAuthService;
use App\Services\GovBrPureService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IGovBrAuthService::class, GovBrPureService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
