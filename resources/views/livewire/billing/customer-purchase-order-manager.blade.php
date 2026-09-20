<div
    class="cpo-ls-page ls-quotation-shell"
    x-data
    @click.outside="if ($wire.filtersOpen) $wire.closeFilters()"
    @keydown.escape.window="if ($wire.filtersOpen) $wire.closeFilters()"
>
    @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')

    <div class="batch-header-bar cpo-burgundy-header mb-3">
        <div class="cpo-burgundy-header__top">
            <div class="cpo-burgundy-header__identity">
                <h2 class="cpo-burgundy-header__title">
                    <i class="mdi mdi-file-document-outline"></i>
                    Customer Purchase Orders
                </h2>
                <span class="cpo-burgundy-header__badge">
                    <i class="mdi mdi-briefcase-outline"></i>
                    Billing registry
                </span>
            </div>
        </div>
        <p class="cpo-burgundy-header__subtitle">
            Client POs recorded after quotation acceptance — linked quotes, TRFs, jobs, and samples.
        </p>
        <div class="cpo-burgundy-header__pills">
            <span class="cpo-stat-pill">{{ $summary['total'] }} in range</span>
            <span class="cpo-stat-pill cpo-stat-pill--ok">{{ $summary['recorded'] }} recorded</span>
            <span class="cpo-stat-pill">{{ $summary['skipped'] }} skipped</span>
            <span class="cpo-stat-pill cpo-stat-pill--file">{{ $summary['with_file'] }} with file</span>
        </div>
    </div>

    <div class="ls-quotation-search-toolbar mb-3">
        <div class="ls-quotation-search-toolbar__row">
            @include('layouts.lab.partials.ls-ui.fields.ls-search-bar', [
                'placeholder' => 'PO number, customer, TRF, quote…',
                'wireModel' => 'search',
                'variant' => 'ghost',
                'ariaLabel' => 'Search purchase orders',
                'fullWidth' => true,
                'filterWireClick' => 'toggleFilters',
                'filterBadge' => $this->activeFilterCount,
                'filtersOpen' => $filtersOpen,
            ])

            @if($this->activeFilterCount > 0 || $search !== '')
                <button type="button" class="ls-quotation-search-toolbar__clear" wire:click="clearFilters" title="Clear filters">
                    <i class="mdi mdi-close-circle-outline"></i>
                    <span>Clear</span>
                </button>
            @endif
        </div>

        @if($filtersOpen)
            <div class="ls-quotation-filter-panel mt-2">
                <div class="ls-quotation-filter-panel__head">
                    <span><i class="mdi mdi-filter-variant"></i> Filters</span>
                    <button type="button" class="ls-quotation-filter-panel__close" wire:click="closeFilters" aria-label="Close filters">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>

                <div class="ls-quotation-filter-panel__grid ls-compact">
                    <div class="ls-field">
                        <label class="ls-field__label" for="cpo-filter-customer">Customer</label>
                        <div class="ls-field__control">
                            <select id="cpo-filter-customer" class="ls-field__input" wire:model.live="customerFilter">
                                <option value="">All customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ls-field">
                        <label class="ls-field__label" for="cpo-filter-status">Status</label>
                        <div class="ls-field__control">
                            <select id="cpo-filter-status" class="ls-field__input" wire:model.live="statusFilter">
                                <option value="">All</option>
                                <option value="recorded">Recorded</option>
                                <option value="skipped">Skipped</option>
                            </select>
                        </div>
                    </div>

                    <div class="ls-field">
                        <label class="ls-field__label" for="cpo-filter-file">File</label>
                        <div class="ls-field__control">
                            <select id="cpo-filter-file" class="ls-field__input" wire:model.live="fileFilter">
                                <option value="">All</option>
                                <option value="with_file">Has file</option>
                                <option value="without_file">No file</option>
                            </select>
                        </div>
                    </div>

                    <div class="ls-field">
                        <label class="ls-field__label" for="cpo-filter-from">Recorded from</label>
                        <div class="ls-field__control">
                            <input id="cpo-filter-from" type="date" class="ls-field__input" wire:model.live="startDate">
                        </div>
                    </div>

                    <div class="ls-field">
                        <label class="ls-field__label" for="cpo-filter-to">Recorded to</label>
                        <div class="ls-field__control">
                            <input id="cpo-filter-to" type="date" class="ls-field__input" wire:model.live="endDate">
                        </div>
                    </div>

                    <div class="ls-field">
                        <label class="ls-field__label" for="cpo-filter-per-page">Show</label>
                        <div class="ls-field__control">
                            <select id="cpo-filter-per-page" class="ls-field__input" wire:model.live="perPage">
                                @foreach($perPageOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="ls-table-wrap" wire:loading.class="opacity-50">
        <table class="table table-hover ls-table ls-table--dense mb-0">
            <thead>
                <tr>
                    <th style="width: 88px;">Actions</th>
                    <th>PO</th>
                    <th>Customer</th>
                    <th>Quotation</th>
                    <th>TRF</th>
                    <th>Job</th>
                    <th>Status</th>
                    <th>File</th>
                    <th>Recorded</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                    @php
                        $enquiry = $po->enquiry;
                        $trfLabel = $enquiry?->submissionFormInstance?->form_number
                            ?: ($enquiry?->unique_identification ?: '—');
                        $jobLabel = $enquiry?->batch?->batch_code ?: '—';
                        $showUrl = route('billing.customer-purchase-orders.show', $po->id);
                    @endphp
                    <tr wire:key="cpo-{{ $po->id }}">
                        <td nowrap>
                            <div class="d-flex align-items-center">
                                <x-imara.row-action-btn
                                    variant="view"
                                    :href="$showUrl"
                                    title="View purchase order"
                                    class="mr-1"
                                />
                                @if($po->hasFile())
                                    <x-imara.row-action-btn
                                        variant="description"
                                        icon="mdi-download"
                                        :href="route('billing.customer-purchase-orders.download', $po->id)"
                                        title="Download PO file"
                                    />
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($po->po_skipped)
                                <span class="text-muted">—</span>
                            @else
                                <a href="{{ $showUrl }}" class="ls-table__stack-primary">
                                    {{ $po->po_number ?: '—' }}
                                </a>
                            @endif
                        </td>
                        <td>
                            <div class="ls-table__stack-primary" style="color: var(--ls-ink);">
                                {{ $po->customer?->name ?? '—' }}
                            </div>
                        </td>
                        <td>
                            @if($po->quotation)
                                <a href="{{ route('add-qoute-details-view', ['id' => $po->quotation->id]) }}">
                                    {{ $po->quotation->quote_number ?: 'Quote' }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($enquiry)
                                <a href="{{ $enquiry->staffViewUrl() }}">{{ $trfLabel }}</a>
                            @else
                                <span class="text-muted">{{ $trfLabel }}</span>
                            @endif
                        </td>
                        <td>{{ $jobLabel }}</td>
                        <td>
                            @if($po->po_skipped)
                                <span class="ls-pill ls-pill--inactive">Skipped</span>
                            @else
                                <span class="ls-pill ls-pill--paid">Recorded</span>
                            @endif
                        </td>
                        <td>
                            @if($po->hasFile())
                                <span class="ls-pill ls-pill--open" title="{{ $po->file_name }}">
                                    <i class="mdi mdi-paperclip"></i> File
                                </span>
                            @else
                                <span class="ls-table__stack-secondary">No file</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ optional($po->recorded_at)->format('d M Y') ?? '—' }}</div>
                            <div class="ls-table__stack-secondary">{{ optional($po->recorded_at)->format('H:i') ?? '' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="text-center py-5">
                                <i class="mdi mdi-file-search-outline text-muted" style="font-size: 2.5rem;"></i>
                                <h5 class="text-muted mt-3 mb-1">No purchase orders found</h5>
                                <p class="text-muted mb-3">Widen the date range or clear filters to see more records.</p>
                                <button type="button" class="ls-btn ls-btn--secondary-fill" wire:click="clearFilters">
                                    Clear filters
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchaseOrders->hasPages())
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-3" style="gap: 8px;">
            <div class="text-muted small">
                Showing {{ $purchaseOrders->firstItem() }}–{{ $purchaseOrders->lastItem() }} of {{ $purchaseOrders->total() }}
            </div>
            <div>{{ $purchaseOrders->links() }}</div>
        </div>
    @endif

    @include('livewire.billing.partials.customer-purchase-order-header-styles')
</div>
