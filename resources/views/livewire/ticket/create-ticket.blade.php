<div>
    <style>
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: #007bff;
            background: #e7f3ff;
        }

        .file-upload-area.dragover {
            border-color: #007bff;
            background: #cfe2ff;
        }

        .file-preview {
            margin-top: 10px;
        }

        .file-item {
            display: inline-block;
            margin: 5px;
            padding: 5px 10px;
            background: #e9ecef;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .file-item .remove-file {
            cursor: pointer;
            color: #dc3545;
            margin-left: 5px;
        }

        /* Make form fill the page */
        .row.h-100 {
            min-height: calc(100vh - 200px);
            margin: 0;
        }

        .card.h-100 {
            min-height: calc(100vh - 200px);
            margin: 0;
        }

        .card-body.flex-grow-1 {
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
        }

        .card-body form {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            height: 100%;
        }

        .form-group:last-of-type {
            margin-top: auto;
            padding-top: 20px;
        }

        /* Make file upload areas larger */
        .file-upload-area {
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>

    <div class="row h-100">
        <div class="col-12">
            <div class="card h-100 d-flex flex-column">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-ticket"></i> Create New Ticket
                    </h5>
                </div>
                <div class="card-body flex-grow-1" style="overflow-y: auto;">
                    <form wire:submit.prevent="store">
                        <!-- Category Selection -->
                        <div class="form-group">
                            <label for="ticket_category_id" class="font-weight-bold">
                                Category <span class="text-danger">*</span>
                                <button type="button" class="btn btn-sm btn-link p-0 ml-2"
                                    wire:click="$set('showCategoryModal', true)" title="Add New Category">
                                    <i class="mdi mdi-plus-circle text-primary" style="font-size: 18px;"></i>
                                </button>
                            </label>
                            <select wire:model="ticket_category_id" id="ticket_category_id"
                                class="form-control no-select2 @error('ticket_category_id') is-invalid @enderror"
                                required>
                                <option value="">Select a category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ticket_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Select the category that best describes your
                                issue</small>
                        </div>

                        <!-- Description -->
                        <div class="form-group flex-grow-1 d-flex flex-column">
                            <label for="description" class="font-weight-bold">
                                Description <span class="text-danger">*</span>
                            </label>
                            <textarea wire:model="description" id="description"
                                class="form-control @error('description') is-invalid @enderror flex-grow-1"
                                placeholder="Please describe your issue in detail..."
                                style="min-height: 200px; resize: vertical;" required></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Provide as much detail as possible to help us resolve
                                your issue quickly</small>
                        </div>

                        <!-- Screenshots Upload -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Screenshots <span class="text-muted">(Optional)</span>
                            </label>
                            <div class="file-upload-area" x-data="{ 
                                    isDragging: false,
                                    handlePaste(event) {
                                        event.preventDefault();
                                        event.stopPropagation();
                                        
                                        console.log('Paste event detected');
                                        
                                        const items = event.clipboardData?.items;
                                        if (!items || items.length === 0) {
                                            console.log('No clipboard items found');
                                            return;
                                        }
                                        
                                        console.log('Clipboard items:', items.length);
                                        
                                        for (let i = 0; i < items.length; i++) {
                                            const item = items[i];
                                            console.log('Checking item type:', item.type);
                                            
                                            if (item.type.indexOf('image') === -1) continue;
                                            
                                            const blob = item.getAsFile();
                                            if (!blob) {
                                                console.log('Could not get blob from item');
                                                continue;
                                            }
                                            
                                            console.log('Image blob found, size:', blob.size, 'type:', blob.type);
                                            
                                            // Determine file extension from MIME type
                                            let extension = 'png';
                                            if (blob.type === 'image/jpeg' || blob.type === 'image/jpg') {
                                                extension = 'jpg';
                                            } else if (blob.type === 'image/gif') {
                                                extension = 'gif';
                                            } else if (blob.type === 'image/webp') {
                                                extension = 'webp';
                                            }
                                            
                                            const fileName = 'pasted-image-' + Date.now() + '.' + extension;
                                            const file = new File([blob], fileName, { type: blob.type });
                                            
                                            // Get the file input
                                            const fileInput = $refs.screenshots;
                                            
                                            // Create DataTransfer with existing files + new file
                                            const dataTransfer = new DataTransfer();
                                            
                                            // Add existing files first (to preserve them)
                                            if (fileInput.files && fileInput.files.length > 0) {
                                                Array.from(fileInput.files).forEach(existingFile => {
                                                    dataTransfer.items.add(existingFile);
                                                });
                                            }
                                            
                                            // Add the new pasted file
                                            dataTransfer.items.add(file);
                                            
                                            // Set files on input
                                            fileInput.files = dataTransfer.files;
                                            
                                            // Create a synthetic change event that Livewire will recognize
                                            // Livewire listens for change events on file inputs with wire:model
                                            const syntheticEvent = new Event('change', {
                                                bubbles: true,
                                                cancelable: true
                                            });
                                            
                                            // Make sure the event has the target property
                                            Object.defineProperty(syntheticEvent, 'target', {
                                                value: fileInput,
                                                enumerable: true
                                            });
                                            
                                            // Dispatch the event - Livewire's wire:model should pick this up
                                            fileInput.dispatchEvent(syntheticEvent);
                                            console.log('Change event dispatched, files count:', fileInput.files.length);
                                            
                                            // Also try using $wire.upload as a fallback
                                            try {
                                                if ($wire && typeof $wire.upload === 'function') {
                                                    console.log('Using $wire.upload method');
                                                    $wire.upload('screenshots', dataTransfer.files);
                                                } else {
                                                    console.log('$wire.upload not available');
                                                }
                                            } catch (e) {
                                                console.error('Error with upload method:', e);
                                            }
                                            
                                            break;
                                        }
                                    },
                                    init() {
                                        console.log('Initializing paste handler for screenshots upload');
                                        
                                        // Add global paste handler - only when not in input/textarea
                                        const pasteHandler = (e) => {
                                            const target = e.target;
                                            console.log('Global paste handler triggered, target:', target.tagName);
                                            
                                            // Skip if user is typing in an input, textarea, or contenteditable
                                            if (target.tagName === 'INPUT' || 
                                                target.tagName === 'TEXTAREA' || 
                                                (target.isContentEditable && target.contentEditable === 'true')) {
                                                console.log('Skipping paste - user is typing in input field');
                                                return;
                                            }
                                            
                                            console.log('Processing paste event');
                                            this.handlePaste(e);
                                        };
                                        
                                        // Use capture phase to catch paste events early
                                        document.addEventListener('paste', pasteHandler, true);
                                        console.log('Global paste handler registered');
                                        
                                        // Cleanup on destroy
                                        this.$el._pasteHandler = pasteHandler;
                                    },
                                    destroy() {
                                        if (this.$el._pasteHandler) {
                                            document.removeEventListener('paste', this.$el._pasteHandler, true);
                                        }
                                    }
                                }" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                @drop.prevent="isDragging = false; $wire.upload('screenshots', $event.dataTransfer.files)"
                                @paste.prevent="handlePaste($event)" @click="$refs.screenshots.click()" tabindex="0">
                                <i class="mdi mdi-camera" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="mt-2 mb-0">Click to upload, drag and drop, or paste</p>
                                <p class="text-muted small">PNG, JPG, GIF up to 2MB each</p>
                                <input type="file" x-ref="screenshots" wire:model="screenshots" accept="image/*"
                                    multiple class="d-none">
                            </div>
                            <div class="file-preview">
                                @if($screenshots)
                                    @foreach($screenshots as $index => $screenshot)
                                        <div class="file-item">
                                            <i class="mdi mdi-image"></i>
                                            {{ $screenshot->getClientOriginalName() }}
                                            <span class="remove-file" wire:click="removeScreenshot({{ $index }})">×</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @error('screenshots.*')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Files Upload -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Files <span class="text-muted">(Optional)</span>
                            </label>
                            <div class="file-upload-area" x-data="{ isDragging: false }"
                                @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                @drop.prevent="isDragging = false; $wire.upload('files', $event.dataTransfer.files)"
                                @click="$refs.files.click()">
                                <i class="mdi mdi-file-document" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="mt-2 mb-0">Click to upload or drag and drop</p>
                                <p class="text-muted small">PDF, DOC, DOCX, XLS, XLSX, TXT up to 2MB each</p>
                                <input type="file" x-ref="files" wire:model="files"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" multiple class="d-none">
                            </div>
                            <div class="file-preview">
                                @if($files)
                                    @foreach($files as $index => $file)
                                        <div class="file-item">
                                            <i class="mdi mdi-file"></i>
                                            {{ $file->getClientOriginalName() }}
                                            <span class="remove-file" wire:click="removeFile({{ $index }})">×</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @error('files.*')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            <strong>Note:</strong> Your ticket will be assigned a unique ticket number once submitted.
                            You can track its progress from the "My Tickets" page.
                        </div>

                        <!-- Form Actions -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="store">
                                    <i class="mdi mdi-send"></i> Submit Ticket
                                </span>
                                <span wire:loading wire:target="store">
                                    <i class="mdi mdi-loading mdi-spin"></i> Submitting...
                                </span>
                            </button>
                            <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-lg">
                                <i class="mdi mdi-cancel"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    @if($showCategoryModal)
        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="createCategory">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="mdi mdi-plus-circle text-primary"></i> Add New Category
                            </h5>
                            <button type="button" class="close" wire:click="closeCategoryModal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="newCategoryName">Category Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="newCategoryName" class="form-control" id="newCategoryName"
                                    required maxlength="255">
                                @error('newCategoryName')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="newCategoryDescription">Description <span
                                        class="text-muted">(Optional)</span></label>
                                <textarea wire:model="newCategoryDescription" class="form-control"
                                    id="newCategoryDescription" rows="3" maxlength="1000"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeCategoryModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="createCategory">
                                    <i class="mdi mdi-check"></i> Create Category
                                </span>
                                <span wire:loading wire:target="createCategory">
                                    <i class="mdi mdi-loading mdi-spin"></i> Creating...
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