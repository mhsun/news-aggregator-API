<?php

use App\Http\Controllers\V1\ArticleController;
use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Auth\PasswordResetController;
use App\Http\Controllers\V1\PreferencesController;
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

    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/preferences', [PreferencesController::class, 'setPreferences']);
        Route::get('/preferences', [PreferencesController::class, 'getPreferences']);
        Route::get('/personalized-feed', [PreferencesController::class, 'personalizedFeed']);
    });
});
