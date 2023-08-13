<?php

namespace Domain\Routes\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\SpatialBuilder;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;


class Route extends Model
{
    use CrudTrait;
    use HasTranslations;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'routes';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [
        'id',
        'name',
        'interval',
        'number',
        'front_line',
        'back_line',
        'start_coords',
        'end_coords',
        'routing_time'
    ];
    // protected $hidden = [];
    // protected $dates = [];
    protected $casts = [
        'front_line' => LineString::class,
        'back_line' => LineString::class
    ];
    protected $translatable = ['name'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function newEloquentBuilder($query): SpatialBuilder
    {
        return new SpatialBuilder($query);
    }

    public function activate(){
        return '<a class="btn btn-sm btn-link" href="/activate_coords?id='. $this->id.'">
        <svg style="width:15px" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg> Activate</a>';
    }
    public function front(){
        return '<a class="btn btn-sm btn-link" href="/draw-coords?id='. $this->id .'&type=front" data-toggle="tooltip">
        <svg style="width:15px" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg> Front</a>';
    }

    public function back(){
        return '<a class="btn btn-sm btn-link" href="/draw-coords?id='. $this->id .'&type=back" data-toggle="tooltip">
        <svg style="width:15px" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg> Back</a>';
    }

    public function sortBus($crud = false)
    {
        return '<a class="btn btn-sm btn-link" target="_blank" href="/sort/'.$this->id.'" data-toggle="tooltip" title="Sort"><i class="la la-sort"></i> Sort start</a>';
    }

    public function sortBusBack($crud = false)
    {
        return '<a class="btn btn-sm btn-link" target="_blank" href="/sort-back/'.$this->id.'" data-toggle="tooltip" title="Sort"><i class="la la-sort"></i> Sort back</a>';
    }
    public function scopeFilter($query){
        if(request('search')) {
            $search = request('search');
            $query = $query->where('name', 'LIKE', '%'. $search . '%')->orWhere('number', 'LIKE', '%'.$search.'%');
        }
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function stops()
    {
        return $this->belongsToMany('\Domain\Stops\Models\Stop', 'route_stops');
    }

    public function endstops()
    {
        return $this->belongsToMany('\Domain\Stops\Models\Stop', 'endroute_stops');
    }
    public function placeroutes(){
        return $this->belongsToMany('\Domain\Places\Models\Place', 'place_routes');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

}
