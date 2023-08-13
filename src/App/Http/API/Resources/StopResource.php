<?php

namespace App\Http\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class StopResource extends JsonResource
{
    /**
     * Transform the resource into an array
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'location'    => $this->location,
            'is_endpoint' => $this->is_endpoint,
            'routes' => RouteListResource::collection($this->routes),
            'end_routes' => RouteListResource::collection($this->endroutes)
        ];

    }
}
