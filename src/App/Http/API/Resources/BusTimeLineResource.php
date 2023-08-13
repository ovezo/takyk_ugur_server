<?php

namespace App\Http\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusTimeLineResource extends JsonResource
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
            'route_number'   => $this['route_number'],
            'route_name'     => $this['route_name'],
            'vehicle_number' =>$this['vehicle_number'],
            'time_type'      => $this['time_type'],
            'time'           => $this['time'],
            'route'          => RouteResource::make($this['route']),
            'bus'            => BusCollectionResource::make($this['bus'])
        ];
    }
}
