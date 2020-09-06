<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

class PaymentGatewayGoldenpayController extends Controller
{
    private $urlGetPaymentKey = "https://rest.goldenpay.az/web/service/merchant/getPaymentKey";
    private $urlGetPaymentResult = "https://rest.goldenpay.az/web/service/merchant/getPaymentResult";
    private $urlRedirect = "https://rest.goldenpay.az/web/paypage?payment_key=";


    private $merchantName = "taxibir";
    private $authKey = "97a862f09dd24a99ab91f1b5de6640e0";


    public function payment()
    {
        $paymentResult = $this->getPaymentKeyJSONRequest(100, 'lv', 'v', 'Odeme heyata kecdi');
        $a = $this->getPaymentKeyJSONRequest(100, 'lv', 'v', 'Odeme');
        dd($a);

        return $a;
    }

    public function getPaymentKeyJSONRequest($amount, $lang = 'lv', $cardType, $description)
    {
        $params = array(
            'merchantName' => $this->merchantName,
            'cardType' => $cardType,
            'amount' => $amount,
            'description' => $description
        );

        $params['hashCode'] = $this->getHashcCode($params);
        $params['lang'] = $lang;

        $request = json_encode($params);

        $json = json_decode($this->getJsonContent($this->urlGetPaymentKey, $request));

        $json->urlRedirect = ($this->urlRedirect) . ($json->paymentKey);

        return $json;
    }


    public function getPaymentResult($payment_key)
    {
        $params = array(
            'payment_key' => $payment_key
        );
        $params['hash_code'] = $this->getHashcCode($params);

        $options = array(
            'http' => array(
                'header' => "Accept: application/json\r\n",
                'method' => 'GET'
            )
        );

        $context = stream_context_create($options);
        $json = file_get_contents($this->urlGetPaymentResult . "?" .
            http_build_query($params), false, $context);

        return json_decode($json);
    }


    private function getHashcCode($params)
    {
        return md5($this->authKey . implode($params));
    }
//
//    private function getParamsClone() {
//        return array_merge(array(), $params);
//    }


    private function getJsonContent($url, $content)
    {
        $options = array(
            'http' => array(
                'header' => "Content-type: application/json\r\nAccept: application/json\r\n",
                'method' => 'POST',
                'content' => $content
            ),
        );
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }


    public function getFilteredParam($param)
    {
        $filterList = array(
            'cardType' => "/^[v|m]$/",
            'amount' => '/^[0-9.]*$/',
            'item' => '/^[a-zA-Z0-9]*$/',
            'lang' => '/^(lv|en|ru)$/',
            'payment_key' => '/^[a-zA-Z0-9\-]*$/'
        );

        $filter = $filterList[$param];

        if (is_null($filter) || !is_string($filter)) {
            echo "Filter for this parameter not found: " . $param;
            exit();
        }

        $new_param = filter_input(INPUT_POST, $param, FILTER_SANITIZE_STRING);

        if ($new_param == null) {
            $new_param = filter_input(INPUT_GET, $param, FILTER_SANITIZE_STRING);
        }


        if (!preg_match($filter, $new_param)) {
            echo "Wrong parameter characters: " . $new_param;
            exit();
        }

        return $new_param;
    }
}
