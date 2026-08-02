<?php

use App\Http\Controllers\Api\Portal\Crm\AmendmentController;
use App\Http\Controllers\Api\Portal\Crm\ComplaintController;
use App\Http\Controllers\Api\Portal\Crm\FeedbackController;
use App\Http\Controllers\Api\Portal\Crm\InvoiceController;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Gateway — CRM & Billing API
|--------------------------------------------------------------------------
|
| Authenticated via PORTAL_GATEWAY_API_KEY (Bearer token or X-Portal-Gateway-Key).
| Customer scoping: route {customer_id} MUST match X-CRM-Customer-Id header.
|
| Gateway traffic is server-to-server from a shared IP, so the default API
| throttle:60,1 would rate-limit the entire portal.
|
*/

Route::prefix('v1/portal')
    ->middleware(['portal.gateway'])
    ->withoutMiddleware([ThrottleRequests::class])
    ->group(function (): void {
        Route::get('feedback/metrics', [FeedbackController::class, 'metrics'])
            ->name('api.portal.feedback.metrics');
        Route::get('complaints/types', [ComplaintController::class, 'types'])
            ->name('api.portal.complaints.types');

        Route::prefix('{customer_id}')->group(function (): void {
            Route::get('feedback', [FeedbackController::class, 'index'])
                ->name('api.portal.feedback.index');
            Route::get('complaints', [ComplaintController::class, 'index'])
                ->name('api.portal.complaints.index');
            Route::post('complaints', [ComplaintController::class, 'store'])
                ->name('api.portal.complaints.store');

            Route::get('amendments', [AmendmentController::class, 'index'])
                ->name('api.portal.amendments.index');
            Route::get('amendments/eligible-reports', [AmendmentController::class, 'eligibleReports'])
                ->name('api.portal.amendments.eligible');
            Route::post('amendments', [AmendmentController::class, 'store'])
                ->name('api.portal.amendments.store');
            Route::get('reports/{batchId}/samples', [AmendmentController::class, 'samples'])
                ->name('api.portal.reports.samples');

            Route::post('feedback', [FeedbackController::class, 'store'])
                ->name('api.portal.feedback.store');
            Route::post('feedback/complete', [FeedbackController::class, 'complete'])
                ->name('api.portal.feedback.complete');

            Route::get('invoices', [InvoiceController::class, 'index'])
                ->name('api.portal.invoices.index');
            Route::get('invoices/{invoice_id}', [InvoiceController::class, 'show'])
                ->name('api.portal.invoices.show');

            Route::get('pricelist', [\App\Http\Controllers\Api\PricelistController::class, 'showForCustomer'])
                ->name('api.portal.pricelist.show');
        });
    });
