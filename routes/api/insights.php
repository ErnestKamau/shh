<?php

use App\Http\Controllers\AI\AiInsightsController;
use App\Http\Controllers\AI\AiGovernanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AI Insights Routes
|--------------------------------------------------------------------------
| Per-entity insights (single predictions with optional Qwen reasoning).
| Batch predictions allow up to 50 mixed-type entities in one request.
*/
Route::middleware('auth:api')->prefix('ai/insights')->name('ai.insights.')->group(function () {
    Route::get('/samples/{id}',   [AiInsightsController::class, 'sample'])->name('samples.show');
    Route::get('/equipment/{id}', [AiInsightsController::class, 'equipment'])->name('equipment.show');
    Route::get('/qc/{id}',        [AiInsightsController::class, 'qc'])->name('qc.show');

    // Batch: POST /api/ai/insights/batch
    Route::post('/batch', [AiInsightsController::class, 'batch'])->name('batch');
});

/*
|--------------------------------------------------------------------------
| AI Governance Routes
|--------------------------------------------------------------------------
| Model registry, performance metrics, drift status, and manual retraining.
*/
Route::middleware('auth:api')->prefix('ai/governance')->name('ai.governance.api.')->group(function () {
    Route::get('/models',                          [AiGovernanceController::class, 'listModels'])->name('models.index');
    Route::get('/models/{id}/performance',         [AiGovernanceController::class, 'modelPerformance'])->name('models.performance');
    Route::get('/dashboard/ml-metrics',            [AiGovernanceController::class, 'mlDashboard'])->name('dashboard.ml-metrics');

    // Drift status across all active models
    Route::get('/drift',                           [AiGovernanceController::class, 'driftStatus'])->name('drift.status');

    // Trigger manual retraining for a specific model type
    // model_type: tat_prediction | equipment_maintenance | qc_anomaly
    Route::post('/models/{modelType}/retrain',     [AiGovernanceController::class, 'triggerRetraining'])->name('models.retrain');
});

/*
|--------------------------------------------------------------------------
| Prometheus scrape endpoint
|--------------------------------------------------------------------------
| No auth middleware — access is restricted at the network level (Docker
| internal network only; this path is never exposed to the public Internet).
*/
Route::get('/ai/metrics', [AiGovernanceController::class, 'prometheusMetrics'])->name('ai.metrics.prometheus');
