<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProfileController;


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile/upload-profile-photo', [ProfileController::class, 'uploadProfilePhoto']);
    Route::post('/profile/upload-gallery', [ProfileController::class, 'uploadGallery']);
});


Route::post('/profile/update', [ProfileController::class, 'update'])->middleware('auth:api');
// Test route to check API
Route::get('/test', function () {
    return response()->json(['message' => 'API working']);
});

// Authentication routes
// Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::post('login', [AuthController::class, 'login']);

// --------------------
// Protected routes (requires Sanctum token)
// --------------------
Route::middleware('auth:sanctum')->group(function () {

    // Get all users
    Route::get('/users', [UserController::class, 'index']);

    // Get single user by ID
    Route::get('/users/{id}', [UserController::class, 'show']);

    // Toggle like for a user
    Route::post('/users/{id}/toggle-like', [UserController::class, 'toggleLike']);

    // Optional: Logout route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});
