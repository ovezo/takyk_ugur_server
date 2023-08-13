<?php

namespace App\Http\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TarifsettingResource extends JsonResource
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
            'banner_status' => $this->banner_status,
            'places_status' => $this->places_status,
            'notified_stops' => $this->notified_stops,
            'notified_mins' => $this->notified_mins
        ];

    }
}
