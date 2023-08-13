<?php

use App\Http\API\Controllers\Stops\StopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| STOPS - API Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'middleware' =>  ['localization', 'auth:api'],
    'namespace' => 'App\Http\API\Controllers\Stops'
    ], function () {
    Route::get('/set-to-cache', [StopController::class, 'set_to_cache']);
    Route::get('/', [StopController::class, 'stops']);

});
