<?php

namespace App\Providers;

use App\Services\Contracts\IGovBrAuthService;
use App\Services\GovBrLibService;
use App\Services\GovBrPureService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\GovBR\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//         $this->app->bind(IGovBrAuthService::class, GovBrPureService::class);
        $this->app->bind(IGovBrAuthService::class, GovBrLibService::class);
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
