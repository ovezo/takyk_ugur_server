<?php

namespace Domain\Routes\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Domain\Stops\Models\Stop;
use Domain\Routes\Models\Route;
use Illuminate\Database\Eloquent\Model;

class EndrouteStop extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'endroute_stops';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [
        'index',
        'route_id',
        'stop_id'
    ];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function route(){
        return $this->belongsTo(Route::class, 'route_id');
    }
    public function stop(){
        return $this->belongsTo(Stop::class, 'stop_id');
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
