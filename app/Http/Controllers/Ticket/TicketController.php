<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\CRM\Complaint;
use App\Models\CRM\TicketCategory;
use App\Models\CRM\TicketStatus;
use App\Models\CRM\TicketPriority;
use App\Models\CRM\TicketComment;
use App\Models\CRM\TicketChat;
use App\Models\CRM\Complaintattachment;
use App\Models\CRM\TicketChangeHistory;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the ticket creation form
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Store a new ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'description' => 'required|string|max:5000',
            'screenshots.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB per screenshot
            'files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt|max:2048', // Max 2MB per file
        ], [
            'ticket_category_id.required' => 'Please select a ticket category.',
            'description.required' => 'Please provide a description of your issue.',
            'screenshots.*.image' => 'Screenshots must be image files.',
            'screenshots.*.max' => 'Each screenshot must be less than 2MB.',
            'files.*.max' => 'Each file must be less than 2MB.',
        ]);

        $user = Auth::user();

        // Generate ticket number
        $ticketCount = Complaint::withTrashed()->count() + 1;
        $ticketNoStr = strval($ticketCount);
        if (strlen($ticketNoStr) < 6) {
            $diff = 6 - strlen($ticketNoStr);
            $zero = str_repeat("0", $diff);
            $ticketNo = "TKT" . $zero . $ticketNoStr;
        } else {
            $ticketNo = "TKT" . $ticketNoStr;
        }

        // Generate complaint_id (for backward compatibility)
        $complaintCount = Complaint::withTrashed()->count() + 1;
        $complaintIdStr = strval($complaintCount);
        if (strlen($complaintIdStr) < 4) {
            $diff = 4 - strlen($complaintIdStr);
            $zero = str_repeat("0", $diff);
            $complaintId = "COMP" . $zero . $complaintIdStr;
        } else {
            $complaintId = "COMP" . $complaintIdStr;
        }

        // Get default priority from database
        $defaultPriority = TicketPriority::active()
            ->where('value', 'medium')
            ->first();

        $priorityValue = $defaultPriority ? $defaultPriority->value : 'medium';

        // Get default status (Open) from database
        $defaultStatus = TicketStatus::active()
            ->where(function ($query) {
                $query->where('name', 'like', '%Open%')
                    ->orWhere('name', 'like', '%Draft%');
            })
            ->orderBy('workflow_value')
            ->first();

        if (!$defaultStatus) {
            return redirect()->back()
                ->with('error', 'Default ticket status not found in the system. Please contact an administrator.');
        }

        $workflowValue = $defaultStatus->workflow_value;

        // Create ticket
        $ticket = new Complaint();
        $ticket->complaint_id = $complaintId;
        $ticket->ticket_no = $ticketNo;
        $ticket->ticket_category_id = $request->ticket_category_id;
        $ticket->description = $request->description;
        $ticket->priority = $priorityValue; // Use from database
        $ticket->received_from = $user->name;
        $ticket->registered_by = $user->name;
        $ticket->created_by = $user->name;
        $ticket->date = now();
        $ticket->time_created = now();
        $ticket->complaint_workflow = $workflowValue; // Use from database
        $ticket->submitted_from = 'ticketing_system';
        $ticket->client_id = $user->client_id ?? 0;
        $ticket->raised_by = $user->name;
        $ticket->save();

        // Log change history
        TicketChangeHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'change_type' => 'created',
            'field_name' => 'ticket',
            'old_value' => null,
            'new_value' => 'Ticket created',
            'created_at' => now(),
        ]);

        // Chain of custody
        $chainCustody = new Chain_of_Custody_Complaint();
        $chainCustody->complaint_id = $ticket->id;
        $chainCustody->action = "Create ticket";
        $chainCustody->action_taker_id = $user->id;
        $chainCustody->workflow_stage = $ticket->complaint_workflow;
        $chainCustody->save();

        // Handle file uploads
        $newAttachmentIds = $this->handleFileUploads($request, $ticket->id);

        // Sync to developer app
        try {
            $syncService = app(\App\Services\DeveloperSyncService::class);
            $syncService->syncAdditionalAttachments($ticket, $newAttachmentIds);
        } catch (\Exception $e) {
            \Log::error("Ticket {$ticket->id}: Failed to sync new ticket after creation - " . $e->getMessage());
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket created successfully! Your ticket number is: ' . $ticketNo);
    }

    /**
     * Handle file uploads (screenshots and files)
     */
    private function handleFileUploads(Request $request, int $ticketId): array
    {
        $user = Auth::user();
        $newAttachmentIds = [];

        // Handle screenshots
        // When using name="screenshots[]" with multiple attribute, Laravel returns an array
        \Log::info("Ticket {$ticketId}: Checking for screenshots - hasFile: " . ($request->hasFile('screenshots') ? 'yes' : 'no'));

        if ($request->hasFile('screenshots')) {
            $screenshots = $request->file('screenshots');

            \Log::info("Ticket {$ticketId}: Screenshots received - type: " . gettype($screenshots) . ", is_array: " . (is_array($screenshots) ? 'yes' : 'no'));

            // Laravel returns array when multiple files are uploaded with []
            // But if only one file, it might be a single UploadedFile object
            if (!is_array($screenshots)) {
                \Log::info("Ticket {$ticketId}: Single screenshot detected, converting to array");
                $screenshots = [$screenshots];
            } else {
                \Log::info("Ticket {$ticketId}: Multiple screenshots detected - count: " . count($screenshots));
            }

            $uploadedCount = 0;
            foreach ($screenshots as $screenshot) {

                // Skip if file is null or invalid
                if (!$screenshot || !$screenshot->isValid()) {
                    \Log::warning("Ticket {$ticketId}: Skipping invalid screenshot file");
                    continue;
                }

                try {
                    $path = $screenshot->path();
                    $file = Storage::putFile('tickets/screenshots', new File($path));
                    $file = explode('/', $file);
                    $fname = '/storage/tickets/screenshots/' . urlencode(end($file));

                    $attachment = Complaintattachment::create([
                        'complaint_id' => $ticketId,
                        'title' => 'Screenshot ' . ($uploadedCount + 1),
                        'type' => 'screenshot',
                        'file_type' => 'screenshot',
                        'file_path' => $fname,
                        'file_size' => $screenshot->getSize() / 1024, // Size in KB
                        'posted_by' => $user->name,
                        'description' => 'Screenshot uploaded with ticket',
                        'is_delete' => false,
                    ]);

                    if ($attachment)
                        $newAttachmentIds[] = $attachment->id;

                    $uploadedCount++;
                    \Log::info("Ticket {$ticketId}: Successfully uploaded screenshot {$uploadedCount}");
                } catch (\Exception $e) {
                    \Log::error("Ticket {$ticketId}: Failed to upload screenshot - " . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
                    // Continue with next file instead of breaking
                }
            }
            \Log::info("Ticket {$ticketId}: Total screenshots uploaded: {$uploadedCount} out of " . count($screenshots) . " provided");
        } else {
            \Log::debug("Ticket {$ticketId}: No screenshots in request");
        }

        // Handle files
        \Log::info("Ticket {$ticketId}: Checking for files - hasFile: " . ($request->hasFile('files') ? 'yes' : 'no'));

        if ($request->hasFile('files')) {
            $files = $request->file('files');

            \Log::info("Ticket {$ticketId}: Files received - type: " . gettype($files) . ", is_array: " . (is_array($files) ? 'yes' : 'no'));

            // Laravel returns array when multiple files are uploaded with []
            // But if only one file, it might be a single UploadedFile object
            if (!is_array($files)) {
                \Log::info("Ticket {$ticketId}: Single file detected, converting to array");
                $files = [$files];
            } else {
                \Log::info("Ticket {$ticketId}: Multiple files detected - count: " . count($files));
            }

            $uploadedCount = 0;
            foreach ($files as $file) {

                // Skip if file is null or invalid
                if (!$file || !$file->isValid()) {
                    \Log::warning("Ticket {$ticketId}: Skipping invalid file");
                    continue;
                }

                try {
                    $path = $file->path();
                    $storedFile = Storage::putFile('tickets/files', new File($path));
                    $storedFile = explode('/', $storedFile);
                    $fname = '/storage/tickets/files/' . urlencode(end($storedFile));

                    $attachment = Complaintattachment::create([
                        'complaint_id' => $ticketId,
                        'title' => $file->getClientOriginalName(),
                        'type' => 'document',
                        'file_type' => 'document',
                        'file_path' => $fname,
                        'file_size' => $file->getSize() / 1024, // Size in KB
                        'posted_by' => $user->name,
                        'description' => 'File uploaded with ticket',
                        'is_delete' => false,
                    ]);

                    if ($attachment)
                        $newAttachmentIds[] = $attachment->id;

                    $uploadedCount++;
                    \Log::info("Ticket {$ticketId}: Successfully uploaded file {$uploadedCount}: " . $file->getClientOriginalName());
                } catch (\Exception $e) {
                    \Log::error("Ticket {$ticketId}: Failed to upload file - " . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
                    // Continue with next file instead of breaking
                }
            }
            \Log::info("Ticket {$ticketId}: Total files uploaded: {$uploadedCount} out of " . count($files) . " provided");
        } else {
            \Log::debug("Ticket {$ticketId}: No files in request");
        }

        return $newAttachmentIds;
    }

    /**
     * Show user's tickets list
     */
    public function myTickets(Request $request)
    {
        return view('tickets.my-tickets');
    }

    /**
     * Show client dashboard with ticket statistics
     */
    public function dashboard()
    {
        return view('tickets.dashboard');
    }

    /**
     * List ticket categories as JSON (for error report modal, API use)
     */
    public function listCategories()
    {
        $categories = TicketCategory::active()->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Show ticket categories (clients can manage)
     */
    public function categories()
    {
        return view('tickets.categories');
    }

    /**
     * Store a newly created category (for clients)
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ticket_categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $category = TicketCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'active' => 1, // Default active
        ]);

        // If AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                ]
            ]);
        }

        return redirect()->route('tickets.categories')
            ->with('success', 'Ticket category created successfully.');
    }

    /**
     * Update a category (for clients)
     */
    public function updateCategory(Request $request, $id)
    {
        $category = TicketCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:ticket_categories,name,' . $id,
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('tickets.categories')
            ->with('success', 'Ticket category updated successfully.');
    }

    /**
     * Toggle category active status (for clients)
     */
    public function toggleCategoryStatus($id)
    {
        $category = TicketCategory::findOrFail($id);

        // Check if category is used by any tickets when trying to deactivate
        $ticketCount = $category->tickets()->count();
        if ($ticketCount > 0 && $category->active) {
            return redirect()->back()
                ->with('error', "Cannot deactivate category. It is used by {$ticketCount} ticket(s).");
        }

        // Toggle the status
        $newStatus = $category->active ? 0 : 1;
        $category->update([
            'active' => $newStatus,
        ]);

        $status = $newStatus ? 'activated' : 'deactivated';
        return redirect()->route('tickets.categories')
            ->with('success', "Ticket category {$status} successfully.");
    }

    /**
     * Show client's archived tickets
     */
    public function deleted()
    {
        return view('tickets.deleted');
    }

    /**
     * Show single ticket details
     */
    public function show($id)
    {
        return view('tickets.show', ['ticketId' => $id]);
    }

    /**
     * Show chat page for a ticket
     */
    public function chat($id)
    {
        return view('tickets.chat', ['ticketId' => $id]);
    }

    /**
     * Send a chat message (client to developer)
     */
    public function sendChatMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'attachments.*' => 'file|max:10240|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt',
        ]);

        $user = Auth::user();
        $ticket = Complaint::findOrFail($id);

        // Check if user owns this ticket (by name, ID, or client_id)
        $isOwner = ($ticket->created_by === $user->name) ||
            ($ticket->created_by == $user->id) ||
            ($ticket->client_id && $ticket->client_id === $user->client_id);

        if (!$isOwner && $user->is_support_staff != 1) {
            abort(403, 'You do not have permission to send messages on this ticket.');
        }

        // Only ticket owner or assigned developer can chat
        $isAssigned = $ticket->assigned_to && trim($ticket->assigned_to) === trim($user->name);
        if (!$isOwner && !$isAssigned) {
            abort(403, 'Only the ticket owner or assigned developer can send messages.');
        }

        // Check if ticket is "In Progress" by querying the database
        $inProgressStatus = TicketStatus::active()
            ->where(function ($query) {
                $query->where('name', 'like', '%In Progress%')
                    ->orWhere('name', 'like', '%Progress%');
            })
            ->where('workflow_value', $ticket->complaint_workflow)
            ->first();
        $isInProgress = $inProgressStatus !== null;
        $isDeveloper = $user->is_support_staff == 1;

        // Clients can only send messages when ticket is In Progress
        if (!$isDeveloper && !$isInProgress) {
            return response()->json([
                'success' => false,
                'error' => 'Chat is only available when the ticket is marked as "In Progress".'
            ], 403);
        }

        // Message or attachments required
        if (empty($request->message) && (!$request->hasFile('attachments') || count($request->file('attachments', [])) === 0)) {
            return response()->json([
                'success' => false,
                'error' => 'Message or attachment is required.'
            ], 422);
        }

        // Limit to 5 attachments
        if ($request->hasFile('attachments') && count($request->file('attachments')) > 5) {
            return response()->json([
                'success' => false,
                'error' => 'Maximum 5 attachments allowed.'
            ], 422);
        }

        $chatMessage = TicketChat::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message ?? '',
        ]);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    try {
                        $path = $file->store('tickets/chat', 'public');
                        $filePath = '/storage/' . $path;

                        \App\Models\CRM\TicketChatAttachment::create([
                            'chat_message_id' => $chatMessage->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_type' => $file->isFile() && !str_starts_with($file->getMimeType(), 'image/') ? 'document' : 'image',
                            'file_size' => round($file->getSize() / 1024, 2), // KB
                            'mime_type' => $file->getMimeType(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to upload chat attachment', [
                            'error' => $e->getMessage(),
                            'ticket_id' => $ticket->id,
                            'chat_message_id' => $chatMessage->id,
                        ]);
                    }
                }
            }
        }

        // Notify the other party (if ticket is assigned, notify developer; if developer sends, notify client)
        if ($ticket->assignedUser && $ticket->assignedUser->id !== $user->id) {
            try {
                $ticketLink = $ticket->assignedUser->is_support_staff
                    ? route('developer.tickets.show', $ticket->id)
                    : route('tickets.show', $ticket->id);
                $body = "Hi {$ticket->assignedUser->name},<br><br>
                        You have a new message on ticket <strong>{$ticket->ticket_no}</strong> from {$user->name}.<br><br>
                        <a href='{$ticketLink}'>View Ticket</a>";
                $subject = '[' . config('app.name') . '] New Message on Ticket: ' . $ticket->ticket_no;
                notify_user($body, $ticket->assignedUser->email, $subject);
            } catch (\Exception $e) {
                \Log::error('Failed to send chat notification', [
                    'user_id' => $ticket->assignedUser->id,
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $chatMessage->load('user'),
            ]);
        }

        // Load attachments for response
        $chatMessage->load(['user', 'attachments']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $chatMessage,
            ]);
        }

        return redirect()->back()->with('success', 'Message sent successfully.');
    }

    /**
     * Get chat messages for a ticket
     */
    public function getChatMessages($id)
    {
        $user = Auth::user();
        $ticket = Complaint::findOrFail($id);

        // Check if user owns this ticket or is assigned developer
        $isOwner = ($ticket->created_by === $user->name) ||
            ($ticket->created_by == $user->id) ||
            ($ticket->client_id && $ticket->client_id === $user->client_id);
        $isAssigned = $ticket->assigned_to && $ticket->assigned_to === $user->name;

        if (!$isOwner && !$isAssigned && $user->is_support_staff != 1) {
            abort(403, 'You do not have permission to view messages on this ticket.');
        }

        $messages = TicketChat::where('ticket_id', $ticket->id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read for the current user
        TicketChat::where('ticket_id', $ticket->id)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'messages' => $messages,
            ]);
        }

        return $messages;
    }

    /**
     * Upload additional files to an existing ticket
     */
    public function uploadFiles(Request $request, $id)
    {
        $request->validate([
            'screenshots.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB per screenshot
            'files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt|max:2048', // Max 2MB per file
        ], [
            'screenshots.*.image' => 'Screenshots must be image files.',
            'screenshots.*.max' => 'Each screenshot must be less than 2MB.',
            'files.*.max' => 'Each file must be less than 2MB.',
        ]);

        $user = Auth::user();
        $ticket = Complaint::findOrFail($id);

        // Check if user owns this ticket (by name, ID, or client_id)
        $isOwner = ($ticket->created_by === $user->name) ||
            ($ticket->created_by == $user->id) ||
            ($ticket->client_id && $ticket->client_id === $user->client_id);

        if (!$isOwner && $user->is_support_staff != 1) {
            abort(403, 'You do not have permission to upload files to this ticket.');
        }

        $newAttachmentIds = $this->handleFileUploads($request, $ticket->id);

        // Sync to developer app
        try {
            $syncService = app(\App\Services\DeveloperSyncService::class);
            $syncService->syncAdditionalAttachments($ticket, $newAttachmentIds);
        } catch (\Exception $e) {
            \Log::error("Ticket {$ticket->id}: Failed to sync new attachments - " . $e->getMessage());
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Files uploaded successfully.');
    }

    /**
     * Parse @mentions from comment text
     */
    private function parseMentions(string $text): array
    {
        $mentions = [];
        // Match @username or @firstname lastname (handles spaces)
        preg_match_all('/@([a-zA-Z0-9_\s]+)/', $text, $matches);

        if (!empty($matches[1])) {
            $usernames = array_map('trim', $matches[1]);
            // Try exact match first
            $users = User::whereIn('name', $usernames)
                ->where('is_support_staff', 1)
                ->get();

            // If exact match fails, try partial matching (for names with spaces)
            if ($users->isEmpty()) {
                foreach ($usernames as $username) {
                    $user = User::where('name', 'like', "%{$username}%")
                        ->where('is_support_staff', 1)
                        ->first();
                    if ($user && !in_array($user->id, $mentions)) {
                        $mentions[] = $user->id;
                    }
                }
            } else {
                $mentions = $users->pluck('id')->toArray();
            }
        }

        return array_values(array_unique($mentions));
    }

    /**
     * Delete a ticket (soft delete for clients)
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $ticket = Complaint::findOrFail($id);

        // Check if user owns this ticket (by name, ID, or client_id)
        $isOwner = ($ticket->created_by === $user->name) ||
            ($ticket->created_by == $user->id) ||
            ($ticket->client_id && $ticket->client_id === $user->client_id);

        if (!$isOwner && $user->is_support_staff != 1) {
            abort(403, 'You do not have permission to archive this ticket.');
        }

        // Only allow archiving if ticket is in draft status
        $draftStatus = TicketStatus::active()
            ->where(function ($query) {
                $query->where('name', 'like', '%Draft%');
            })
            ->where('workflow_value', $ticket->complaint_workflow)
            ->first();

        if (!$draftStatus) {
            return redirect()->back()
                ->with('error', 'Only draft tickets (status: ' . $ticket->workflowName . ') can be archived. Once a ticket is opened, in progress, or resolved, it cannot be archived.');
        }

        // Validate archive reason is required
        $request->validate([
            'archive_reason' => 'required|string|min:10|max:1000',
        ], [
            'archive_reason.required' => 'Please provide a reason for archiving this ticket.',
            'archive_reason.min' => 'The archive reason must be at least 10 characters.',
            'archive_reason.max' => 'The archive reason may not be greater than 1000 characters.',
        ]);

        // Save archive reason and soft delete the ticket
        $ticket->archive_reason = $request->archive_reason;
        $ticket->save();
        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket archived successfully.');
    }
}
