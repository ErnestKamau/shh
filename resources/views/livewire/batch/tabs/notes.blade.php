<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-android-messages"></i> Notes & Reminders</h5>
            <div class="d-flex align-items-center">
                <button type="button" 
                        class="btn btn-primary btn-sm text-nowrap" 
                        data-target="#add-sample-notes" 
                        data-toggle="modal"
                        style="box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px; margin-right: 15px;">
                    <i class="mdi mdi-message-plus"></i> Add Note
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
                       placeholder="Search notes by message, type, or user...">
            </div>

            @if($comments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Type</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comments as $comment)
                                <tr>
                                    <td>{{ $comment->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $comment->creator->name ?? 'N/A' }}</td>
                                    <td>{{ $comment->reminderRecipient->name ?? 'N/A' }}</td>
                                    <td>{{ $comment->comment_type }}</td>
                                    <td>
                                        <span class="show-hoverable">
                                            <span class="partial">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($comment->comments), 75) }}
                                            </span>
                                            <span class="complete">
                                                {{ strip_tags($comment->comments) }}
                                            </span>
                                        </span>
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
                            Showing {{ $comments->firstItem() ?? 0 }} to {{ $comments->lastItem() ?? 0 }} of {{ $comments->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $comments->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <tr>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Type</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="mdi mdi-comment-text-outline text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Notes Found</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No notes match your search criteria
                                        @else
                                            There are no batch notes or comments to display
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
