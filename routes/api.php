<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('/user')->group(function () {
    Route::middleware(['authLess'])->group(function () {
        Route::post('/signup-otp', [\App\Http\Controllers\UserAuthController::class, 'sendSignupOtp']);
        Route::post('/signup', [\App\Http\Controllers\UserAuthController::class, 'signup']);
        Route::post('/login', [\App\Http\Controllers\UserAuthController::class, 'login']);

    });
    Route::middleware(['withAuth'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\UserAuthController::class, 'logout']);
        Route::post('/auth-info', [\App\Http\Controllers\UserAuthController::class, 'authInfo']);
        Route::put('/update-active-log', [\App\Http\Controllers\UserAuthController::class, 'UpdateActiveLog']);
        Route::get('/get-user', [\App\Http\Controllers\UserAuthController::class, 'getUser']);
    });

});

Route::prefix('/message')->group(function () {

    Route::middleware(['withAuth'])->group(function () {
        Route::post('/conversation/create', [\App\Http\Controllers\MessageController::class, 'sendMessage']);
           
    });

});
