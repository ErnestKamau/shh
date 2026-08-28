<div>
    <div class="workflow-board-panel batch-tabs-panel">
        <div class="workflow-board-panel-header">
            <h5>
                <i class="mdi mdi-view-dashboard-outline"></i>
                Batch workspace
            </h5>
        </div>
        <div class="workflow-board-panel-body flush-top">
        @if(isset($batch->status) && in_array($batch->status, array("Sample Verification","Sample Approval","Reports for Collection","Reports In Payment")))
            @if($batch->status == "Sample Verification")
                @if(sizeof($not_captured) > 0)
                    <div class="text-center mb-3">
                        <span class="workflow-status-chip" style="--chip-accent: #dc3545; font-size: 0.78rem;">
                            <i class="mdi mdi-alert-decagram"></i> Data partially captured
                        </span>
                    </div>
                @endif
            @endif
        @endif

    @php
        $isVerificationStage = ($batch->status ?? '') === 'Sample Verification'
            || ($batch->prelim_batch_status ?? '') === 'Sample Verification';
    @endphp

    {{-- Tab Navigation --}}
    <ul class="nav batch-nav-tabs mb-0" id="batch-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="samples-tab" data-toggle="tab" href="#samples" role="tab" aria-controls="samples" aria-selected="true">
                <i class="mdi mdi-flask-outline"></i> {{ $isVerificationStage ? 'Verifications' : 'Samples' }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="notes-tab" data-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="false">
                <i class="mdi mdi-comment-text-outline"></i> Notes 
                <span class="badge">{{ $batch->comments?->count() ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="chain-of-custody-tab" data-toggle="tab" href="#chain-of-custody" role="tab" aria-controls="custody" aria-selected="false">
                <i class="mdi mdi-sitemap"></i> Chain of Custody 
                <span class="badge">{{ $batch->custody?->count() ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="amendment-tab" data-toggle="tab" href="#amendment" role="tab" aria-controls="amendment" aria-selected="false">
                <i class="mdi mdi-file-document-edit"></i> Amendment
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="interlabs-tab" data-toggle="tab" href="#interlabs" role="tab" aria-controls="interlabs" aria-selected="false">
                <i class="mdi mdi-flask-outline"></i> Interlab Logs
            </a>
        </li>
        @if(in_array($batch->status, ['Samples In Lab','Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status ?? '', ['Sample Verification','Sample Approval']))
        <li class="nav-item">
            <a class="nav-link" id="raw-results-tab" data-toggle="tab" href="#raw-results" role="tab" aria-controls="raw-results" aria-selected="false">
                <i class="mdi mdi-sync-alert"></i> Raw Results
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="processed-results-tab" data-toggle="tab" href="#processed-results" role="tab" aria-controls="processed-results" aria-selected="false">
                <i class="mdi mdi-sync"></i> Processed Results
            </a>
        </li>
        @endif
        @if(in_array($batch->status, ['Sample Verification','Sample Approval','Reports In Payment','Reports for Collection']) || in_array($batch->prelim_batch_status ?? '', ['Sample Verification','Sample Approval']))
        <li class="nav-item">
            <a class="nav-link" id="approvals-tab" data-toggle="tab" href="#approvals" role="tab" aria-controls="approvals" aria-selected="false">
                <i class="mdi mdi-account-check-outline"></i> {{ $isVerificationStage ? 'Verification' : 'Approvals' }}
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" id="payment-details-tab" data-toggle="tab" href="#payment-details" role="tab" aria-controls="payment-details" aria-selected="false">
                <i class="mdi mdi-account-cash-outline"></i> Payment Details
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="attachments-tab" data-toggle="tab" href="#attachments" role="tab" aria-controls="attachments" aria-selected="false">
                <i class="mdi mdi-paperclip"></i> Attachments 
                <span class="badge">{{ $batch->batch_attachments?->count() ?? 0 }}</span>
            </a>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content mt-3 pt-1 border-top" id="batch-tabs-content" style="border-color: #f1f5f9 !important;">
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
    </div>
</div>

<script>
(function () {
    if (window.__batchTabsHashScriptLoaded) {
        return;
    }
    window.__batchTabsHashScriptLoaded = true;

    var tabActivationAttempts = 0;
    var hashNavigationDone = false;
    var batchDetailsHashes = [
        'sample-receipt-notification',
        'laboratory-acceptance',
        'laboratory-acceptance-part-5'
    ];

    function isBatchDetailsHash(tabId) {
        if (batchDetailsHashes.indexOf(tabId) !== -1) {
            return true;
        }

        return tabId.indexOf('laboratory-acceptance-part-') === 0;
    }

    function scrollToBatchDetailsAnchor(tabId) {
        var anchorId = tabId.indexOf('laboratory-acceptance-part-') === 0 ? tabId : 'laboratory-acceptance-part-5';
        var target = document.getElementById(anchorId);

        if (!target) {
            if (tabActivationAttempts < 12) {
                tabActivationAttempts += 1;
                window.setTimeout(function () {
                    scrollToBatchDetailsAnchor(tabId);
                }, 100);
            }
            return;
        }

        tabActivationAttempts = 0;
        hashNavigationDone = true;
        setTimeout(function () {
            target.scrollIntoView({ behavior: 'auto', block: 'start' });
        }, 150);
    }

    function activateTabFromHash(force) {
        if (hashNavigationDone && !force) {
            return;
        }

        var hash = window.location.hash;
        if (!hash) {
            return;
        }

        var tabId = hash.replace('#', '');

        if (isBatchDetailsHash(tabId)) {
            scrollToBatchDetailsAnchor(tabId);
            return;
        }

        var tabLink = document.querySelector('#' + tabId + '-tab')
            || document.querySelector('[href="#' + tabId + '"]');

        if (!tabLink) {
            if (tabActivationAttempts < 12) {
                tabActivationAttempts += 1;
                window.setTimeout(function () {
                    activateTabFromHash(force);
                }, 100);
            }
            return;
        }

        tabActivationAttempts = 0;
        hashNavigationDone = true;

        if (window.bootstrap && bootstrap.Tab) {
            bootstrap.Tab.getOrCreateInstance(tabLink).show();
        } else if (typeof tabLink.click === 'function') {
            tabLink.click();
        }

        setTimeout(function () {
            tabLink.scrollIntoView({ behavior: 'auto', block: 'nearest' });
        }, 300);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            activateTabFromHash(true);
        });
    } else {
        activateTabFromHash(true);
    }

    window.addEventListener('hashchange', function () {
        hashNavigationDone = false;
        tabActivationAttempts = 0;
        activateTabFromHash(true);
    });
})();
</script>
