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
use App\Models\CRM\Complaint;
use App\Models\SampleSubmissionRequest;
use App\Observers\TicketObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use App\Livewire\Personnel\Zones\ConfigurationManager as PersonnelZonesConfigurationManager;
use App\Livewire\Personnel\Zones\Manager as PersonnelZonesManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        config(['audit.enabled' => false]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            if (!file_exists(public_path('storage'))) {
                @symlink(storage_path('app/public'), public_path('storage'));
            }
        } catch (\Throwable $e) {}

        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE inventory_sub_categories ALTER COLUMN location_id TYPE varchar(255) USING location_id::varchar');
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('sampling_schedules') && !Schema::hasColumn('sampling_schedules', 'is_collected')) {
                Schema::table('sampling_schedules', function ($table) {
                    $table->boolean('is_collected')->default(false);
                });
            }
        } catch (\Throwable $e) {}

        try {
            $configs = [
                'sys_theme_primary_color' => '#6D0A0E',
                'sys_theme_secondary_color' => '#8B1E22',
                'sys_theme_accent_color' => '#ffffff',
                'sys_sidebar_bg_color' => '#1A1D24',
                'sys_sidebar_link_bg' => 'rgba(255, 255, 255, 0.08)',
            ];
            $type = \App\Models\System\SystemConfigurationsType::where('configuration_type', 'Global System Theme Settings')->first();
            if ($type) {
                foreach ($configs as $key => $val) {
                    \App\Models\System\SystemConfiguration::updateOrCreate(
                        ['key' => $key],
                        [
                            'configuration_type_id' => $type->id,
                            'value' => $val,
                            'status' => true,
                        ]
                    );
                }
                \Illuminate\Support\Facades\Cache::forget('global_theme_variables');
            }
        } catch (\Throwable $e) {}

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        
        $mainPath = database_path('migrations');
        $directories = glob($mainPath . '/*' , GLOB_ONLYDIR);
        $paths = array_merge([$mainPath], $directories);
        
        $this->loadMigrationsFrom($paths);

        View::composer('layouts.lab.layout.app', function ($view): void {
            $submissionRequestTotals = 0;

            if (Schema::hasTable('sample_submission_requests')) {
                $submissionRequestTotals = SampleSubmissionRequest::query()
                    ->where(function ($query) {
                        $query->whereNull('status')
                            ->orWhere('status', '!=', 'received_at_lab');
                    })
                    ->count();
            }

            $view->with('submissionRequestTotals', $submissionRequestTotals);
        });
        
        CapturedResult::observe(CapturedObserver::class);
        Supplier::observe(SupplierObserver::class);
        InventorySubCategories::observe(ItemObserver::class);
        RequestEntity::observe(PurchaseOrderObserver::class);
        Complaint::observe(TicketObserver::class);

        // Keep old and current aliases stable while classes live under Personnel\Zones.
        Livewire::component('zone-configuration-manager', PersonnelZonesConfigurationManager::class);
        Livewire::component('personnel.zones.zone-configuration-manager', PersonnelZonesConfigurationManager::class);
        Livewire::component('personnel.zones.configuration-manager', PersonnelZonesConfigurationManager::class);

        Livewire::component('zone-manager', PersonnelZonesManager::class);
        Livewire::component('personnel.zones.zone-manager', PersonnelZonesManager::class);
        Livewire::component('personnel.zones.manager', PersonnelZonesManager::class);
    }
}
