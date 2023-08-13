<?php

use App\Http\API\Controllers\Tarifs\TarifController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['localization', 'auth:api'],
], function () {
    Route::get('/', [App\Http\API\Controllers\Tarifs\TarifController::class, 'tarifs']);
    Route::post('/payment',[App\Http\API\Controllers\Payments\PaymentController::class, 'tarif_payment']);
    Route::get('/payments',[App\Http\API\Controllers\Payments\PaymentController::class, 'payments']);
    Route::post('/settings',[App\Http\API\Controllers\Tarifs\TarifSettingController::class, 'tarif_settings']);
    //Route::resource('notification', 'App\Http\API\Controllers\UserNotification\UserNotificationController');
    //Route::post('get_stop_qty', [App\Http\API\Controllers\UserNotification\UserNotificationController::class, 'check_stops']);

});

Route::group([
    'middleware' => ['localization', 'auth:api', 'tarif_check'],
], function () {
    //Route::post('/settings',[App\Http\API\Controllers\Tarifs\TarifSettingController::class, 'tarif_settings']);
    Route::resource('notification', 'App\Http\API\Controllers\UserNotification\UserNotificationController');
    Route::post('get_stop_qty', [App\Http\API\Controllers\UserNotification\UserNotificationController::class, 'check_stops']);
});


//test notificate user
Route::post('notificate_user', [App\Http\API\Controllers\UserNotification\UserNotificationController::class, 'notificate_user']);

