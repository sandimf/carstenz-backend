<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarstenzController;

Route::prefix('v1')->group(function () {
    Route::get('/screenings/questionnaires/carstensz', [CarstenzController::class, 'index']);
    Route::post('/screening/cartensz', [CarstenzController::class, 'store']);
    Route::get('/screening/cartensz/success/{uuid}', [CarstenzController::class, 'success']);
    Route::get('/screening/cartensz/list', [CarstenzController::class, 'listScreenings']);
    Route::get('/screening/cartensz/detail/{uuid}', [CarstenzController::class, 'getScreeningDetail']);
});


require __DIR__.'/auth.php';
