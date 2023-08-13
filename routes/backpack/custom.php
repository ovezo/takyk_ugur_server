<?php

use Illuminate\Support\Facades\Route;
use App\Http\Admin\Controllers\RouteStopsController;
use App\Http\Admin\Controllers\RouteGeoDataController;
use App\Http\Admin\Controllers\StopGeoDataController;
use App\Http\API\Controllers\Stops\StopController;
use App\Http\Import\Controllers\ImportController;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Admin\Controllers',
], function () { // custom admin routes
    Route::crud('stop', 'StopCrudController');
    Route::crud('route', 'RouteCrudController');
    Route::crud('route-stop', 'RouteStopCrudController');
    Route::crud('endroute-stop', 'EndrouteStopCrudController');
    Route::crud('bus', 'BusCrudController');
    Route::crud('active-bus', 'ActiveBusCrudController');
    Route::crud('banner', 'BannerCrudController');
    Route::crud('place-categories', 'PlaceCategoriesCrudController');
    Route::crud('place', 'PlaceCrudController');
    Route::crud('place-route', 'PlaceRouteCrudController');
    Route::crud('tarif', 'TarifCrudController');
    Route::crud('payment', 'PaymentCrudController');
    Route::crud('apk-trial', 'ApkTrialCrudController');
    Route::crud('tarif-setting', 'TarifSettingCrudController');
    Route::crud('place-favorites', 'PlaceFavoritesCrudController');
    Route::crud('stop-favorites', 'StopFavoritesCrudController');
    Route::crud('route-favorites', 'RouteFavoritesCrudController');
    Route::crud('mroute', 'MrouteCrudController');
    Route::crud('user-notification', 'UserNotificationCrudController');
    Route::crud('tarif-notification', 'TarifNotificationCrudController');
    Route::crud('route-history', 'RouteHistoryCrudController');
    //Route::crud('user', 'UserCrudController');
}); // this should be the absolute last line of this file
