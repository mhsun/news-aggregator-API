<?php

use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Auth\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);

        Route::post('/login', [AuthController::class, 'login']);

        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth:sanctum');

        Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail']);

        Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])
            ->name('password.reset');
    });
});
