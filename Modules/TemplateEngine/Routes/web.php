<?php

use Illuminate\Support\Facades\Route;
use Modules\TemplateEngine\Http\Controllers\TemplateBuilderController;
use Modules\TemplateEngine\Http\Controllers\TemplateFieldsController;
use Modules\TemplateEngine\Http\Controllers\TemplateDataController;

Route::prefix('form-templates')->name('templates.')->middleware(['auth'])->group(function () {
    // Template CRUD
    Route::get('/', [TemplateBuilderController::class, 'index'])->name('index');
    Route::get('/create', [TemplateBuilderController::class, 'create'])->name('create');
    Route::post('/', [TemplateBuilderController::class, 'store'])->name('store');
    Route::delete('/{id}', [TemplateBuilderController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/edit', [TemplateBuilderController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TemplateBuilderController::class, 'update'])->name('update');
    
    // Builder
    Route::get('/{id}/builder', [TemplateBuilderController::class, 'builder'])->name('builder');
    
    // Preview
    Route::get('/{id}/preview', [TemplateBuilderController::class, 'preview'])->name('preview');
    
    // Submissions
    Route::get('/{template}/submissions/{submission}', [TemplateBuilderController::class, 'showSubmission'])->name('submissions.show');

    // Field API (AJAX/Fetch)
    Route::post('/sections/{section}/fields', [TemplateFieldsController::class, 'store'])->name('fields.store');
    Route::put('/fields/{field}', [TemplateFieldsController::class, 'update'])->name('fields.update');
    Route::delete('/fields/{field}', [TemplateFieldsController::class, 'destroy'])->name('fields.destroy');
});

// Data API for Dynamic Fields
Route::prefix('template-data')->name('template-data.')->middleware(['auth'])->group(function () {
    Route::get('/tables', [TemplateDataController::class, 'getTables'])->name('tables');
    Route::get('/tables/{table}/columns', [TemplateDataController::class, 'getColumns'])->name('columns');
});


