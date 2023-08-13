<?php

use App\Http\API\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'localization',
    'namespace' => 'App\Http\API\Controllers\Auth'
], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify_otp', [AuthController::class, 'verify_otp']);
});

Route::group([
    'middleware' => ['localization', 'auth:api'],
    'namespace' => 'App\Http\API\Controllers\Auth'
], function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('update_fcm_token', [AuthController::class, 'update_fcm_token']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-user', [AuthController::class, 'updateUser']);
});
