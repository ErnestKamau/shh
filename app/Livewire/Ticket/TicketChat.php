<?php

namespace App\Livewire\Ticket;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CRM\Complaint;
use App\Models\CRM\TicketChat as TicketChatModel;
use App\Models\CRM\TicketStatus;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TicketChat extends Component
{
    use WithFileUploads;

    public $ticketId;
    public $ticket;
    public $message = '';
    public $attachments = [];
    public $canChat = false;
    public $isOwner = false;
    public $chatPartner = null;

    protected $listeners = ['refreshChat' => '$refresh'];

    public function mount(int $id): void
    {
        $this->ticketId = $id;
        $this->loadTicket();
    }

    /**
     * Load ticket and check chat permissions
     */
    private function loadTicket(): void
    {
        $user = Auth::user();

        $this->ticket = Complaint::with([
            'category',
            'ticketStatus',
            'ticketPriority',
            'chat.user',
            'chat.attachments',
            'assignedUser',
            'assignedDevelopers',
            'customer'
        ])->findOrFail($this->ticketId);

        // Check if user owns this ticket
        $this->isOwner = ($this->ticket->created_by === $user->name) ||
            ($this->ticket->created_by == $user->id) ||
            ($this->ticket->client_id && $this->ticket->client_id === $user->client_id);

        if (!$this->isOwner && $user->is_support_staff != 1) {
            abort(403, 'You do not have permission to view this ticket.');
        }

        // Check if user can chat
        $isAssigned = $this->ticket->assigned_to && trim($this->ticket->assigned_to) === trim($user->name);
        $isDeveloper = $user->is_support_staff == 1;

        // Check if ticket is "In Progress"
        $inProgressStatus = TicketStatus::active()
            ->where(function ($query) {
                $query->where('name', 'like', '%In Progress%')
                    ->orWhere('name', 'like', '%Progress%');
            })
            ->where('workflow_value', $this->ticket->complaint_workflow)
            ->first();
        $isInProgress = $inProgressStatus !== null;

        // Clients can only chat when ticket is In Progress, developers can always chat
        if ($isDeveloper) {
            $this->canChat = $this->isOwner || $isAssigned;
        } else {
            $this->canChat = ($this->isOwner || $isAssigned) && $isInProgress;
        }

        if (!$this->canChat) {
            if (!$isInProgress && !$isDeveloper) {
                throw ValidationException::withMessages([
                    'message' => ['Chat is only available when the ticket is marked as "In Progress".'],
                ]);
            }
            throw ValidationException::withMessages([
                'message' => ['You can only chat on tickets you own or are assigned to.'],
            ]);
        }

        // Determine chat partner
        if ($this->isOwner) {
            // First try the assignedUser relationship
            $this->chatPartner = $this->ticket->assignedUser;

            // If not found, try assignedDevelopers relationship
            if (!$this->chatPartner && $this->ticket->assignedDevelopers && $this->ticket->assignedDevelopers->count() > 0) {
                $this->chatPartner = $this->ticket->assignedDevelopers->first();
            }

            // If still not found and assigned_to exists, try to find by name
            if (!$this->chatPartner && $this->ticket->assigned_to) {
                $assignedToName = trim($this->ticket->assigned_to);
                // Try exact match first (case-insensitive)
                $this->chatPartner = \App\User::whereRaw('LOWER(name) = LOWER(?)', [$assignedToName])
                    ->first();

                // If still not found, try partial match
                if (!$this->chatPartner) {
                    $this->chatPartner = \App\User::whereRaw('LOWER(name) LIKE LOWER(?)', [$assignedToName . '%'])
                        ->first();
                }
            }
        } else {
            // For developers/support staff, find the ticket owner
            $createdBy = trim($this->ticket->created_by);

            // Try to find by ID first (in case created_by is an ID)
            if (is_numeric($createdBy)) {
                $this->chatPartner = \App\User::find($createdBy);
            }

            // If not found by ID, try by name (exact match, case-insensitive)
            if (!$this->chatPartner) {
                $this->chatPartner = \App\User::whereRaw('LOWER(name) = LOWER(?)', [$createdBy])
                    ->first();
            }

            // If still not found, try partial match
            if (!$this->chatPartner) {
                $this->chatPartner = \App\User::whereRaw('LOWER(name) LIKE LOWER(?)', [$createdBy . '%'])
                    ->first();
            }
        }

        // Mark messages as read
        TicketChatModel::where('ticket_id', $this->ticket->id)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Remove an attachment
     */
    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    /**
     * Send a chat message
     */
    public function sendMessage(): void
    {
        $this->validate([
            'message' => 'nullable|string|max:5000',
            'attachments.*' => 'file|max:10240|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt',
        ], [
            'attachments.*.max' => 'Each attachment must be less than 10MB.',
        ]);

        $user = Auth::user();

        // Strip HTML tags to check if message is empty (but keep the HTML for storage)
        $messageText = strip_tags($this->message ?? '');
        $messageText = trim($messageText);

        // Message or attachments required
        if (empty($messageText) && empty($this->attachments)) {
            $this->addError('message', 'Message or attachment is required.');
            return;
        }

        // Limit to 5 attachments
        if (count($this->attachments) > 5) {
            $this->addError('attachments', 'Maximum 5 attachments allowed.');
            return;
        }

        // Check if ticket is "In Progress"
        $inProgressStatus = TicketStatus::active()
            ->where(function ($query) {
                $query->where('name', 'like', '%In Progress%')
                    ->orWhere('name', 'like', '%Progress%');
            })
            ->where('workflow_value', $this->ticket->complaint_workflow)
            ->first();
        $isInProgress = $inProgressStatus !== null;
        $isDeveloper = $user->is_support_staff == 1;

        // Clients can only send messages when ticket is In Progress
        if (!$isDeveloper && !$isInProgress) {
            $this->addError('message', 'Chat is only available when the ticket is marked as "In Progress".');
            return;
        }

        $chatMessage = TicketChatModel::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => $user->id,
            'message' => $this->message ?? '',
        ]);

        // Handle file uploads
        $syncedAttachments = [];
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                if ($file->isValid()) {
                    try {
                        $path = $file->store('tickets/chat', 'public');
                        $filePath = '/storage/' . $path;

                        $attachment = \App\Models\CRM\TicketChatAttachment::create([
                            'chat_message_id' => $chatMessage->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_type' => $file->isFile() && !str_starts_with($file->getMimeType(), 'image/') ? 'document' : 'image',
                            'file_size' => round($file->getSize() / 1024, 2),
                            'mime_type' => $file->getMimeType(),
                        ]);

                        $syncedAttachments[] = [
                            'file_name' => $attachment->file_name,
                            'file_path' => $attachment->file_path,
                            'file_url' => url($attachment->file_path),
                            'file_type' => $attachment->file_type,
                            'file_size' => $attachment->file_size,
                            'mime_type' => $attachment->mime_type,
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Failed to upload chat attachment', [
                            'error' => $e->getMessage(),
                            'ticket_id' => $this->ticket->id,
                            'chat_message_id' => $chatMessage->id,
                        ]);
                    }
                }
            }
        }

        // Notify the other party
        if ($this->ticket->assignedUser && $this->ticket->assignedUser->id !== $user->id) {
            try {
                $ticketLink = $this->ticket->assignedUser->is_support_staff
                    ? route('developer.tickets.show', $this->ticket->id)
                    : route('tickets.show', $this->ticket->id);
                $body = "Hi {$this->ticket->assignedUser->name},<br><br>
                        You have a new message on ticket <strong>{$this->ticket->ticket_no}</strong> from {$user->name}.<br><br>
                        <a href='{$ticketLink}'>View Ticket</a>";
                $subject = '[' . config('app.name') . '] New Message on Ticket: ' . $this->ticket->ticket_no;
                notify_user($body, $this->ticket->assignedUser->email, $subject);
            } catch (\Exception $e) {
                \Log::error('Failed to send chat notification', [
                    'user_id' => $this->ticket->assignedUser->id,
                    'ticket_id' => $this->ticket->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Sync to developer app via webhook
        try {
            $webhookService = app(WebhookService::class);
            $webhookService->dispatch('ticket.chat_message', $this->ticket, [
                'message' => $chatMessage->message,
                'message_id' => $chatMessage->id,
                'sender' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => 'client',
                ],
                'attachments' => $syncedAttachments,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to dispatch chat webhook to developer', [
                'ticket_id' => $this->ticket->id,
                'message_id' => $chatMessage->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Reset form
        $this->reset(['message', 'attachments']);

        // Dispatch event to clear TinyMCE editor
        $this->dispatch('clear-tinymce-editor');

        // Reload ticket to get new messages
        $this->loadTicket();

        // Dispatch event to refresh chat
        $this->dispatch('message-sent');
    }

    public function render()
    {
        $this->loadTicket();
        return view('livewire.ticket.ticket-chat');
    }
}

