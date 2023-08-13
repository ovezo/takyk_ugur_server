<?php

namespace Domain\Permissions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';


    public function tarifpermissions(){
        return $this->belongsToMany('\Domain\Tarifs\Models\Tarif', 'tarif_permissions');
    }

}
