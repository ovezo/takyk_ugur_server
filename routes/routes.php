<?php

use App\Http\API\Controllers\Routes\RoutesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROUTES - API Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'middleware' => ['localization', 'auth:api'],
    'namespace' => 'App\Http\API\Controllers\Routes'
], function () {
    Route::get('/', [RoutesController::class, 'routes']);
    Route::get('/route/{id}', [RoutesController::class, 'route']);
    Route::get('/getTimeLine', [RoutesController::class, 'getTimeLine']);
    //Route::get('/getCustom', [RoutesController::class, 'getcustomdistance']);

});

Route::group([
    'middleware' => ['localization', 'auth:api','tarif_check'],
    'namespace' => 'App\Http\API\Controllers\Routes'
], function () {
    //Route::get('/route/{id}', [RoutesController::class, 'route']);
   // Route::get('/getTimeLine', [RoutesController::class, 'getTimeLine']);
    Route::get('/getCustom', [RoutesController::class, 'getcustomdistance']);

});
