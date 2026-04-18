<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Portal\AuthController;
use App\Http\Controllers\Api\Portal\ReferenceDataController;
use App\Http\Controllers\Api\Portal\TestRequestController;
use App\Http\Controllers\Api\Portal\SupportingDocumentController;

/*
|--------------------------------------------------------------------------
| Customer Portal API Routes
|--------------------------------------------------------------------------
|
| These routes serve the external customer portal (Vue/Nuxt.js frontend).
| Authenticated routes require a Sanctum Bearer token obtained via the
| two-step login flow (POST /auth/login → POST /auth/verify-2fa).
|
| All routes are prefixed with /api/portal via the require in routes/api.php
|
*/

Route::prefix('portal')->group(function () {

    // -------------------------------------------------------------------------
    // Authentication — public (no token needed)
    // -------------------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        // Step 1: validate credentials → trigger 2FA code email/SMS
        Route::post('/login', [AuthController::class, 'login']);

        // Step 2: submit 2FA code → receive Bearer token
        Route::post('/verify-2fa', [AuthController::class, 'verifyTwoFactor']);

        // Resend 2FA code (rate-limited: 5 attempts/min)
        Route::middleware('throttle:5,1')->post('/resend-2fa', [AuthController::class, 'resendTwoFactor']);
    });

    // -------------------------------------------------------------------------
    // Authenticated routes — require valid Sanctum token + is_client = 1
    // -------------------------------------------------------------------------
    Route::middleware(['auth:sanctum', 'portal.client'])->group(function () {

        // Auth helpers
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Reference data — used to populate form dropdowns in the portal
        Route::prefix('reference')->group(function () {
            Route::get('/sample-types', [ReferenceDataController::class, 'sampleTypes']);
            Route::get('/analysis-types', [ReferenceDataController::class, 'analysisTypes']);
            Route::get('/sample-conditions', [ReferenceDataController::class, 'sampleConditions']);
            Route::get('/sample-points', [ReferenceDataController::class, 'samplePoints']);
            Route::get('/units', [ReferenceDataController::class, 'units']);
            Route::get('/contacts', [ReferenceDataController::class, 'contacts']);
        });

        // Test requests (Samples En-Route batches)
        Route::prefix('test-requests')->group(function () {
            Route::get('/', [TestRequestController::class, 'index']);
            Route::post('/', [TestRequestController::class, 'store']);
            Route::get('/{id}', [TestRequestController::class, 'show']);
            Route::patch('/{id}/cancel', [TestRequestController::class, 'cancel']);

            // Supporting documents attached to a test request
            Route::get('/{id}/supporting-documents', [SupportingDocumentController::class, 'instancesForTestRequest']);
            Route::post('/{id}/supporting-documents/submit', [SupportingDocumentController::class, 'submitForTestRequest']);
        });

        // Supporting document templates (published + active)
        Route::get('/supporting-document-templates', [SupportingDocumentController::class, 'templates']);
        Route::get('/supporting-document-templates/{template}', [SupportingDocumentController::class, 'template']);
    });
});
