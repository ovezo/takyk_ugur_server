<?php


use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('import.stops');
});
Route::get('/map', function(){
    return view('map');
});
Route::post('/import-stops',[App\Http\Import\Controllers\ImportController::class, 'importStops']);
Route::get('/payment-result/{id}',[App\Http\API\Controllers\Payments\PaymentController::class, 'payment_result']);
Route::get('/import-buses',[App\Http\Import\Controllers\ImportController::class, 'importGps']);
Route::get('/bus-info',[App\Http\Import\Controllers\ImportController::class, 'bus_info']);

//set to cache
Route::get('/setEverythingInCache',[App\Http\Import\Controllers\ImportController::class, 'setEverythingInCache']);
Route::get('/getEverything',[App\Http\Import\Controllers\ImportController::class, 'getEverything']);

Route::get('/test-stop',[App\Http\Import\Controllers\ImportController::class, 'long']);

Route::get('activate_coords', [App\Http\Import\Controllers\ImportController::class, 'activate_coords']);
Route::get('draw-coords', [App\Http\Controllers\MapController::class, 'show']);
Route::get('/show-m/{id}', [App\Http\Controllers\MapController::class, 'showm']);

