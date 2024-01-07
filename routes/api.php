<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
        Route::post('/send', [\App\Http\Controllers\MessageControllerDraft::class, 'sendMessage']);
        Route::delete('/delete/{id}', [\App\Http\Controllers\MessageControllerDraft::class, 'deleteForMe']);
        Route::delete('/delete/file/{id}', [\App\Http\Controllers\MessageControllerDraft::class, 'deleteFileForMe']);
        Route::get('/messages/{unique_id}', [\App\Http\Controllers\MessageControllerDraft::class, 'messageList']);
        Route::delete('/conversation/clear/{unique_id}', [\App\Http\Controllers\MessageControllerDraft::class, 'clearConversationForMe']);

    });

});
