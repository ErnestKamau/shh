<div>
    <div class="card-header- p-2 mt-2" style="background-color: inherit !important">
        <h5 style="font-size: large"><i class="mdi mdi-calendar-month"></i> Sample(s)</h5>
        @if(isset($batch->status) && in_array($batch->status, array("Sample Verification","Sample Approval","Reports for Collection","Reports In Payment")))
            @if($batch->status == "Sample Verification")
                @if(sizeof($not_captured) > 0)
                    <div class="col-md-3 m-2">
                        <span style="font-size: 11px;" class="badge badge-pill bg-white text-danger p-2">
                            <i class="mdi mdi-alert-decagram"></i> Data Partially Captured
                        </span>
                    </div>
                @endif
            @endif
        @endif
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs card-header-tabs mt-3" id="batch-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="samples-tab" data-toggle="tab" href="#samples" role="tab" aria-controls="samples" aria-selected="true">
                <i class="mdi mdi-flask-outline"></i> Samples
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="notes-tab" data-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="false">
                <i class="mdi mdi-comment-text-outline"></i> Notes 
                <span class="badge badge-pill badge-primary">{{ $batch->comments?->count() ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="chain-of-custody-tab" data-toggle="tab" href="#chain-of-custody" role="tab" aria-controls="custody" aria-selected="false">
                <i class="mdi mdi-sitemap"></i> Chain of Custody 
                <span class="badge badge-pill badge-primary">{{ $batch->custody?->count() ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="attachments-tab" data-toggle="tab" href="#attachments" role="tab" aria-controls="attachments" aria-selected="false">
                <i class="mdi mdi-paperclip"></i> Attachments 
                <span class="badge badge-pill badge-primary">{{ $batch->batch_attachments?->count() ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="interlabs-tab" data-toggle="tab" href="#interlabs" role="tab" aria-controls="interlabs" aria-selected="false">
                <i class="mdi mdi-flask-outline"></i> Interlab Logs
            </a>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content mt-3" id="batch-tabs-content">
        {{-- Samples Tab --}}
        <div class="tab-pane fade show active" id="samples" role="tabpanel" aria-labelledby="samples-tab">
            @livewire('batch.tabs.samples', ['batch' => $batch], 'samples-tab-'.$batch->id)
        </div>

        {{-- Notes Tab --}}
        <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
            <div class="card">
                <div class="card-body">
                    <h5><i class="mdi mdi-comment-text-outline"></i> Batch Notes/Comments</h5>
                    {{-- TODO: Implement Notes component or include existing notes functionality --}}
                    <p class="text-muted">Notes functionality will be migrated here.</p>
                </div>
            </div>
        </div>

        {{-- Chain of Custody Tab --}}
        <div class="tab-pane fade" id="chain-of-custody" role="tabpanel" aria-labelledby="chain-of-custody-tab">
            <div class="card">
                <div class="card-body">
                    <h5><i class="mdi mdi-sitemap"></i> Chain of Custody</h5>
                    @if($batch->custody && $batch->custody->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batch->custody as $custody)
                                        <tr>
                                            <td>{{ $custody->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $custody->from_user->name ?? 'N/A' }}</td>
                                            <td>{{ $custody->to_user->name ?? 'N/A' }}</td>
                                            <td>{{ $custody->purpose ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $custody->status == 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($custody->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No chain of custody records found.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Attachments Tab --}}
        <div class="tab-pane fade" id="attachments" role="tabpanel" aria-labelledby="attachments-tab">
            <div class="card">
                <div class="card-body">
                    <h5><i class="mdi mdi-paperclip"></i> Batch Attachments</h5>
                    @if($batch->batch_attachments && $batch->batch_attachments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>File Name</th>
                                        <th>Type</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batch->batch_attachments as $attachment)
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
                    @else
                        <p class="text-muted">No attachments found.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Interlab Logs Tab --}}
        <div class="tab-pane fade" id="interlabs" role="tabpanel" aria-labelledby="interlabs-tab">
            <div class="card">
                <div class="card-body">
                    <h5><i class="mdi mdi-flask-outline"></i> Interlab Transfer Logs</h5>
                    <p class="text-muted">Interlab logs functionality will be migrated here.</p>
                </div>
            </div>
        </div>
    </div>
</div>
