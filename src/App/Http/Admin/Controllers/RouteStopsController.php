<?php

namespace App\Http\Admin\Controllers;

use App\Http\Controller;
use Domain\Routes\Models\EndrouteStop;
use Domain\Routes\Models\RouteStop;

class RouteStopsController extends Controller{

    public function sort($id){
        $route_stops = RouteStop::with('stop')->where('route_id', $id)->get();
        return view('admin.order',[
            'route_stops' => $route_stops,
            'id' => $id
        ]);
    }

    public function sortback($id){
        $route_stops = EndrouteStop::with('stop')->where('route_id', $id)->get();
        return view('admin.orderback',[
            'route_stops' => $route_stops,
            'id' => $id
        ]);
    }

}
