<div x-data="{
    ammendBatchId: '',
    returnBatchId: '',
    initSelect2() {
        // We will initialize specifically when the modal opens to ensure robustness
    },
    openAmmendment(batchId, samples) {
        console.log('Opening Amendment Modal for Batch:', batchId);
        console.log('Received Samples:', samples);
        
        this.ammendBatchId = batchId;
        
        // Safety check for jQuery and Select2
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            console.error('jQuery or Select2 is not loaded!');
            return;
        }

        let selectElement = $('#ammend_samples');
        selectElement.empty();
        
        if (Array.isArray(samples) && samples.length > 0) {
            $.each(samples, function(i, sample) {
                // Ensure sample has required properties
                if (sample.sample_code && sample.id) {
                    let option = new Option(`${sample.sample_code}`, sample.id, false, false);
                    selectElement.append(option);
                } else {
                    console.warn('Skipping invalid sample:', sample);
                }
            });
        } else {
            console.warn('No samples found or invalid data format:', samples);
        }
        
        if (selectElement.hasClass('select2-hidden-accessible')) {
            selectElement.select2('destroy');
        }

        // Initialize Select2 with proper configuration
        selectElement.select2({
            width: '100%',
            placeholder: 'Select Samples...',
            allowClear: true,
            closeOnSelect: true,
            dropdownParent: $('#ammendment-detail') // Critical for modal z-index
        }).on('change', function (e) {
            var data = $(this).val();
            // Debounce or safety check could go here if needed
            @this.set('amendmentSamples', data);
        });
        
        // Reset value and trigger change to ensure UI sync
        selectElement.val(null).trigger('change');
        
        $('#ammendment-detail').modal('show');
    },
    openReturn(batchId) {
        console.log('Opening Return Modal for Batch:', batchId);
        this.returnBatchId = batchId;
        $('#return-verification').modal('show');
    },
    closeModal(id) {
        console.log('Closing Modal:', id);
        $(`#${id}`).modal('hide');
    }
}" x-on:open-amendment-modal.window="openAmmendment($event.detail.batchId, $event.detail.samples)"
    x-on:open-return-modal.window="openReturn($event.detail.batchId)"
    x-on:close-modal.window="closeModal($event.detail.id)" x-init="initSelect2()">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#f5f0ff;">
                <i class="mdi mdi-test-tube" style="font-size:1rem;color:#7c3aed;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Laboratory Results
                    Archive</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">Completed test reports, amendments
                    &amp; return-to-verification records</small>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div class="crm-search-wrapper mr-2">
                <i class="mdi mdi-magnify crm-search-icon"></i>
                <input type="text" class="form-control" placeholder="Search reports..."
                    wire:model.live.debounce.300ms="search">
            </div>
            <button class="btn btn-outline-success btn-sm mr-2 text-nowrap" wire:click="exportToExcel">
                <i class="mdi mdi-file-excel"></i> Export to Excel
            </button>
        </div>
    </div>

    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th class="text-center" style="width: 100px;">Actions</th>
                <th class="text-center" style="width: 50px;">No</th>
                <th>Code</th>
                <th>Client Unit</th>
                <th>Ref. No.</th>
                <th>Sample Analysis</th>
                <th>Reason</th>
                <th class="text-center">Lab Date</th>
                <th class="text-center">Collected Date</th>
                <th>Description</th>
                <th class="text-center">Report</th>
            </tr>
        </x-slot:header>
        <tbody>
            @forelse($reports as $report)
                <tr wire:key="report-{{ $report->batch_id }}">
                    <td class="text-center">
                        @if($report->status == 'Completed')
                            <x-crm.action-buttons class="justify-content-center">
                                <button type="button" class="btn crm-btn crm-btn-edit btn-sm"
                                    wire:click.prevent="loadAmendment({{ $report->batch_id }})" wire:loading.attr="disabled"
                                    title="Amendment">
                                    <span wire:loading.remove wire:target="loadAmendment({{ $report->batch_id }})">
                                        <i class="mdi mdi-file-document-edit-outline"></i>
                                    </span>
                                    <span wire:loading wire:target="loadAmendment({{ $report->batch_id }})">
                                        <i class="mdi mdi-loading mdi-spin"></i>
                                    </span>
                                </button>
                                <button type="button" class="btn crm-btn crm-btn-view btn-sm"
                                    wire:click.prevent="loadReturn({{ $report->batch_id }})" wire:loading.attr="disabled"
                                    title="Return">
                                    <span wire:loading.remove wire:target="loadReturn({{ $report->batch_id }})">
                                        <i class="mdi mdi-undo-variant"></i>
                                    </span>
                                    <span wire:loading wire:target="loadReturn({{ $report->batch_id }})">
                                        <i class="mdi mdi-loading mdi-spin"></i>
                                    </span>
                                </button>
                                <a href="{{ route('view-batch-details', ['batch' => $report->batch_id, 'client' => $customer->id, 'portal' => $customer->id, 'status' => $report->status]) }}"
                                    class="btn crm-btn crm-btn-view btn-sm" title="View Details">
                                    <i class="mdi mdi-eye-outline"></i>
                                </a>
                            </x-crm.action-buttons>
                        @endif
                    </td>
                    <td class="text-center text-muted">{{ $loop->iteration }}</td>
                    <td class="font-weight-bold">{{ $report->batch_code }}</td>
                    <td>{{ $report->unit_name ?? ($report->unit->name ?? '-') }}</td>
                    <td>{{ $report->reference_number ?? '-' }}</td>
                    <td>{{ $report->sample_type }}</td>
                    <td>{{ $report->reasons ?? '-' }}</td>
                    <td class="text-center">{{ $report->created_at->format('Y-m-d') }}</td>
                    <td class="text-center">{{ $report->date_collected }}</td>
                    <td>
                        <small>
                            {{ $report->description ? strip_tags($report->description) : '-' }}
                        </small>
                    </td>
                    <td class="text-center">
                        @if($report->batch_report_url)
                            <?php        $path = '/storage' . $report->batch_report_url;?>
                            <a href="{{$path}}" target="_blank" class="btn btn-primary btn-xs px-2" title="Download Report">
                                <i class="mdi mdi-download"></i> PDF
                            </a>
                        @else
                            <span class="crm-badge crm-badge-neutral p-2" title="No Report URL">
                                <i class="mdi mdi-file-hidden"></i> N/A
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">
                        <x-crm.empty-state icon="mdi-alert" message="No reports found." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($reports->firstItem() ?? 0) . ' to ' . ($reports->lastItem() ?? 0) . ' of ' . $reports->total() . ' results'">
        {{ $reports->links() }}
    </x-crm.pagination>

    <!-- Legacy Modals -->
    <template x-teleport="body">
        <div id="ammendment-detail" class="modal fade" x-on:click.self="$('#ammendment-detail').modal('hide')"
            role="dialog" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Raise Amendment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group" wire:ignore>
                            <label>Select Sample(s)</label>
                            <select class="form-control select2" multiple id="ammend_samples" style="width: 100%;"
                                data-placeholder="Select Samples...">
                            </select>
                            @error('amendmentSamples') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <textarea class="form-control" wire:model="amendmentReason"></textarea>
                            @error('amendmentReason') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveAmendment">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div id="return-verification" class="modal fade" x-on:click.self="$('#return-verification').modal('hide')"
            role="dialog" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Return To Verification</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Reason</label>
                            <textarea class="form-control" wire:model="returnComment"></textarea>
                            @error('returnComment') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveReturnVerification">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>