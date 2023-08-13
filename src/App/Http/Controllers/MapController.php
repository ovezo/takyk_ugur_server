<?php

namespace App\Http\Controllers;

use App\Http\Controller;
use Domain\Routes\Models\Route;
use Domain\Routes\Models\Mroute;
use Illuminate\Http\Request;


class MapController extends Controller
{
    public function show(Request $request){
        $route = Route::find($request->id);
        $array = [];
        if($request->type == "front"){
            $coords = $route->front_line->toArray();

            foreach ($coords['coordinates'] as $coords){
                $new_array = [];
                array_push($new_array,$coords['1']);
                array_push($new_array,$coords['0']);
                array_push($array, $new_array);
            }
        }
        else{
            $coords = $route->back_line->toArray();
            foreach ($coords['coordinates'] as $coords){
                $new_array = [];
                array_push($new_array,$coords['1']);
                array_push($new_array,$coords['0']);
                array_push($array, $new_array);
            }
        }
        return view('show', [
            'array' => $array
        ]);
    }
    public function showm($id, Request $request){

        $route = Mroute::find($id);
        $array = [];
        if($request->type == "front"){
            foreach (json_decode($route->start_coords) as $coords){
                $new_array = [];
                array_push($new_array,$coords->lat);
                array_push($new_array,$coords->lng);
                array_push($array, $new_array);
            }
        }
        else{
            foreach (json_decode($route->end_coords) as $coords){
                $new_array = [];
                array_push($new_array,$coords->lat);
                array_push($new_array,$coords->lng);
                array_push($array, $new_array);
            }
        }
        return view('show', [
            'array' => $array
        ]);
    }
}
