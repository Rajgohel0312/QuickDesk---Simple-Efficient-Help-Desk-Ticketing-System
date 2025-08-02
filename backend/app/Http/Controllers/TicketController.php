<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketActivity;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::query();

        if ($request->user()->role == 'user') {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('sort')) {
            $query->orderBy($request->sort, $request->get('order', 'desc'));
        }

        return response()->json($query->with('user')->latest()->paginate(10));
    }

    public function store(StoreTicketRequest $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        $ticket = Ticket::create($data);

        // ✅ Log creation
        $this->logActivity($ticket->id, 'created', 'Ticket created by user');

        return response()->json(['message' => 'Ticket created', 'ticket' => $ticket], 201);
    }

    public function show(Ticket $ticket, Request $request)
    {
        if ($request->user()->id !== $ticket->user_id && $request->user()->role === 'user') {
            abort(403, 'Unauthorized');
        }

        return response()->json($ticket->load('user'));
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($request->user()->role === 'user' && $ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|in:Low,Medium,High,Critical',
        ]);

        $ticket->update($validated);

        // ✅ Log update
        $this->logActivity($ticket->id, 'updated', 'Ticket updated');

        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket
        ]);
    }
    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'ticket_id' => 'nullable|exists:tickets,id',
            'comment_id' => 'nullable|exists:comments,id',
            'file' => 'required|file|max:2048', // 2MB max
        ]);

        $path = $request->file('file')->store('attachments', 'public');

        $attachment = Attachment::create([
            'ticket_id' => $request->ticket_id,
            'comment_id' => $request->comment_id,
            'user_id' => auth()->id(),
            'file_path' => $path,
            'original_name' => $request->file('file')->getClientOriginalName(),
        ]);

        return response()->json(['attachment' => $attachment], 201);
    }
    public function updateStatus(Request $request, Ticket $ticket)
    {
        if (!in_array($request->user()->role, ['agent', 'admin'])) {
            abort(403, 'Only agents or admins can update status');
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $ticket->status = $request->status;
        $ticket->save();

        // ✅ Log status update
        $this->logActivity($ticket->id, 'status_updated', 'Status changed to ' . $request->status);

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
            $this->logActivity($ticket->id, 'status_updated', 'Status changed to ' . $request->status);
        }

        if ($request->has('assigned_to')) {
            $ticket->assigned_to = $request->assigned_to;
            $this->logActivity($ticket->id, 'assigned', 'Assigned to user ID ' . $request->assigned_to);
        }

        if ($request->has('internal_notes')) {
            $ticket->internal_notes = $request->internal_notes;
            $this->logActivity($ticket->id, 'notes_updated', 'Internal notes updated');
        }

        $ticket->save();

        return response()->json(['message' => 'Ticket updated successfully.', 'ticket' => $ticket]);
    }

    public function logs($id)
    {
        $logs = TicketActivity::where('ticket_id', $id)
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json($logs);
    }

    protected function logActivity($ticketId, $action, $description = null)
    {
        TicketActivity::create([
            'ticket_id' => $ticketId,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}
