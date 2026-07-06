<?php

namespace App\Providers;

use App\Auth\SafeEloquentUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        // \App\Models\CertificateTemplate::class => \App\Policies\CertificateTemplatePolicy::class,
        \App\Models\Lab\EquipmentUsageRequest::class => \App\Policies\Lab\EquipmentUsageRequestPolicy::class,
        \App\Models\Registry\RegistryRequest::class => \App\Policies\RegistryRequestPolicy::class,
        \App\Models\DMS\Document::class => \App\Policies\DmsDocumentPolicy::class,
    ];

    /**
     * Portal dashboard authorization is enforced via {@see \App\Http\Requests\Api\Dashboard\DashboardCustomerRequest}
     * and {@see \App\Policies\DashboardPolicy} for programmatic checks.
     */

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::provider('safe-eloquent', function ($app, array $config) {
            return new SafeEloquentUserProvider($app['hash'], $config['model']);
        });

        $this->registerPolicies();

        //
    }
}
