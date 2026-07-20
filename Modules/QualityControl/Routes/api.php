<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes (deprecated stub)
|--------------------------------------------------------------------------
|
| No public QC API is exposed from this module. Keep this file empty of
| meaningful routes until a real versioned API is designed in App.
|
*/

Route::middleware('auth:api')->get('/qualitycontrol', function (Request $request) {
    abort(404);
});
