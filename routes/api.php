<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Middleware\AdminMiddleware;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Admin routes with combined middleware
    Route::middleware(['auth:sanctum', AdminMiddleware::class])->prefix('admin')->group(function () {
        // User management
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);

        // Role management
        Route::get('/roles', [AdminController::class, 'getRoles']);
        Route::post('/roles', [AdminController::class, 'createRole']);
        Route::put('/roles/{role}', [AdminController::class, 'updateRole']);
        Route::delete('/roles/{role}', [AdminController::class, 'deleteRole']);
    });
});
