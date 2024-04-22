<?php
namespace Segwitz\Auth\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Segwitz\Auth\Services\DiscordService;
use Segwitz\Auth\Services\MailService;

class AuthController
{
    public function register(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
        ]);  
        if($request->has('mobile_number') AND !empty($request->mobile_number)){
            $request->validate([
                'mobile_number' => 'required|unique:users,mobile_number',
            ]);      
        }
        if($request->has('username') AND !empty($request->username)){
            $request->validate([
                'username' => 'required|unique:users,username',
            ]);      
        }

        $otp = rand(1000,9999);
        $user = User::create([
            'name' => $request->name,
            'username' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number ?? null,
            'username' => $request->username ?? null,
            'password' => bcrypt($request->password),
            'otp' => $otp,
        ]);

        $message = "Speakup | Hello $user->full_name, Please use this OTP $otp to activate your account.";
        DiscordService::sendOtp($message);
        MailService::sendOtp($user->email,$message);   

        return response()->json(['status'=> 'success', 'message' => 'User registered successfully', 'user' => $user->refresh()], 200);
    }

    public function verifyOtp(Request $request){
    
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required',
            'otp_type' => 'required|in:verify_account,forgot_password,change_phone,change_email',
        ]);
        $user = User::find($request->user_id);
        if ($request->otp != $user->otp) {
            return response()->json(['status' => 'error', 'message' => 'Invalid OTP.'], 400);
        }
        if($request->otp_type == 'verify_account'){
            $user->account_verified = true;
            $user->otp = null;
        }

        $user->update();

        $token = $user->createToken('API Token')->plainTextToken;
        return response()->json(['status' => 'success', 'message' => 'OTP verified successfully.', 'user' => $user, 'access_token' => $token], 200);

    }

    public function login(Request $request){
        if(!$request->has('mobile_number') AND !$request->has('email') AND !$request->has('username')){
            return response()->json(['status' => 'error', 'message' => 'Email, phone number or username is required.'], 400);
        }
        if ($request->has('mobile_number')) {
            $checkUser = User::where('mobile_number', $request->mobile_number);
            if (!$checkUser->exists()) {
                return response()->json(['status' => 'error', 'message' => 'User with this mobile number does not exist.'], 400);
            }
            $credentials = $request->only(['mobile_number', 'password']);
        }
        if ($request->has('email')) {
            $checkUser = User::where('email', $request->email);
            if (!$checkUser->exists()) {    
                return response()->json(['status' => 'error', 'message' => 'User with this email does not exist..'], 400);
            }
            $credentials = $request->only(['email', 'password']);
        }
        if ($request->has('username')) {
            $checkUser = User::where('username', $request->username);
            if (!$checkUser->exists()) {    
                return response()->json(['status' => 'error', 'message' => 'User with this username does not exist..'], 400);
            }
            $credentials = $request->only(['username', 'password']);
        }
  
        if (!Auth::attempt($credentials))
            return response()->json(['status' => 'error', 'message' => 'Invalid Credentials.'], 400);

        $user = Auth::user();

        if(!$user->account_verified){
            $otp = rand(1000,9999);
            $user->otp = $otp;
            $user->update();
    
            $message = "RM 0.0 | " . env('APP_NAME') . " | Hello $user->full_name, Please use this OTP $otp to reset your password.";
            
            DiscordService::sendOtp($message);
            MailService::sendOtp($user->email,$message);    

            return response()->json(['status' => 'error', 'message' => 'Account not verified.'], 400);

        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json(['status' => 'success', 'message' => 'Login successfully.', 'user' => $user, 'access_token' => $token], 200);
    }

    public function forgotPassword(Request $request)
    {
        if(!$request->has('mobile_number') AND !$request->has('email')){
            return response()->json(['status' => 'error', 'message' => 'Email or phone number is required.'], 400);
        }
        if ($request->has('mobile_number')) {
            $user = User::where('mobile_number', $request->mobile_number)->first();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User with this mobile number does not exist.'], 400);
            }
        }
        if ($request->has('email')) {
            $user = User::where('email', $request->email)->first();
            if (!$user) {    
                return response()->json(['status' => 'error', 'message' => 'User with this email does not exist..'], 400);
            }
        }
        
        $otp = rand(1000,9999);
        $user->otp = $otp;
        $user->update();

        $message = "RM 0.0 | " . env('APP_NAME') . " | Hello $user->full_name, Please use this OTP $otp to reset your password.";
        
        DiscordService::sendOtp($message);
        MailService::sendOtp($user->email,$message);

        return response()->json(['status' => 'success', 'message' => 'OTP sent successfully.', 'user' => $user], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'otp' => 'required',
            'user_id' => 'required',
            'password' => 'required|confirmed',
        ]);
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User does not exist.'], 400);
        }
        if ($request->otp != $user->otp) {
            return response()->json(['status' => 'error', 'message' => 'Invalid Otp.'], 400);
        }
 
        $user->otp = null;
        $user->password = bcrypt($request->password);
        $user->update();

        return response()->json(['status' => 'success', 'message' => 'Password updated successfully.', 'user' => $user], 200);
    }
}