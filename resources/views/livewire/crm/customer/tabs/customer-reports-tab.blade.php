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
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.laboratory_results_archive') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.laboratory_results_archive_subtitle') }}</small>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div class="crm-search-wrapper mr-2">
                <i class="mdi mdi-magnify crm-search-icon"></i>
                <input type="text" class="form-control" placeholder="{{ __('crm.search_reports') }}"
                    wire:model.live.debounce.300ms="search">
            </div>
            <button class="btn btn-outline-success btn-sm mr-2 text-nowrap" wire:click="exportToExcel">
                <i class="mdi mdi-file-excel"></i> {{ __('crm.export_to_excel') }}
            </button>
        </div>
    </div>

    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th class="text-center" style="width: 100px;">{{ __('crm.actions') }}</th>
                <th class="text-center" style="width: 50px;">No</th>
                <th>{{ __('crm.code') }}</th>
                <th>{{ __('crm.client_unit') }}</th>
                <th>{{ __('crm.reference_no') }}</th>
                <th>{{ __('crm.sample_analysis') }}</th>
                <th>{{ __('crm.reason') }}</th>
                <th class="text-center">{{ __('crm.lab_date') }}</th>
                <th class="text-center">{{ __('crm.collected_date') }}</th>
                <th>{{ __('crm.description') }}</th>
                <th class="text-center">{{ __('crm.report') }}</th>
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
                                    title="{{ __('crm.amendments') }}">
                                    <span wire:loading.remove wire:target="loadAmendment({{ $report->batch_id }})">
                                        <i class="mdi mdi-file-document-edit-outline"></i>
                                    </span>
                                    <span wire:loading wire:target="loadAmendment({{ $report->batch_id }})">
                                        <i class="mdi mdi-loading mdi-spin"></i>
                                    </span>
                                </button>
                                <button type="button" class="btn crm-btn crm-btn-view btn-sm"
                                    wire:click.prevent="loadReturn({{ $report->batch_id }})" wire:loading.attr="disabled"
                                    title="{{ __('crm.return') }}">
                                    <span wire:loading.remove wire:target="loadReturn({{ $report->batch_id }})">
                                        <i class="mdi mdi-undo-variant"></i>
                                    </span>
                                    <span wire:loading wire:target="loadReturn({{ $report->batch_id }})">
                                        <i class="mdi mdi-loading mdi-spin"></i>
                                    </span>
                                </button>
                                <a href="{{ route('view-batch-details', ['batch' => $report->batch_id, 'client' => $customer->id, 'portal' => $customer->id, 'status' => $report->status]) }}"
                                    class="btn crm-btn crm-btn-view btn-sm" title="{{ __('crm.view_details') }}">
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
                            <span class="crm-badge crm-badge-neutral p-2" title="{{ __('crm.no_report_url') }}">
                                <i class="mdi mdi-file-hidden"></i> N/A
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">
                        <x-crm.empty-state icon="mdi-alert" :message="__('crm.no_reports_found')" />
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
                        <h5 class="modal-title">{{ __('crm.raise_amendment') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group" wire:ignore>
                            <label>{{ __('crm.select_samples') }}</label>
                            <select class="form-control select2" multiple id="ammend_samples" style="width: 100%;"
                                data-placeholder="{{ __('crm.select_samples') }}...">
                            </select>
                            @error('amendmentSamples') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('crm.reason') }}</label>
                            <textarea class="form-control" wire:model="amendmentReason"></textarea>
                            @error('amendmentReason') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveAmendment">{{ __('crm.save_changes') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('crm.close') }}</button>
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
                        <h5 class="modal-title">{{ __('crm.return_to_verification') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('crm.reason') }}</label>
                            <textarea class="form-control" wire:model="returnComment"></textarea>
                            @error('returnComment') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveReturnVerification">{{ __('crm.save_changes') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('crm.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>