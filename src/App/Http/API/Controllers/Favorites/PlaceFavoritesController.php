<?php

namespace App\Http\API\Controllers\Favorites;

use App\Http\API\Resources\PlaceCollectionResource;
use App\Http\Controller;
use Illuminate\Http\Request;
use Domain\Favorites\Models\PlaceFavorites;
use Auth;

class PlaceFavoritesController extends Controller
{

    public function add_or_remove(Request $request, $place_id){

       $place_favorites = PlaceFavorites::where('user_id', Auth::guard('api')->user()->id)->where('place_id', $place_id)->first();

       if(!$place_favorites){
           PlaceFavorites::create([
              'user_id'  =>  Auth::guard('api')->user()->id,
              'place_id' =>  $place_id
           ]);
           return response()->json(['message'=> 'Successfully added to favorites']);
       }else{
           PlaceFavorites::where('place_id', $place_id)->delete();
           return response()->json(['message'=> 'Successfully removed from favorites']);
       }

    }
    public function favorites(Request $request){

        $favorites = PlaceFavorites::where('user_id', Auth::guard('api')->user()->id)->with('place')->get()->pluck('place');
        return PlaceCollectionResource::collection($favorites);

    }
}
