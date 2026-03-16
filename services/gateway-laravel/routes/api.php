<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductGatewayController;
use App\Http\Controllers\Api\SaleGatewayController;

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

    Route::get('/sales', [SaleGatewayController::class, 'index']);
    Route::get('/sales/{id}', [SaleGatewayController::class, 'show']);
    Route::get('/sales/user/{userId}', [SaleGatewayController::class, 'byUser']);
    Route::get('/sales/date-range/search', [SaleGatewayController::class, 'byDateRange']);
    Route::get('/sales/date-range/search', [SaleGatewayController::class, 'byDateRange']);
    Route::post('/sales/process', [SaleGatewayController::class, 'processSale']);
    
});

