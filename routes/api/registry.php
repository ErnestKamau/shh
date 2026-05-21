<?php

use Illuminate\Support\Facades\Route;

Route::prefix('registry')->name('api.registry.')->middleware(['auth'])->group(function () {
    Route::get('/requests', 'Api\Registry\RegistryApiController@index')->name('requests.index');
    Route::post('/requests', 'Api\Registry\RegistryApiController@store')->name('requests.store');
    Route::get('/requests/{id}', 'Api\Registry\RegistryApiController@show')->name('requests.show');
    Route::put('/requests/{id}', 'Api\Registry\RegistryApiController@update')->name('requests.update');
    Route::post('/requests/{id}/approve', 'Api\Registry\RegistryApiController@approve')->name('requests.approve');
    Route::post('/requests/{id}/reject', 'Api\Registry\RegistryApiController@reject')->name('requests.reject');
    Route::post('/requests/{id}/assign', 'Api\Registry\RegistryApiController@assign')->name('requests.assign');
    Route::post('/requests/{id}/documents', 'Api\Registry\RegistryApiController@uploadDocument')->name('requests.documents');
    Route::get('/dashboard/statistics', 'Api\Registry\RegistryApiController@statistics')->name('dashboard.statistics');
});
