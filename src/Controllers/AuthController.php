<?php
namespace Segwitz\Auth\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Segwitz\Auth\Services\DiscordService;
use Segwitz\Auth\Services\MailService;

class AuthController
{
    public function __construct()
    {
        // dd('hit');
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

    public function register(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|confirmed',
            'user_type' => 'required',
        ]);  

        $otp = rand(1000,9999);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'type' => $request->user_type,
            'otp' => $otp,
        ]);

        $message = "Speakup | Hello $user->full_name, Please use this OTP $otp to activate your account.";
        DiscordService::sendOtp($message);
        MailService::sendOtp($user->email,$message);   

        return response()->json(['status'=> 'success', 'message' => 'User registered successfully', 'user' => $user->refresh()], 200);
    }

    public function login(Request $request){
        if(!$request->has('phone_number') AND !$request->has('email') AND !$request->has('username')){
            return response()->json(['status' => 'error', 'message' => 'Email, phone number or username is required.'], 400);
        }
        if ($request->has('phone_number')) {
            $checkUser = User::where('phone_number', $request->phone_number);
            if (!$checkUser->exists()) {
                return response()->json(['status' => 'error', 'message' => 'User with this mobile number does not exist.'], 400);
            }
            $credentials = $request->only(['phone_number', 'password']);
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
        if(!$request->has('phone_number') AND !$request->has('email')){
            return response()->json(['status' => 'error', 'message' => 'Email or phone number is required.'], 400);
        }
        if ($request->has('phone_number')) {
            $user = User::where('phone_number', $request->phone_number)->first();
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