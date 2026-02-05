<?php

namespace App\Providers;

use App\CapturedResult;
use App\Observers\CapturedObserver;
use App\InventorySubCategories;
use App\Observers\ItemObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\SupplierObserver;
use App\RequestEntity;
use App\Supplier;
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
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        
        $mainPath = database_path('migrations');
        $directories = glob($mainPath . '/*' , GLOB_ONLYDIR);
        $paths = array_merge([$mainPath], $directories);
        
        $this->loadMigrationsFrom($paths);
        
        CapturedResult::observe(CapturedObserver::class);
        Supplier::observe(SupplierObserver::class);
        InventorySubCategories::observe(ItemObserver::class);
        RequestEntity::observe(PurchaseOrderObserver::class);
    }
}
