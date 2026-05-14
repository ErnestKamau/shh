<?php

namespace App\Livewire\Ticket;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CRM\Complaint;
use App\Models\CRM\TicketStatus;
use Illuminate\Support\Facades\Auth;

class ShowTicket extends Component
{
    use WithFileUploads;
    public $ticketId;
    public $ticket;
    public $isOwner = false;
    public $isDraft = false;

    // File upload
    public $uploadScreenshots = [];
    public $uploadDocuments = [];
    public $showUploadForm = false;

    // Archive
    public $showArchiveModal = false;
    public $archiveReason = '';

    public function mount(string $id): void
    {
        $this->ticketId = $id;
        $this->loadTicket();
    }

    /**
     * Load ticket with all relationships
     */
    private function loadTicket(): void
    {
        $user = Auth::user();

        // Build eager load array - only load changeHistory for support staff
        $with = [
            'category',
            'ticketStatus',
            'ticketPriority',
            'chat.user',
            'attachments' => function ($query) {
                $query->where('is_delete', false)->orderBy('created_at', 'desc');
            },
            'assignedUser',
            'assignedDevelopers',
            'customer'
        ];

        // Only load change history for support staff
        if ($user->is_support_staff == 1) {
            $with[] = 'changeHistory.user';
        }

        $this->ticket = Complaint::with($with)->findOrFail($this->ticketId);

        // Check if user owns this ticket (by name, ID, or client_id)
        $this->isOwner = ($this->ticket->created_by === $user->name) ||
            ($this->ticket->created_by == $user->id) ||
            ($this->ticket->client_id && $this->ticket->client_id === $user->client_id);

        // Check if ticket is in draft status
        $draftStatus = TicketStatus::active()
            ->where(function ($query) {
                $query->where('name', 'like', '%Draft%');
            })
            ->where('workflow_value', $this->ticket->complaint_workflow)
            ->first();

        $this->isDraft = $draftStatus !== null;

        if (!$this->isOwner && $user->is_support_staff != 1) {
            abort(403, 'You do not have permission to view this ticket.');
        }

        // Mark messages as read when client views the ticket
        if ($this->isOwner && $user->is_support_staff != 1) {
            \App\Models\CRM\TicketChat::where('ticket_id', $this->ticket->id)
                ->where('user_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        // Share ticket with layout for sidebar chat
        \View::share('ticket', $this->ticket);
    }

    /**
     * Toggle upload form visibility
     */
    public function toggleUploadForm(): void
    {
        $this->showUploadForm = !$this->showUploadForm;
        if (!$this->showUploadForm) {
            $this->reset(['uploadScreenshots', 'uploadDocuments']);
        }
    }

    /**
     * Remove a screenshot from upload
     */
    public function removeUploadScreenshot(int $index): void
    {
        unset($this->uploadScreenshots[$index]);
        $this->uploadScreenshots = array_values($this->uploadScreenshots);
    }

    /**
     * Remove a file from upload
     */
    public function removeUploadFile(int $index): void
    {
        unset($this->uploadDocuments[$index]);
        $this->uploadDocuments = array_values($this->uploadDocuments);
    }

    /**
     * Updated hook for file uploads - called when files are uploaded
     */
    public function updatedUploadScreenshots($value): void
    {
        \Log::info("Ticket {$this->ticket->id}: uploadScreenshots updated");
    }

    public function updatedUploadDocuments($value): void
    {
        \Log::info("Ticket {$this->ticket->id}: uploadDocuments updated");
    }

    /**
     * Upload additional files
     */
    public function uploadFiles(): void
    {
        $user = Auth::user();

        // Check if user owns this ticket
        if (!$this->isOwner && $user->is_support_staff != 1) {
            $this->addError('upload', 'You do not have permission to upload files to this ticket.');
            return;
        }

        // Log file state before processing
        \Log::info("Ticket {$this->ticket->id}: uploadFiles called");
        \Log::info("Ticket {$this->ticket->id}: uploadScreenshots - " . json_encode([
            'empty' => empty($this->uploadScreenshots),
            'is_array' => is_array($this->uploadScreenshots),
            'count' => is_array($this->uploadScreenshots) ? count($this->uploadScreenshots) : 'N/A',
            'type' => gettype($this->uploadScreenshots),
        ]));
        \Log::info("Ticket {$this->ticket->id}: uploadDocuments - " . json_encode([
            'empty' => empty($this->uploadDocuments),
            'is_array' => is_array($this->uploadDocuments),
            'count' => is_array($this->uploadDocuments) ? count($this->uploadDocuments) : 'N/A',
            'type' => gettype($this->uploadDocuments),
        ]));

        // Check if any files are selected
        if (empty($this->uploadScreenshots) && empty($this->uploadDocuments)) {
            \Log::warning("Ticket {$this->ticket->id}: No files selected");
            $this->addError('upload', 'Please select at least one file to upload.');
            return;
        }

        // Validate files only if they exist - use nullable so empty arrays don't cause validation errors
        try {
            $this->validate([
                'uploadScreenshots.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'uploadDocuments.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt|max:2048',
            ], [
                'uploadScreenshots.*.image' => 'Screenshots must be image files.',
                'uploadScreenshots.*.mimes' => 'Screenshots must be JPEG, PNG, JPG, or GIF format.',
                'uploadScreenshots.*.max' => 'Each screenshot must be less than 2MB.',
                'uploadDocuments.*.mimes' => 'Files must be PDF, DOC, DOCX, XLS, XLSX, or TXT format.',
                'uploadDocuments.*.max' => 'Each file must be less than 2MB.',
            ]);
            \Log::info("Ticket {$this->ticket->id}: Validation passed");
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error("Ticket {$this->ticket->id}: Validation failed - " . json_encode($e->errors()));
            $this->addError('upload', 'File validation failed: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors())));
            return;
        }

        try {
            // Log what we're about to process
            \Log::info("Ticket {$this->ticket->id}: Starting file upload process");

            $newAttachmentIds = $this->handleFileUploads($this->ticket->id);
            $uploadedCount = count($newAttachmentIds);

            \Log::info("Ticket {$this->ticket->id}: Upload process completed. Uploaded count: {$uploadedCount}");

            if ($uploadedCount > 0) {
                // Sync to developer app
                try {
                    $syncService = app(\App\Services\DeveloperSyncService::class);
                    $syncService->syncAdditionalAttachments($this->ticket, $newAttachmentIds);
                } catch (\Exception $e) {
                    \Log::error("Ticket {$this->ticket->id}: Failed to sync new attachments - " . $e->getMessage());
                }

                // Reset upload fields
                $this->reset(['uploadScreenshots', 'uploadDocuments', 'showUploadForm']);

                // Reload ticket to show new attachments
                $this->loadTicket();

                session()->flash('upload_success', "Successfully uploaded {$uploadedCount} file(s).");
                $this->dispatch('files-uploaded');
            } else {
                \Log::warning("Ticket {$this->ticket->id}: No files were uploaded despite validation passing");
                $this->addError('upload', 'No files were uploaded. Please check that your files are valid and try again.');
            }
        } catch (\Exception $e) {
            \Log::error("Ticket {$this->ticket->id}: File upload error - " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->addError('upload', 'An error occurred while uploading files: ' . $e->getMessage());
        }
    }

    /**
     * Handle file uploads (screenshots and files)
     */
    private function handleFileUploads(int $ticketId): array
    {
        $user = Auth::user();
        $newAttachmentIds = [];

        // Handle screenshots
        if (!empty($this->uploadScreenshots)) {
            $screenshots = is_array($this->uploadScreenshots) ? $this->uploadScreenshots : [$this->uploadScreenshots];
            $screenshots = array_filter($screenshots, function ($file) {
                return $file !== null && $file !== '';
            });

            foreach ($screenshots as $index => $screenshot) {
                if (!$screenshot || !is_object($screenshot) || !method_exists($screenshot, 'store'))
                    continue;

                try {
                    $path = $screenshot->store('tickets/screenshots', 'public');
                    $fname = '/storage/' . $path;

                    $attachment = \App\Models\CRM\Complaintattachment::create([
                        'complaint_id' => $ticketId,
                        'title' => $screenshot->getClientOriginalName(),
                        'type' => 'screenshot',
                        'file_type' => 'screenshot',
                        'file_path' => $fname,
                        'file_size' => $screenshot->getSize() / 1024,
                        'posted_by' => $user->name,
                        'description' => 'Screenshot uploaded',
                        'is_delete' => false,
                    ]);

                    if ($attachment)
                        $newAttachmentIds[] = $attachment->id;
                } catch (\Exception $e) {
                    \Log::error("Ticket {$ticketId}: Failed to upload screenshot - " . $e->getMessage());
                }
            }
        }

        // Handle files
        if (!empty($this->uploadDocuments)) {
            $documents = is_array($this->uploadDocuments) ? $this->uploadDocuments : [$this->uploadDocuments];
            $documents = array_filter($documents, function ($file) {
                return $file !== null && $file !== '';
            });

            foreach ($documents as $index => $file) {
                if (!$file || !is_object($file) || !method_exists($file, 'store'))
                    continue;

                try {
                    $path = $file->store('tickets/files', 'public');
                    $fname = '/storage/' . $path;

                    $attachment = \App\Models\CRM\Complaintattachment::create([
                        'complaint_id' => $ticketId,
                        'title' => $file->getClientOriginalName(),
                        'type' => 'document',
                        'file_type' => 'document',
                        'file_path' => $fname,
                        'file_size' => $file->getSize() / 1024,
                        'posted_by' => $user->name,
                        'description' => 'File uploaded',
                        'is_delete' => false,
                    ]);

                    if ($attachment)
                        $newAttachmentIds[] = $attachment->id;
                } catch (\Exception $e) {
                    \Log::error("Ticket {$ticketId}: Failed to upload file - " . $e->getMessage());
                }
            }
        }

        return $newAttachmentIds;
    }

    /**
     * Open archive modal
     */
    public function openArchiveModal(): void
    {
        if (!$this->isDraft) {
            $this->addError('archive', 'Only draft tickets can be archived.');
            return;
        }
        $this->archiveReason = '';
        $this->showArchiveModal = true;
    }

    /**
     * Close archive modal
     */
    public function closeArchiveModal(): void
    {
        $this->showArchiveModal = false;
        $this->archiveReason = '';
    }

    /**
     * Archive ticket
     */
    public function archiveTicket()
    {
        $user = Auth::user();

        // Check if user owns this ticket
        if (!$this->isOwner && $user->is_support_staff != 1) {
            $this->addError('archive', 'You do not have permission to archive this ticket.');
            return;
        }

        // Check if ticket is in draft status
        if (!$this->isDraft) {
            $this->addError('archive', 'Only draft tickets can be archived.');
            return;
        }

        // Validate archive reason
        $this->validate([
            'archiveReason' => 'required|string|min:10|max:1000',
        ], [
            'archiveReason.required' => 'Please provide a reason for archiving this ticket.',
            'archiveReason.min' => 'The archive reason must be at least 10 characters.',
            'archiveReason.max' => 'The archive reason may not be greater than 1000 characters.',
        ]);

        // Save archive reason and soft delete the ticket
        $this->ticket->is_archived = true;
        $this->ticket->archived_at = now();
        $this->ticket->archived_by = Auth::user()->name;
        $this->ticket->archive_reason = $this->archiveReason;
        $this->ticket->save();

        // Sync to developer app
        try {
            $syncService = app(\App\Services\DeveloperSyncService::class);
            $syncService->syncArchivedTicket($this->ticket);
        } catch (\Exception $e) {
            \Log::error("Ticket {$this->ticket->id}: Failed to sync archive - " . $e->getMessage());
        }

        $this->ticket->delete();

        $this->showArchiveModal = false;
        $this->reset(['archiveReason']);

        // Redirect to tickets index
        session()->flash('success', 'Ticket archived successfully.');
        return $this->redirect(route('tickets.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.ticket.show-ticket');
    }
}

