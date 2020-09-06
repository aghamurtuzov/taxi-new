<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;

class ClientSmsController extends Controller
{

    public function sms()
    {
        $payment = new PaymentGatewayGoldenpayController();
        $paymentResult = $payment->getPaymentKeyJSONRequest(100, 'lv', 'v', 'Ödəmə');
        dd($paymentResult);
        return $this->sendSms($this->getPhoneForUlduzum('(055) 746-3160)'));
    }

    public function getPhoneForUlduzum($phone)
    {
        $a = array('(', ')', '-', ' ');

        $b = array('', '', '', '');

        return '994' . substr(str_replace($a, $b, $phone), 1);
    }

    public function sendSms($phone)
    {
        $login = 'otos';
        $password = 'H6sMceg6QM';
        $msisdn = $phone;
        $smsCode = rand(0, 10000);
        $text = 'Tesdiqlenme kodu : ' . $smsCode;
        $sender = 'Otos.az';
        $key = md5(md5($password) . $login . $text . $msisdn . $sender);
        $request_uri = "http://apps.lsim.az/quicksms/v1/send?login=" . $login . "&msisdn=" . $msisdn . "&text=" . str_replace(' ', '%20', $text) . "&sender=" . $sender . "&key=" . $key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $smsCode;
    }

}
