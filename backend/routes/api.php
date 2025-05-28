<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandsController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::get('/admin/user', [AuthController::class, 'listUser']);
});

Route::get('/brands', [BrandsController::class, 'index']);
Route::post('/brands', [BrandsController::class, 'store']);
Route::put('/brands/{id}', [BrandsController::class, 'update']);
Route::delete('/brands/{id}', [BrandsController::class, 'destroy']);

Route::get('/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
