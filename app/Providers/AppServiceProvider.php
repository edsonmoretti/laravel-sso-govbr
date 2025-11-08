<?php

namespace App\Providers;

use App\Services\Contracts\IGovBrAuthService;
use App\Services\GovBrPureService;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\GovBR\Provider;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('govbr', Provider::class);
        });
    }
}
