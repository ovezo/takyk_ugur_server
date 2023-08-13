<?php

namespace App\Http\API\Controllers\UserNotification;

use App\Http\API\Resources\NotificationCollectionResource;
use Carbon\Carbon;
use Domain\Buses\Models\Bus;
use Domain\Routes\Models\Route;
use Domain\Stops\Models\Stop;
use Domain\Routes\Models\EndrouteStop;
use Domain\Routes\Models\RouteStop;
use Domain\Payments\Models\Payment;
use Domain\UserNotification\Models\TarifNotification;
use Domain\UserNotification\Models\UserNotification;
use Illuminate\Http\Request;
use App\Http\Controller;
use Auth;
use Domain\Users\Models\User;
use Kutia\Larafirebase\Facades\Larafirebase;

class UserNotificationController extends Controller
{
    public function index(){

        $notifications = UserNotification::orderBy('created_at', 'desc')->where('user_id', Auth::guard('api')->user()->id)->get();
        return NotificationCollectionResource::collection($notifications);

    }
    public function store(Request $request){

        $notifications = UserNotification::create([
            'user_id'   =>  Auth::guard('api')->user()->id,
            'stop_id'   =>  $request->stop_id,
            'route_id'  =>  $request->route_id,
            'time_from' =>  $request->time_from,
            'time_to'   =>  $request->time_to,
            'stops_notification_qty' => $request->stops_notification_qty,
            'active_status' => true
        ]);

        $notifications = UserNotification::where('user_id', Auth::guard('api')->user()->id)->get();//return $notifications;
        return NotificationCollectionResource::collection($notifications);

    }

    public function check_stops(Request $request){

        $front_line_stop = RouteStop::where('stop_id', $request->stop_id)->where('route_id', $request->route_id)->first();
        $back_line_stop = EndrouteStop::where('stop_id', $request->stop_id)->where('route_id', $request->route_id)->first();

        if($front_line_stop){
            $available_stop = $front_line_stop->index - 2; // available stops before the user stop
            $index_of_stop = $front_line_stop->index;
        }
        if($back_line_stop){
            $available_stop = $back_line_stop->index - 2;
            $index_of_stop = $back_line_stop->index;
        }
        if( $available_stop>0 && $index_of_stop>$available_stop ){
            return response()->json(['data' => $available_stop]);
        }else{
            return response()->json(['data' => 0]);
        }
         //check this function
    }
    public function update($id){

        $user_notification = UserNotification::find($id);
        if($user_notification->active_status) $user_notification->active_status = false;
        else $user_notification->active_status = true;
        $user_notification->save();
        return response()->json(['data' => NotificationCollectionResource::make($user_notification)]);

    }

    public function destroy($id){

        $user_notification = UserNotification::find($id);
        $user_notification->delete();
        return response()->json(['message' => 'Successfully deleted']);

    }

    public function notificate_user(){

        $now = \Carbon\Carbon::now()->format('H:i');
        $users = UserNotification::get();
        foreach($users as $user){

            if( $this->user_check_tarif($user->user_id) || $this->user_trial($user->user_id) ){


                if( $user->time_from<=$now && $user->time_to>=$now ){
                    //if user notification time equal to time now

                    $front_line_stop = RouteStop::where('stop_id', $user->stop_id)->where('route_id', $user->route_id)->first();
                    $back_line_stop = EndrouteStop::where('stop_id', $user->stop_id)->where('route_id', $user->route_id)->first();

                    $stop_side = ''; // side of bus
                    if($front_line_stop){
                        $route_stop_ordered = RouteStop::where('route_id', $front_line_stop->route_id)->orderBy('index')->get();
                        $stop_side = 'ahead';
                    }
                    if($back_line_stop){
                        $route_stop_ordered = EndrouteStop::where('route_id', $back_line_stop->route_id)->orderBy('index')->get();
                        $stop_side = 'back';
                    }

                    $buses = Bus::where('route_id', $user->route_id)->where('status', 1)->where('side', $stop_side)->get()->toArray();

                    $check=0;
                    foreach($buses as $bus){
                        //if bus side a head
                        if( $stop_side=='ahead' ){

                            $user_notificate_stop = RouteStop::where('route_id', $user->route_id)->where('stop_id', $user->stop_id)->first()->index - $user->stops_notification_qty -1;
                            $bus_stop_index = RouteStop::where('route_id', $front_line_stop->route_id)->where('stop_id', $bus['prev_stop_id'])->first()->index + 1;

                            if( $user_notificate_stop==$bus_stop_index ){//barlamaly egere yalnysh ishlese check varian => $user_notificate_stop==$bus_stop_index

                                return $this->create_notification($user, $bus, $front_line_stop, $user_notificate_stop);

                            }
                        }
                        else{

                            $user_notificate_stop = EndrouteStop::where('route_id', $user->route_id)->where('stop_id', $user->stop_id)->first()->index - $user->stops_notification_qty -1;
                            $bus_stop_index = EndrouteStop::where('route_id', $back_line_stop->route_id)->where('stop_id', $bus['prev_stop_id'])->first()->index + 1;

                            if( $user_notificate_stop==$bus_stop_index ){

                                $this->create_notification($user, $bus, $back_line_stop, $user_notificate_stop);

                            }

                        }
                    }
                }
            }
        }
    }

    public function create_notification($user, $bus, $front_line_stop, $user_notificate_stop){

        $latest_exist_notificated = TarifNotification::where('user_id', $user->user_id)->
        where('stop_id', $user->stop_id)->
        where('route_id', $user->route_id)->
        where('bus_id', $bus['id'])->orderBy('created_at', 'desc')->first();
        if($latest_exist_notificated){
            //check time latest sent notification
            if(Carbon::parse($latest_exist_notificated->created_at)->addMinutes(5)->format('Y-m-d H:i') < Carbon::now()->format('Y-m-d H:i')){
                //add five minute to latest record for not repeat message to user

                $message = Route::find($user->route_id)->number. ' ' . Route::find($user->route_id)->name .' ugur => '. Stop::where('id', RouteStop::where('route_id', $front_line_stop->route_id)->where('index', $user_notificate_stop)->pluck('stop_id'))->first()->name .' duralgasyna ýetdi.';
                TarifNotification::create([
                    'user_id' => $user->user_id,
                    'stop_id' => $user->stop_id,
                    'route_id'=> $user->route_id,
                    'bus_id'  => $bus['id'],
                    'message' => $message
                ]);

                //sent notification
                $this->send_notification($message, User::find($user->user_id)->fcm_token, $bus['id'], $user->route_id);
            }

        }else{
            //if notification doesn't exist
            $message = Route::find($user->route_id)->number. ' ' . Route::find($user->route_id)->name .' ugur => '. Stop::where('id', RouteStop::where('route_id', $front_line_stop->route_id)->where('index', $user_notificate_stop)->pluck('stop_id'))->first()->name .' duralgasyna ýetdi.';

            TarifNotification::create([
                'user_id' => $user->user_id,
                'stop_id' => $user->stop_id,
                'route_id'=> $user->route_id,
                'bus_id'  => $bus['id'],
                'message' => $message
            ]);
            //sent notification
            $this->send_notification($message, User::find($user->user_id)->fcm_token, $bus['id'], $user->route_id);
        }
    }

    public function send_notification($message, $token, $bus_id, $route_id){

        try{

            return Larafirebase::withTitle('Duralgadan yatlatma')
                ->withBody($message)
                ->withImage('http://119.235.115.196/duralga_logo.png')
                ->withIcon('http://119.235.115.196/duralga_logo.png')
                ->withSound('default')
                //->withClickAction('https://www.google.com')
                ->withPriority('high')
                ->withAdditionalData([
                    'bus_id' => $bus_id,
                    'route_id' => $route_id,
                ])->sendNotification($token);

        }catch(\Exception $e){
            report($e);
            return 'Something goes wrong while sending notification.';
        }
    }

    public function user_check_tarif($user_id)
    {
        $now = \Carbon\Carbon::now()->format('Y-m-d');
        $payment = Payment::orderBy('created_at', 'desc')
            ->where('user_id', $user_id)
            ->where('payment_status', 'success')
            ->whereDate('date_from','<=', $now)
            ->whereDate('date_to','>=', $now)
            ->first();
        if($payment) return true;
        return false;

    }

    public function user_trial($user_id)
    {
        $now = \Carbon\Carbon::now()->format('Y-m-d');

        $user_apk_trial = \Domain\ApkTrials\Models\ApkTrial::orderBy('created_at', 'desc')
            ->where('user_id', $user_id)
            ->whereDate('date_from','<=', $now)
            ->whereDate('date_to','>=', $now)
            ->first();
        if($user_apk_trial) return true;
        return false;
    }

    public function test_notification($token){

        try{

            return Larafirebase::withTitle('Duralgadan yatlatma')
                ->withBody('Duralgadan test')
                ->withImage('http://119.235.115.196/duralga_logo.png')
                ->withIcon('http://119.235.115.196/duralga_logo.png')
                ->withSound('default')
                //->withClickAction('https://www.google.com')
                ->withPriority('high')
                ->withAdditionalData([
                    'bus_id' => 3,
                    'route_id' => 4,
                ])->sendNotification($token);

        }catch(\Exception $e){
            report($e);
            return 'Something goes wrong while sending notification.';
        }
    }
}
