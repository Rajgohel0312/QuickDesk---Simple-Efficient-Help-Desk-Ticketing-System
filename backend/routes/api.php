<?php
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// =============== Public Auth Routes =========================

// Register a new user
Route::post('/register', [AuthController::class, 'register']);

// Login a user and return token
Route::post('/login', [AuthController::class, 'login']);

// =============== Protected Routes (Require Auth) =========================

Route::middleware('auth:sanctum')->group(function () {
    // Get logged-in user's profile
    Route::get('/me', [AuthController::class, 'me']);

    // Logout the user
    Route::post('/logout', [AuthController::class, 'logout']);

    // =============== Ticket Routes =========================

    // Get all tickets (filtered based on role and query)
    Route::get('/tickets', [TicketController::class, 'index']);

    // Create a new ticket
    Route::post('/tickets', [TicketController::class, 'store']);

    // View a specific ticket (with role-based access control)
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);

    // Update status of a ticket (only agent/admin)
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);

    // =============== Comment Routes =========================

    // Get all comments for a specific ticket
    Route::get('/tickets/{ticketId}/comments', [CommentController::class, 'index']);

    // Add a comment to a specific ticket
    Route::post('/tickets/{ticketId}/comments', [CommentController::class, 'store']);
});


