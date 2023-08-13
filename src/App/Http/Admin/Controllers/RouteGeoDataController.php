<?php

namespace App\Http\Admin\Controllers;

use App\Http\Controller;
use Domain\Routes\Models\Route;
use Domain\Stops\Models\Stop;
use Illuminate\Http\Request;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;

class RouteGeoDataController extends Controller
{
    public function draw_coords(Request $request){
        return view('admin.draw',[
            'type' => $request->type,
            'id' => $request->id
        ]);
    }

    public function draw_post(Request $request){
        $data = $request->all();
        $route = Route::find($request->id);
        if($route){
            $geoarray = [];
            foreach($data['obj']['coordinates'] as $geodata){
                $lat = $geodata[0];
                $lng = $geodata[1];
                $point = new Point($lat, $lng, 4326);
                array_push($geoarray, $point);
            }
            if($data['type'] == 'front'){
                $route->front_line = new LineString($geoarray);
            }
            else{
                $route->back_line = new LineString($geoarray);
            }
            $route->save();
        }
        return $route;
    }

    public function getRoute(){
        return Route::get();
    }
}
