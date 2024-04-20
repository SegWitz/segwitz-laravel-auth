<?php

use Illuminate\Support\Facades\Route;
use Segwitz\Auth\Controllers\AuthController;

Route::get('inspire', [AuthController::class,'index']);
