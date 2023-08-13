<?php

namespace App\Http\Import\Controllers;
use Domain\Routes\Models\EndrouteStop;
use Domain\Routes\Models\RouteStop;
use Domain\Buses\Models\Bus;
use Illuminate\Http\Request;
use Domain\Stops\Models\Stop;
use Domain\Routes\Models\Route;
use MatanYadaev\EloquentSpatial\Objects\GeometryCollection;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\MultiPoint;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use GuzzleHttp\Client;
use App\Http\Controller;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Termwind\Components\Li;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller{


    public function importStopsView(){
        return view('import.stops');
    }

    public function importStops(Request $request){

        //test
        $string = file_get_contents($request->file);
        $old_stops = json_decode($string, true);
        foreach($old_stops[2]['data'] as $key => $old_stop)
        {
            //import routes
            $objects = json_decode($old_stop['start_coords']);
            $objects = collect($objects);
            $objects_back = json_decode($old_stop['end_coords']);
            $objects_back = collect($objects_back);

            $points_front =
                $objects->map(function($data) {
                return new Point((float) $data->lat, (float)$data->long);
            });
            $objects_back =
                $objects_back->map(function($data) {
                    return new Point((float) $data->lat, (float)$data->long);
                });

             $route = Route::where('id', $old_stop['id'])->first();// update gerekgal
//              $route = new Route([
//                 'id' => $old_stop['id'],
//                 'name' => $old_stop['d'],
//                 'interval' => $old_stop['interval'],
//                 'number' => $old_stop['n'],
//                 'front_line' => new LineString($points_front),
//                 'back_line' => new LineString($objects_back),
//                 'routing_time' =>$old_stop['routing_time']
//              ]);
             $route->front_line = new LineString($points_front);
             $route->back_line = new LineString($objects_back);// update gerekgal
             $route->save();// update gerekgal

                //import EndrouteStop
//              $end_route_stop = new EndrouteStop([
//                 'route_id' => $old_stop['route_id'],
//                 'stop_id' => $old_stop['stop_id'],
//                 'index' => $old_stop['index']
//              ]);
//              $end_route_stop->save();

                //import RouteStop
//              $route_stop = new RouteStop([
//                 'route_id' => $old_stop['route_id'],
//                 'stop_id' => $old_stop['stop_id'],
//                 'index' => $old_stop['index']
//              ]);
//              $route_stop->save();

                //import Stop
//              $stop = new Stop([
//                  'id' => $old_stop['id'],
//                  'name' => $old_stop['n'],
//                  'location' => new Point($old_stop['y'], $old_stop['x']),
//                  'is_endpoint' => $old_stop['is_endpoint']
//               ]);
//              $stop->save();
        }
        return 'ok';
    }

    //get nearest-point
    function getnearestpoint($locations, $locs){
        $base_location = $locs;
        $distances = array();
        foreach ($locations as $key => $location)
        {
            $a = $base_location['lat'] - (double)$location->location->coordinates[1];
            $b = $base_location['lng'] - (double)$location->location->coordinates[0];
            $distance = sqrt(($a**2) + ($b**2));
            $distances[$key] = $distance;
        }

        asort($distances);
        $closest = $locations[key($distances)];

        return $closest;
    }

    // gps tayyn
    public function importGps(){

        $uri = "http://atlogistika.com/api/api.php?cmd=list";
        $username = 'turkmenavtoulag';
        $password = 'Awto996';
        $params['headers'] = [
            "Authorization" => "Basic " . base64_encode("$username:$password"),
            'Content-type' => 'application/json'
        ];
        $params['verify'] = false;
        $params['timeout'] = 180;
        $client = new Client();
        $response = $client->request('GET', $uri, $params);
        $data = json_decode($response->getBody(), true);
        if($data['list'] != null){
            foreach($data['list'] as $item){
                $item = (array)$item;
                $existingBus = Bus::where('car_number', $item['vehiclenumber'])->first();
                if($existingBus == null){

                    $bus = Bus::create([
                        'car_number' => $item['vehiclenumber'],
                        'location'   => new Point((float)$item['status']['lat'], (float)$item['status']['lon']),
                        'speed' => $item['status']['speed'],
                        'dir' => $item['status']['dir'],
                        'imei' => $item['imei'],
                        'route_id' => null
                    ]);
                }
                else{
                    if($item['status']){
                        $existingBus->location = new Point((float)$item['status']['lat'], (float)$item['status']['lon'] );
                        $existingBus->speed = $item['status']['speed'];
                        $existingBus->dir = $item['status']['dir'];
                        $existingBus->save();
                    }
                }
            }
        }

        //$buses = Bus::get();

        $stops = Redis::get('db:stops');
        $all_stops = json_decode($stops);

        Bus::where('status', true)->whereNotNull('route_id')->chunk(100, function($buses) use ($all_stops)
        {
           foreach ($buses as $bus){

               if($bus->route_id != null && $bus->status == 1){
                   $slocs = [];
                   $slocs['lat'] = $bus->location->latitude;
                   $slocs['lng'] = $bus->location->longitude;
                   $nstop = $this->getnearestpoint($all_stops, $slocs);

                   $route_stops = RouteStop::where('route_id', $bus->route_id)->orderBy('index', 'ASC')->get();
                   $first_elem = $route_stops[0];
                   $last_elem = $route_stops[count($route_stops)-1];

                   if($nstop && $nstop->id == $first_elem->stop_id){
                       $bus->prev_stop_id = $nstop->id;
                       $bus->side = 'ahead';
                       $bus->save();
                   }
                   if($nstop && $nstop->id == $last_elem->stop_id){
                       $bus->prev_stop_id = $nstop->id;
                       $bus->side = 'back';
                       $bus->save();
                   }
               }
           }

        });

        Bus::chunk(100, function($buses) use ($all_stops)
        {
            foreach ($buses as $bus){
                if($bus->side == 'ahead' && $bus->route_id != null){
                    $slocs = [];
                    $slocs['lat'] = $bus->location->latitude;
                    $slocs['lng'] = $bus->location->longitude;
                    $nstop = $this->getnearestpoint($all_stops, $slocs);

                    $route_stop = RouteStop::where('route_id', $bus->route_id)->where('stop_id', $nstop->id)->first();

                    if($route_stop != null){
                        $bus->prev_stop_id = $nstop->id;
                        $bus->save();
                    }
                }
                if($bus->side == 'back' && $bus->route_id != null){
                    $slocs = [];
                    $slocs['lat'] = $bus->location->latitude;
                    $slocs['lng'] = $bus->location->longitude;
                    $nstop = $this->getnearestpoint($all_stops, $slocs);

                    $route_stop = EndrouteStop::where('route_id', $bus->route_id)->where('stop_id', $nstop->id)->first();

                    if($route_stop != null){
                        $bus->prev_stop_id = $nstop->id;
                        $bus->save();
                    }
                }
            }
        });
        dd('worked');
        Log::info('worked');
    }

    //import buses, route data from Imdat Admin Panel // stops info tayyn
    public function bus_info(){

        try{
            $params['verify'] = false;
            $uri = "https://edu.ayauk.gov.tm/gps/buses/info";
            $client = new Client();
            $response = $client->request('GET', $uri, $params);
            $data = json_decode($response->getBody(), true);
            $all_buses = Bus::get();
            $count = 0;
            foreach($all_buses as $bus){
                $bus->status = 0;
                $bus->route_id = null;
                $bus->save();
            }
            if(count($data) > 0){
                foreach($data as $item){
                    $car_number = substr($item['car_number'], 0, 2) . '-' . substr($item['car_number'], 2);
                    $route = Route::where('number', (int)$item['number'])->first();
                    $bus = Bus::where('car_number', 'LIKE', '%' . $car_number . '%')->first();
                    if($bus){
                        $bus->status = 1;
                        if($route){
                            $bus->route_id = $route->id;
                        }
                        $bus->save();
                        $count++;
                    }
                }
            }
            else{
                foreach($all_buses as $bus){
                    $bus->status = 0;
                    $bus->prev_stop_id = 0;
                    $bus->route_id = null;
                    $bus->save();
                }
            }
            $routes = Route::get();

            foreach($routes as $route){
                $count = Bus::where('route_id', $route->id)->count();
                if($count > 0){
                    $route->interval = floor($route->routing_time/$count) . ' min';
                }
                else{
                    $route->interval = 0 . ' min';
                }
                $route->save();
            }
           // Log::info("Imported from Imdat");
        }
        catch(\Exception $e){
            $all_buses = Bus::get();
            foreach($all_buses as $bus){
                $bus->status = 0;
                $bus->prev_stop_id = 0;
                $bus->route_id = null;
                $bus->save();
            }//return $e->getMessage();
            Log::info($e->getMessage());
        }
        return "Imported from Imdat";

    }

    public function long(){

        $stop = Stop::first();
        return $stop->location->latitude;
    }

    function setEverythingInCache(){
        $stops = Stop::get()->toArray();
        $stops = json_encode($stops);
        Redis::set('db:stops', $stops);
    }
    function getEverything(){
        $stops_redis = Redis::get('db:stops');
        $stops = Stop::get()->toArray();
        dd(json_decode($stops_redis), $stops);
    }

    public function activate_coords(Request $request){

        $route = Route::find($request->id);
        if( $route->front_line==null && $route->back_line==null || $route->front_line!=null && $route->back_line!=null){

            $objects = json_decode($route->start_coords);
            $objects = collect($objects);
            $objects_back = json_decode($route->end_coords);
            $objects_back = collect($objects_back);

            $points_front =
                $objects->map(function($data) {
                    return new Point((float) $data->lat, (float)$data->long);
                });

            $objects_back =
                $objects_back->map(function($data) {
                    return new Point((float) $data->lat, (float)$data->long);
                });
            $route->front_line = new LineString($points_front);
            $route->back_line = new LineString($objects_back);
            $route->save();

        }
        return redirect()->back();
    }

}
