<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('api/v1')->middleware('web')->group(function () {

    Route::post('login',  [AuthController::class, 'login'])
        ->name('auth.login')
        ->middleware('throttle:6,1');

    Route::post('logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::get('me', [AuthController::class, 'me'])
        ->name('auth.me');
});
