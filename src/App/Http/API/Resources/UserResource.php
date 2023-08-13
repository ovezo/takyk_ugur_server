<?php

namespace App\Http\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'token' => $this->token,
            'apk_trial' => user_trial(),
            'apk_trial_to_date' => user_trial_to_date(),
            'user_has_tarif' => user_has_tarif(),
            'tarif_to_date'  => user_tarif_to_date()
        ];
    }
}
