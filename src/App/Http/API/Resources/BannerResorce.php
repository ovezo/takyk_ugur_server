<?php

namespace App\Http\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResorce extends JsonResource
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
            'id'    => $this->id,
            'name'  => $this->name,
            'phone' => $this->phone,
            'email' => $this->mail,
            'image' => 'http://119.235.115.196/storage/'.$this->logo,
            'web_link'=> $this->link,
        ];
    }
}
