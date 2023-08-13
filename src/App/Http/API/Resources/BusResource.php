<?php

namespace App\Http\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this['route']){
            return [
                'id'          => $this['id'],
                'car_number'  => $this['car_number'],
                'location'    => $this['location'],
                'side'        => $this['side'],
                'dir'        => $this['dir'],
                'route'       => [
                    'id'      => $this['route']->id,
                    'number'      => $this['route']->number,
                    'name'      => $this['route']->name,
                    'interval'      => $this['route']->interval
                ]
            ];
        }else{
            return [
                'id'          => $this['id'],
                'car_number'  => $this['car_number'],
                'location'    => $this['location'],
                'side'        => $this['side'],
                'dir'        => $this['dir']
            ];
         }

    }
}
