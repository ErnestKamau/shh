<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('qualitycontrol')->group(function() {
    Route::get('/', 'QualityControlController@index');
    Route::get('/', 'QualityControlController@index')->name('qc_index');
    Route::get('/configuration-index','QualityControlController@configuration_index')->name('qc_configuration_index');

    Route::post('/delete/Qc-Types','QualityControlController@deleteQCTypes')->name('qc_deleteQCTypes');
    Route::post('/create/Qc-Types','QualityControlController@createQcTypes')->name('qc_createQcTypes');

    Route::post('/add/Qc-Standard','QualityControlController@addQcStandard')->name('qc_addQcStandard');
    Route::post('/delete/Qc-Standard','QualityControlController@deleteQcStandard')->name('qc_deleteQcStandard');

    Route::get('/qc-standard/show/{id}','QualityControlController@qcStandardShow')->name('qc_StandardShow');
    Route::post('/add/Qc-Standard/Analyte','QualityControlController@addQcStandardAnalyte')->name('qc_addQcStandardAnalyte');
    Route::post('/delete/Qc-Standard/Analyte','QualityControlController@deleteQcStandardAnalyte')->name('qc_deleteQcStandardAnalyte');

    Route::post('/Maintain-Qc-Schemes','QualityControlController@MaintainQcSchemes')->name('MaintainQcSchemes');
    Route::post('/Delete-Qc-Schemes','QualityControlController@DeleteQcSchemes')->name('DeleteQcSchemes');

});
