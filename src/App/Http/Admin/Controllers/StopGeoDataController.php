<?php

namespace App\Http\Admin\Controllers;

use App\Http\Controller;
use Domain\Stops\Models\Stop;
use Illuminate\Http\Request;

class StopGeoDataController extends Controller
{
    public function locate($id){
        $stop = Stop::find($id);
        return view('admin.locate',[
            'stop' => $stop
        ]);
    }
}
