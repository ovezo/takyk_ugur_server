<?php

namespace App\Http\API\Controllers\Payments;
use App\Http\API\Resources\PaymentResource;
use Domain\Payments\Models\Payment;
use Domain\Tarifs\Models\Tarif;
use Domain\Tarifs\Models\TarifSetting;
use GuzzleHttp\Client;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controller;
use Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentController extends Controller
{

    public function tarif_payment(Request $request){

        $payment = $this->payment_create($request);
        $result = $this->online_pay($payment);

        return response()->json(["redirect_url" => $result]);
    }
    public function payment_create($request){

        $tarif_setting = TarifSetting::where('user_id', Auth::guard('api')->user()->id)->first();
        if(!$tarif_setting){
            TarifSetting::create(
                [   'user_id' => Auth::guard('api')->user()->id,
                    'tarif_id' => $request->input('tarif_id')
                ],
            );
        }
        return Payment::create([
            'user_id'   => Auth::guard('api')->user()->id,
            'tarif_id'  => $request->input('tarif_id'),
            'date_from' => \Carbon\Carbon::now()->format('Y-m-d'),
            'date_to'   => \Carbon\Carbon::now()->addMonths($request->input('months')),
            'payment_status' => 'pending',
            'expired'=> false
        ]);
    }

    public function online_pay($payment){

        $client = new Client();
        $response = $client->post('https://mpi.gov.tm/payment/rest/register.do', [
            'verify' => false,
            'connect_timeout' => 15,
            'timeout' => 15,
            'form_params' => [
                'password' => 'Snd623Jhs7Psv36',
                'userName' => '101211007762',
                'pageView' => 'DESKTOP',
                'sessionTimeoutSecs' => 600,
                'description' => 'duralga_toleg',
                'orderNumber' => '000'.$payment->id ,
                //'amount'   => Tarif::find($request->tarif_id)['price'] * 100,
                'amount'   => 1.5 * 100,
                'currency'      => '934',
                'language'      => 'ru',
                'returnUrl'     => 'https://mpi.gov.tm/payment/finish.html',
                'failUrl'       => 'https://mpi.gov.tm/payment/finish.html',
            ],
        ]);
        $arr = json_decode($response->getBody(), true);

        if ($arr['errorCode'] == 0) {

            $payment->order_id = $arr['orderId'];
            $payment->form_url = $arr['formUrl'];
            $payment->payment_status = 'pending';
            $payment->save();
            return $arr['formUrl'];

        }else{
            $payment->payment_status = 'fail';
            $payment->save();
            return $arr;
        }
    }

    public function payments(){

        $payments = Payment::where('user_id', Auth::guard('api')->user()->id)->where('payment_status', 'pending')->whereNotNull('order_id')->get();

        foreach($payments as $payment){

            $client = new Client();
            $response = $client->request('POST', 'https://mpi.gov.tm/payment/rest/getOrderStatus.do', [
                'verify' => false,
                'connect_timeout' => 15,
                'timeout' => 15,
                'form_params' => [
                    'userName' => "101211007762",
                    'password' => "Snd623Jhs7Psv36",
                    'orderId' => $payment->order_id,
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            if($data['ErrorCode'] == 0 && $data['OrderStatus'] == 2){
                $payment->error_message = 'Оплата прошла успешно!';
                $payment->check_response_body = $response->getBody();
                $payment->payment_status = 'success';
                $payment->save();
            }
            else{
                $payment->error_message = ' Оплата не прошла.';
                $payment->payment_status = 'fail';
                $payment->save();
            }
        }
        return PaymentResource::collection(Payment::where('user_id', Auth::guard('api')->user()->id)->get());


    }
}
