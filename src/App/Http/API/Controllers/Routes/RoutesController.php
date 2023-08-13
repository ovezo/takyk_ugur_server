<?php

namespace App\Http\API\Controllers\Routes;

use App\Http\API\Resources\BusTimeLineResource;
use App\Http\API\Resources\RouteListResource;
use App\Http\API\Resources\RouteResource;
use App\Http\Controller;
use App\Http\API\Resources\SpecificRouteResource;
use Domain\Routes\Models\RouteHistory;
use Domain\Stops\Models\Stop;
use Domain\Routes\Models\Route;
use Domain\Routes\Models\RouteStop;
use Domain\Routes\Models\EndrouteStop;
use Domain\Buses\Models\Bus;
use GuzzleHttp\Client;
use Domain\Routes\Models\Mroute;
use Illuminate\Http\Request;
use MatanYadaev\EloquentSpatial\Objects\Point;
use function GuzzleHttp\json_decode;
use Auth;

class RoutesController extends Controller{

    public function routes(Request $request){

        /**
         * @OA\GET(6
         *   path="/api/routes",
         *   summary=" - Get list of stops",
         *   tags = {"Routes"},
         *   @OA\Response(
         *         response=201,
         *         description="OK",
         *   ),
         * )
         */
        if($request->stop_id){

            $stop = Stop::with('routes')->with('endroutes')->find($request->stop_id);
            $routes = $stop->routes;
            $endroutes = $stop->endroutes;
            $routes = $routes->toArray();
            $endroutes = $endroutes->toArray();
            foreach($routes as $route){
                foreach($endroutes as $key=>$value){
                    if($route['id'] == $value['id']){
                        array_splice($endroutes, $key, 1);
                    }
                }
            }
            $result = array_merge($routes, $endroutes);

            return RouteResource::collection($result);
            //return response()->json($result);
        }
        if($request->search){

            $routes = Route::orderBy('number','asc')->filter();
            $routes = $routes->get();
            return RouteResource::collection($routes);
            //return response()->json($routes);
        }
        if($request->a){

            $a = $request->a; // ID of A point
            $astopsRoutes = [];
            $routes = Route::orderBy('number','asc')->with('stops')->with('endstops')->select('id','number','name', 'interval')->get();

            //find which routes contain point A
            foreach($routes as $route){
                foreach($route->stops as $stop){
                    if($stop->id == $a){
                        array_push($astopsRoutes, $route);
                    }
                }
                foreach($route->endstops as $stop){
                    if($stop->id == $a){
                        if(!in_array($route, $astopsRoutes)){
                            array_push($astopsRoutes, $route);
                        }
                    }
                }
            }

            if($request->b){
                $b = $request->b; // ID of B point
                $result = [];
                //find which routes that contain A also contains B point
                foreach($astopsRoutes as $astopRoute){
                    foreach($astopRoute->stops as $stop){
                        if($stop->id == $b){
                            array_push($result, $astopRoute);
                        }
                    }
                    foreach($astopRoute->endstops as $stop){
                        if($stop->id == $b){
                            if(!in_array($astopRoute, $result)){
                                array_push($result, $astopRoute);
                            }
                        }
                    }
                }

                //return array of routes that
                return  RouteListResource::collection($result);
            }
        }

        if($request->b){
            $a = $request->b; // ID of A point
            $astopsRoutes = [];
            $routes = Route::orderBy('number','asc')->with('stops')->with('endstops')->get();

            //find which routes contain point A
            foreach($routes as $route){
                foreach($route->stops as $stop){
                    if($stop->id == $a){
                        array_push($astopsRoutes, $route);
                    }
                }
                foreach($route->endstops as $stop){
                    if($stop->id == $a){
                        if(!in_array($route, $astopsRoutes)){
                            array_push($astopsRoutes, $route);
                        }
                    }
                }
            }
            //return array of routes that
             return RouteListResource::collection($astopsRoutes);
        }
        return RouteResource::collection(Route::orderBy('number','asc')->get(['id', 'name', 'number','interval']));
    }

    public function route($id){

        $route = Route::with(['stops' => function ($q){
            $q->orderBy('index', 'asc');
        }])->with(['endstops' => function ($q) {
            $q->orderBy('index', 'asc');
        }])->find($id);
       
        RouteHistory::create([
            'route_id' => $route->id,
            'user_id'  => Auth::user()->id
        ]);
        return SpecificRouteResource::make($route);
        //return response()->json($route);
    }

    public function getTimeline(Request $request){

        $stop = Stop::with('routes')->with('endroutes')->find($request->stop_id);
        $final_response = [];

        foreach($stop->routes as $route){
            $routyStop = RouteStop::where('stop_id', $stop->id)->where('route_id', $route->id)->first();

            if($routyStop != null){
                $stop_side = 'ahead';
            }
            else{
                $stop_side = 'back';
            }

            $buses = Bus::where('route_id', $route->id)->where('status', 1)->where('side', $stop_side)->get()->toArray();

            if(count($buses) > 0){

                $stop_side = 'ahead';
                $nearest = $this->getNearestBus($buses, $stop, 'ahead');

                if($nearest){

                    $meters = $this->getDistanceInMeters($nearest['location']['coordinates'][1], $nearest['location']['coordinates'][0], $stop->location->latitude, $stop->location->longitude);

                    if($nearest['speed'] > 25){
                        $time = ((($meters*0.001)/$nearest['speed'])*60);
                    }
                    else{
                        $time = ((($meters*0.001)/20)*60);
                    }
                    if($time > 1){
                        $route = Route::select('id','name','number','interval')->find($nearest['route_id']);
                        $array = [
                            'route' => $route,
                            'bus' => $nearest,
                            'route_number' => $route->number,
                            'route_name' => $route->name,
                            'vehicle_number' => $nearest['car_number'],
                            'time_type' => 'minute',
                            'time' => floor($time),
                        ];
                        array_push($final_response, $array);
                    }
                    else{
                        $route = Route::select('id','name','number','interval')->find($nearest['route_id']);
                        $array = [
                            'route' => $route,
                            'bus' => $nearest,
                            'route_number' => $route->number,
                            'route_name' => $route->name,
                            'vehicle_number' => $nearest['car_number'],
                            'time_type' => 'sekunt',
                            'time' => $time,
                        ];
                        array_push($final_response, $array);
                    }
                }
            }
        }

        foreach($stop->endroutes as $endroute){
            $routyStop = RouteStop::where('stop_id', $stop->id)->where('route_id', $endroute->id)->first();
            if($routyStop != null){
                $stop_side = 'ahead';
            }
            else{
                $stop_side = 'back';
            }

            $buses = Bus::where('route_id', $endroute->id)->where('status', 1)->where('side', $stop_side)->get()->toArray();

            if(count($buses) > 0){
                $nearest = $this->getNearestBus($buses, $stop, 'back');
                if($nearest){
                    $meters = $this->getDistanceInMeters($nearest['location']['coordinates'][1], $nearest['location']['coordinates'][0], $stop->location->latitude, $stop->location->longitude);
                    if($nearest['speed'] > 25){
                        $time = ((($meters*0.001)/$nearest['speed'])*60);
                    }
                    else{
                        $time = floor(((($meters*0.001)/20)*60));
                    }
                    $route = Route::select('id','name','number','interval')->find($nearest['route_id']);
                    if($time > 1){
                        $array = [
                            'route' => $route,
                            'bus' => $nearest,
                            'route_number' => $route->number,
                            'route_name' => $route->name,
                            'time_type' => 'minute',
                            'vehicle_number' => $nearest['car_number'],
                            'time' => floor($time),
                        ];
                        array_push($final_response, $array);
                    }
                    else{
                        $array = [
                            'route' => $route,
                            'bus' => $nearest,
                            'route_number' => $route->number,
                            'route_name' => $route->name,
                            'time_type' => 'sekunt',
                            'vehicle_number' => $nearest['car_number'],
                            'time' => $time,
                        ];
                        array_push($final_response, $array);
                    }
                }
            }
        }

        usort($final_response, function ($item1, $item2){
            return $item1['time'] <=> $item2['time'];
        });

        $resp = [];

        foreach($final_response as $response){
            if($response['time_type'] == 'minute'){
                $response['time'] = '~ ' . $response['time'] . ' min';
            }
            else{
                $response['time'] = '~ ' . floor($response['time']*60) . ' sec';
            }
            array_push($resp, $response);
        }

        return BusTimeLineResource::collection($resp);
       // return response()->json($resp);
    }

    function getNearestBus($buses, $stop, $side){

        $distances = array();

        foreach($buses as $key=>$bus){

            if($side == 'ahead'){
                $checkStop = RouteStop::where('stop_id', $stop->id)->where('route_id', $bus['route_id'])->first();
                $checkBus = RouteStop::where('stop_id', $bus['prev_stop_id'])->where('route_id', $bus['route_id'])->first();


                if($checkBus && $checkStop && $checkBus->index > $checkStop->index){
                    array_splice($buses, $key, 1);
                }
            }

            if($side == 'back'){
                $checkStop = EndrouteStop::where('stop_id', $stop->id)->where('route_id', $bus['route_id'])->first();
                $checkBus = EndrouteStop::where('stop_id', $bus['prev_stop_id'])->where('route_id', $bus['route_id'])->first();


                if($checkBus && $checkStop && $checkBus->index > $checkStop->index){
                    array_splice($buses, $key, 1);
                }
            }

        }

        foreach ($buses as $key => $bus){
            //$a = $stop->y - (double)$bus['y'];
            //$b = $stop->x - (double)$bus['x'];

            $a = $stop->location->latitude - $bus['location']['coordinates'][0];
            $b = $stop->location->longitude - $bus['location']['coordinates'][1];
            $distance = sqrt(($a**2) + ($b**2));
            $distances[$key] = $distance;
        }

        asort($distances);

        if(count($buses) > 0){
            $closest = $buses[key($distances)];
        }
        else{
            $closest = null;
        }

        return $closest;
    }


    //get distance between two points
    public function getDistanceInMeters($ltFrom, $lngFrom, $ltTo, $lngTo){
        // convert from degrees to radians
        $latFrom = deg2rad($ltFrom);
        $lonFrom = deg2rad($lngFrom);
        $latTo = deg2rad($ltTo);
        $lonTo = deg2rad($lngTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        $result = $angle * 6371000.0;
        return $result;
    }


    function getthreenearestpoints($locations, $locs){
         
        $base_location = $locs;
        $distances = array();
        foreach ($locations as $key => $location)
        {
            $a = $base_location['lat'] - (double)$location->location->latitude;
            $b = $base_location['lng'] - (double)$location->location->longitude;
            $distance = sqrt(($a**2) + ($b**2));
            $distances[$key] = $distance;
        }

        asort($distances);
        $count = 0;
        $result = [];
        foreach($distances as $key => $value){
            if($count < 4){
                array_push($result, $locations[$key]);
                $count++;
            }
        }

        return $result;
    }

    //get osrm-peshyi-hod
    function getLocationBetweenStopAndPoint($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo){
        $uri = "http://localhost:5000/route/v1/foot/" .
            $longitudeFrom . ',' . $latitudeFrom . ';'. $longitudeTo . ',' . $latitudeTo .
            '?overview=false&alternatives=true&steps=true&hints=HfAAgE_wAIAXAAAAMAAAADgAAAAAAAAAV0wZQc2JoEG9F7lBAAAAABcAAAAwAAAAOAAAAAAAAAApAAAAYJB7A8CTQgJCkXsDoJNCAgEA7wvjQRwm;2-8AgHzwAIAcAAAAJQAAAAAAAAAAAAAAhzGcQUokzkEAAAAAAAAAABwAAAAlAAAAAAAAAAAAAAApAAAA2YJ7A-GUQgLcgnsDP5VCAgAAvwXjQRwm';
        $client = new Client();
        $response = $client->request('GET', $uri);
        $data = json_decode($response->getBody(), true);
        return $data;
    }

    //get nearest-point
    function getnearestpointosrm($locations, $locs){
       
        $base_location = $locs;
        $distances = array();
        $locations = json_decode($locations);
        foreach ($locations as $key => $location)
        {     
            if($location->is_stop == 1){
                $a = $base_location->location->latitude - (double)$location->lat;
                $b = $base_location->location->longitude - (double)$location->lng;
                $distance = sqrt(($a**2) + ($b**2));
                $distances[$key] = $distance;
            }
        }

        asort($distances);

        $closest = $locations[key($distances)];
        return $closest;
    }
    //get nearest-point
    function getnearestpoint($locations, $locs){
         
        $base_location = $locs;
        $distances = array();
        foreach ($locations as $key => $location)
        {
            $a = $base_location['lat'] - (double)$location->location->latitude;
            $b = $base_location['lng'] - (double)$location->location->longitude;
            $distance = sqrt(($a**2) + ($b**2));
            $distances[$key] = $distance;
        }

        asort($distances);
        $closest = $locations[key($distances)];

        return $closest;
    }

    public function getcustomdistance(Request $request){

        $stops = Stop::get();

        $slocs = [];
        $slocs['lat'] = $request->start_lat;
        $slocs['lng'] = $request->start_lng;
        $sstops = $this->getthreenearestpoints($stops, $slocs);

        $elocs = [];
        $elocs['lat'] = $request->end_lat;
        $elocs['lng'] = $request->end_lng;
        $estops = $this->getthreenearestpoints($stops, $elocs);

        foreach($sstops as $sstop){
            foreach($estops as $estop){
                try{
                    $sresult = $this->getLocationBetweenStopAndPoint($request->start_lat, $request->start_lng, $sstop->location->latitude, $sstop->location->longitude);
                    $eresult = $this->getLocationBetweenStopAndPoint($estop->location->latitude, $estop->location->longitude, $request->end_lat, $request->end_lng);
                   
                }
                catch(\Exception $e){
                    $smeters = $this->getDistanceInMeters($request->start_lat, $request->start_lng, $sstop->location->latitude, $sstop->location->longitude);
                    $emeters = $this->getDistanceInMeters($estop->location->latitude, $estop->location->longitude, $request->end_lat, $request->end_lng);
                    $sresult = [
                        "routes" => [
                            [
                                "legs" => [
                                    [
                                        "steps" => [
                                            [
                                                "maneuver" => [
                                                    "location" => [
                                                        $request->start_lng,
                                                        $request->start_lat
                                                    ],
                                                    "type" => "depart"
                                                ],
                                                "mode" => "walking",
                                            ],
                                            [
                                                "maneuver" => [
                                                    "location" => [
                                                        $sstop->location->longitude,
                                                        $sstop->location->latitude
                                                    ],
                                                    "type" => "depart"
                                                ],
                                                "mode" => "walking",
                                            ]
                                        ],
                                        "duration" => $smeters/1.42,
                                        "distance" => $smeters
                                    ]
                                ]
                            ]
                        ]
                    ];
                    $eresult = [
                        "routes" => [
                            [
                                "legs" => [
                                    [
                                        "steps" => [
                                            [
                                                "maneuver" => [
                                                    "location" => [
                                                        $estop->location->longitude,
                                                        $estop->location->latitude
                                                    ],
                                                    "type" => "depart"
                                                ],
                                                "mode" => "walking",
                                            ],
                                            [
                                                "maneuver" => [
                                                    "location" => [
                                                        $request->end_lng,
                                                        $request->end_lat
                                                    ],
                                                    "type" => "depart"
                                                ],
                                                "mode" => "walking",
                                            ]
                                        ],
                                        "duration" => $emeters/1.42,
                                        "distance" => $emeters
                                    ]
                                ]
                            ]
                        ]
                    ];
                }

                $start_stop = Stop::with(['routes' => function ($query) {
                    $query->select('number', 'name', 'interval', 'route_id');
                }])->with(['endroutes' => function ($query) {
                    $query->select('number', 'name', 'route_id');
                }])->find($sstop->id);
                $end_stop = Stop::with(['routes' => function ($query) {
                    $query->select('number', 'name', 'interval', 'route_id');
                }])->with(['endroutes' => function ($query) {
                    $query->select('number', 'name', 'route_id');
                }])->find($estop->id);

                $avaroutes = [];
                $routesArray = [];

                foreach($start_stop->routes as $starting_route){
                    foreach($end_stop->routes as $ending_route){
                        if($starting_route->route_id == $ending_route->route_id){
                            $checkArrayForThisRoute = false; // checking array for this route to get non repeating routes

                            foreach($routesArray as $item){
                                if($item == $starting_route->route_id){
                                    $checkArrayForThisRoute = true;
                                }
                            }

                            if(!$checkArrayForThisRoute){
                                $start_index = RouteStop::where('stop_id', $start_stop->id)->where('route_id', $starting_route->route_id)->first();
                                $end_index = RouteStop::where('stop_id', $end_stop->id)->where('route_id', $ending_route->route_id)->first();


                                if($end_index != null && $start_index != null && (int)$start_index->index < (int)$end_index->index){
                                    $stops_of_the_route = RouteStop::with('stop')->where('route_id', $starting_route->route_id)->where('index', '>=', $start_index->index)->where('index', '<=', $end_index->index)->orderBy('index', 'ASC')->get();
                                    $drawable = Mroute::where('name', $starting_route->number)->first();
                                    $draw_array = [];

                                    if($drawable){
                                        $starting_point = $this->getnearestpointosrm($drawable->start_coords, $start_stop);
                                        $ending_point = $this->getnearestpointosrm($drawable->start_coords, $end_stop);

                                        $decoded_array = json_decode($drawable->start_coords);

                                        if($starting_point != null && $ending_point != null && (int)$starting_point->index<(int)$ending_point->index){
                                            foreach($decoded_array as $coordinats){
                                                if(isset($coordinats->index)){
                                                    if((int)$coordinats->index >= (int)$starting_point->index && (int)$coordinats->index <= (int)$ending_point->index){
                                                        array_push($draw_array, $coordinats);
                                                    }
                                                }
                                            }
                                        }
                                        else{
                                            $starting_point = $this->getnearestpointosrm($drawable->end_coords, $start_stop);
                                            $ending_point = $this->getnearestpointosrm($drawable->end_coords, $end_stop);
                                            foreach(json_decode($drawable->end_coords) as $coordinats){
                                                if(isset($coordinats->index)){
                                                    if((int)$coordinats->index >= (int)$starting_point->index && (int)$coordinats->index <= (int)$ending_point->index){
                                                        array_push($draw_array, $coordinats);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $array_result = [];
                                    array_push($array_result, ["type" => "foot", "values" => $sresult['routes'][0]['legs'][0]]);
                                    array_push($array_result, ["type" => "bus", "values" => $starting_route->toArray(), 'polyline' => $draw_array, 'stops' => $stops_of_the_route]);
                                    array_push($array_result, ["type" => "foot", "values" => $eresult['routes'][0]['legs'][0]]);
                                    array_push($avaroutes, $array_result);
                                }
                                elseif($end_index != null && $start_index != null && (int)$start_index->index > (int)$end_index->index){ 
                                    $start_index = EndrouteStop::where('stop_id', $start_stop->id)->where('route_id', $starting_route->route_id)->first();
                                    $end_index = EndrouteStop::where('stop_id', $end_stop->id)->where('route_id', $ending_route->route_id)->first();
                                    if($start_index != null && $end_index != null){
                                        $stops_of_the_route = EndrouteStop::with('stop')->where('route_id', $starting_route->route_id)->where('index', '>=', $start_index->index)->where('index', '<=', $end_index->index)->orderBy('index', 'ASC')->get();

                                        $drawable = Mroute::where('name', $starting_route->number)->first();
                                        $draw_array = [];

                                        if($drawable){
                                            $starting_point = $this->getnearestpointosrm($drawable->start_coords, $start_stop);
                                            $ending_point = $this->getnearestpointosrm($drawable->start_coords, $end_stop);

                                            $decoded_array = json_decode($drawable->end_coords);

                                            if($starting_point != null && $ending_point != null && (int)$starting_point->index<(int)$ending_point->index){
                                                foreach($decoded_array as $coordinats){
                                                    if(isset($coordinats->index)){
                                                        if((int)$coordinats->index >= (int)$starting_point->index && (int)$coordinats->index <= (int)$ending_point->index){
                                                            array_push($draw_array, $coordinats);
                                                        }
                                                    }
                                                }
                                            }
                                            else{
                                                $starting_point = $this->getnearestpointosrm($drawable->end_coords, $start_stop);
                                                $ending_point = $this->getnearestpointosrm($drawable->end_coords, $end_stop);
                                                foreach(json_decode($drawable->end_coords) as $coordinats){
                                                    if(isset($coordinats->index)){
                                                        if((int)$coordinats->index >= (int)$starting_point->index && (int)$coordinats->index <= (int)$ending_point->index){
                                                            array_push($draw_array, $coordinats);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $array_result = [];
                                        array_push($array_result, ["type" => "foot", "values" => $sresult['routes'][0]['legs'][0]]);
                                        array_push($array_result, ["type" => "bus", "values" => $starting_route->toArray(), 'polyline' => $draw_array, 'stops' => $stops_of_the_route]);
                                        array_push($array_result, ["type" => "foot", "values" => $eresult['routes'][0]['legs'][0]]);
                                        array_push($avaroutes, $array_result);return $array_result;
                                    }
                                }
                            }

                        }
                    }
                }
                
                foreach($start_stop->endroutes as $starting_route){
                    foreach($end_stop->endroutes as $ending_route){
                        if($starting_route->route_id == $ending_route->route_id){
                            $checkArrayForThisRoute = false; // checking array for this route to get non repeating routes

                            foreach($routesArray as $item){
                                if($item == $starting_route->route_id){
                                    $checkArrayForThisRoute = true;
                                }
                            }

                            if(!$checkArrayForThisRoute){
                                $start_index = RouteStop::where('stop_id', $start_stop->id)->where('route_id', $starting_route->route_id)->first();
                                $end_index = RouteStop::where('stop_id', $end_stop->id)->where('route_id', $ending_route->route_id)->first();

                                if($end_index != null && $start_index != null && $start_index->index < $end_index->index){
                                    $stops_of_the_route = RouteStop::with('stop')->where('route_id', $starting_route->route_id)->where('index', '>=', $start_index->index)->where('index', '<=', $end_index->index)->orderBy('index', 'ASC')->get();

                                    $drawable = Mroute::where('name', $starting_route->number)->first();
                                    $draw_array = [];

                                    if($drawable){
                                        $starting_point = $this->getnearestpointosrm($drawable->start_coords, $start_stop);
                                        $ending_point = $this->getnearestpointosrm($drawable->start_coords, $end_stop);

                                        $decoded_array = json_decode($drawable->start_coords);

                                        if($starting_point != null && $ending_point != null && (int)$starting_point->index<(int)$ending_point->index){
                                            foreach($decoded_array as $coordinats){
                                                if(isset($coordinats->index)){
                                                    if((int)$coordinats->index >= (int)$starting_point->index && (int)$coordinats->index <= (int)$ending_point->index){
                                                        array_push($draw_array, $coordinats);
                                                    }
                                                }
                                            }
                                        }
                                        else{
                                            $starting_point = $this->getnearestpointosrm($drawable->end_coords, $start_stop);
                                            $ending_point = $this->getnearestpointosrm($drawable->end_coords, $end_stop);
                                            foreach(json_decode($drawable->end_coords) as $coordinats){
                                                if(isset($coordinats->index)){
                                                    if((int)$coordinats->index >= (int)$starting_point->index && (int)$coordinats->index <= (int)$ending_point->index){
                                                        array_push($draw_array, $coordinats);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $array_result = [];
                                    array_push($array_result, ["type" => "foot", "values" => $sresult['routes'][0]['legs'][0]]);
                                    array_push($array_result, ["type" => "bus", "values" => $starting_route->toArray(), 'polyline' => $draw_array, 'stops' => $stops_of_the_route]);
                                    array_push($array_result, ["type" => "foot", "values" => $eresult['routes'][0]['legs'][0]]);
                                    array_push($avaroutes, $array_result);
                                }
                                else{
                                    $start_index = EndrouteStop::where('stop_id', $start_stop->id)->where('route_id', $starting_route->route_id)->first();
                                    $end_index = EndrouteStop::where('stop_id', $end_stop->id)->where('route_id', $ending_route->route_id)->first();


                                    $stops_of_the_route = EndrouteStop::with('stop')->where('route_id', $starting_route->route_id)->where('index', '>=', $start_index->index)->where('index', '<=', $end_index->index)->orderBy('index', 'ASC')->get();
					
                                    $drawable = Mroute::where('name', $starting_route->number)->first();
                                    $draw_array = [];

                                    if($drawable){
                                        $starting_point = $this->getnearestpointosrm($drawable->end_coords, $start_stop);
                                        $ending_point = $this->getnearestpointosrm($drawable->end_coords, $end_stop);

                                        $decoded_array = json_decode($drawable->end_coords);

                                        if($starting_point != null && $ending_point != null &&  (int)$starting_point->index<(int)$ending_point->index){
                                            foreach($decoded_array as $coordinats){
                                                if(isset($coordinats->index)){
                                                    if((int)$coordinats->index >= (int)$starting_point->index && (int)$coordinats->index <= (int)$ending_point->index){
                                                        array_push($draw_array, $coordinats);
                                                    }
                                                }
                                            }
                                        }
                                        else{
                                            $starting_point = $this->getnearestpointosrm($drawable->end_coords, $start_stop);
                                            $ending_point = $this->getnearestpointosrm($drawable->end_coords, $end_stop);
                                            foreach(json_decode($drawable->end_coords) as $coordinats){
                                                if(isset($coordinats->index)){
                                                    if((int)$coordinats->index >= (int)$starting_point->index && (int)$coordinats->index <= (int)$ending_point->index){
                                                        array_push($draw_array, $coordinats);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $array_result = [];
                                    array_push($array_result, ["type" => "foot", "values" => $sresult['routes'][0]['legs'][0]]);
                                    array_push($array_result, ["type" => "bus", "values" => $starting_route->toArray(), 'polyline' => $draw_array, 'stops' => $stops_of_the_route]);
                                    array_push($array_result, ["type" => "foot", "values" => $eresult['routes'][0]['legs'][0]]);
                                    array_push($avaroutes, $array_result);
                                }
                            }

                        }
                    }
                }
            }
        }

        if(count($avaroutes) > 0){
            return response()->json($avaroutes);
        }

        $allvariants = [];
        $routes_array = [];
        $slocs = [];
        $slocs['lat'] = $request->start_lat;
        $slocs['lng'] = $request->start_lng;
        $sstop = $this->getnearestpoint($stops, $slocs);

        $elocs = [];
        $elocs['lat'] = $request->end_lat;
        $elocs['lng'] = $request->end_lng;
        $estop = $this->getnearestpoint($stops, $elocs);

        foreach($start_stop->routes as $route_s){
            foreach($end_stop->routes as $route_e){
                if($route_s->route_id != $route_e->route_id){
                    $checkArrayForThisRoute = false; // checking array for this route to get non repeating routes

                    foreach($routes_array as $item){
                        if($item == $route_s->route_id){
                            $checkArrayForThisRoute = true;
                        }
                    }
                    if(!$checkArrayForThisRoute){
                        $sroutestops = RouteStop::with('stop')->where('route_id', $route_s->route_id)->orderBy('index', 'ASC')->get();
                        $eroutestops = RouteStop::with('stop')->where('route_id', $route_e->route_id)->orderBy('index', 'ASC')->get();
                        foreach($sroutestops as $sroutestop){
                            foreach($eroutestops as $eroutestop){
                                if($sroutestop->stop_id == $eroutestop->stop_id){

                                    $stopA = $start_stop;
                                    $stopIntersection = $sroutestop;
                                    $stopB = $end_stop;

                                    $drawable_1 = Mroute::where('name', $route_s->number)->first();
                                    $drawable_2 = Mroute::where('name', $route_e->number)->first();
                                    $draw_array_1 = [];
                                    $draw_array_2 = [];

                                    $front_1=true;
                                    $front_2=true;

                                    if($drawable_1){
                                        $starting_point_1 = $this->getnearestpointosrm($drawable_1->start_coords, $stopA);
                                        $ending_point_1 = $this->getnearestpointosrm($drawable_1->start_coords, $stopIntersection->stop);

                                        //dd($starting_point_1, $ending_point_1, json_decode($drawable_1->start_coords));
                                        $decoded_array_1 = json_decode($drawable_1->start_coords);

                                        if($starting_point_1 != null && $ending_point_1 != null && (int)$starting_point_1->index < (int)$ending_point_1->index){
                                            foreach($decoded_array_1 as $coordinats){
                                                if((int)$coordinats->index >= (int)$starting_point_1->index && (int)$coordinats->index <= (int)$ending_point_1->index){
                                                    array_push($draw_array_1, $coordinats);
                                                }
                                            }
                                        }
                                    }

                                    if($drawable_2){

                                        $starting_point_2 = $this->getnearestpointosrm($drawable_2->start_coords, $stopIntersection->stop);
                                        $ending_point_2 = $this->getnearestpointosrm($drawable_2->start_coords, $stopB);

                                        $decoded_array_2 = json_decode($drawable_2->start_coords);

                                        if($starting_point_2 != null && $ending_point_2 != null && (int)$starting_point_2->index < (int)$ending_point_2->index){
                                            foreach($decoded_array_2 as $coordinats){
                                                if((int)$coordinats->index >= (int)$starting_point_2->index && (int)$coordinats->index <= (int)$ending_point_2->index){
                                                    array_push($draw_array_2, $coordinats);
                                                }
                                            }
                                        }
                                        elseif($starting_point_2 != null && $ending_point_2 != null && (int)$starting_point_2->index > (int)$ending_point_2->index){
                                            $front_2=false;
                                            $starting_point_2 = $this->getnearestpointosrm($drawable_2->end_coords, $stopIntersection->stop);
                                            $decoded_array_2 = $this->getnearestpointosrm($drawable_2->end_coords, $stopB);
                                            foreach(json_decode($drawable_2->end_coords) as $coordinats){
                                                if(isset($coordinats->index)){
                                                    if((int)$coordinats->index >= (int)$starting_point_2->index && (int)$coordinats->index <= (int)$decoded_array_2->index){
                                                        array_push($draw_array_2, $coordinats);
                                                    }
                                                }
                                            }
                                        }

                                    }

                                    if($front_1){
                                        $start_index_1 = RouteStop::with('stop')->where('route_id', $route_s->route_id)->where('stop_id', $stopA->id)->first();
                                        $stop_index_1 = RouteStop::with('stop')->where('route_id', $route_s->route_id)->where('stop_id', $stopIntersection->stop_id)->first();
                                        $stops_of_the_route_1 = RouteStop::with('stop')->where('route_id', $route_s->route_id)->orderBy('index', 'ASC')->where('index', '>=', (int)$start_index_1->index)->where('index', '<=', (int)$stop_index_1->index)->get();
                                    }

                                    if($front_2){
                                        $start_index_2 = RouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopIntersection->stop_id)->first();
                                        $stop_index_2 = RouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopB->id)->first();
                                        if($start_index_2 && $stop_index_2){
                                            $stops_of_the_route_2 = RouteStop::with('stop')->where('route_id', $route_e->route_id)->orderBy('index', 'ASC')->where('index', '>=', (int)$start_index_2->index)->where('index', '<=', (int)$stop_index_2->index)->get();
                                        }
                                        else{
                                            $start_index_2 = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopIntersection->stop_id)->first();
                                            $stop_index_2 = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopB->id)->first();
                                            $stops_of_the_route_2 = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->orderBy('index', 'ASC')->where('index', '>=', (int)$start_index_2->index)->where('index', '<=', (int)$stop_index_2->index)->get();
                                        }
                                    }

                                    if($stops_of_the_route_2){
                                        $array_result = [
                                            'variant' => $sroutestop,
                                            'route_1' => ["type" => "bus", "values" => $route_s->toArray(), 'polyline' => $draw_array_1, 'stops' => $stops_of_the_route_1],
                                            'route_2' => ["type" => "bus", "values" => $route_e->toArray(), 'polyline' => $draw_array_2, 'stops' => $stops_of_the_route_2]
                                        ];
                                    }
                                    else{
                                        $array_result = [
                                            'variant' => $sroutestop,
                                            'route_1' => ["type" => "bus", "values" => $route_s->toArray(), 'polyline' => $draw_array_1, 'stops' => $stops_of_the_route_1],
                                            'route_2' => ["type" => "bus", "values" => $route_e->toArray(), 'polyline' => $draw_array_2, 'stops' => null]
                                        ];
                                    }
                                    array_push($allvariants, $array_result);
                                }
                            }
                        }
                        array_push($routes_array, $route_s->route_id);
                    }

                }
            }
        }

        foreach($start_stop->endroutes as $route_s){
            foreach($end_stop->endroutes as $route_e){
                if($route_s->route_id != $route_e->route_id){
                    $checkArrayForThisRoute = false; // checking array for this route to get non repeating routes

                    foreach($routes_array as $item){
                        if($item == $route_s->route_id){
                            $checkArrayForThisRoute = true;
                        }
                    }
                    if(!$checkArrayForThisRoute){
                        $sroutestops = EndrouteStop::with('stop')->where('route_id', $route_s->route_id)->orderBy('index', 'ASC')->get();
                        $eroutestops = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->orderBy('index', 'ASC')->get();
                        foreach($sroutestops as $sroutestop){
                            foreach($eroutestops as $eroutestop){
                                if($sroutestop->stop_id == $eroutestop->stop_id){

                                    $stopA = $start_stop;
                                    $stopIntersection = $sroutestop;
                                    $stopB = $end_stop;

                                    $drawable_1 = Mroute::where('name', $route_s->number)->first();
                                    $drawable_2 = Mroute::where('name', $route_e->number)->first();
                                    $draw_array_1 = [];
                                    $draw_array_2 = [];

                                    $front_1=true;
                                    $front_2=true;

                                    if($drawable_1){
                                        $starting_point_1 = $this->getnearestpointosrm($drawable_1->end_coords, $stopA);
                                        $ending_point_1 = $this->getnearestpointosrm($drawable_1->end_coords, $stopIntersection->stop);

                                        //dd($starting_point_1, $ending_point_1, json_decode($drawable_1->start_coords));
                                        $decoded_array_1 = json_decode($drawable_1->end_coords);

                                        if($starting_point_1 != null && $ending_point_1 != null && (int)$starting_point_1->index < (int)$ending_point_1->index){
                                            foreach($decoded_array_1 as $coordinats){
                                                if((int)$coordinats->index >= (int)$starting_point_1->index && (int)$coordinats->index <= (int)$ending_point_1->index){
                                                    array_push($draw_array_1, $coordinats);
                                                }
                                            }
                                        }
                                    }

                                    if($drawable_2){

                                        $starting_point_2 = $this->getnearestpointosrm($drawable_2->start_coords, $stopIntersection->stop);
                                        $ending_point_2 = $this->getnearestpointosrm($drawable_2->start_coords, $stopB);

                                        $decoded_array_2 = json_decode($drawable_2->start_coords);

                                        if($starting_point_2 != null && $ending_point_2 != null && (int)$starting_point_2->index < (int)$ending_point_2->index){
                                            foreach($decoded_array_2 as $coordinats){
                                                if((int)$coordinats->index >= (int)$starting_point_2->index && (int)$coordinats->index <= (int)$ending_point_2->index){
                                                    array_push($draw_array_2, $coordinats);
                                                }
                                            }
                                        }
                                        elseif($starting_point_2 != null && $ending_point_2 != null && (int)$starting_point_2->index > (int)$ending_point_2->index){
                                            $front_2=false;
                                            $starting_point_2 = $this->getnearestpointosrm($drawable_2->end_coords, $stopIntersection->stop);
                                            $decoded_array_2 = $this->getnearestpointosrm($drawable_2->end_coords, $stopB);
                                            foreach(json_decode($drawable_2->end_coords) as $coordinats){
                                                if(isset($coordinats->index)){
                                                    if((int)$coordinats->index >= (int)$starting_point_2->index && (int)$coordinats->index <= (int)$decoded_array_2->index){
                                                        array_push($draw_array_2, $coordinats);
                                                    }
                                                }
                                            }
                                        }

                                    }

                                    if($front_1){
                                        $start_index_1 = EndrouteStop::with('stop')->where('route_id', $route_s->route_id)->where('stop_id', $stopA->id)->first();
                                        $stop_index_1 = EndrouteStop::with('stop')->where('route_id', $route_s->route_id)->where('stop_id', $stopIntersection->stop_id)->first();
                                        $stops_of_the_route_1 = EndrouteStop::with('stop')->where('route_id', $route_s->route_id)->orderBy('index', 'ASC')->where('index', '>=', (int)$start_index_1->index)->where('index', '<=', (int)$stop_index_1->index)->get();
                                    }

                                    if($front_2){
                                        $start_index_2 = RouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopIntersection->stop_id)->first();
                                        $stop_index_2 = RouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopB->id)->first();
                                        if($start_index_2 != null && $stop_index_2 != null){
                                            $stops_of_the_route_2 = RouteStop::with('stop')->where('route_id', $route_e->route_id)->orderBy('index', 'ASC')->where('index', '>=', (int)$start_index_2->index)->where('index', '<=', (int)$stop_index_2->index)->get();
                                        }
                                        else{
                                            $start_index_2 = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopIntersection->stop_id)->first();
                                            $stop_index_2 = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopB->id)->first();
                                            $stops_of_the_route_2 = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->orderBy('index', 'ASC')->where('index', '>=', (int)$start_index_2->index)->where('index', '<=', (int)$stop_index_2->index)->get();
                                        }
                                    }
                                    else{
                                        $start_index_2 = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopIntersection->stop_id)->first();
                                        $stop_index_2 = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->where('stop_id', $stopB->id)->first();
                                        $stops_of_the_route_2 = EndrouteStop::with('stop')->where('route_id', $route_e->route_id)->orderBy('index', 'ASC')->where('index', '>=', (int)$start_index_2->index)->where('index', '<=', (int)$stop_index_2->index)->get();
                                    }

                                    $array_result = [
                                        'variant' => $sroutestop,
                                        'route_1' => ["type" => "bus", "values" => $route_s->toArray(), 'polyline' => $draw_array_1, 'stops' => $stops_of_the_route_1],
                                        'route_2' => ["type" => "bus", "values" => $route_e->toArray(), 'polyline' => $draw_array_2, 'stops' => $stops_of_the_route_2]
                                    ];
                                    array_push($allvariants, $array_result);
                                }
                            }
                        }
                        array_push($routes_array, $route_s->route_id);
                    }
                }
            }
        }

        $variants = [];

        foreach($allvariants as $variant){
            $check = false;
            foreach($variants as $varriant){
                if($varriant['variant']['route_id'] == $variant['variant']['route_id']){
                    $check = true;
                }
            }
            if(!$check){
                array_push($variants, $variant);
                $array_result = [];
                array_push($array_result, ["type" => "foot", "values" => $sresult['routes'][0]['legs'][0]]);
                array_push($array_result, $variant['route_1']);
                array_push($array_result, $variant['route_2']);
                array_push($array_result, ["type" => "foot", "values" => $eresult['routes'][0]['legs'][0]]);
                array_push($avaroutes, $array_result);
            }
        }

        return response()->json($avaroutes);
    }
}
