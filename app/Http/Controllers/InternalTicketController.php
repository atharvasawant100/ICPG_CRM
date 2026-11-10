<?php

namespace App\Http\Controllers;

use App\Models\InternalTicket;
use App\Models\InternalTicketAttachment;
use App\Models\InternalTicketMessage;
use App\Models\Task;
use App\Models\User;
use App\Notifications\InternalTicketResolvedNotification;
use App\Notifications\NewInternalTicketAdminNotification;
use App\Rbac\Rbac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InternalTicketController extends Controller
{
    /**
     * List tickets.
     * Admin sees all company tickets.
     * Employees and Managers see strictly their own tickets.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', InternalTicket::class);

        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $query = InternalTicket::with(['user', 'task', 'resolvedBy']);

        // Strict confidentiality: Non-admins can only see their own tickets
        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        // Filter: Status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Filter: Category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter: Priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Search: Ticket number, subject, or description
        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', $search)
                  ->orWhere('subject', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        // Sorting: default urgent/open first, then newest
        $tickets = $query->orderByRaw("FIELD(status, 'open', 'in_review', 'resolved', 'closed') ASC")
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low') ASC")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Calculate summary counters
        $baseCounterQuery = InternalTicket::query();
        if (! $isAdmin) {
            $baseCounterQuery->where('user_id', $user->id);
        }

        $counters = [
            'total' => (clone $baseCounterQuery)->count(),
            'open' => (clone $baseCounterQuery)->where('status', 'open')->count(),
            'in_review' => (clone $baseCounterQuery)->where('status', 'in_review')->count(),
            'resolved' => (clone $baseCounterQuery)->where('status', 'resolved')->count(),
            'urgent' => (clone $baseCounterQuery)->where('priority', 'urgent')->whereIn('status', ['open', 'in_review'])->count(),
        ];

        return view('tickets.index', compact('tickets', 'counters', 'isAdmin'));
    }

    /**
     * Show form to raise a new confidential ticket.
     */
    public function create(Request $request)
    {
        $this->authorize('create', InternalTicket::class);

        $user = $request->user();

        // Pre-selected task if provided
        $preselectedTask = null;
        if ($request->filled('task_id')) {
            $preselectedTask = Task::find($request->input('task_id'));
        }

        // User's own accessible tasks to optionally link
        $myTasks = Task::where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhere('assigned_by', $user->id);
        })->whereNotIn('status', ['completed', 'cancelled'])
          ->latest()
          ->get(['id', 'title', 'status', 'priority', 'department']);

        return view('tickets.create', compact('preselectedTask', 'myTasks'));
    }

    /**
     * Store newly created confidential ticket.
     */
    public function store(Request $request)
    {
        $this->authorize('create', InternalTicket::class);

        $user = $request->user();

        $validated = $request->validate([
            'subject' => 'required|string|min:5|max:255',
            'category' => ['required', Rule::in(array_keys(InternalTicket::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(InternalTicket::PRIORITIES))],
            'task_id' => 'nullable|exists:tasks,id',
            'description' => 'required|string|min:10|max:5000',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,zip,txt',
        ]);

        $ticket = DB::transaction(function () use ($user, $validated, $request) {
            $ticket = InternalTicket::create([
                'user_id' => $user->id,
                'task_id' => $validated['task_id'] ?? null,
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'subject' => trim($validated['subject']),
                'description' => trim($validated['description']),
                'status' => 'open',
                'is_confidential' => true,
            ]);

            // Handle file attachments if present
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (! $file->isValid()) {
                        continue;
                    }

                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs("tickets/{$ticket->id}", $filename, 'local');

                    InternalTicketAttachment::create([
                        'internal_ticket_id' => $ticket->id,
                        'user_id' => $user->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            return $ticket;
        });

        // Notify all Company Administrators (Manager is completely bypassed)
        try {
            $admins = User::active()->whereHas('roles', function ($q) {
                $q->where('name', Rbac::ADMIN_ROLE);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new NewInternalTicketAdminNotification($ticket));
            }
        } catch (\Throwable) {
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Confidential Ticket {$ticket->ticket_number} has been submitted directly to Company Administration.");
    }

    /**
     * Show ticket details and confidential discussion.
     */
    public function show(InternalTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'user',
            'task.assignee',
            'task.creator',
            'resolvedBy',
            'messages.user',
            'attachments.user',
        ]);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Add a message / reply to the confidential ticket discussion.
     */
    public function reply(Request $request, InternalTicket $ticket)
    {
        $this->authorize('reply', $ticket);

        $validated = $request->validate([
            'message' => 'required|string|min:2|max:3000',
            'is_admin_note' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $isAdminNote = $isAdmin && $request->boolean('is_admin_note');

        $message = InternalTicketMessage::create([
            'internal_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => trim($validated['message']),
            'is_admin_note' => $isAdminNote,
        ]);

        // If ticket was closed or resolved and employee replies, reopen it to in_review
        if (! $isAdmin && $ticket->isResolved()) {
            $ticket->update(['status' => 'in_review']);
        }

        // Notify counterpart if not an internal admin note
        if (! $isAdminNote) {
            try {
                if ($isAdmin && $ticket->user && (int) $ticket->user_id !== (int) $user->id) {
                    $ticket->user->notify(new InternalTicketResolvedNotification($ticket, $user));
                } elseif (! $isAdmin) {
                    $admins = User::active()->whereHas('roles', function ($q) {
                        $q->where('name', Rbac::ADMIN_ROLE);
                    })->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new NewInternalTicketAdminNotification($ticket));
                    }
                }
            } catch (\Throwable) {
            }
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', $isAdminNote ? 'Private internal note saved.' : 'Your message has been posted.');
    }

    /**
     * Update ticket status, resolution, and admin directive.
     */
    public function resolve(Request $request, InternalTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_review', 'resolved', 'closed'])],
            'admin_response' => 'nullable|string|max:3000',
            'admin_notes' => 'nullable|string|max:3000',
        ]);

        $user = $request->user();
        $status = $validated['status'];

        $updateData = [
            'status' => $status,
            'admin_response' => $validated['admin_response'] ?? $ticket->admin_response,
            'admin_notes' => $validated['admin_notes'] ?? $ticket->admin_notes,
        ];

        if (in_array($status, ['resolved', 'closed'], true)) {
            $updateData['resolved_by'] = $user->id;
            $updateData['resolved_at'] = now();
        } elseif ($status === 'open') {
            $updateData['resolved_by'] = null;
            $updateData['resolved_at'] = null;
        }

        $ticket->update($updateData);

        // Notify the ticket owner
        if ($ticket->user && (int) $ticket->user_id !== (int) $user->id) {
            try {
                $ticket->user->notify(new InternalTicketResolvedNotification($ticket, $user));
            } catch (\Throwable) {
            }
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} updated to [{$ticket->statusLabel()}].");
    }

    /**
     * Download secure attachment.
     */
    public function downloadAttachment(InternalTicket $ticket, InternalTicketAttachment $attachment)
    {
        $this->authorize('view', $ticket);

        if ((int) $attachment->internal_ticket_id !== (int) $ticket->id) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'File not found on server.');
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }
}

