<?php

namespace App\Http\API\Controllers\Banners;

use App\Http\API\Resources\BannerResorce;
use App\Http\Controller;
use Illuminate\Http\Request;
use Domain\Banners\Models\Banner;
use Auth;

class BannerController extends Controller
{
    /**
     * @OA\GET(
     *   path="/api/banners",
     *   summary=" - Get list of stops",
     *   tags = {"Banners"},
     *   @OA\Response(
     *         response=201,
     *         description="OK",
     *   ),
     * )
     */
    public function banners(){

	return BannerResorce::collection(Banner::get());//payment goshulanda ayyrmaly


        //if( user_has_tarif() ){
        //    if(isset(Auth::guard('api')->user()->tarif_settings->banner_status) && Auth::guard('api')->user()->tarif_settings->banner_status) return BannerResorce::collection(Banner::get());
        //    else return BannerResorce::collection([]);

        //}else{
        //    return BannerResorce::collection(Banner::get());
        //}

    }
}
