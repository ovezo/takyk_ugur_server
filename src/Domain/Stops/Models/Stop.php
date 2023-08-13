<?php

namespace Domain\Stops\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\SpatialBuilder;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;

class Stop extends Model
{
    use CrudTrait;
    use HasTranslations;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'stops';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [
        'id',
        'name',
        'location',
        'is_endpoint',
    ];
    // protected $hidden = [];
    // protected $dates = [];
    protected $casts = [
        'location' => Point::class,
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

    public function locate(){
        return '<a class="btn btn-sm btn-link" href="/locate/' . $this->id . '" data-toggle="tooltip">
        <svg style="width:15px" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
        </svg> Locate</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function routes(){
        return $this->belongsToMany('\Domain\Routes\Models\Route', 'route_stops');
    }

    public function endroutes(){
        return $this->belongsToMany('\Domain\Routes\Models\Route', 'endroute_stops');
    }

    public function stop_favorites(){
        return $this->hasmany('\Domain\Favorites\Models\StopFavorites', );
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeFilter($q)
    {

        if(request()->filled('is_endpoint')) {
            $q->where('is_endpoint', request()->is_endpoint);
        }

        $search = request()->search;

        if (request()->filled('search')) {
            $q->where('name', 'LIKE', '%'. $search . '%');
        }
        return $q->get();
    }


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
