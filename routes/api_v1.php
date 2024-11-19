<?php

use App\Http\Controllers\API\V1\ArticleController;
use App\Http\Controllers\API\V1\Auth\AuthController;
use App\Http\Controllers\API\V1\Auth\PasswordResetController;
use App\Http\Controllers\API\V1\PreferencesController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);

        Route::post('/login', [AuthController::class, 'login']);

        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth:sanctum');

        Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail']);

        Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])
            ->name('password.reset');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/articles', [ArticleController::class, 'index'])
            ->name('articles.index');

        Route::get('/articles/{id}', [ArticleController::class, 'show'])
            ->name('articles.show');

        Route::get('/preferences', [PreferencesController::class, 'getPreferences']);
        Route::post('/preferences', [PreferencesController::class, 'setPreferences']);

        Route::get('/personalized-feed', [PreferencesController::class, 'personalizedFeed']);
    });
});
