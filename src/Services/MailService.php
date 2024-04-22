<?php
namespace Segwitz\Auth\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService{
    public static function sendOtp($email, $message){
        if(!env('EMAIL_OTP')){
            return false;
        }
        $to = $email;
        $subject = env('APP_NAME') . ' OTP';
        $emailMessage = $message;
        try{
            Mail::raw($emailMessage, function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject);
            });
        }catch(Exception $e){
            Log::debug('email error',[$e->getMessage()]);
            return false;
        }

        return true;
    }
}
