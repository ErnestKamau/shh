<div
    class="cpo-ls-page ls-quotation-shell"
    x-data
    @click.outside="if ($wire.filtersOpen) $wire.closeFilters()"
    @keydown.escape.window="if ($wire.filtersOpen) $wire.closeFilters()"
>
    @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')

    @php
        $createUrl = route('billing.customer-purchase-orders.create', $lockedCustomerId ? ['customer' => $lockedCustomerId] : []);
    @endphp

    <div class="batch-header-bar cpo-burgundy-header mb-3">
        <div class="cpo-burgundy-header__top">
            <div class="cpo-burgundy-header__identity">
                <h2 class="cpo-burgundy-header__title">
                    <i class="mdi mdi-file-document-outline"></i>
                    {{ $lockedCustomerId ? 'Purchase Orders' : 'Customer Purchase Orders' }}
                </h2>
                @unless($lockedCustomerId)
                    <span class="cpo-burgundy-header__badge">
                        <i class="mdi mdi-briefcase-outline"></i>
                        Billing registry
                    </span>
                @endunless
            </div>
            @if($this->canCreate)
                <div class="cpo-burgundy-header__actions">
                    <a href="{{ $createUrl }}" class="cpo-header-btn cpo-header-btn--light">
                        <i class="mdi mdi-plus"></i> New BPA
                    </a>
                </div>
            @endif
        </div>
        <p class="cpo-burgundy-header__subtitle">
            Blanket Purchase Agreements (BPA) cover many enquiries until their quantity runs out or they expire. One-off POs are recorded per enquiry.
        </p>
        <div class="cpo-burgundy-header__pills">
            <button type="button" class="cpo-stat-pill cpo-stat-pill--ok border-0" wire:click="$set('statusFilter', 'active')">{{ $summary['active'] }} active</button>
            <button type="button" class="cpo-stat-pill border-0" wire:click="$set('statusFilter', 'expiring')">{{ $summary['expiring'] }} expiring soon</button>
            <button type="button" class="cpo-stat-pill border-0" wire:click="$set('statusFilter', 'exhausted')">{{ $summary['exhausted'] }} exhausted</button>
            <button type="button" class="cpo-stat-pill border-0" wire:click="$set('statusFilter', 'expired')">{{ $summary['expired'] }} expired</button>
            <button type="button" class="cpo-stat-pill border-0" wire:click="$set('statusFilter', 'skipped')">{{ $summary['skipped'] }} skipped</button>
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
                    @unless($lockedCustomerId)
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
                    @endunless

                    <div class="ls-field">
                        <label class="ls-field__label" for="cpo-filter-status">Status</label>
                        <div class="ls-field__control">
                            <select id="cpo-filter-status" class="ls-field__input" wire:model.live="statusFilter">
                                <option value="">All</option>
                                @foreach($statuses as $statusOption)
                                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                @endforeach
                                <option value="expiring">Expiring soon</option>
                                <option value="skipped">Skipped</option>
                            </select>
                        </div>
                    </div>

                    <div class="ls-field">
                        <label class="ls-field__label" for="cpo-filter-type">Type</label>
                        <div class="ls-field__control">
                            <select id="cpo-filter-type" class="ls-field__input" wire:model.live="typeFilter">
                                <option value="">All</option>
                                @foreach($types as $typeOption)
                                    <option value="{{ $typeOption->value }}">{{ $typeOption->label() }}</option>
                                @endforeach
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
                    @unless($lockedCustomerId)
                        <th>Customer</th>
                    @endunless
                    <th>Validity</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Quotation</th>
                    <th>TRF / Job</th>
                    <th>Recorded</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                    @php
                        $enquiry = $po->enquiry;
                        $trfLabel = $enquiry?->submissionFormInstance?->form_number
                            ?: ($enquiry?->unique_identification ?: null);
                        $showUrl = route('billing.customer-purchase-orders.show', $po->id);
                        $effective = $po->effectiveStatus();
                        $ordered = (int) ($po->ordered_total ?? 0);
                        $remaining = (int) ($po->remaining_total ?? 0);
                        $usedPct = $ordered > 0 ? (int) round((($ordered - $remaining) / $ordered) * 100) : 0;
                        $daysLeft = $po->daysUntilExpiry();
                    @endphp
                    <tr wire:key="cpo-{{ $po->id }}">
                        <td nowrap>
                            <div class="d-flex align-items-center">
                                <x-imara.row-action-btn variant="view" :href="$showUrl" title="View purchase order" class="mr-1" />
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
                                <a href="{{ $showUrl }}" class="ls-table__stack-primary">{{ $po->po_number ?: '—' }}</a>
                                <div class="ls-table__stack-secondary">
                                    {{ $po->isBlanket() ? 'Blanket Purchase Agreement (BPA)' : 'One-off PO' }}
                                    @if($po->hasFile()) · <i class="mdi mdi-paperclip" title="{{ $po->file_name }}"></i> @endif
                                </div>
                            @endif
                        </td>
                        @unless($lockedCustomerId)
                            <td>
                                <div class="ls-table__stack-primary" style="color: var(--ls-ink);">{{ $po->customer?->name ?? '—' }}</div>
                            </td>
                        @endunless
                        <td>
                            @if($po->valid_from || $po->valid_to)
                                <div>{{ optional($po->valid_from)->format('d M Y') ?? 'Open' }} – {{ optional($po->valid_to)->format('d M Y') ?? 'Open' }}</div>
                                @if($daysLeft !== null && $daysLeft >= 0 && $effective === \App\Enums\Commercial\PurchaseOrderStatus::Active)
                                    <div class="ls-table__stack-secondary {{ $daysLeft <= (int) $po->expiry_notice_days ? 'text-warning' : '' }}">{{ $daysLeft }} day(s) left</div>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="min-width: 150px;">
                            @if($po->lines_count > 0)
                                <div class="cpo-qty"><strong>{{ number_format($remaining) }}</strong> <span class="text-muted">of {{ number_format($ordered) }} left</span></div>
                                <div class="cpo-balance-bar mt-1" style="height: 6px;">
                                    <div class="cpo-balance-bar__seg--committed" style="width: {{ $usedPct }}%"></div>
                                </div>
                            @elseif(! $po->po_skipped)
                                <span class="ls-table__stack-secondary">No lines</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="cpo-status cpo-status--{{ $po->po_skipped ? 'skipped' : $effective->value }}">{{ $po->statusLabel() }}</span>
                        </td>
                        <td>
                            @if($po->quotation)
                                <a href="{{ route('add-qoute-details-view', ['id' => $po->quotation->id]) }}">{{ $po->quotation->quote_number ?: 'Quote' }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($enquiry)
                                <a href="{{ $enquiry->staffViewUrl() }}">{{ $trfLabel ?: 'Request' }}</a>
                                @if($enquiry->batch?->batch_code)
                                    <div class="ls-table__stack-secondary">{{ $enquiry->batch->batch_code }}</div>
                                @endif
                            @elseif($po->isBlanket())
                                <span class="ls-table__stack-secondary">Many enquiries</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ optional($po->recorded_at)->format('d M Y') ?? '—' }}</div>
                            <div class="ls-table__stack-secondary">{{ optional($po->recorded_at)->format('H:i') ?? '' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $lockedCustomerId ? 8 : 9 }}">
                            <div class="text-center py-5">
                                <i class="mdi mdi-file-search-outline text-muted" style="font-size: 2.5rem;"></i>
                                <h5 class="text-muted mt-3 mb-1">No purchase orders found</h5>
                                @if($this->activeFilterCount > 0 || $search !== '')
                                    <p class="text-muted mb-3">Clear the filters to see more records.</p>
                                    <button type="button" class="ls-btn ls-btn--secondary-fill" wire:click="clearFilters">Clear filters</button>
                                @elseif($this->canCreate)
                                    <p class="text-muted mb-3">Record the customer's Blanket Purchase Agreement (BPA) to cover future enquiries.</p>
                                    <a href="{{ $createUrl }}" class="ls-btn ls-btn--primary">New BPA</a>
                                @endif
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
    @include('livewire.billing.partials.customer-purchase-order-ledger-styles')
</div>
