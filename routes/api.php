<?php

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\ProductCategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get(uri: '/test', action: [ApiController::class, 'test'])->name('test');

// new user reg route
Route::post(uri: '/register', action: [ApiController::class, 'register'])->name('register');

Route::post(uri: '/login', action: [ApiController::class, 'login'])->name('login');

Route::get(uri: '/all-users', action: [ApiController::class, 'getAllUsers'])->name('getAllUsers');

Route::put(uri: '/editUser/{userId}', action: [ApiController::class, 'editUser'])->name('editUser');

Route::delete(uri: '/deleteUser/{userId}', action: [ApiController::class, 'deleteUser'])->name('deleteUser');


Route::apiResource('product-categories', ProductCategoryController::class);