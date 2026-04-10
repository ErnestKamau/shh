<div wire:poll.2s>
    <style>
        .chat-container {
            height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .message-bubble {
            max-width: 70%;
            margin-bottom: 15px;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
        }

        .message-own {
            background: #007bff;
            color: white;
            margin-left: auto;
            text-align: right;
        }

        .message-other {
            background: white;
            color: #333;
            margin-right: auto;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .chat-input-area {
            border-top: 1px solid #dee2e6;
            padding: 15px;
            background: white;
        }

        .ticket-info-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>

    <div class="row">
        <div class="col-12">
            <!-- Ticket Info Card -->
            <div class="ticket-info-card">
                <div class="d-flex justify-content-between align-items-start">
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
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Customer:</strong> {{ $ticket->customer ?? 'N/A' }}<br>
                                <strong>Category:</strong> {{ $ticket->category->name ?? 'N/A' }}<br>
                                <strong>Created By:</strong> {{ $ticket->created_by ?? 'N/A' }}
                            </div>
                            <div class="col-md-6">
                                <strong>Assigned To:</strong>
                                @if($ticket->assignedDevelopers && $ticket->assignedDevelopers->count() > 0)
                                    {{ $ticket->assignedDevelopers->pluck('name')->implode(', ') }}
                                @elseif($ticket->assignedUser)
                                    {{ $ticket->assignedUser->name }}
                                @elseif($ticket->assigned_to)
                                    {{ $ticket->assigned_to }}
                                @else
                                    Unassigned
                                @endif<br>
                                <strong>Created:</strong> {{ $ticket->created_at->format('F d, Y \a\t H:i') }}
                            </div>
                        </div>
                        @if($ticket->description)
                            <div class="mt-3">
                                <strong>Description:</strong>
                                <div class="mt-2 p-3 bg-light rounded">
                                    {{ $ticket->description }}
                                </div>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-outline-secondary">
                        <i class="mdi mdi-arrow-left"></i> Back to Ticket
                    </a>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="mdi mdi-message-text"></i> Chat with {{ $chatPartner->name ?? 'Unknown' }}
                    </h5>
                </div>
                <div class="chat-container">
                    <!-- Chat Messages -->
                    <div id="chat-messages" class="chat-messages" wire:ignore.self>
                        @if($ticket->chat && $ticket->chat->count() > 0)
                            @foreach($ticket->chat as $message)
                                <div class="mb-3">
                                    <div
                                        class="d-flex {{ $message->user_id === Auth::id() ? 'justify-content-end' : 'justify-content-start' }}">
                                        <div
                                            class="message-bubble {{ $message->user_id === Auth::id() ? 'message-own' : 'message-other' }}">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong style="font-size: 0.9rem;">
                                                    {{ $message->user->name ?? 'Support' }}
                                                </strong>
                                                <small style="opacity: 0.8; font-size: 0.75rem; margin-left: 10px;">
                                                    {{ $message->created_at->format('M d, Y H:i') }}
                                                </small>
                                            </div>
                                            <div style="line-height: 1.6; white-space: pre-wrap;">{!! $message->message !!}
                                            </div>
                                            @if($message->attachments && $message->attachments->count() > 0)
                                                <div class="mt-2">
                                                    @foreach($message->attachments as $attachment)
                                                        @if($attachment->file_type === 'image')
                                                            <div class="mb-2">
                                                                <a href="{{ $attachment->file_path }}" target="_blank">
                                                                    <img src="{{ $attachment->file_path }}"
                                                                        alt="{{ $attachment->file_name }}"
                                                                        style="max-width: 200px; max-height: 200px; border-radius: 8px; cursor: pointer;">
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="mb-2">
                                                                <a href="{{ $attachment->file_path }}" target="_blank"
                                                                    class="btn btn-sm {{ $message->user_id === Auth::id() ? 'btn-outline-light' : 'btn-outline-primary' }}">
                                                                    <i class="mdi mdi-file"></i> {{ $attachment->file_name }}
                                                                    @if($attachment->file_size)
                                                                        <small>({{ number_format($attachment->file_size, 2) }} KB)</small>
                                                                    @endif
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted mt-5">
                                <i class="mdi mdi-message-text-outline" style="font-size: 3rem;"></i>
                                <p class="mt-3">No messages yet. Start the conversation!</p>
                            </div>
                        @endif
                    </div>

                    <!-- Chat Input -->
                    <div class="chat-input-area">
                        <form wire:submit.prevent="sendMessage" id="chat-message-form">
                            <div id="chat-attachment-preview" class="mb-2"></div>
                            <div class="d-flex align-items-end" style="gap: 8px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="document.getElementById('chat-attachments').click()"
                                    style="flex-shrink: 0; height: 40px; width: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="mdi mdi-paperclip"></i>
                                </button>
                                <input type="file" id="chat-attachments" wire:model="attachments"
                                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" multiple class="d-none">
                                <div class="form-group mb-0 flex-grow-1" wire:ignore>
                                    <!-- Hidden textarea for Livewire binding -->
                                    <textarea id="chat-message-input" wire:model="message" class="d-none"></textarea>
                                    <!-- TinyMCE Editor Container -->
                                    <div id="chat-message-editor" style="min-height: 100px;"></div>
                                </div>
                                <button type="submit" class="btn btn-primary"
                                    style="flex-shrink: 0; height: 40px; white-space: nowrap;"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="sendMessage">
                                        <i class="mdi mdi-send"></i> Send
                                    </span>
                                    <span wire:loading wire:target="sendMessage">
                                        <i class="mdi mdi-loading mdi-spin"></i> Sending...
                                    </span>
                                </button>
                            </div>
                            @if($attachments)
                                @foreach($attachments as $index => $file)
                                    <div class="mt-2">
                                        <span class="badge badge-info">
                                            {{ $file->getClientOriginalName() }}
                                            <button type="button" class="btn btn-sm p-0 ml-1"
                                                wire:click="removeAttachment({{ $index }})"
                                                style="background: none; border: none; color: white;">
                                                ×
                                            </button>
                                        </span>
                                    </div>
                                @endforeach
                            @endif
                            <small class="text-muted d-block mt-1">
                                <i class="mdi mdi-information"></i> You can upload images and files (10MB each, max 5
                                attachments)
                            </small>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
    <script>
        let chatEditor = null;
        let isInitializing = false;

        function initChatTinyMCE() {
            // Prevent multiple initializations
            if (isInitializing || (chatEditor && tinymce.get('chat-message-editor'))) {
                return;
            }

            isInitializing = true;

            // Remove existing editor if it exists
            if (tinymce.get('chat-message-editor')) {
                tinymce.remove('#chat-message-editor');
            }

            // Initialize TinyMCE
            tinymce.init({
                selector: '#chat-message-editor',
                menubar: false,
                height: 150,
                plugins: 'lists link code paste',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                branding: false,
                resize: true,
                setup: function (editor) {
                    chatEditor = editor;

                    // Sync content to hidden textarea on change
                    editor.on('change keyup input', function () {
                        const content = editor.getContent();
                        const hiddenInput = document.getElementById('chat-message-input');
                        if (hiddenInput) {
                            hiddenInput.value = content;
                            // Trigger Livewire update
                            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });

                    // Handle paste events
                    editor.on('paste', function () {
                        setTimeout(function () {
                            const content = editor.getContent();
                            const hiddenInput = document.getElementById('chat-message-input');
                            if (hiddenInput) {
                                hiddenInput.value = content;
                                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        }, 100);
                    });
                }
            });

            isInitializing = false;
        }

        function clearChatEditor() {
            if (chatEditor) {
                chatEditor.setContent('');
                // Also clear the hidden input
                const hiddenInput = document.getElementById('chat-message-input');
                if (hiddenInput) {
                    hiddenInput.value = '';
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }

        function syncEditorToLivewire() {
            if (chatEditor) {
                const content = chatEditor.getContent();
                const hiddenInput = document.getElementById('chat-message-input');
                if (hiddenInput) {
                    hiddenInput.value = content;
                    // Trigger Livewire update using multiple methods for reliability
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    // Also trigger Livewire directly if available
                    if (window.Livewire) {
                        const component = Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'));
                        if (component) {
                            component.set('message', content);
                        }
                    }
                }
                return content;
            }
            return '';
        }

        // Intercept form submission to sync TinyMCE content
        document.addEventListener('DOMContentLoaded', function () {
            const chatForm = document.getElementById('chat-message-form');
            if (chatForm) {
                chatForm.addEventListener('submit', function (e) {
                    // Sync TinyMCE content before form submission
                    syncEditorToLivewire();
                }, true); // Use capture phase to run before Livewire's handler
            }
        });

        document.addEventListener('livewire:init', () => {
            // Initialize TinyMCE after Livewire is ready
            setTimeout(() => {
                initChatTinyMCE();
            }, 300);

            // Handle clear event from Livewire
            Livewire.on('clear-tinymce-editor', () => {
                clearChatEditor();
            });

            // Handle message sent event
            Livewire.on('message-sent', () => {
                // Clear editor after message is sent
                setTimeout(() => {
                    clearChatEditor();
                }, 100);
            });

            // Re-initialize after Livewire updates
            Livewire.hook('message.processed', (message, component) => {
                // Auto-scroll chat to bottom after message is sent
                const chatMessages = document.getElementById('chat-messages');
                if (chatMessages) {
                    setTimeout(() => {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, 100);
                }

                // Re-initialize TinyMCE if it was destroyed
                setTimeout(() => {
                    if (!tinymce.get('chat-message-editor')) {
                        initChatTinyMCE();
                    }
                }, 200);
            });
        });

        // Also handle page load
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(() => {
                initChatTinyMCE();
            }, 300);
        });
    </script>
</div>