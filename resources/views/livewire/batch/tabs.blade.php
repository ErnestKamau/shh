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

    <style>
        .nav-modern {
            border-bottom: 2px solid #e9ecef !important;
            gap: 10px;
            flex-wrap: nowrap !important;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 5px;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none;  /* IE 10+ */
        }
        
        /* Hide scrollbar for Chrome/Safari/Opera */
        .nav-modern::-webkit-scrollbar {
            display: none;
        }
        
        .nav-link-modern {
            color: #495057 !important;
            background-color: #f8f9fa !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 50px !important;
            padding: 10px 20px !important;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            white-space: nowrap; /* Ensure text doesn't wrap */
            margin-bottom: 5px; /* Spacing for potential shadow */
        }

        .nav-link-modern i {
            margin-right: 8px;
            font-size: 1.1em;
            color: #6c757d;
        }

        .nav-link-modern:hover {
            color: #212529 !important;
            background-color: #e2e6ea !important;
            border-color: #ced4da !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .nav-link-modern.active {
            color: #fff !important;
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            box-shadow: 0 4px 6px rgba(108, 117, 125, 0.3);
            font-weight: 600;
        }
        
        .nav-link-modern.active i {
            color: #fff !important;
        }

        .nav-link-modern .badge {
            margin-left: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link-modern:hover .badge {
            background-color: #495057;
        }

        .nav-link-modern.active .badge {
            background-color: #fff !important;
            color: #6c757d !important;
        }
    </style>

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs nav-modern card-header-tabs mt-3" id="batch-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link nav-link-modern active" id="samples-tab" data-toggle="tab" href="#samples" role="tab" aria-controls="samples" aria-selected="true">
                <i class="mdi mdi-flask-outline"></i> Samples
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link nav-link-modern" id="notes-tab" data-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="false">
                <i class="mdi mdi-comment-text-outline"></i> Notes 
                <span class="badge badge-pill badge-primary">{{ $batch->comments?->count() ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link nav-link-modern" id="chain-of-custody-tab" data-toggle="tab" href="#chain-of-custody" role="tab" aria-controls="custody" aria-selected="false">
                <i class="mdi mdi-sitemap"></i> Chain of Custody 
                <span class="badge badge-pill badge-primary">{{ $batch->custody?->count() ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link nav-link-modern" id="amendment-tab" data-toggle="tab" href="#amendment" role="tab" aria-controls="amendment" aria-selected="false">
                <i class="mdi mdi-file-document-edit"></i> Amendment
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link nav-link-modern" id="interlabs-tab" data-toggle="tab" href="#interlabs" role="tab" aria-controls="interlabs" aria-selected="false">
                <i class="mdi mdi-flask-outline"></i> Interlab Logs
            </a>
        </li>
        @if(in_array($batch->status, ['Samples In Lab','Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status ?? '', ['Sample Verification','Sample Approval']))
        <li class="nav-item">
            <a class="nav-link nav-link-modern" id="raw-results-tab" data-toggle="tab" href="#raw-results" role="tab" aria-controls="raw-results" aria-selected="false">
                <i class="mdi mdi-sync-alert"></i> Raw Results
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link nav-link-modern" id="processed-results-tab" data-toggle="tab" href="#processed-results" role="tab" aria-controls="processed-results" aria-selected="false">
                <i class="mdi mdi-sync"></i> Processed Results
            </a>
        </li>
        @endif
        @if(in_array($batch->status, ['Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status ?? '', ['Sample Verification','Sample Approval']))
        <li class="nav-item">
            <a class="nav-link nav-link-modern" id="approvals-tab" data-toggle="tab" href="#approvals" role="tab" aria-controls="approvals" aria-selected="false">
                <i class="mdi mdi-account-check-outline"></i> Approvals
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link nav-link-modern" id="payment-details-tab" data-toggle="tab" href="#payment-details" role="tab" aria-controls="payment-details" aria-selected="false">
                <i class="mdi mdi-account-cash-outline"></i> Payment Details
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link nav-link-modern" id="attachments-tab" data-toggle="tab" href="#attachments" role="tab" aria-controls="attachments" aria-selected="false">
                <i class="mdi mdi-paperclip"></i> Attachments 
                <span class="badge badge-pill badge-primary">{{ $batch->batch_attachments?->count() ?? 0 }}</span>
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
            @livewire('batch.tabs.notes', ['batch' => $batch], 'notes-tab-'.$batch->id)
        </div>

        {{-- Chain of Custody Tab --}}
        <div class="tab-pane fade" id="chain-of-custody" role="tabpanel" aria-labelledby="chain-of-custody-tab">
            @livewire('batch.tabs.chain-of-custody', ['batch' => $batch], 'custody-tab-'.$batch->id)
        </div>

        {{-- Amendment Tab --}}
        <div class="tab-pane fade" id="amendment" role="tabpanel" aria-labelledby="amendment-tab">
            @livewire('batch.tabs.amendment', ['batch' => $batch], 'amendment-tab-'.$batch->id)
        </div>

        {{-- Interlab Logs Tab --}}
        <div class="tab-pane fade" id="interlabs" role="tabpanel" aria-labelledby="interlabs-tab">
            @livewire('batch.tabs.interlab-logs', ['batch' => $batch], 'interlabs-tab-'.$batch->id)
        </div>

        @if(in_array($batch->status, ['Samples In Lab','Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status ?? '', ['Sample Verification','Sample Approval']))
        {{-- Raw Results Tab --}}
        <div class="tab-pane fade" id="raw-results" role="tabpanel" aria-labelledby="raw-results-tab">
            @livewire('batch.tabs.raw-results', ['batch' => $batch], 'raw-results-tab-'.$batch->id)
        </div>

        {{-- Processed Results Tab --}}
        <div class="tab-pane fade" id="processed-results" role="tabpanel" aria-labelledby="processed-results-tab">
            @livewire('batch.tabs.processed-results', ['batch' => $batch], 'processed-results-tab-'.$batch->id)
        </div>
        @endif

        @if(in_array($batch->status, ['Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status ?? '', ['Sample Verification','Sample Approval']))
        {{-- Approvals Tab --}}
        <div class="tab-pane fade" id="approvals" role="tabpanel" aria-labelledby="approvals-tab">
            @livewire('batch.tabs.approvals', ['batch' => $batch], 'approvals-tab-'.$batch->id)
        </div>
        @endif

        {{-- Payment Details Tab --}}
        <div class="tab-pane fade" id="payment-details" role="tabpanel" aria-labelledby="payment-details-tab">
            @livewire('batch.tabs.payment-details', ['batch' => $batch], 'payment-details-tab-'.$batch->id)
        </div>

        {{-- Attachments Tab --}}
        <div class="tab-pane fade" id="attachments" role="tabpanel" aria-labelledby="attachments-tab">
            @livewire('batch.tabs.attachments', ['batch' => $batch], 'attachments-tab-'.$batch->id)
        </div>
    </div>
</div>
