<?php

use App\Http\Controllers\Api\v1\Auth\LoginApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [LoginApiController::class, 'login']);
    Route::post('/logout', [LoginApiController::class, 'logout'])->middleware('auth.api');
    Route::post('/refresh', [LoginApiController::class, 'refresh'])->middleware('auth.api');
    Route::get('/me', [LoginApiController::class, 'me'])->middleware('auth.api');
});
