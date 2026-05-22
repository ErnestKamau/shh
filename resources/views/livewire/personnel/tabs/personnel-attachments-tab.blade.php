<div>
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="card-title mb-1">Attachments</h5>
            <p class="text-muted small mb-0">Manage documents attached to this personnel profile.</p>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" wire:click="openUploadModal">
            <i class="mdi mdi-upload"></i> Upload Attachment
        </button>
    </div>

    @if($attachments->isEmpty())
        <div class="text-center py-5 capability-empty-card">
            <i class="mdi mdi-file-document-outline capability-empty-icon" style="font-size: 3rem; color: #cbd5e0;"></i>
            <h5 class="mt-3 text-muted mb-1">No attachments found</h5>
            <p class="text-muted small mb-0">Upload documents such as CV, ID, or other related files.</p>
        </div>
    @else
        <div class="table-responsive bg-light p-3 rounded">
            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                <thead class="bg-light p-2">
                    <tr>
                        <th>#</th>
                        <th>Document Name</th>
                        <th>Uploaded By</th>
                        <th>Date Uploaded</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attachments as $index => $attachment)
                        <tr>
                            <td>{{ $attachments->firstItem() + $index }}</td>
                            <td>{{ $attachment->file_name }}</td>
                            <td>{{ $attachment->uploader->name ?? 'System' }}</td>
                            <td>{{ $attachment->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="btn btn-sm pm-act-btn pm-act-btn--info" title="View Document">
                                    <i class="mdi mdi-eye"></i> View
                                </a>
                                <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--delete" wire:click="deleteAttachment({{ $attachment->id }})" title="Delete Document" onclick="confirm('Are you sure you want to delete this attachment?') || event.stopImmediatePropagation()">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $attachments->links() }}
        </div>
    @endif

    @if($showUploadModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-upload"></i> Upload Attachment</h5>
                        <button type="button" class="close" wire:click="closeUploadModal"><span>&times;</span></button>
                    </div>
                    <form wire:submit.prevent="uploadAttachment">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Document Name *</label>
                                <input type="text" class="form-control" wire:model="fileName" placeholder="e.g. Resume, ID Card">
                                @error('fileName') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group mt-3">
                                <label>File *</label>
                                <input type="file" class="form-control-file" wire:model="attachmentFile">
                                @error('attachmentFile') <small class="text-danger">{{ $message }}</small> @enderror
                                <div wire:loading wire:target="attachmentFile" class="text-primary mt-2 small">Uploading...</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" wire:click="closeUploadModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
