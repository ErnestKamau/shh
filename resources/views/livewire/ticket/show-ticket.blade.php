<div>
    <style>
        .ticket-header {
            background: #f8f9fa;
            border-left: 4px solid #6c757d;
            padding: 1.5rem;
            border-radius: 4px;
        }

        .ticket-header.priority-high {
            border-left-color: #dc3545;
        }

        .ticket-header.priority-medium {
            border-left-color: #ffc107;
        }

        .ticket-header.priority-low {
            border-left-color: #28a745;
        }

        .attachment-item {
            display: inline-block;
            margin: 5px;
            padding: 8px 12px;
            background: #e9ecef;
            border-radius: 4px;
        }
    </style>

    <div class="row">
        <div class="col-lg-10 col-xl-9 mx-auto">
            <!-- Ticket Header -->
            <div class="card mb-3">
                <div class="ticket-header priority-{{ strtolower($ticket->priority) }}">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <h4 class="mb-2">
                                <i class="mdi mdi-ticket"></i>
                                <strong>{{ $ticket->ticket_no ?: $ticket->complaint_id }}</strong>
                                <span class="badge {{ $ticket->priorityBadge }} ml-2">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </h4>
                            <div class="mb-2">
                                <span class="badge {{ $ticket->statusBadge }}">
                                    {{ $ticket->workflowName }}
                                </span>
                                @if($ticket->category)
                                    <span class="badge badge-secondary ml-1">
                                        {{ $ticket->category->name }}
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">
                                Created: {{ $ticket->created_at->format('F d, Y \a\t H:i') }}
                                @if($ticket->assignedDevelopers && $ticket->assignedDevelopers->count() > 0)
                                    | Assigned to: {{ $ticket->assignedDevelopers->pluck('name')->implode(', ') }}
                                @elseif($ticket->assigned_to)
                                    | Assigned to: {{ $ticket->assigned_to }}
                                @endif
                            </small>
                        </div>
                        <div>
                            @if($isDraft)
                                <button wire:click="openArchiveModal" class="btn btn-warning btn-sm">
                                    <i class="mdi mdi-archive"></i> Archive Ticket
                                </button>
                            @endif
                            <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left"></i> Back to Tickets
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Description -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="mdi mdi-information"></i> Description</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $ticket->description }}</p>
                </div>
            </div>

            <!-- Attachments -->
            @if($ticket->attachments && $ticket->attachments->count() > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="mdi mdi-paperclip"></i> Attachments
                            <span class="badge badge-light">{{ $ticket->attachments->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($ticket->attachments as $attachment)
                            <div class="attachment-item">
                                <i class="mdi mdi-{{ $attachment->file_type === 'screenshot' ? 'image' : 'file' }}"></i>
                                <a href="{{ $attachment->file_path }}" target="_blank" class="ml-1">
                                    {{ $attachment->title }}
                                </a>
                                @if($attachment->file_size)
                                    <small class="text-muted">({{ number_format($attachment->file_size, 2) }} KB)</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Upload Additional Files -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="mdi mdi-upload"></i> Upload Additional Files</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="toggleUploadForm">
                        <i class="mdi mdi-{{ $showUploadForm ? 'chevron-up' : 'chevron-down' }}"></i>
                    </button>
                </div>
                @if($showUploadForm)
                    <div class="card-body">
                        @if (session()->has('upload_success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-check-circle"></i> {{ session('upload_success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @error('upload')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-alert-circle"></i> {{ $message }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @enderror

                        @error('uploadScreenshots.*')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-alert-circle"></i> {{ $message }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @enderror

                        @error('uploadDocuments.*')
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-alert-circle"></i> {{ $message }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @enderror

                        <form wire:submit.prevent="uploadFiles">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Screenshots</label>
                                        <input type="file" wire:model="uploadScreenshots" accept="image/*" multiple
                                            class="form-control">
                                        <div wire:loading wire:target="uploadScreenshots" class="text-muted small">
                                            Uploading screenshots...
                                        </div>
                                        @if($uploadScreenshots)
                                            @foreach($uploadScreenshots as $index => $screenshot)
                                                <div class="mt-2">
                                                    <span class="badge badge-info">
                                                        {{ is_object($screenshot) && method_exists($screenshot, 'getClientOriginalName') ? $screenshot->getClientOriginalName() : 'Screenshot ' . ($index + 1) }}
                                                        <button type="button" class="btn btn-sm p-0 ml-1"
                                                            wire:click="removeUploadScreenshot({{ $index }})"
                                                            style="background: none; border: none; color: white;">
                                                            ×
                                                        </button>
                                                    </span>
                                                </div>
                                            @endforeach
                                        @endif
                                        <small class="form-text text-muted">PNG, JPG, GIF up to 2MB each</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Files</label>
                                        <input type="file" wire:model="uploadDocuments"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" multiple class="form-control">
                                        <div wire:loading wire:target="uploadDocuments" class="text-muted small">
                                            Uploading files...
                                        </div>
                                        @if($uploadDocuments)
                                            @foreach($uploadDocuments as $index => $file)
                                                <div class="mt-2">
                                                    <span class="badge badge-info">
                                                        {{ is_object($file) && method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'File ' . ($index + 1) }}
                                                        <button type="button" class="btn btn-sm p-0 ml-1"
                                                            wire:click="removeUploadFile({{ $index }})"
                                                            style="background: none; border: none; color: white;">
                                                            ×
                                                        </button>
                                                    </span>
                                                </div>
                                            @endforeach
                                        @endif
                                        <small class="form-text text-muted">PDF, DOC, DOCX, XLS, XLSX, TXT up to 2MB
                                            each</small>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-info" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="uploadFiles">
                                    <i class="mdi mdi-upload"></i> Upload Files
                                </span>
                                <span wire:loading wire:target="uploadFiles">
                                    <i class="mdi mdi-loading mdi-spin"></i> Uploading...
                                </span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Chat Section (if available) -->
            @php
                $currentUser = Auth::user();
                $isAssigned = false;
                if ($ticket->assignedDevelopers && $ticket->assignedDevelopers->count() > 0) {
                    $isAssigned = $ticket->assignedDevelopers->contains('id', $currentUser->id);
                } else {
                    $isAssigned = $ticket->assigned_to && trim($ticket->assigned_to) === trim($currentUser->name);
                }

                $inProgressStatus = \App\Models\CRM\TicketStatus::active()
                    ->where(function ($query) {
                        $query->where('name', 'like', '%In Progress%')
                            ->orWhere('name', 'like', '%Progress%');
                    })
                    ->where('workflow_value', $ticket->complaint_workflow)
                    ->first();
                $isInProgress = $inProgressStatus !== null;

                $canChat = ($isOwner || $isAssigned) && $isInProgress;

                if ($isOwner) {
                    $chatPartner = $ticket->assignedUser;
                    if (!$chatPartner && $ticket->assignedDevelopers && $ticket->assignedDevelopers->count() > 0) {
                        $chatPartner = $ticket->assignedDevelopers->first();
                    }
                    // If still not found and assigned_to exists, try to find by name (case-insensitive)
                    if (!$chatPartner && $ticket->assigned_to) {
                        $assignedToName = trim($ticket->assigned_to);
                        $chatPartner = \App\User::whereRaw('LOWER(name) = LOWER(?)', [$assignedToName])->first();
                        if (!$chatPartner) {
                            $chatPartner = \App\User::whereRaw('LOWER(name) LIKE LOWER(?)', [$assignedToName . '%'])->first();
                        }
                    }
                } else {
                    // For developers/support staff, find the ticket owner
                    $createdBy = trim($ticket->created_by);
                    // Try to find by ID first (in case created_by is an ID)
                    if (is_numeric($createdBy)) {
                        $chatPartner = \App\User::find($createdBy);
                    }
                    // If not found by ID, try by name (exact match, case-insensitive)
                    if (!$chatPartner) {
                        $chatPartner = \App\User::whereRaw('LOWER(name) = LOWER(?)', [$createdBy])->first();
                    }
                    // If still not found, try partial match
                    if (!$chatPartner) {
                        $chatPartner = \App\User::whereRaw('LOWER(name) LIKE LOWER(?)', [$createdBy . '%'])->first();
                    }
                }
            @endphp

            @if($canChat && $chatPartner)
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="mdi mdi-message-text"></i> Chat with {{ $chatPartner->name ?? 'Unknown' }}
                            @if($ticket->chat)
                                @php
                                    $unreadCount = $ticket->chat->filter(function ($message) use ($currentUser) {
                                        return $message->user_id != $currentUser->id
                                            && $message->read_at === null
                                            && $message->user
                                            && $message->user->is_support_staff == 1;
                                    })->count();
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="badge badge-danger">{{ $unreadCount }}</span>
                                @endif
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('tickets.chat', $ticket->id) }}" class="btn btn-primary">
                            <i class="mdi mdi-message-text"></i> Open Chat
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Archive Modal -->
    @if($showArchiveModal)
        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="archiveTicket">
                        <div class="modal-header">
                            <h5 class="modal-title">Archive Ticket</h5>
                            <button type="button" class="close" wire:click="closeArchiveModal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i>
                                Are you sure you want to archive this ticket? This action cannot be undone.
                            </div>
                            <div class="form-group">
                                <label>Archive Reason <span class="text-danger">*</span></label>
                                <textarea wire:model="archiveReason" rows="4" class="form-control"
                                    placeholder="Please provide a reason for archiving this ticket (minimum 10 characters)..."></textarea>
                                @error('archiveReason')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Minimum 10 characters required</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeArchiveModal">Cancel</button>
                            <button type="submit" class="btn btn-warning" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="archiveTicket">
                                    <i class="mdi mdi-archive"></i> Archive Ticket
                                </span>
                                <span wire:loading wire:target="archiveTicket">
                                    <i class="mdi mdi-loading mdi-spin"></i> Archiving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>