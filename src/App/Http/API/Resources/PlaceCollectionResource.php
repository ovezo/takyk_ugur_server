<?php

namespace App\Http\API\Resources;

use Domain\Places\Models\PlaceCategories;
use Illuminate\Http\Resources\Json\JsonResource;
use Domain\Favorites\Models\PlaceFavorites;
use Auth;

class PlaceCollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'location' => $this->location,
            'logo'     => 'http://119.235.115.196/storage/'.$this->logo,
            'category' => $this->place_category->name,
            'is_favorite'   => $this->is_favorite($this->id),
        ];
    }
    public function is_favorite($id){

        $place = PlaceFavorites::where('user_id', Auth::guard('api')->user()->id)->where('place_id', $id)->first();
        if($place) return true;
        else return false;
    }


}
