<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-attachment"></i> Attachments</h5>
            <div class="d-flex align-items-center">
                <button type="button" 
                        class="btn btn-primary btn-sm text-nowrap" 
                        data-target="#add-batch-attachment" 
                        data-toggle="modal"
                        style="box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px; margin-right: 15px;">
                    <i class="mdi mdi-file-upload"></i> Add Attachment
                </button>
                <div class="d-flex align-items-center ml-2 border-left pl-3">
                    <label for="perPage" class="form-label mb-0 mr-2 text-muted small text-nowrap">Show:</label>
                    <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm d-inline-block" style="width: 70px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" 
                       wire:model.live="search" 
                       class="form-control" 
                       placeholder="Search attachments by filename, type, or uploader...">
            </div>

            @if($attachments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attachments as $attachment)
                                <tr>
                                    <td>{{ $attachment->file_name }}</td>
                                    <td>{{ $attachment->file_type ?? '-' }}</td>
                                    <td>{{ $attachment->uploader->name ?? 'N/A' }}</td>
                                    <td>{{ $attachment->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('download-attachment', $attachment->id) }}" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span class="text-muted">
                            Showing {{ $attachments->firstItem() ?? 0 }} to {{ $attachments->lastItem() ?? 0 }} of {{ $attachments->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $attachments->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="mdi mdi-paperclip text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Attachments Found</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No attachments match your search criteria
                                        @else
                                            There are no batch attachments to display
                                        @endif
                                    </small></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
