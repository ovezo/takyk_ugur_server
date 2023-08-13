<?php

namespace App\Http\API\Resources;

use Domain\Favorites\Models\RouteFavorites;
use Illuminate\Http\Resources\Json\JsonResource;
use Auth;

class RouteResource extends JsonResource
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
            'id'            => $this['id'],
            'name'          => $this['name'],
            'number'        => $this['number'],
            'interval'      => $this['interval'],
            'is_favorite'   => $this->is_favorite($this['id']),
            'front_line'    => $this['front_line'],
            'back_line'     => $this['back_line']
        ];
    }
    public function is_favorite($id){

        $route = RouteFavorites::where('user_id', Auth::guard('api')->user()->id)->where('route_id', $id)->first();
        if($route) return true;
        else return false;
    }
}
