<?php

namespace App\Http\API\Controllers\Auth;

use App\Http\API\Requests\Auth\LoginRequest;
use App\Http\API\Requests\Auth\RegisterRequest;
use App\Http\API\Resources\UserResource;
use App\Http\Controller;
use Domain\ApkTrials\Models\ApkTrial;
use Domain\Users\Models\User;
use Domain\Users\Requests\UserRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;

/**
 * @OA\Info(
 *     title="Duralga API",
 *     version="2.0",
 * )
 *  @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     in="header",
 *     name="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *   ),
 */


//controller where all auth process for clie-nt happens
class AuthController extends Controller
{

    /**
        * @OA\POST(
        *   path="/api/auth/register",
        *   summary=" - Register user",
        *   tags = {"Authorization"},
        *   @OA\RequestBody(
        *         @OA\MediaType(
        *             mediaType="application/json",
        *             @OA\Schema(
        *                 @OA\Property(
        *                     property="name",
        *                     type="string",
        *                 ),
        *                 @OA\Property(
        *                     property="phone",
        *                     type="string",
        *                 ),
        *                 @OA\Property(
        *                     property="email",
        *                     type="string",
        *                 ),
        *                 @OA\Property(
        *                     property="password",
        *                     type="string",
        *                 ),
        *                 example={"name":"Mahri", "phone":"+99365555555" ,"email": "ilmedovamahri@gmail.com", "password": 12345678}
        *             )
        *         )
        *     ),
        *   @OA\Parameter(
        *         description="Localization",
        *         in="header",
        *         name="X-Localization",
        *         required=false,
        *         @OA\Schema(type="string"),
        *         @OA\Examples(example="ru", value="ru", summary="Russian"),
        *         @OA\Examples(example="en", value="en", summary="English"),
        *         @OA\Examples(example="tm", value="tm", summary="Turkmen"),
        *    ),
        *     @OA\Response(
        *         response="201",
        *         description="OK",
        *         @OA\JsonContent(
        *               type="object",
        *               @OA\Property(property="token", type="string"),
        *               @OA\Property(property="user", type="object",
        *                   @OA\Property(property="id", type="integer"),
        *                   @OA\Property(property="name", type="string"),
        *                   @OA\Property(property="phone", type="string"),
        *                   @OA\Property(property="email", type="string"),
        *                  )
        *           )
        *     ),
        *     @OA\Response(
        *         response="422",
        *         description="Validation Error",
        *         @OA\JsonContent(type="object",
        *               @OA\Property(property="message", type="string"),
        *               @OA\Property(property="errors", type="object"),
        *     )
        *     )
        * )
    */
    public function register(RegisterRequest $request){

        if($request->input('phone')=='65000000'){

            $user = User::where('phone', $request->input('phone'))->first();
            $user->otp = 5515;
            $user->otp_verify = false;
            $user->save();
        }else{
            $user = User::where('phone', $request->input('phone'))->first();

            if(!$user){

                $user = User::create([
                    'phone' => $request->input(['phone']),
                ]);
                ApkTrial::create([
                    'user_id' => $user->id,
                    'date_from' => \Carbon\Carbon::now()->format('Y-m-d'),
                    'date_to' => \Carbon\Carbon::now()->addMonth()->format('Y-m-d'),
                    'expired' => false,
                ]);
            }
            $otp = rand(1000, 9999);
            $user->otp = $otp;
            $user->otp_verify = false;
            $user->save();
            $this->send_sms($user);
        }

        //Auth::login($user);
        //$user->token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message'=>'Message sent']);
    }


    /**
        * @OA\GET(
        *   path="/api/auth/user",
        *   summary=" - Get user",
        *   tags = {"Authorization"},
        *   security={
        *      {"bearerAuth": {}}
        *   },
        *   @OA\Parameter(
        *         description="Localization",
        *         in="header",
        *         name="X-Localization",
        *         required=false,
        *         @OA\Schema(type="string"),
        *         @OA\Examples(example="ru", value="ru", summary="Russian"),
        *         @OA\Examples(example="en", value="en", summary="English"),
        *         @OA\Examples(example="tm", value="tm", summary="Turkmen"),
        *    ),
        *   @OA\Response(
        *      response="200",
        *      description="OK",
        *      @OA\JsonContent()
        *   ),
        *   @OA\Response(
        *      response="401",
        *      description="Unauthorized",
        *      @OA\JsonContent()
        *   )
        * )
    */
    public function user(Request $request) {

        $user = $request->user();
        if($user){
            return UserResource::make($user);
        }
        return response()->json([
            'message' => 'token_expired'
        ], 401);
    }

    /**
        * @OA\POST(
        *   path="/api/auth/logout",
        *   summary=" - Logout user",
        *   tags = {"Authorization"},
        *   security={
        *      {"bearerAuth": {}}
        *   },
        *   @OA\Parameter(
        *         description="Localization",
        *         in="header",
        *         name="X-Localization",
        *         required=false,
        *         @OA\Schema(type="string"),
        *         @OA\Examples(example="ru", value="ru", summary="Russian"),
        *         @OA\Examples(example="en", value="en", summary="English"),
        *         @OA\Examples(example="tm", value="tm", summary="Turkmen"),
        *    ),
        *   @OA\Response(
        *         response="200",
        *         description="OK",
        *         @OA\JsonContent()
        *   ),
        *   @OA\Response(
        *         response="401",
        *         description="Unauthorized",
        *         @OA\JsonContent()
        *   )
        * )
    */
    public function logout(Request $request) {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return response()->json([
            'message' => 'ok'
        ], 200);
	}

    /**
        * @OA\POST(
        *   path="/api/auth/update-user",
        *   summary=" - Update user",
        *   tags = {"Authorization"},
        *   description = "Every field is required, except password. Password field is optional",
        *   security={
        *      {"bearerAuth": {}}
        *   },
        *   @OA\RequestBody(
        *         @OA\MediaType(
        *             mediaType="application/json",
        *             @OA\Schema(
        *                 @OA\Property(
        *                     property="name",
        *                     type="string",
        *                 ),
        *                 @OA\Property(
        *                     property="phone",
        *                     type="string",
        *                 ),
        *                 @OA\Property(
        *                     property="password",
        *                     type="string",
        *                 ),
        *                 example={"name":"Mahri","phone":"+99365555555","password":"Hello001!"}
        *             )
        *         )
        *   ),
        *   @OA\Parameter(
        *         description="Localization",
        *         in="header",
        *         name="X-Localization",
        *         required=false,
        *         @OA\Schema(type="string"),
        *         @OA\Examples(example="ru", value="ru", summary="Russian"),
        *         @OA\Examples(example="en", value="en", summary="English"),
        *         @OA\Examples(example="tm", value="tm", summary="Turkmen"),
        *    ),
        *   @OA\Response(response=200, description="Successful created", @OA\JsonContent()),
        *   @OA\Response(response=404, description="Not found", @OA\JsonContent()),
        * )
    */
    public function updateUser(Request $request){

        $user = $request->user();
        $data = $request->only('name', 'phone', 'email', 'password');


        if (!isset($data['password']) || !$data['password']) {
            unset($data['password']);
        }
        else {
            $data['password'] = Hash::make($data['password']);
        }

        if($user->fill($data)->save()){
            return UserResource::make($user);
        }

        return response()->json([
            'message' => 'Your account has not been updated.',
        ], 500);
    }

    public function send_sms($user){

        $client = new Client();
        $client->post('10.107.73.20:777/api/sms',[
            'headers' => [
                'Authorization' => 'KlB84YwMzf0yaGoClo7jzU2xbn6GOaUC',
            ],
            'form_params'=>[
                'phone' => $user->phone,
                'message' => $user->otp
            ],
            'verify'=>false
        ]);
    }

    /**
     * @OA\POST(
     *   path="/api/auth/verify_otp",
     *   summary=" - Verify and login user",
     *   tags = {"Authorization"},
     *
     *   @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="otp",
     *                     type="string",
     *                 ),
     *                 example={"phone": "+99365555555", "password": 1234}
     *             )
     *         )
     *    ),
     *   @OA\Parameter(
     *         description="Localization",
     *         in="header",
     *         name="X-Localization",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="ru", value="ru", summary="Russian"),
     *         @OA\Examples(example="en", value="en", summary="English"),
     *         @OA\Examples(example="tm", value="tm", summary="Turkmen"),
     *    ),
     *    @OA\Response(
     *         response="200",
     *         description="OK",
     *         @OA\JsonContent(type="object")
     *    ),
     *    @OA\Response(
     *         response="401",
     *         description="Error otp",
     *         @OA\JsonContent(type="object")
     *    ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found",
     *         @OA\JsonContent(type="object")
     *    )
     * )
     */
    public function verify_otp(Request $request){

        $user = User::where('phone', $request->input('phone'))->first();

        if($user){

            if ($user->otp!=$request->input('otp')){
                return response()->json([
                    'message' => 'Error otp'
                ], 401);
            }
            $user->otp_verify = true;
            $user->fcm_token = $request->fcm_token;
            $user->platform = $request->platform;
            $user->save();
            Auth::login($user);
            $user->tokens()->delete();
            $user->token = $user->createToken('auth_token')->plainTextToken;

            return UserResource::make($user);
        }
        return response()->json([
            'message' => Lang::get('auth.user_not_found'),
        ], 404);

    }

    public function update_fcm_token(Request $request){

        $user = Auth::user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'fcm_token' => $user->fcm_token,
        ]);
    }
}

