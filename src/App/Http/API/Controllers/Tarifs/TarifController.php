<?php

namespace App\Http\API\Controllers\Tarifs;

use App\Http\API\Resources\TarifCollectionResource;
use App\Http\API\Resources\UserResource;
use App\Http\Controller;
use Domain\Tarifs\Models\Tarif;
use Illuminate\Http\Request;
use Auth;

class TarifController extends Controller
{

    public function tarifs(){

        return TarifCollectionResource::collection(Tarif::get());
    }

}
