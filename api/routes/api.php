<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobPostController;
use Illuminate\Support\Facades\Route;

// Auth (rate limited: 5 requests per minute)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public job routes (anyone can browse)
Route::get('/job-posts', [JobPostController::class, 'index']);
Route::get('/job-posts/{jobPost}', [JobPostController::class, 'show']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Job posts (authorization is enforced by JobPostPolicy)
    Route::get('/my/job-posts', [JobPostController::class, 'mine']);
    Route::post('/job-posts', [JobPostController::class, 'store']);
    Route::put('/job-posts/{jobPost}', [JobPostController::class, 'update']);
    Route::delete('/job-posts/{jobPost}', [JobPostController::class, 'destroy']);
});