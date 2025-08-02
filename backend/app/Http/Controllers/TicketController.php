<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets.
     * - Admins and agents see all tickets.
     * - Users only see their own tickets.
     * - Supports filtering by status, category, and sorting.
     */
    public function index(Request $request)
    {
        $query = Ticket::query();

        // If the user is a normal user, show only their tickets
        if ($request->user()->role == 'user') {
            $query->where('user_id', $request->user()->id);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        // Filter by ticket status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply sorting if requested
        if ($request->has('sort')) {
            $query->orderBy($request->sort, $request->get('order', 'desc'));
        }

        // Return paginated tickets with related user info
        return response()->json($query->with('user')->latest()->paginate(10));
    }

    /**
     * Store a newly created ticket.
     * - Validates input using StoreTicketRequest.
     * - Associates ticket with authenticated user.
     * - Handles file attachment upload.
     */
    public function store(StoreTicketRequest $request)
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate and prepare data
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        // Handle file upload if present
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        // Create ticket
        $ticket = Ticket::create($data);

        return response()->json(['message' => 'Ticket created', 'ticket' => $ticket], 201);
    }

    /**
     * Display a specific ticket.
     * - Only the ticket owner or an agent/admin can access it.
     */
    public function show(Ticket $ticket, Request $request)
    {
        // Restrict access to owner or authorized roles
        if ($request->user()->id !== $ticket->user_id && $request->user()->role === 'user') {
            abort(403, 'Unauthorized');
        }

        return response()->json($ticket->load('user'));
    }

    /**
     * Update the status of a ticket.
     * - Only agents or admins are allowed to change status.
     * - Accepts statuses: open, in_progress, resolved, closed.
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        // ✅ Only allow ticket owner or agent/admin
        if ($request->user()->role === 'user' && $ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ✅ Validation (now includes 'priority')
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|in:Low,Medium,High,Critical',
        ]);

        // ✅ Update all fields, including priority
        $ticket->update($validated);

        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket
        ]);
    }


    public function updateStatus(Request $request, Ticket $ticket)
    {
        // Only agents or admins can update ticket status
        if (!in_array($request->user()->role, ['agent', 'admin'])) {
            abort(403, 'Only agents or admins can update status');
        }

        // Validate status input
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        // Update ticket status
        $ticket->status = $request->status;
        $ticket->save();

        return response()->json(['message' => 'Status updated', 'ticket' => $ticket]);
    }

    public function assignOrUpdate(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'nullable|in:open,in_progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
            'internal_notes' => 'nullable|string',
        ]);

        if ($request->has('status')) {
            $ticket->status = $request->status;
        }

        if ($request->has('assigned_to')) {
            $ticket->assigned_to = $request->assigned_to;
        }

        if ($request->has('internal_notes')) {
            $ticket->internal_notes = $request->internal_notes;
        }

        $ticket->save();

        return response()->json(['message' => 'Ticket updated successfully.', 'ticket' => $ticket]);
    }

}
