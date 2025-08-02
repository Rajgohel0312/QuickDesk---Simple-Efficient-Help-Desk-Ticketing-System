<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\CategoryPublicController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ======================= Public Auth Routes =======================

// User Registration
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('/attachments/upload', [TicketController::class, 'uploadAttachment']);


// User Login - returns token on success
Route::post('/login', [AuthController::class, 'login']);

// Public Route - Get all categories (for ticket creation)
Route::get('/categories', [CategoryPublicController::class, 'index']);


// ======================= Protected Routes =======================
// All routes inside this group require a valid auth token via Sanctum

Route::middleware('auth:sanctum')->group(function () {

    // Get the authenticated user's details
    Route::get('/me', [AuthController::class, 'me']);

    // Logout the user
    Route::post('/logout', [AuthController::class, 'logout']);

    // =================== Ticket Routes ===================

    // Get all tickets (visible based on user role)
    Route::get('/tickets', [TicketController::class, 'index']);

    // Create a new ticket
    Route::post('/tickets', [TicketController::class, 'store']);

    // Get a single ticket by ID (only owner, agent, or admin)
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);

    // Update ticket status (only by agent or admin)
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);

    // =================== Comment Routes ===================

    // Get all comments for a ticket
    Route::get('/tickets/{ticketId}/comments', [CommentController::class, 'index']);

    // Add a comment to a ticket
    Route::post('/tickets/{ticketId}/comments', [CommentController::class, 'store']);


});
Route::middleware('auth:sanctum')->put('/tickets/{id}', [TicketController::class, 'update']);


// ======================= Admin Routes =======================
// Only accessible to authenticated users with 'admin' middleware

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // RESTful API routes for category management (CRUD)
    Route::apiResource('categories', CategoryController::class);
});

Route::middleware(['auth:sanctum', 'role:admin,agent'])->group(function () {
    Route::post('/tickets/{ticket}/update-status', [TicketController::class, 'assignOrUpdate']);
});