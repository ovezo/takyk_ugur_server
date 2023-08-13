<?php

namespace App\Http\API\Controllers\Favorites;

use App\Http\API\Resources\Favorites;
use App\Http\API\Resources\FavoritesCollection;
use App\Http\Controller;
use Illuminate\Http\Request;
use Domain\Favorites\Models\StopFavorites;
use App\Http\API\Resources\StopCollectionResource;
use Auth;

class StopFavoritesController extends Controller
{

    public function add_or_remove(Request $request, $stop_id){

        $stop_favorites = StopFavorites::where('user_id', Auth::guard('api')->user()->id)->where('stop_id', $stop_id)->first();

        if(!$stop_favorites){
            StopFavorites::create([
                'user_id'  =>  Auth::guard('api')->user()->id,
                'stop_id' =>  $stop_id
            ]);
            return response()->json(['message'=> 'Successfully added to favorites']);
        }else{
            StopFavorites::where('stop_id', $stop_id)->delete();
            return response()->json(['message'=> 'Successfully removed from favorites']);
        }

    }
    public function favorites(Request $request){

        $favorites = StopFavorites::where('user_id', Auth::guard('api')->user()->id)->with('stop')->get()->pluck('stop');
        return StopCollectionResource::collection($favorites);

    }
}
