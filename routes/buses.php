<?php

use App\Http\API\Controllers\Buses\BusController;
use Illuminate\Support\Facades\Bus;

/*
|--------------------------------------------------------------------------
| STOPS - API Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'middleware' => ['localization','auth:api'],
    'namespace' => 'App\Http\API\Controllers\Buses'
], function () {
   // Route::get('/', [BusController::class, 'buses']);
   // Route::post('/create', [BusController::class, 'create']);
    Route::get('/get-bus-time', [BusController::class, 'getBusTime']);
});

Route::group([
    'middleware' => ['localization','auth:api', 'tarif_check'],
    'namespace' => 'App\Http\API\Controllers\Buses'
], function () {

    Route::get('/', [BusController::class, 'buses']);
});
