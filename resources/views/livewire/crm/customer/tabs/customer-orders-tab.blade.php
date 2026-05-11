<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#e6f7f7;">
                <i class="mdi mdi-clipboard-list-outline" style="font-size:1rem;color:#0d9488;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.service_order_ledger') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.service_order_ledger_subtitle') }}</small>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div class="crm-search-wrapper mr-2">
                <i class="mdi mdi-magnify crm-search-icon"></i>
                <input type="text" class="form-control" placeholder="{{ __('crm.search_orders') }}"
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
            {{-- <a class="btn btn-primary btn-sm"
                href="{{ route('view-batch-details', ['batch'=>time(), 'client'=>$customer->id]) }}">
                <i class="mdi mdi-plus"></i> Order
            </a> --}}
        </div>
    </div>

    <div wire:loading wire:target="search,perPage" class="crm-loading-indicator"><i
            class="mdi mdi-loading mdi-spin"></i>
        {{ __('crm.loading') }}...</div>
    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th style="min-width: 100px;">{{ __('crm.actions') }}</th>
                <th>No</th>
                <th nowrap>{{ __('crm.batch_id') }}</th>
                <th nowrap>{{ __('crm.collection_date') }}</th>
                <th>{{ __('crm.reference_no') }}</th>
                <th nowrap>{{ __('crm.doc_no') }}</th>
                <th nowrap>{{ __('crm.analysis_type') }}</th>
                <th>{{ __('crm.samples') }}</th>
                <th>{{ __('crm.stage') }}</th>
            </tr>
        </x-slot:header>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <x-crm.action-buttons>
                                    <a class="btn crm-btn crm-btn-view btn-sm" title="{{ __('crm.view') }}"
                                        href="{{ route('view-batch-details', ['batch' => $order->id, 'client' => $customer->id, 'portal' => $customer->id, 'status' => $order->status]) }}">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </a>
                                </x-crm.action-buttons>
                            </td>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $order->batch_code }}</td>
                            <td>{{ $order->date_collected }}</td>
                            <td>{{ $order->reference_number ?? '-' }}</td>
                            <td>{{ $order->document_number ?? '-' }}</td>
                            <td nowrap>{{ $order->sample_type }}</td>
                            <td>{{ $order->samples }}</td>
                            <td>
                                @php $s = strtolower($order->status ?? ''); @endphp
                                <span
                                    class="crm-badge {{ $s === 'completed' ? 'crm-badge-success' : ($s === 'pending' ? 'crm-badge-warning' : 'crm-badge-neutral') }}">
                                    {{ $order->status ?? 'N/A' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-crm.empty-state
                                    icon="mdi-clipboard-list-outline"
                                    :message="__('crm.no_service_orders_for_client')"
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($orders->firstItem() ?? 0) . ' to ' . ($orders->lastItem() ?? 0) . ' of ' . $orders->total() . ' results'">
        {{ $orders->links() }}
    </x-crm.pagination>
</div>