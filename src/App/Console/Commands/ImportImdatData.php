<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Domain\Routes\Models\Route;
use Domain\Buses\Models\Bus;
use GuzzleHttp\Client;
use Log;

class ImportImdatData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:imdat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Bus Data from Imdat';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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
             Log::info("Imported from Imdat");
        }
        catch(\Exception $e){
            $all_buses = Bus::get();
            foreach($all_buses as $bus){
                $bus->status = 0;
                $bus->prev_stop_id = 0;
                $bus->route_id = null;
                $bus->save();
            }
            Log::info("Imported from Imdat Successfully");
        }
    }
}
