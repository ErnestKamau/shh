<?php

namespace App\Providers;

use App\CapturedResult;
use App\Observers\CapturedObserver;
use App\InventorySubCategories;
use App\Observers\ItemObserver;
use App\Observers\PurchaseOrderObserver;
use App\Observers\SupplierObserver;
use App\Observers\TrackSampleResultObserver;
use App\RequestEntity;
use App\Supplier;
use App\Models\CRM\Complaint;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Models\TrackSampleResult;
use App\Observers\SampleSubmissionRequestObserver;
use App\Observers\SubmissionFormInstanceObserver;
use App\Observers\TicketObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use App\Livewire\Personnel\Zones\ConfigurationManager as PersonnelZonesConfigurationManager;
use App\Livewire\Personnel\Zones\Manager as PersonnelZonesManager;
use App\Livewire\Crm\Customer\Tabs\CustomerContractsTab;

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
            if (
                Schema::hasTable('inventory_sub_categories')
                && Schema::hasColumn('inventory_sub_categories', 'location_id')
            ) {
                $locationType = DB::table('information_schema.columns')
                    ->where('table_schema', 'public')
                    ->where('table_name', 'inventory_sub_categories')
                    ->where('column_name', 'location_id')
                    ->value('udt_name');

                if (in_array($locationType, ['int2', 'int4', 'int8'], true)) {
                    DB::statement('ALTER TABLE inventory_sub_categories ALTER COLUMN location_id TYPE varchar(255) USING location_id::varchar');
                }
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('sampling_schedules') && !Schema::hasColumn('sampling_schedules', 'is_collected')) {
                Schema::table('sampling_schedules', function ($table) {
                    $table->boolean('is_collected')->default(false);
                });
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('companies') && ! Schema::hasColumn('companies', 'code')) {
                Schema::table('companies', function ($table) {
                    $table->string('code', 32)->nullable()->unique();
                });
            }
        } catch (\Throwable $e) {}

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Blade::if('companyCode', function (string|\App\Enums\CompanyCode ...$codes): bool {
            return companyHasCode(...$codes);
        });
        
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
        TrackSampleResult::observe(TrackSampleResultObserver::class);
        Supplier::observe(SupplierObserver::class);
        InventorySubCategories::observe(ItemObserver::class);
        RequestEntity::observe(PurchaseOrderObserver::class);
        Complaint::observe(TicketObserver::class);
        SampleSubmissionRequest::observe(SampleSubmissionRequestObserver::class);
        SubmissionFormInstance::observe(SubmissionFormInstanceObserver::class);

        // Keep old and current aliases stable while classes live under Personnel\Zones.
        Livewire::component('zone-configuration-manager', PersonnelZonesConfigurationManager::class);
        Livewire::component('personnel.zones.zone-configuration-manager', PersonnelZonesConfigurationManager::class);
        Livewire::component('personnel.zones.configuration-manager', PersonnelZonesConfigurationManager::class);

        Livewire::component('zone-manager', PersonnelZonesManager::class);
        Livewire::component('personnel.zones.zone-manager', PersonnelZonesManager::class);
        Livewire::component('personnel.zones.manager', PersonnelZonesManager::class);

        // Explicit alias: namespace is App\Livewire\Crm\* while directory is app/Livewire/CRM
        // (case mismatch breaks PSR-4 autoload on Linux without a classmap dump).
        Livewire::component('crm.customer.tabs.customer-contracts-tab', CustomerContractsTab::class);
    }
}
