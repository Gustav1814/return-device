<?php
namespace App\Libraries\Services;
  
class Paypal
{
    public function payment($params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $params['apiEndpoint']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;

    }
}