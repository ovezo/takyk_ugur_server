<?php

namespace App\Http\API\Resources;

use Domain\Favorites\Models\PlaceFavorites;
use Domain\Places\Models\Place;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Lang;
use Auth;

class PlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'phone'    => $this->phone,
            'email'    => $this->email,
            'website'  => $this->website,
            'address'  => $this->address,
            'location' => $this->location,
            'images' => [
                'logo'   => 'http://119.235.115.196/storage/'.$this->logo,
                'gallery'=> $this->images,
            ],
            'work_days'  => [
                Lang::get('place.mo') => $this->mo,
                Lang::get('place.tu') => $this->tu,
                Lang::get('place.we') => $this->we,
                Lang::get('place.th') => $this->th,
                Lang::get('place.fr') => $this->fr,
                Lang::get('place.sa') => $this->sa,
                Lang::get('place.su') => $this->su
            ],
            'place_route' => RouteListResource::collection($this->placeroutes),
            'work_time'   => $this->time,
            'category'    => $this->place_category->name,
            'is_favorite' => $this->is_favorite($this->id),
            'related'     => $this->related($this->place_category->id)
        ];
    }
    public function is_favorite($id){

        $place = PlaceFavorites::where('user_id', Auth::guard('api')->user()->id)->where('place_id', $id)->first();
        if($place) return true;
        else return false;
    }
    public function related($place_category_id){

        return PlaceCollectionResource::collection(Place::where('place_category_id', $place_category_id)->take(5)->get());

    }
}
