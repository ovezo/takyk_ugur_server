<?php

namespace App\Console\Commands;

use Domain\Buses\Models\Bus;
use Domain\Routes\Models\EndrouteStop;
use Domain\Routes\Models\RouteStop;
use Domain\Stops\Models\Stop;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\DB;


class RefreshBusLocationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bus:gps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GPS refresh';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->importGPS();
    }


    public function importGPS(){
        // gps tayyn
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

        $buses = Bus::get();

        //$stops = Stop::get();

        $stops = Redis::get('db:stops');
        $stops = json_decode($stops);

        foreach($buses as $bus){
            if($bus->route_id != null && $bus->status == 1){
                $slocs = [];
                $slocs['lat'] = $bus->location->latitude;
                $slocs['lng'] = $bus->location->longitude;
                $nstop = $this->getnearestpoint($stops, $slocs);

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

        foreach($buses as $bus){
            if($bus->side == 'ahead'){
                $slocs = [];
                $slocs['lat'] = $bus->location->latitude;
                $slocs['lng'] = $bus->location->longitude;
                $nstop = $this->getnearestpoint($stops, $slocs);

                $route_stop = RouteStop::where('route_id', $bus->route_id)->where('stop_id', $nstop->id)->first();

                if($route_stop != null){
                    $bus->prev_stop_id = $nstop->id;
                    $bus->save();
                }
            }
            if($bus->side == 'back'){
                $slocs = [];
                $slocs['lat'] = $bus->location->latitude;
                $slocs['lng'] = $bus->location->longitude;
                $nstop = $this->getnearestpoint($stops, $slocs);

                $route_stop = EndrouteStop::where('route_id', $bus->route_id)->where('stop_id', $nstop->id)->first();

                if($route_stop != null){
                    $bus->prev_stop_id = $nstop->id;
                    $bus->save();
                }
            }
        }
        Log::info('Bus old cron work');
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
}
