<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationController;
use Illuminate\Support\Facades\Request;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::put('/organizations/{id}', [OrganizationController::class, 'update']);
    Route::get('/organizations/{id}', [OrganizationController::class, 'show']);
    Route::delete('/organizations/{id}', [OrganizationController::class, 'destroy']);
});
