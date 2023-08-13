<?php

namespace App\Http\API\Controllers\Stops;

use App\Http\API\Resources\StopResource;
use App\Http\Controller;
use App\Http\API\Resources\StopCollectionResource;
use Domain\Routes\Models\EndrouteStop;
use Domain\Stops\Models\Stop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use function GuzzleHttp\json_decode;

class StopController extends Controller
{
    public function set_to_cache(){

        $stops = Stop::get()->toArray();
        $stops = json_encode($stops);
        Redis::set('db:stops', $stops);
    }
    /**
     * @OA\GET(
     *   path="/api/stops",
     *   summary=" - Get list of stops",
     *   tags = {"Stops"},
     *   @OA\Response(
     *         response=201,
     *         description="OK",
     *   ),
     *  @OA\Parameter(
     *      name="search",
     *      description="search query parameter",
     *      example="Awtokombinat",
     *      in="query"
     *  ),
     *  @OA\Parameter(
     *      name="endpoints",
     *      description="returns only endpoints if true",
     *      example="true",
     *      in="query"
     *  ),
     * @OA\Parameter(
     *         description="Localization",
     *         in="header",
     *         name="X-Localization",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="ru", value="ru", summary="Russian"),
     *         @OA\Examples(example="en", value="en", summary="English"),
     *         @OA\Examples(example="tm", value="tm", summary="Turkmen"),
     *    ),
     * )
     */
    public function stops(Request $request){

        if($request->id){
           return StopResource::make(Stop::with('routes')->with('endroutes')->find($request->id));
        }
        return StopCollectionResource::collection(Stop::filter());

    }

}
