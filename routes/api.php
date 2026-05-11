<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Portal\PortalAccessRequestController;

$ticketSyncController = 'App\\Http\\Controllers\\Api\\TicketSyncController';
$developerWebhookController = 'App\\Http\\Controllers\\Api\\DeveloperWebhookController';

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/kcb/receive', 'KCBIntegrationController@receivepayment')->name('receive-payment');

// Include Knowledge Assistant routes (Phase 2 integration)
require __DIR__ . '/api/knowledge.php';

// AI Predictions API
Route::prefix('ai')->group(function () {
    Route::post('/predictions/tat', 'AIPredictionsController@predictTAT')->name('ai.predict.tat');
    Route::post('/predictions/maintenance', 'AIPredictionsController@predictMaintenance')->name('ai.predict.maintenance');
    Route::get('/health', 'AIPredictionsController@health')->name('ai.health');
});

// Ticket/developer sync routes are optional in this branch. Only register
// them when the backing controllers are available.
if (class_exists($ticketSyncController, false)) {
    Route::post('/tickets/sync', 'Api\TicketSyncController@sync')
        ->middleware('api.key')
        ->name('api.tickets.sync');
}

if (class_exists($developerWebhookController, false)) {
    Route::post('/webhooks/developer', 'Api\DeveloperWebhookController@handleWebhook')
        ->middleware('verify.developer.webhook')
        ->name('api.webhooks.developer');
}

Route::get('/translations', [\App\Http\Controllers\Api\TranslationController::class, 'index'])
    ->name('api.translations.index');

// Submission request API endpoints
Route::get('/submission-request/parameters', [\App\Http\Controllers\Api\SubmissionRequestController::class, 'getParametersWithPricing'])
    ->name('api.submission-request-parameters');

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('access-requests', [PortalAccessRequestController::class, 'store'])->middleware('throttle:60,1');
    });
});
// Customer Portal API routes (auth + test requests)
// Note: portal.php was removed; portal auth now lives in web routes (TOTP flow).
if (file_exists(__DIR__ . '/api/portal.php')) {
    require __DIR__ . '/api/portal.php';
}
