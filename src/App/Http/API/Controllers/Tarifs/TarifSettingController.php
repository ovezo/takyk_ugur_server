<?php

namespace App\Http\API\Controllers\Tarifs;

use App\Http\API\Resources\TarifCollectionResource;
use App\Http\API\Resources\TarifSettingResource;
use App\Http\API\Resources\UserResource;
use App\Http\Controller;
use Domain\Tarifs\Models\TarifSetting;
use Illuminate\Http\Request;
use Auth;

class TarifSettingController extends Controller
{

    public function tarif_settings(Request $request){

        $tarif_settings = TarifSetting::where('user_id', Auth::guard('api')->user()->id)->first();
        if($tarif_settings){

           if($request->has('banner_status')) $tarif_settings->banner_status = $request->banner_status;
           if($request->has('places_status')) $tarif_settings->places_status = $request->places_status;
           if($request->has('notified_stops')) $tarif_settings->notified_stops = $request->notified_stops;
           if($request->has('notified_mins')) $tarif_settings->notified_mins = $request->notified_mins;
           $tarif_settings->save();

           return TarifSettingResource::make($tarif_settings);
        }else{
   	   return "User doesn't have tarif setting";
        }
        
    }

}
