<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuestionOptionController;
use App\Http\Controllers\Api\UserCourseProgressController;
use App\Http\Controllers\Api\UserVideoProgressController;
use App\Http\Controllers\Api\QuizAttemptController;
use App\Http\Controllers\Api\CertificateController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::get('/user', [AuthController::class, 'user']); // Duplicates the one outside, commented out

    // Admin routes with combined middleware
    Route::middleware([AdminMiddleware::class])->prefix('admin')->group(function () { // auth:sanctum is already applied to the parent group
        // User management
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);

        // Role management
        Route::get('/roles', [AdminController::class, 'getRoles']);
        Route::post('/roles', [AdminController::class, 'createRole']);
        Route::put('/roles/{role}', [AdminController::class, 'updateRole']);
        Route::delete('/roles/{role}', [AdminController::class, 'deleteRole']);

        // Category Management
        Route::apiResource('categories', CategoryController::class);

        // Course Management
        Route::apiResource('courses', CourseController::class);

        // Video Management
        Route::apiResource('videos', VideoController::class);

        // Question Management
        Route::apiResource('questions', QuestionController::class);

        // Question Option Management
        Route::apiResource('question-options', QuestionOptionController::class);

        // User Course Progress Management
        Route::apiResource('user-course-progress', UserCourseProgressController::class);

        // User Video Progress Management
        Route::apiResource('user-video-progress', UserVideoProgressController::class);

        // Quiz Attempt Management
        Route::apiResource('quiz-attempts', QuizAttemptController::class);

        // Certificate Management
        Route::apiResource('certificates', CertificateController::class);
    });
});
