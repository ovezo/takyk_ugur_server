<?php

namespace App\Http\API\Controllers\Buses;

use App\Http\Controller;
use App\Http\Resources\BusCollection;
use Illuminate\Http\Request;
use App\Http\API\Resources\BusResource;
use Domain\Buses\Models\Bus;
use Domain\Stops\Models\Stop;
use Domain\Routes\Models\EndrouteStop;
use Domain\Routes\Models\RouteStop;
use Domain\Routes\Models\Mroute;

class BusController extends Controller
{
    public function buses(Request $request){

        /**
         * @OA\GET(6
         *   path="/api/buses",
         *   summary=" - Get list of stops",
         *   tags = {"Buses"},
         *   @OA\Response(
         *         response=201,
         *         description="OK",
         *   ),
         * )
         */
        if($request->route_id){
            $respArray = Bus::with(['route' => function ($query) {
                $query->select('id', 'number', 'name', 'interval');
            }])->where('route_id', $request->route_id)->where('status', 1)->get();//return $respArray;
            return BusResource::collection($respArray);
        }
        return BusResource::collection(Bus::get());
    }

    public function getBusTime(Request $request){
        $id = $request->id;
        $lng = $request->lng;
        $lat = $request->lat;


        $stops = Stop::get();
        $slocs = [];
        $slocs['lat'] = $lat;
        $slocs['lng'] = $lng;


        $sstop_without_routes = $this->getnearestpoint($stops, $slocs);
        $stop_with_routes = Stop::with('routes')->with('endroutes')->find($sstop_without_routes->id);

        $bus = Bus::with('route')->find($id);
        $slocsbus = [];
        $slocsbus['lat'] = $bus->location->latitude;
        $slocsbus['lng'] = $bus->location->longitude;
        $sstop_bus = Stop::find($bus->prev_stop_id);

        $stop_contains_route = false;
        foreach($stop_with_routes->routes as $route){
            if($route->id == $bus->route_id){
                $stop_contains_route = true;
            }
        }

        foreach($stop_with_routes->endroutes as $endroute){
            if($endroute->id == $bus->route_id){
                $stop_contains_route = true;
            }
        }

        if(!$stop_contains_route){
            return response()->json([
                'error' => 'not_found',
                'message_ru' => 'Вы находитесь не по пути указанного маршрута',
                'message_tm' => 'Siziň geolokasiýaňyz marşrudyň ugrunda däl',
                'message_en' => 'You are not on the way of a given route',
            ]);
        }

        $side = 'ahead';
        $routeStopForward = RouteStop::with('stop')->where('stop_id', $sstop_without_routes->id)->where('route_id', $bus->route_id)->first();
        $routeStopBackward = EndrouteStop::with('stop')->where('stop_id', $sstop_without_routes->id)->where('route_id', $bus->route_id)->first();
        $busRouteStopForward = RouteStop::with('stop')->where('stop_id', $bus->prev_stop_id)->where('route_id', $bus->route_id)->first();
        $busRouteStopBackward = EndrouteStop::with('stop')->where('stop_id', $bus->prev_stop_id)->where('route_id', $bus->route_id)->first();


        if($routeStopForward){
            $side = 'ahead';
        }
        if($routeStopBackward){
            $side = 'back';
        }

        $mroute = Mroute::where('name', $bus->route->number)->first();

        if($side == 'ahead' && $bus->side == 'ahead'){
            if($routeStopForward->index > $busRouteStopForward->index){
                $distance = $this->calculateDistance($mroute->start_coords, $routeStopForward, $busRouteStopForward);
                $distance = $distance/1000;

                if($bus->speed > 20){
                    $t = ($distance/$bus->speed)*60;
                    if($t > 1){
                        return response()->json([
                            'error' => 'ok',
                            'message_ru' => 'D: ' . $distance . ', S: ' . $bus->speed . 'T: ' . floor($t) .'min',
                            'message_tm' => floor($t) .'min',
                            'message_en' => floor($t) .'min',
                        ]);
                    }
                    else{
                        return response()->json([
                            'error' => 'ok',
                            'message_ru' => 'D: ' . $distance . ', S: ' . $bus->speed . 'T: ' . floor($t) .'min',
                            'message_tm' => $t .' sec',
                            'message_en' => $t .' sec',
                        ]);
                    }
                }
                else{
                    $t = ($distance/20)*60;
                    if($t > 1){
                        return response()->json([
                            'error' => 'ok',
                            'message_ru' => floor($t) .'min',
                            'message_tm' => floor($t) .'min',
                            'message_en' => floor($t) .'min',
                        ]);
                    }
                    else{
                        return response()->json([
                            'error' => 'ok',
                            'message_ru' => $t .'min',
                            'message_tm' => $t .'min',
                            'message_en' => $t .'min',
                        ]);
                    }
                }
            }
            else{
                return response()->json([
                    'error' => 'not_found',
                    'message_ru' => 'Автобус уже проехал остановку на которой вы находитесь',
                    'message_tm' => 'Awtobus siziň lokasiýaňyzy geçip gitdi',
                    'message_en' => 'The bus has already passed the stop you are at',
                ]);
            }
        }
        if($side == 'back' && $bus->side == 'ahead'){

            $last_stop_of_one_side = RouteStop::with('stop')->where('route_id', $bus->route_id)->orderBy('index', 'DESC')->first();
            $distance_1 = $this->calculateDistance($mroute->start_coords, $busRouteStopForward, $last_stop_of_one_side);

            $first_stop_of_one_side = EndrouteStop::with('stop')->where('route_id', $bus->route_id)->orderBy('index', 'ASC')->first();
            $distance_2 = $this->calculateDistance($mroute->end_coords, $first_stop_of_one_side, $routeStopBackward);

            $distance_sum = $distance_1 + $distance_2;

            $distance = $distance_sum/1000;

            if($bus->speed > 20){
                $t = ($distance/$bus->speed)*60;
                if($t > 1){
                    return response()->json([
                        'error' => 'ok',
                        'message_ru' => 'D: ' . $distance . ', S: ' . $bus->speed . 'T: ' . floor($t) .'min',
                        'message_tm' => floor($t) .' min',
                        'message_en' => floor($t) .' min',
                    ]);
                }
                else{
                    return response()->json([
                        'error' => 'ok',
                        'message_ru' => 'D: ' . $distance . ', S: ' . $bus->speed . 'T: ' . floor($t) .'min',
                        'message_tm' => floor($t) .' sec',
                        'message_en' => floor($t) .' sec',
                    ]);
                }
            }
            else{
                $t = ($distance/20)*60;
                if($t > 1){
                    return response()->json([
                        'error' => 'ok',
                        'message_ru' => 'D: ' . $distance . ', S: ' . $bus->speed . 'T: ' . floor($t) .'min',
                        'message_tm' => floor($t) .' min',
                        'message_en' => floor($t) .' min',
                    ]);
                }
                else{
                    return response()->json([
                        'error' => 'ok',
                        'message_ru' => 'D: ' . $distance . ', S: ' . $bus->s . 'T: ' . floor($t) .'min',
                        'message_tm' => floor($t) .' sec',
                        'message_en' => floor($t) .' sec',
                    ]);
                }
            }
        }
        if($side == 'back' && $bus->side == 'back'){
            if($routeStopBackward->index > $busRouteStopBackward->index){
                $distance = $this->calculateDistance($mroute->back_coords, $routeStopBackward, $busRouteStopBackward);

                $distance = $distance/1000;

                if($bus->speed > 20){
                    $t = ($distance/$bus->speed)*60;
                    if($t > 1){
                        return response()->json([
                            'error' => 'ok',
                            'message_ru' => floor($t) .'min',
                            'message_tm' => floor($t) .'min',
                            'message_en' => floor($t) .'min',
                        ]);
                    }
                    else{
                        return response()->json([
                            'error' => 'ok',
                            'message_ru' => $t .'min',
                            'message_tm' => $t .'min',
                            'message_en' => $t .'min',
                        ]);
                    }
                }
                else{
                    $t = ($distance/20)*60;
                    if($t > 1){
                        return response()->json([
                            'error' => 'ok',
                            'message_ru' => floor($t) .'min',
                            'message_tm' => floor($t) .'min',
                            'message_en' => floor($t) .'min',
                        ]);
                    }
                    else{
                        return response()->json([
                            'error' => 'ok',
                            'message_ru' => $t .'min',
                            'message_tm' => $t .'min',
                            'message_en' => $t .'min',
                        ]);
                    }
                }
            }
            else{
                return response()->json([
                    'error' => 'not_found',
                    'message_ru' => 'Автобус уже проехал остановку на которой вы находитесь',
                    'message_tm' => 'Awtobus siziň lokasiýaňyzy geçip gitdi',
                    'message_en' => 'The bus has already passed the stop you are at',
                ]);
            }
        }
        if($side == 'ahead' && $bus->side == 'back'){
            return response()->json([
                'error' => 'not_found',
                'message_ru' => 'Автобус уже проехал остановку на которой вы находитесь',
                'message_tm' => 'Awtobus siziň lokasiýaňyzy geçip gitdi',
                'message_en' => 'The bus has already passed the stop you are at',
            ]);
        }

    }

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

    //calculate distance
    public function calculateDistance($coords, $stopLoc, $busLoc){
        $slocs['lat'] = $stopLoc->stop->location->latitude;
        $slocs['lng'] = $stopLoc->stop->location->longitude;

        $blocs['lat'] = $busLoc->stop->location->latitude;
        $blocs['lng'] = $busLoc->stop->location->longitude;

        $nearest_to_stop = $this->getnearestpointosrmtime($coords, $slocs);
        $nearest_to_bus = $this->getnearestpointosrmtime($coords, $blocs);


        $coords = json_decode($coords);

        $sum_distance = 0;
        for($i=0; $i<count($coords)-1; $i++){

            if((int)$coords[$i]->index > (int)$nearest_to_stop->index && (int)$coords[$i]->index < (int)$nearest_to_bus->index){
                $distance = $this->getDistanceInMeters($coords[$i]->lat, $coords[$i]->lng, $coords[$i+1]->lat, $coords[$i+1]->lng);
                $sum_distance = $sum_distance + $distance;
            }
        }

        return $sum_distance;
    }

    //get nearest-point
    function getnearestpointosrmtime($locations, $locs){

        if(isset($locs['y']) && isset($locs['x'])){
            $base_location = $locs;
        }
        else{
            $base_location['y'] = $locs['lat'];
            $base_location['x'] = $locs['lng'];
        }
        $distances = array();
        $locations = json_decode($locations);
        foreach ($locations as $key => $location)
        {
            if($location->is_stop == 1){
                $a = $base_location['y'] - (double)$location->lat;
                $b = $base_location['x'] - (double)$location->lng;
                $distance = sqrt(($a**2) + ($b**2));
                $distances[$key] = $distance;
            }
        }

        asort($distances);

        $closest = $locations[key($distances)];
        return $closest;
    }

}
