<?php

namespace App\Http\API\Controllers\Places;

use App\Http\API\Resources\PlaceCollectionResource;
use App\Http\Controller;
use Illuminate\Http\Request;
use Domain\Places\Models\Place;
use App\Http\API\Resources\PlaceResource;
use Auth;

class PlaceController extends Controller
{
    public function index(){

	//uncomment for apply payment
        //$places = [];
        //if( user_has_tarif() ){
        //    if(isset(Auth::guard('api')->user()->tarif_settings->places_status) && Auth::guard('api')->user()->tarif_settings->places_status)
            $places = Place::whereDate('to_date', '>=', \Carbon\Carbon::now()->format('Y-m-d'))->get();

            return PlaceCollectionResource::collection($places);

        //}else{

        //    $places = Place::whereDate('to_date', '>=', \Carbon\Carbon::now()->format('Y-m-d'))->get();
        //    return PlaceCollectionResource::collection($places);
       // }

    }

    public function show($id){

       $place = Place::find($id);

       return PlaceResource::make($place);
    }
}
