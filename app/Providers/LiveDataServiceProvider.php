<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LiveData\LiveDataQueryService;
use App\Services\LiveData\Contracts\IntentDetectorInterface;
use App\Services\LiveData\Contracts\FormatterInterface;
use App\Services\LiveData\Detection\IntentDetector;
use App\Services\LiveData\Formatting\MarkdownFormatter;
use App\Services\LiveData\Handlers\SampleHandler;
use App\Services\LiveData\Handlers\InventoryHandler;
use App\Services\LiveData\Handlers\EquipmentHandler;
use App\Services\LiveData\Handlers\QualityHandler;

class LiveDataServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 1. Register simple services
        $this->app->singleton(IntentDetectorInterface::class, IntentDetector::class);
        $this->app->singleton(FormatterInterface::class, MarkdownFormatter::class);

        // 2. Register and tag handlers
        $this->app->singleton(SampleHandler::class);
        $this->app->singleton(InventoryHandler::class);
        $this->app->singleton(EquipmentHandler::class);
        $this->app->singleton(QualityHandler::class);

        $this->app->tag([
            SampleHandler::class,
            InventoryHandler::class,
            EquipmentHandler::class,
            QualityHandler::class,
        ], 'live_data_handlers');

        // 3. Register Coordinator with tagged handlers
        $this->app->singleton(LiveDataQueryService::class, function ($app) {
            return new LiveDataQueryService(
                $app->make(IntentDetectorInterface::class),
                $app->tagged('live_data_handlers')
            );
        });
    }

    public function boot()
    {
        //
    }
}
