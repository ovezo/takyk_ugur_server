<?php

namespace App\Http\API\Controllers\Favorites;

use App\Http\API\Resources\Favorites;
use App\Http\API\Resources\RouteListResource;
use App\Http\Controller;
use Illuminate\Http\Request;
use Domain\Favorites\Models\RouteFavorites;
use Auth;

class RouteFavoritesController extends Controller
{

    public function add_or_remove(Request $request, $route_id){

        $route_favorites = RouteFavorites::where('user_id', Auth::guard('api')->user()->id)->where('route_id', $route_id)->first();

        if(!$route_favorites){
            RouteFavorites::create([
                'user_id'  =>  Auth::guard('api')->user()->id,
                'route_id' =>  $route_id
            ]);
            return response()->json(['message'=> 'Successfully added to favorites']);
        }else{
            RouteFavorites::where('route_id', $route_id)->delete();
            return response()->json(['message'=> 'Successfully removed from favorites']);
        }

    }
    public function favorites(Request $request){

        $favorites = RouteFavorites::where('user_id', Auth::guard('api')->user()->id)->with('route')->get()->pluck('route');
        return RouteListResource::collection($favorites);

    }
}
