<?php

namespace Domain\Routes\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Mroute extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'mroutes';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['name','start_coords','end_coords'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function drawLine($crud = false){
        return '<a class="btn btn-sm btn-link" target="_blank" href="/show-m/'.$this->id.'?type=front" data-toggle="tooltip" title="Show on the map"><i class="la la-search"></i> Draw start</a>';
    }

    public function drawLineBack($crud = false){
        return '<a class="btn btn-sm btn-link" target="_blank" href="/show-m/'.$this->id.'?type=back" data-toggle="tooltip" title="Show on the map"><i class="la la-search"></i> Draw back</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

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
