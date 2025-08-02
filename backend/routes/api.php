<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;



// Post Route for Registering User
Route::post('/register', [AuthController::class, 'register']);
// Post Route for Login User
Route::post('/login', [AuthController::class, 'login']);

// Checked using sanctum user is logged in or not
Route::middleware('auth:sanctum')->group(function () {
    // Route for getting profile
    Route::get('/me', [AuthController::class, 'me']);
    // Route for Logout user
    Route::post('/logout', [AuthController::class, 'logout']);
});