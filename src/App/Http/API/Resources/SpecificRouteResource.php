<?php

namespace App\Http\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpecificRouteResource extends JsonResource
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
            'front_line'    => $this->front_line,
            'back_line'     => $this->back_line,
            'stops' => StopCollectionResource::collection($this->stops),
            'end_stops' => StopCollectionResource::collection($this->endstops)
        ];
    }
}
