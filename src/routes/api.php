<?php

use Illuminate\Support\Facades\Route;
use Segwitz\Auth\Controllers\AuthController;

Route::prefix('api')->group(function () {
    Route::post('verify/otp', [AuthController::class, 'verifyOtp']);
    Route::post('register', [AuthController::class,'register']);
    Route::post('login', [AuthController::class,'login']);
    Route::post('forgot/password', [AuthController::class, 'forgotPassword']);
    Route::post('reset/password', [AuthController::class, 'resetPassword']);
});