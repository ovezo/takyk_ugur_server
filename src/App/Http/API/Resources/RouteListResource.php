<?php

namespace App\Http\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Domain\Favorites\Models\RouteFavorites;
use Auth;

class RouteListResource extends JsonResource
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
            'id'            => $this->id,
            'name'          => $this->name,
            'number'        => $this->number,
            'interval'      => $this->interval,
            'stops'         => RouteStopsCollection::collection($this->stops),
            'endstops'      => RouteStopsCollection::collection($this->stops),
            'is_favorite'   => $this->is_favorite($this->id)
        ];
    }
    public function is_favorite($id){

        $route = RouteFavorites::where('user_id', Auth::guard('api')->user()->id)->where('route_id', $id)->first();
        if($route) return true;
        else return false;
    }

}
