<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header">
            <h5><i class="mdi mdi-file-document-edit"></i> Amendments</h5>
            <div class="d-flex align-items-center gap-2">
                <label for="perPage" class="form-label mb-0 me-2 text-muted small">Show</label>
                <select wire:model.live="perPage" id="perPage" class="form-control form-control-sm" style="width: auto; min-width: 4.5rem; border-radius: 6px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="workflow-board-panel-body flush-top">
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" 
                       wire:model.live="search" 
                       class="form-control" 
                       placeholder="Search by sample, creator, or reason...">
            </div>

            @if($amendments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: rgba(0, 0, 0, .03);">
                            <th>Version No</th>
                            <th nowrap>Samples</th>
                            <th nowrap>Amended By</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th nowrap>Report</th>
                        </thead>
                        <tbody>
                            @foreach($amendments as $a)
                            <tr>
                                <td>V {{$a->version_number}}</td>
                                <td>
                                    @php
                                        $samples = is_string($a->samples) ? json_decode($a->samples, true) : $a->samples;
                                        $sampleNames = is_array($samples) ? collect($samples)->pluck('sample_code')->implode(', ') : '-';
                                    @endphp
                                    {{ $sampleNames }}
                                </td>
                                <td>{{$a->creator->name ?? 'N/A'}}</td>
                                <td>{{$a->created_at->format('Y-m-d H:i')}}</td>
                                <td>
                                    <span class="btn-sm btn-outline-dark mdi mdi-comment-text" 
                                          data-toggle="modal" 
                                          data-target="#reason-{{$a->id}}" 
                                          data-toggle="tooltip" 
                                          title="Amendment Reason"></span>
                                    
                                    <div class="modal fade" id="reason-{{$a->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-light">
                                                    <h4 class="modal-title">
                                                        <i class="mdi mdi-comment-text"></i> Amendment V{{$a->version_number}} Reason
                                                    </h4>
                                                </div>
                                                <div class="modal-body">
                                                    {{$a->reason}}
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td nowrap>
                                    @if($a->report_url)
                                        <a href="{{ $a->report_url }}" target="_blank">
                                            <i class="mdi mdi-download"></i> Download Report
                                        </a>
                                    @else
                                        <span class="text-muted">No report</span>
                                    @endif
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
                            Showing {{ $amendments->firstItem() ?? 0 }} to {{ $amendments->lastItem() ?? 0 }} of {{ $amendments->total() }} entries
                        </span>
                    </div>
                    <div>
                        {{ $amendments->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover workflow-table">
                        <thead>
                            <tr>
                                <th>Version No</th>
                                <th nowrap>Samples</th>
                                <th nowrap>Amended By</th>
                                <th>Date</th>
                                <th>Reason</th>
                                <th nowrap>Report</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-5 workflow-empty-state">
                                    <i class="mdi mdi-file-document-edit-outline text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Amendments Found</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No amendments match your search criteria
                                        @else
                                            There are no report amendments to display
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
