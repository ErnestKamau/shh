<?php

namespace Modules\TemplateEngine\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class TemplateEngineServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'template-engine');
        $this->registerRoutes();
        $this->registerLivewireComponents();
        
        // Register observers
        \Modules\TemplateEngine\Models\TemplateSubmission::observe(\Modules\TemplateEngine\Observers\TemplateSubmissionObserver::class);
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/web.php');
    }

    protected function registerLivewireComponents(): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('template-engine::builder', \Modules\TemplateEngine\Http\Livewire\Builder::class);
            \Livewire\Livewire::component('template-engine::dataset-selector', \Modules\TemplateEngine\Http\Livewire\DatasetSelector::class);
            \Livewire\Livewire::component('template-engine::render-form', \Modules\TemplateEngine\Http\Livewire\RenderForm::class);
        }
    }
}
