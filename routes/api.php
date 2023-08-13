<?php

use App\Http\Admin\Controllers\RouteGeoDataController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use function GuzzleHttp\json_decode;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/draw_post', [RouteGeoDataController::class, 'draw_post']);


Route::group([
    'middleware' =>  ['localization', 'auth:api'],
], function () {

    Route::get('/banners', [App\Http\API\Controllers\Banners\BannerController::class, 'banners']);
    Route::resource('/places','App\Http\API\Controllers\Places\PlaceController');
    Route::post('/add/place/{id}/favorites',[App\Http\API\Controllers\Favorites\PlaceFavoritesController::class, 'add_or_remove']);
    Route::post('/add/stop/{id}/favorites',[App\Http\API\Controllers\Favorites\StopFavoritesController::class, 'add_or_remove']);
    Route::post('/add/route/{id}/favorites',[App\Http\API\Controllers\Favorites\RouteFavoritesController::class, 'add_or_remove']);

    Route::post('/list/stop/favorites', [App\Http\API\Controllers\Favorites\StopFavoritesController::class, 'favorites']);
    Route::post('/list/route/favorites', [App\Http\API\Controllers\Favorites\RouteFavoritesController::class, 'favorites']);
    Route::post('/list/place/favorites', [App\Http\API\Controllers\Favorites\PlaceFavoritesController::class, 'favorites']);

//Route::get('test_fcm_production', [App\Http\API\Controllers\UserNotification\UserNotificationController::class, 'send_notification']);
    Route::get('test_fcm/{token}', [App\Http\API\Controllers\UserNotification\UserNotificationController::class, 'test_notification']);
});


Route::get('osrm', function(){
    //get osrm-peshyi-hod
    $latitudeFrom = '37.9175274';
    $longitudeFrom = '58.4270839';
    $latitudeTo = '37.917446';
    $longitudeTo = '58.434905';
    $uri = "http://localhost:5000/route/v1/foot/" .
        $longitudeFrom . ',' . $latitudeFrom . ';'. $longitudeTo . ',' . $latitudeTo .
        '?overview=false&alternatives=true&steps=true&hints=HfAAgE_wAIAXAAAAMAAAADgAAAAAAAAAV0wZQc2JoEG9F7lBAAAAABcAAAAwAAAAOAAAAAAAAAApAAAAYJB7A8CTQgJCkXsDoJNCAgEA7wvjQRwm;2-8AgHzwAIAcAAAAJQAAAAAAAAAAAAAAhzGcQUokzkEAAAAAAAAAABwAAAAlAAAAAAAAAAAAAAApAAAA2YJ7A-GUQgLcgnsDP5VCAgAAvwXjQRwm';
    $client = new Client();
    $response = $client->request('GET', $uri);
    $data = json_decode($response->getBody(), true);
    return $data;

});
