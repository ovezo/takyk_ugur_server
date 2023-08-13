<?php

use Support\ResponseErrorMessages;
use Domain\Clients\Models\Client;
use Illuminate\Http\Request;
use Domain\Payments\Models\Payment;


function currentUser()
{
    return auth('api')->user();
}

function getClassName($object)
{
    $classNameWithNamespace = get_class($object);

    return substr($classNameWithNamespace, strrpos($classNameWithNamespace, '\\')+1);
}
function user_has_tarif()
{
        $now = \Carbon\Carbon::now()->format('Y-m-d');
        $payment = Payment::orderBy('created_at', 'desc')
            ->where('user_id', Auth::guard('api')->user()->id)
            ->where('payment_status', 'success')
            ->whereDate('date_from','<=', $now)
            ->whereDate('date_to','>=', $now)
            ->first();
        if($payment) return true;
        return null;

}
function user_tarif_to_date(){

    $now = \Carbon\Carbon::now()->format('Y-m-d');

    $payment = Payment::orderBy('created_at', 'desc')
        ->where('user_id', Auth::guard('api')->user()->id)
        ->where('payment_status', 'success')
        ->whereDate('date_from','<=', $now)
        ->whereDate('date_to','>=', $now)
        ->first();

    if($payment) return $payment->date_to;
    return null;

}
function user_trial()
{
    $now = \Carbon\Carbon::now()->format('Y-m-d');

    $user_apk_trial = \Domain\ApkTrials\Models\ApkTrial::orderBy('created_at', 'desc')
        ->where('user_id', Auth::guard('api')->user()->id)
        ->whereDate('date_from','<=', $now)
        ->whereDate('date_to','>=', $now)
        ->first();
    if($user_apk_trial) return true;
    return null;
}

function user_trial_to_date()
{
    $now = \Carbon\Carbon::now()->format('Y-m-d');

    $user_apk_trial = \Domain\ApkTrials\Models\ApkTrial::orderBy('created_at', 'desc')
        ->where('user_id', Auth::guard('api')->user()->id)
        ->whereDate('date_from','<=', $now)
        ->whereDate('date_to','>=', $now)
        ->first();
    if($user_apk_trial) return $user_apk_trial->date_to;
    return null;
}


