<?php

namespace App\Providers;

use App\CapturedResult;
use App\Observers\CapturedObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // if (config('app.env') === 'production') {
        //     URL::forceScheme('https');
        // }
        CapturedResult::observe(CapturedObserver::class);
    }
}
