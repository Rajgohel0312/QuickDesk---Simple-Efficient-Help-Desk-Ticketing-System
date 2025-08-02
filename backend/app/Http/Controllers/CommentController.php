<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    // ✅ List all comments for a specific ticket
    public function index($ticketId)
    {
        // Eager load comments with their associated users
        $ticket = Ticket::with('comments.user')->findOrFail($ticketId);

        // If the logged-in user is a normal 'user', ensure they own this ticket
        if (Auth::user()->role === 'user' && $ticket->user_id !== Auth::id()) {
            abort(403); // Forbidden
        }

        // Return the comments as JSON
        return response()->json($ticket->comments);
    }

    // ✅ Store a new comment on a ticket
    public function store(Request $request, $ticketId)
    {
        // Find the ticket or return 404
        $ticket = Ticket::findOrFail($ticketId);

        // If user is 'user', restrict commenting only on their own tickets
        if (Auth::user()->role === 'user' && $ticket->user_id !== Auth::id()) {
            abort(403);
        }

        // Validate incoming request
        $validated = $request->validate([
            'content' => 'required|string',               // Comment text required
            'attachment' => 'nullable|file|max:2048'     // Optional file, max 2MB
        ]);

        $path = null;

        // If a file is uploaded, store it in 'public/attachments'
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
        }

        // Create and save the comment
        $comment = Comment::create([
            'ticket_id' => $ticketId,
            'user_id' => Auth::id(),           // Set the user who added the comment
            'content' => $validated['content'],
            'attachment' => $path,
        ]);

        // Return the comment with user info, and 201 status (created)
        return response()->json($comment->load('user'), 201);
    }
}
