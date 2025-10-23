<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::prefix('inventory')->group(function () {
    Route::get('/', [InventoryController::class, 'index']);
    Route::post('/', [InventoryController::class, 'store']);
});

Route::prefix('sales')->group(function () {
    Route::post('/', [SalesController::class, 'store']);
    Route::get('/{id}', [SalesController::class, 'show']);
});

Route::prefix('reports')->group(function () {
    Route::get('/sales', [ReportController::class, 'sales']);
});
