<div x-data x-on:open-view-modal.window="$('#viewFeedbackModal').modal('show')">
    <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <span class="mr-2 d-flex align-items-center justify-content-center rounded" style="width:28px;height:28px;background:#fffbe6;">
                    <i class="mdi mdi-star-check-outline text-warning" style="font-size:1rem;"></i>
                </span>
                <div>
                    <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Client Satisfaction Signals</small>
                    <small class="text-muted d-block" style="font-size:0.67rem;">Submitted feedback, satisfaction scores &amp; service quality signals</small>
                </div>
            </div>
            <div class="d-flex justify-content-end align-items-center">
                <div class="crm-search-wrapper mr-2">
                    <i class="mdi mdi-magnify crm-search-icon"></i>
                    <input type="text" class="form-control" placeholder="Search feedback..."
                        wire:model.live.debounce.300ms="search">
                </div>
                <!-- Show Entries -->
            <div class="d-flex align-items-center mb-2 mb-md-0 mr-3 flex-shrink-0">
                <label class="mb-0 mr-2 crm-filter-label text-nowrap">Show</label>
                <select wire:model.live="perPage" wire:key="per-page-select" class="custom-select custom-select-sm no-select2" style="width: 70px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="mb-0 ml-2 crm-filter-label text-nowrap">entries</label>
            </div>
                <button class="btn btn-outline-success btn-sm mr-2 text-nowrap" wire:click="exportToExcel">
                    <i class="mdi mdi-file-excel"></i> Export to Excel
                </button>
            </div>
        </div>

    <div wire:loading wire:target="search,perPage" class="crm-loading-indicator"><i class="mdi mdi-loading mdi-spin"></i> Loading...</div>
    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>Ref Code</th>
                <th nowrap>Service Contact</th>
                <th>Respondent Type</th>
                <th nowrap>Logged By</th>
                <th>Feedback Date</th>
                <th>State</th>
                <th>Actions</th>
            </tr>
        </x-slot:header>
                @forelse($feedbacks as $item)
                    <tr wire:key="feedback-{{ $item->id }}">
                        <td valign="center">
                            {{ $item->code ?? 'FB' . str_pad($item->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            @if($item->contact && $item->contact->email)
                                <div class="d-flex flex-column">
                                    <span class="font-weight-bold text-dark">
                                        {{ $item->customer->name ?? $item->received_from }}
                                    </span>
                                    <small class="text-muted">{{ $item->contact->email }}
                                        @if($item->contact->first_name)
                                            ({{ $item->contact->first_name }})
                                        @endif
                                    </small>
                                </div>
                            @elseif($item->customer)
                                <div class="d-flex flex-column">
                                    <span class="font-weight-bold text-dark">
                                        {{ $item->customer->name }}
                                    </span>
                                    <small class="text-muted">{{ $item->customer->email }}</small>
                                </div>
                            @else
                                {{ $item->received_from }}
                            @endif
                        </td>
                        <td>
                            @if($item->customer_id)
                                Customer
                            @else
                                {{ $item->user_type }}
                            @endif
                        </td>
                        <td>{{ $item->submitter_name }}</td>
                        <td>
                            @if(!empty($item->results_issued_date))
                                {{ date('d M Y', strtotime($item->results_issued_date)) }}
                            @elseif(!empty($item->date) && $item->date != '0000-00-00 00:00:00')
                                {{ date('d M Y', strtotime($item->date)) }}
                            @elseif($item->created_at)
                                {{ $item->created_at->format('d M Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="text-small">
                            @if($item->status == \App\Models\CRM\CustomerFeedback::STATUS_SUBMITTED)
                                <span class="crm-badge crm-badge-success">Submitted</span>
                            @elseif($item->status == \App\Models\CRM\CustomerFeedback::STATUS_PENDING)
                                <span class="crm-badge crm-badge-warning">Pending</span>
                            @else
                                <span class="crm-badge crm-badge-secondary">Unknown</span>
                            @endif
                        </td>
                        <td class="text-center" nowrap>
                            <x-crm.action-buttons class="justify-content-center">
                                <button class="btn crm-btn crm-btn-view btn-sm" wire:click="viewFeedback({{ $item->id }})">
                                    <i class="mdi mdi-eye-outline"></i>
                                </button>
                            </x-crm.action-buttons>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-crm.empty-state
                                icon="mdi-star-check-outline"
                                message="No feedbacks found."
                                help="Submitted feedback will appear here."
                            />
                        </td>
                    </tr>
                @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($feedbacks->firstItem() ?? 0) . ' to ' . ($feedbacks->lastItem() ?? 0) . ' of ' . $feedbacks->total() . ' results'">
        {{ $feedbacks->links() }}
    </x-crm.pagination>

    <!-- Read-Only Feedback Modal: teleported to body so backdrop and modal are siblings and overlay behaves correctly -->
    @teleport('body')
    <div class="modal fade" id="viewFeedbackModal" x-on:click.self="$('#viewFeedbackModal').modal('hide')" tabindex="-1"
        role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-primary">
                        <i class="mdi mdi-clipboard-text-outline mr-1"></i> Feedback Details
                        <small class="text-muted d-block" style="font-size: 0.8rem;">
                            Ref: {{ $selectedFeedback ? 'FB' . str_pad($selectedFeedback->id, 5, '0', STR_PAD_LEFT) : '---' }}
                        </small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
                    @if($selectedFeedback)
                        @include('livewire.crm.feedback.partials.feedback-detail-content', ['feedback' => $selectedFeedback])
                    @else
                        <div class="text-center p-5">
                            <i class="mdi mdi-loading mdi-spin display-4 text-primary"></i>
                            <p class="mt-3 text-muted">Loading feedback details...</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endteleport

    @if($showForm)
        @livewire(\App\Livewire\Crm\Feedback\FeedbackForm::class, [
            'feedbackId' => $editingFeedbackId,
            'customerId' => $customer->id
        ], 'feedback-form-tab-' . ($editingFeedbackId ?? 'new'))
    @endif
</div>