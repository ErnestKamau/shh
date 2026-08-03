<?php

use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Gateway — Customer Dashboard API
|--------------------------------------------------------------------------
|
| Authenticated via PORTAL_GATEWAY_API_KEY (Bearer token or X-Portal-Gateway-Key).
| Customer scoping: route {customer_id} MUST match X-CRM-Customer-Id header.
|
| Gateway traffic is server-to-server from a shared IP, so the default API
| throttle:60,1 would rate-limit the entire portal. Portal.gateway auth is
| the protection boundary for these routes.
|
*/

Route::prefix('v1/dashboard')
    ->middleware(['portal.gateway'])
    ->withoutMiddleware([ThrottleRequests::class])
    ->group(function (): void {
        Route::get('{customer_id}', [DashboardController::class, 'show'])
            ->name('api.dashboard.show');

        Route::get('{customer_id}/analytics', [DashboardController::class, 'analytics'])
            ->name('api.dashboard.analytics');

        Route::get('{customer_id}/notifications', [DashboardController::class, 'notifications'])
            ->name('api.dashboard.notifications');

        Route::get('{customer_id}/reports', [DashboardController::class, 'reports'])
            ->name('api.dashboard.reports');

        Route::get('{customer_id}/complaints', [DashboardController::class, 'complaints'])
            ->name('api.dashboard.complaints');
    });
