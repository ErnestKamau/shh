<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/kcb/receive','KCBIntegrationController@receivepayment')->name('receive-payment');

// API routes for custom element modals
Route::get('/clients', 'Api\ClientController@index');
Route::post('/clients', 'Api\ClientController@store');

Route::get('/client-units', 'Api\ClientUnitController@index');
Route::post('/client-units', 'Api\ClientUnitController@store');

Route::get('/company-sub-units', 'Api\CompanySubUnitController@index');
Route::post('/company-sub-units', 'Api\CompanySubUnitController@store');

Route::get('/client-contacts', 'Api\ClientContactController@index');
Route::post('/client-contacts', 'Api\ClientContactController@store');

Route::get('/sample-points', 'Api\SamplePointController@index');
Route::post('/sample-points', 'Api\SamplePointController@store');

Route::get('/sample-conditions', 'Api\SampleConditionController@index');
Route::post('/sample-conditions', 'Api\SampleConditionController@store');

Route::get('/sample-types', 'Api\SampleTypeController@index');

// Supporting data endpoints for client modal
Route::get('/countries', 'Api\CountryController@index');
Route::get('/account-settings', 'Api\AccountSettingsController@index');
Route::get('/zoho-customers', 'Api\ZohoCustomerController@index');

