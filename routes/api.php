<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::put('/organizations/{id}', [OrganizationController::class, 'update']);
    Route::get('/organizations/{id}', [OrganizationController::class, 'show']);
    Route::delete('/organizations/{id}', [OrganizationController::class, 'destroy']);
});
