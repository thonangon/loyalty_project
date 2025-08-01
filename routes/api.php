<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\OccupationController;
use App\Http\Controllers\Api\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['api', 'auth:sanctum']],function () {

    Route::prefix('organizations')->group(function () {
        Route::get('/', [OrganizationController::class, 'index']);
        Route::post('/', [OrganizationController::class, 'store']);
        Route::put('{id}', [OrganizationController::class, 'update']);
        Route::get('{id}', [OrganizationController::class, 'show']);
        Route::delete('{id}', [OrganizationController::class, 'destroy']);
    });

    Route::prefix('occupations')->group(function () {
        Route::get('/', [OccupationController::class, 'index']);
        Route::post('/', [OccupationController::class, 'store']);
        Route::put('{id}', [OccupationController::class, 'update']);
        Route::get('{id}', [OccupationController::class, 'show']);
        Route::delete('{id}', [OccupationController::class, 'destroy']);
    });
});
Route::prefix('user')->group(function (){
    Route::post('signup', [AuthenticationController::class, 'register']);
    Route::post('login', [AuthenticationController::class, 'login']);
    Route::post('refresh-token', [AuthenticationController::class, 'refreshToken']);
    Route::middleware('auth:sanctum')->post('logout', [AuthenticationController::class, 'logout']);
    Route::middleware('auth:sanctum')->post('delete_account', [AuthenticationController::class, 'deleteAccount']);
});
