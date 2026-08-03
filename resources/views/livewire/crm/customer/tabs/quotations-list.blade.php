<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#eef2ff;">
                <i class="mdi mdi-file-settings text-primary" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.quotations') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.quote_history_status') }}</small>
            </div>
        </div>
    </div>

    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>No</th>
                <th>{{ __('crm.quote_no') }}</th>
                <th>{{ __('crm.quote_type') }}</th>
                <th>{{ __('crm.status') }}</th>
                <th>{{ __('crm.quote_date') }}</th>
                <th>{{ __('crm.expiry_date') }}</th>
                <th>{{ __('crm.prepared_by') }}</th>
                <th>{{ __('crm.total') }}</th>
                <th style="min-width: 120px;">{{ __('crm.actions') }}</th>
            </tr>
        </x-slot:header>
                    @forelse($this->quotes as $quote)
                        <tr wire:key="quote-{{ $quote->id }}">
                            <td>{{ ($this->quotes->currentPage() - 1) * $this->quotes->perPage() + $loop->iteration }}</td>
                            <td>{{ $quote->quote_number }}</td>
                            <td>{{ $quote->quotation_type }}</td>
                            <td>{{ $quote->status }}</td>
                            <td>{{ $quote->quote_date }}</td>
                            <td>{{ $quote->expiring_date }}</td>
                            <td>{{ $quote->creator }}</td>
                            <td style="text-align: right;">{{ number_format($quote->total_amount, 2) }}</td>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <a href="{{ route('add-qoute-details-view', ['id' => $quote->id]) }}"
                                        target="_blank" rel="noopener"
                                        class="btn crm-btn crm-btn-view btn-sm" title="{{ __('crm.view') }}">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </a>
                                    @can('laboratory.components.quotation.add')
                                        @if($quote->status === 'Quote Complete'
                                            && $quote->quotation_type === 'Analysis'
                                            && $quote->expiring_date
                                            && \Carbon\Carbon::parse($quote->expiring_date)->startOfDay()->gte(now()->startOfDay()))
                                            <button type="button"
                                                    wire:click="openCreateEnquiryModal(@js((string) $quote->id))"
                                                    class="btn crm-btn btn-sm"
                                                    style="border-color:#fed7aa;color:#c2410c;background:#fff7ed;"
                                                    title="Create enquiry from quotation">
                                                <i class="mdi mdi-flask-outline"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </x-crm.action-buttons>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-crm.empty-state
                                    icon="mdi-file-settings"
                                    :message="__('crm.no_quotations_for_client')"
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($this->quotes->firstItem() ?? 0) . ' to ' . ($this->quotes->lastItem() ?? 0) . ' of ' . $this->quotes->total() . ' results'">
        {{ $this->quotes->links() }}
    </x-crm.pagination>

    @if($showCreateEnquiryModal && $this->enquirySourceQuotation)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">
                                <i class="mdi mdi-flask-outline text-primary"></i>
                                Create Enquiry from {{ $this->enquirySourceQuotation->quote_number }}
                            </h5>
                            <small class="text-muted">
                                {{ $this->enquirySourceQuotation->customer?->name ?? $customer->name }}
                            </small>
                        </div>
                        <button type="button" class="close" wire:click="closeCreateEnquiryModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit="createEnquiryFromQuotation">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Workflow start</label>
                                <select wire:model.live="enquiryForm.creation_intent" class="form-control">
                                    <option value="prepare">Prepare for sending</option>
                                    <option value="already_sent">Quotation already sent</option>
                                    <option value="accepted">Customer already accepted</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Physical samples *</label>
                                        <input type="number" min="1" max="10000" wire:model="enquiryForm.number_of_samples" class="form-control">
                                        @error('enquiryForm.number_of_samples') <div class="text-danger small">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Customer reference</label>
                                        <input type="text" wire:model="enquiryForm.reference_number" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Expected sample date</label>
                                        <input type="date" wire:model="enquiryForm.date_expected" class="form-control">
                                    </div>
                                </div>
                            </div>
                            @if(($enquiryForm['creation_intent'] ?? '') === 'accepted')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Customer PO</label>
                                            <input type="text" wire:model="enquiryForm.client_po_number" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mt-4">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="crm-enquiry-po-skipped" wire:model="enquiryForm.po_skipped">
                                                <label class="custom-control-label" for="crm-enquiry-po-skipped">Skip PO</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <label>Sample description</label>
                                <textarea rows="2" wire:model="enquiryForm.sample_description" class="form-control"></textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label>Internal notes</label>
                                <textarea rows="2" wire:model="enquiryForm.enquiry_notes" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer" style="gap: 8px;">
                            <button type="button" class="btn btn-secondary" wire:click="closeCreateEnquiryModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                Create &amp; Prefill Enquiry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
