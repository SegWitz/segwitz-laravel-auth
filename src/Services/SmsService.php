<?php
namespace Segwitz\Auth\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SmsService{
    public static function sendOtp($mobileNo, $message){
        if(!env('SMS_OTP')){
            return false;
        }

        $data = array(
            'Gw-Username' => env('FIREMOBILE_USERNAME'),
            'Gw-Password' => env('FIREMOBILE_PASSWORD'),
            'Gw-From' => env('APP_NAME'),
            'Gw-To' => '+6' . $mobileNo,
            'Gw-Coding' => '1',
            'Gw-Text' => $message
        );
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://110.4.44.41:15001/cgi-bin/sendsms');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
        ));
        
        // Disable SSL certificate verification (for debugging only)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            Log::debug('cURL sms Error',[curl_error($ch)]);
        } else {
            Log::debug('cURL sms reponse',[$response]);
        }
        curl_close($ch);

        return true;
    }
}
