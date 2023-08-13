<?php

namespace App\Http\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Domain\Favorites\Models\StopFavorites;
use Auth;

class StopCollectionResource extends JsonResource
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
            'id'          => $this->id,
            'name'        => $this->name,
            'location'    => $this->location,
            'is_endpoint' => $this->is_endpoint,
            'is_favorite' => $this->is_favorite($this->id)
        ];
    }

    public function is_favorite($id){

        $stop = StopFavorites::where('user_id', Auth::guard('api')->user()->id)->where('stop_id', $id)->first();
        if($stop) return true;
        else return false;
    }
}
