<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductGatewayController;

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/products', [ProductGatewayController::class, 'index']);
    Route::post('/products', [ProductGatewayController::class, 'store']);
    Route::get('/products/{id}', [ProductGatewayController::class, 'show']);
    Route::put('/products/{id}', [ProductGatewayController::class, 'update']);
    Route::delete('/products/{id}', [ProductGatewayController::class, 'destroy']);
    Route::get('/products/{id}/stock', [ProductGatewayController::class, 'stock']);
    Route::patch('/products/{id}/stock', [ProductGatewayController::class, 'decreaseStock']);
    
});

