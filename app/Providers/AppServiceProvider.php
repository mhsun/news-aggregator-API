<?php

namespace App\Providers;

use App\Contracts\NewsProviderInterface;
use App\Services\News\NewsApiOrgService;
use App\Services\News\TheGuardianService;
use App\Services\News\TheNYTimesService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(NewsProviderInterface::class, function () {
            return [
                new NewsApiOrgService,
                new TheGuardianService,
                new TheNYTimesService,
            ];
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
