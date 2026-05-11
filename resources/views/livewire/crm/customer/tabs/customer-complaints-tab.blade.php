<div x-data x-on:open-view-modal.window="$('#complaint-description').modal('show')">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#fff3f3;">
                <i class="mdi mdi-comment-alert-outline text-danger" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.complaints') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.complaints_tab_subtitle') }}</small>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div class="crm-search-wrapper mr-2">
                <i class="mdi mdi-magnify crm-search-icon"></i>
                <input type="text" class="form-control" placeholder="{{ __('crm.search_complaints') }}"
                    wire:model.live.debounce.300ms="search">
            </div>
            <!-- Show Entries -->
            <div class="d-flex align-items-center mb-2 mb-md-0 mr-3 flex-shrink-0">
                <label class="mb-0 mr-2 crm-filter-label text-nowrap">{{ __('crm.show') }}</label>
                <select wire:model.live="perPage" wire:key="per-page-select" class="custom-select custom-select-sm no-select2" style="width: 70px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="mb-0 ml-2 crm-filter-label text-nowrap">{{ __('crm.entries') }}</label>
            </div>
            <button class="btn btn-outline-success btn-sm mr-2 text-nowrap" wire:click="exportToExcel">
                <i class="mdi mdi-file-excel"></i> {{ __('crm.export_to_excel') }}
            </button>
        </div>
    </div>

    <div wire:loading wire:target="search,perPage" class="crm-loading-indicator"><i
            class="mdi mdi-loading mdi-spin"></i>
        {{ __('crm.loading') }}...</div>
    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>No</th>
                <th>{{ __('crm.severity') }}</th>
                <th>{{ __('crm.complaint_no') }}</th>
                <th>{{ __('crm.complaint_category') }}</th>
                <th>{{ __('crm.reported_by') }}</th>
                <th nowrap>{{ __('crm.logged_by') }}</th>
                <th>{{ __('crm.complaint_date') }}</th>
                <th>{{ __('crm.status') }}</th>
                <th nowrap>{{ __('crm.workflow_stage') }}</th>
                <th>{{ __('crm.actions') }}</th>
            </tr>
        </x-slot:header>
                    @forelse($complaints as $item)
                        <tr wire:key="complaint-{{ $item->id }}">
                            <td valign="center">{{ $loop->iteration }}</td>
                            <td>
                                @if($item->priority == 'high')
                                    <span class="crm-badge crm-badge-priority-high"><i
                                            class="mdi mdi-alert-octagon-outline"></i>
                                        {{ __('crm.high') }}</span>
                                @elseif($item->priority == 'medium')
                                    <span class="crm-badge crm-badge-priority-medium">{{ __('crm.medium') }}</span>
                                @else
                                    <span
                                        class="crm-badge crm-badge-priority-low">{{ ucfirst($item->priority ?? __('crm.low')) }}</span>
                                @endif
                            </td>
                            <td>{{ $item->complaint_id}}</td>
                            <td>{{$item->type}}</td>
                            <td nowrap>{{ $item->received_from}}</td>
                            <td>{{ $item->registered_by}}</td>
                            <td>{{ $item->date }}</td>
                            <td class="text-small">
                                @if($item->rejected == 0)
                                    <span class="crm-badge crm-badge-success">{{ ucfirst(__('crm.active')) }}</span>
                                @else
                                    <span class="crm-badge crm-badge-danger">{{ __('crm.rejected') }}</span>
                                @endif
                            </td>
                            <td>{{$item->stage}}</td>
                            <td class="text-center" nowrap>
                                <x-crm.action-buttons class="justify-content-center">
                                    <button class="btn crm-btn crm-btn-view btn-sm" wire:click="viewComplaint({{ $item->id }})" title="{{ __('crm.view') }}">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </button>
                                </x-crm.action-buttons>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <x-crm.empty-state
                                    icon="mdi-comment-check-outline"
                                    :message="__('crm.no_complaints_for_client')"
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($complaints->firstItem() ?? 0) . ' to ' . ($complaints->lastItem() ?? 0) . ' of ' . $complaints->total() . ' results'">
        {{ $complaints->links() }}
    </x-crm.pagination>

    @if($showForm)
        <template x-teleport="body">
            <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1"
                role="dialog" wire:click.self="closeForm">
                <div class="modal-dialog modal-lg" role="document">
                    @livewire(\App\Livewire\Crm\Complaint\ComplaintForm::class, [
                        'complaintId' => $editingComplaintId,
                        'customerId' => $customer->id
                    ], 'complaint-form-tab-' . ($editingComplaintId ?? 'new'))
                </div>
            </div>
        </template>
    @endif
    <!-- View Modal -->
    <template x-teleport="body">
        <div id="complaint-description" class="modal fade" role="dialog" wire:ignore.self>
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-information-outline"></i> {{ __('crm.complaint_details') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($selectedComplaint)
                            <b>{{ __('crm.complaint') }} {{ $selectedComplaint->complaint_id }} {{ __('crm.description') }}.</b>
                            <div class="alert alert-primary p-2 mt-2 d-flex">
                                <i class="mdi mdi-information-variant" style="font-size:30px"></i>
                                <span class="p-2">{{ $selectedComplaint->description }}</span>
                            </div>
                        @else
                            <div class="text-center p-3">
                                <i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.loading') }}...
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('crm.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>