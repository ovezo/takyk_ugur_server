<?php

namespace App\Http\API\Resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollectionResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'stop'  => $this->stop->name,
            'route' => $this->route->number . ' '. $this->route->name,
            'time_from'=> Carbon::parse($this->time_from)->format('H:i'),
            'time_to'=> Carbon::parse($this->time_to)->format('H:i'),
            'stops_notification_qty'=> $this->stops_notification_qty,
            'active_status' => (bool) $this->active_status
        ];
    }
}
