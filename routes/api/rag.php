<?php

/**
 * RAG API Routes
 * 
 * Include in your routes/api.php:
 * require __DIR__ . '/api/rag.php';
 */

use App\Http\Controllers\API\AI\RagChunkController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('rag')->name('rag.')->group(function () {
    // Chunk management
    Route::apiResource('chunks', RagChunkController::class);

    // Search endpoint
    Route::post('search', [RagChunkController::class, 'search'])->name('search');

    // Bulk operations
    Route::post('bulk-import', [RagChunkController::class, 'bulkImport'])->name('bulk-import');

    // Statistics and info
    Route::get('statistics', [RagChunkController::class, 'statistics'])->name('statistics');
    Route::get('collections', [RagChunkController::class, 'collections'])->name('collections');
});
