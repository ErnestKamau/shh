@php
    $po = $purchaseOrder;
    $status = $po?->effectiveStatus();
    $statusKey = $po?->po_skipped ? 'skipped' : $status?->value;
    $amendable = $po && ! $po->po_skipped && $po->isAmendable();
    $daysLeft = $po?->daysUntilExpiry();
    $currencyCode = $po?->currency?->code;
    $trfLabel = $po?->enquiry?->submissionFormInstance?->form_number
        ?: ($po?->enquiry?->unique_identification ?: null);
    $usedPercent = $totals['ordered'] > 0
        ? (int) round((($totals['committed'] + $totals['reserved']) / $totals['ordered']) * 100)
        : 0;
    $entryDots = [
        'order' => '#0f766e', 'adjust' => '#0f766e',
        'reserve' => '#f59e0b', 'unreserve' => '#94a3b8',
        'commit' => '#8b1e2d', 'release' => '#94a3b8',
        'invoice' => '#2563eb', 'uninvoice' => '#94a3b8',
    ];
@endphp

<div class="cpo-ls-show ls-quotation-shell">
    @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')

    @if($po)
        @if(session('success'))
            <div class="cpo-note cpo-note--info mb-3"><i class="mdi mdi-check-circle-outline"></i> {{ session('success') }}</div>
        @endif

        <div class="batch-header-bar cpo-burgundy-header mb-3">
            <div class="cpo-burgundy-header__top">
                <div class="cpo-burgundy-header__identity" style="flex-direction: column; align-items: flex-start;">
                    <a href="{{ route('billing.customer-purchase-orders') }}" class="cpo-burgundy-header__back">
                        <i class="mdi mdi-arrow-left"></i>
                        Back to purchase orders
                    </a>

                    <div class="d-flex flex-wrap align-items-center" style="gap: 0.55rem;">
                        <h2 class="cpo-burgundy-header__title">
                            @if($po->po_skipped)
                                Purchase order skipped
                            @else
                                PO {{ $po->po_number ?: '—' }}
                            @endif
                        </h2>
                        <span class="cpo-status cpo-status--{{ $statusKey }}">{{ $po->statusLabel() }}</span>
                        @if(! $po->po_skipped)
                            <span class="cpo-burgundy-header__badge">{{ $po->po_type?->label() ?? 'Single enquiry' }}</span>
                        @endif
                        @if($po->fileExists())
                            <span class="cpo-stat-pill cpo-stat-pill--file">
                                <i class="mdi mdi-paperclip"></i> File attached
                            </span>
                        @endif
                    </div>

                    <p class="cpo-burgundy-header__meta">
                        <strong>{{ $po->customer?->name ?? 'Unknown customer' }}</strong>
                        @if($po->valid_from || $po->valid_to)
                            <span class="mx-1">·</span>
                            Valid {{ optional($po->valid_from)->format('d M Y') ?? 'open' }} – {{ optional($po->valid_to)->format('d M Y') ?? 'open' }}
                        @endif
                        @if($po->invoicing_mode)
                            <span class="mx-1">·</span>
                            Invoicing: {{ $po->invoicing_mode->label() }}@if($po->invoicing_period) ({{ ucfirst($po->invoicing_period) }})@endif
                        @endif
                        <span class="mx-1">·</span>
                        Recorded {{ optional($po->recorded_at)->format('d M Y') ?? '—' }}
                        @if($po->uploader)
                            by {{ $po->uploader->name }}
                        @endif
                    </p>
                </div>

                <div class="cpo-burgundy-header__actions">
                    @if($amendable && $canAmend)
                        <button type="button" class="cpo-header-btn cpo-header-btn--light" wire:click="openDetails">
                            <i class="mdi mdi-pencil-outline"></i> Edit details
                        </button>
                        <button type="button" class="cpo-header-btn cpo-header-btn--ghost" wire:click="openValidity">
                            <i class="mdi mdi-calendar-edit"></i> Validity
                        </button>
                    @endif
                    @if($amendable && $canClose)
                        <button type="button" class="cpo-header-btn cpo-header-btn--ghost" wire:click="openFinalise('close')">
                            <i class="mdi mdi-lock-outline"></i> Close
                        </button>
                    @endif
                    @if($po->fileExists())
                        <a href="{{ route('billing.customer-purchase-orders.download', $po->id) }}" class="cpo-header-btn cpo-header-btn--ghost">
                            <i class="mdi mdi-download"></i> File
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if($status === \App\Enums\Commercial\PurchaseOrderStatus::Expired && ! $po->po_skipped)
            <div class="cpo-note cpo-note--danger mb-3">
                <i class="mdi mdi-calendar-alert"></i>
                This PO expired on {{ optional($po->valid_to)->format('d M Y') }}. New samples will not be covered until the validity is extended.
            </div>
        @elseif($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= (int) $po->expiry_notice_days && $amendable)
            <div class="cpo-note cpo-note--warn mb-3">
                <i class="mdi mdi-calendar-clock"></i>
                Expires in {{ $daysLeft }} day(s), on {{ optional($po->valid_to)->format('d M Y') }}.
            </div>
        @endif

        @if($po->lines->isNotEmpty())
            <div class="row mb-1">
                @foreach([
                    ['label' => 'Ordered', 'value' => $totals['ordered'], 'hint' => 'Across '.$po->lines->count().' line(s)'],
                    ['label' => 'Committed', 'value' => $totals['committed'], 'hint' => $totals['invoiced'].' invoiced'],
                    ['label' => 'Reserved', 'value' => $totals['reserved'], 'hint' => 'Enquiries not yet received'],
                    ['label' => 'Remaining', 'value' => $totals['remaining'], 'hint' => $usedPercent.'% used'],
                ] as $card)
                    <div class="col-md-3 col-6 mb-2">
                        <div class="ls-soft-card is-expanded h-100">
                            <div class="ls-soft-card__header" style="cursor:default;"><span>{{ $card['label'] }}</span></div>
                            <div class="ls-soft-card__body">
                                <div class="ls-table__stack-primary cpo-qty" style="font-size:1.25rem;">{{ number_format($card['value']) }}</div>
                                <div class="ls-table__stack-secondary">{{ $card['hint'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Lines --}}
        @if(! $po->po_skipped)
            <div class="ls-soft-card is-expanded mb-3">
                <div class="ls-soft-card__header d-flex justify-content-between align-items-center" style="cursor:default;">
                    <span>Lines</span>
                    @if($amendable && $canAmend)
                        <button type="button" class="ls-btn ls-btn--primary" style="height:auto;padding:0.25rem 0.65rem;font-size:0.78rem;" wire:click="openAddLine">
                            <i class="mdi mdi-plus"></i> Add line
                        </button>
                    @endif
                </div>
                <div class="ls-soft-card__body">
                    @forelse($po->lines as $line)
                        @php
                            $ordered = max(1, (int) $line->ordered_qty);
                            $billedPct = ((int) $line->invoiced_qty / $ordered) * 100;
                            $unbilledPct = (((int) $line->committed_qty - (int) $line->invoiced_qty) / $ordered) * 100;
                            $reservedPct = ((int) $line->reserved_qty / $ordered) * 100;
                            $isLow = $line->notify_remaining_qty !== null && (int) $line->remaining_qty <= (int) $line->notify_remaining_qty && ! $line->isExhausted();
                        @endphp
                        <div class="cpo-line-card {{ $line->isExhausted() ? 'is-exhausted' : '' }} {{ $isLow ? 'is-low' : '' }}" wire:key="cpo-line-{{ $line->id }}">
                            <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 0.5rem;">
                                <div class="min-w-0">
                                    <div class="ls-table__stack-primary" style="color: var(--ls-ink);">
                                        <span class="text-muted">#{{ $line->line_no }}</span> {{ $line->description }}
                                    </div>
                                    <div class="cpo-help">
                                        @if($line->sampleType)
                                            <span class="ls-pill ls-pill--info">{{ $line->sampleType->name }}</span>
                                        @endif
                                        @if($line->is_package)
                                            <span class="ls-pill ls-pill--open">Package</span>
                                        @endif
                                        {{ $currencyCode }} {{ number_format((float) $line->unit_price_gross, 2) }} per sample (VAT incl.)
                                        @if($line->notify_remaining_qty !== null)
                                            · alert at {{ number_format($line->notify_remaining_qty) }} remaining
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center" style="gap: 0.4rem;">
                                    @if($line->isExhausted())
                                        <span class="cpo-status cpo-status--exhausted">Exhausted</span>
                                    @elseif($isLow)
                                        <span class="cpo-status cpo-status--exhausted">Low balance</span>
                                    @endif
                                    @if($amendable && $canAmend)
                                        <button type="button" class="ls-btn ls-btn--secondary-fill" style="height:auto;padding:0.2rem 0.55rem;font-size:0.76rem;" wire:click="openAdjust('{{ $line->id }}', 'top_up')">
                                            <i class="mdi mdi-plus-circle-outline"></i> Top up
                                        </button>
                                        <button type="button" class="ls-btn ls-btn--secondary-fill" style="height:auto;padding:0.2rem 0.55rem;font-size:0.76rem;" wire:click="openAdjust('{{ $line->id }}', 'reduce')">
                                            <i class="mdi mdi-minus-circle-outline"></i> Reduce
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="cpo-balance-bar mt-2" role="img" aria-label="{{ $line->committed_qty }} committed, {{ $line->reserved_qty }} reserved, {{ $line->remaining_qty }} remaining of {{ $line->ordered_qty }}">
                                <div class="cpo-balance-bar__seg--invoiced" style="width: {{ $billedPct }}%"></div>
                                <div class="cpo-balance-bar__seg--committed" style="width: {{ $unbilledPct }}%"></div>
                                <div class="cpo-balance-bar__seg--reserved" style="width: {{ $reservedPct }}%"></div>
                            </div>
                            <div class="cpo-balance-legend cpo-qty">
                                <span style="--cpo-legend:#cbd5e1;"><strong>{{ number_format($line->ordered_qty) }}</strong> ordered</span>
                                <span style="--cpo-legend:#0f766e;"><strong>{{ number_format($line->invoiced_qty) }}</strong> invoiced</span>
                                <span style="--cpo-legend:#8b1e2d;"><strong>{{ number_format((int) $line->committed_qty - (int) $line->invoiced_qty) }}</strong> committed, not invoiced</span>
                                <span style="--cpo-legend:#f59e0b;"><strong>{{ number_format($line->reserved_qty) }}</strong> reserved</span>
                                <span style="--cpo-legend:#e2e8f0;"><strong>{{ number_format($line->remaining_qty) }}</strong> remaining</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="mdi mdi-format-list-bulleted-square" style="font-size: 1.8rem;"></i>
                            <p class="mb-0 mt-2">
                                No lines yet.
                                @if(! $po->isBlanket())
                                    Run <code>php artisan purchase-orders:backfill-ledger</code> to convert this enquiry PO, or add a line.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if($this->heldJobs->isNotEmpty())
            <div class="cpo-note cpo-note--warn mb-3">
                <i class="mdi mdi-pause-circle-outline"></i>
                {{ $this->heldJobs->count() }} job(s) are held awaiting PO cover:
                @foreach($this->heldJobs as $held)
                    <strong>{{ $held->batch_code ?: $held->id }}</strong> (held {{ (int) floor(($held->po_held_at ?? $held->created_at)?->diffInDays(now()) ?? 0) }} days)@if(! $loop->last), @endif
                @endforeach
            </div>
        @endif

        <div class="row">
            {{-- Ledger timeline --}}
            <div class="col-lg-7 mb-3">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header d-flex justify-content-between align-items-center" style="cursor:default;">
                        <span>Allocation timeline</span>
                        <span class="ls-pill ls-pill--inactive">{{ $this->timelineTotal }}</span>
                    </div>
                    <div class="ls-soft-card__body">
                        @php $refs = $this->timelineReferences; @endphp
                        <ul class="cpo-timeline">
                            @forelse($this->timeline as $entry)
                                @php
                                    $enquiryRef = $entry->enquiry_id ? $refs['enquiries']->get((string) $entry->enquiry_id) : null;
                                    $jobRef = $entry->sample_header_id ? $refs['jobs']->get((string) $entry->sample_header_id) : null;
                                    $invoiceRef = $entry->invoice_id ? $refs['invoices']->get((string) $entry->invoice_id) : null;
                                @endphp
                                <li class="cpo-timeline__item" style="--cpo-dot: {{ $entryDots[$entry->entry_type->value] ?? '#94a3b8' }};" wire:key="cpo-entry-{{ $entry->id }}">
                                    <div>
                                        <strong>{{ $entry->entry_type->label() }}</strong>
                                        <span class="cpo-qty">{{ $entry->quantity > 0 ? '+' : '' }}{{ number_format($entry->quantity) }}</span>
                                        <span class="text-muted">on line #{{ $entry->line?->line_no ?? '?' }}</span>
                                        @if($jobRef)
                                            · job <strong>{{ $jobRef->batch_code ?: $jobRef->id }}</strong>
                                        @endif
                                        @if($enquiryRef)
                                            · <a href="{{ $enquiryRef->staffViewUrl() }}">{{ $enquiryRef->submissionFormInstance?->form_number ?: ($enquiryRef->unique_identification ?: 'enquiry') }}</a>
                                        @endif
                                        @if($invoiceRef)
                                            · <a href="{{ route('billing.invoices.show', $invoiceRef->id) }}">{{ $invoiceRef->invoice_number ?: 'invoice' }}</a>
                                        @endif
                                    </div>
                                    <div class="cpo-timeline__meta">
                                        {{ optional($entry->created_at)->format('d M Y H:i') }}
                                        @if($entry->creator) · {{ $entry->creator->name }} @endif
                                        @if($entry->reason) · {{ $entry->reason }} @endif
                                    </div>
                                </li>
                            @empty
                                <li class="text-muted">No allocations yet.</li>
                            @endforelse
                        </ul>
                        @if($this->timelineTotal > $timelineLimit)
                            <button type="button" class="ls-btn ls-btn--secondary-fill mt-2" wire:click="loadMoreTimeline">Show more</button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Amendment history --}}
            <div class="col-lg-5 mb-3">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header d-flex justify-content-between align-items-center" style="cursor:default;">
                        <span>Amendment history</span>
                        <span class="ls-pill ls-pill--inactive">{{ $this->amendments->count() }}</span>
                    </div>
                    <div class="ls-soft-card__body">
                        <ul class="cpo-timeline">
                            @forelse($this->amendments as $amendment)
                                <li class="cpo-timeline__item" style="--cpo-dot: #8b1e2d;" wire:key="cpo-amendment-{{ $amendment->id }}">
                                    <div>
                                        <strong>{{ $amendment->amendment_type->label() }}</strong>
                                        @if($amendment->line)
                                            <span class="text-muted">· line #{{ $amendment->line->line_no }}</span>
                                        @endif
                                    </div>
                                    @foreach((array) $amendment->changes as $field => $change)
                                        <div class="cpo-help mt-0">
                                            {{ \Illuminate\Support\Str::headline((string) $field) }}:
                                            @if(is_array($change) && array_key_exists('from', $change))
                                                {{ is_scalar($change['from']) || $change['from'] === null ? ($change['from'] ?? '—') : json_encode($change['from']) }}
                                                → {{ is_scalar($change['to'] ?? null) || ($change['to'] ?? null) === null ? ($change['to'] ?? '—') : json_encode($change['to']) }}
                                            @else
                                                {{ is_scalar($change) ? $change : json_encode($change) }}
                                            @endif
                                        </div>
                                    @endforeach
                                    <div class="cpo-timeline__meta">
                                        {{ optional($amendment->created_at)->format('d M Y H:i') }}
                                        @if($amendment->creator) · {{ $amendment->creator->name }} @endif
                                    </div>
                                    @if($amendment->reason)
                                        <div class="cpo-help mt-0"><em>“{{ $amendment->reason }}”</em></div>
                                    @endif
                                </li>
                            @empty
                                <li class="text-muted">No amendments.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Enquiries --}}
            <div class="col-lg-6 mb-3">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header d-flex justify-content-between align-items-center" style="cursor:default;">
                        <span>Enquiries</span>
                        <span class="ls-pill ls-pill--inactive">{{ $this->enquiries->count() }}</span>
                    </div>
                    <div class="ls-soft-card__body p-0">
                        <div class="ls-table-wrap" style="border:0; border-radius:0; box-shadow:none;">
                            <table class="table ls-table ls-table--dense mb-0">
                                <thead>
                                    <tr>
                                        <th>TRF / request</th>
                                        <th>Status</th>
                                        <th>Job</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($this->enquiries as $enquiry)
                                        <tr>
                                            <td>
                                                <a href="{{ $enquiry->staffViewUrl() }}">
                                                    {{ $enquiry->submissionFormInstance?->form_number ?: ($enquiry->unique_identification ?: 'Open request') }}
                                                </a>
                                            </td>
                                            <td>
                                                @if(filled($enquiry->status))
                                                    <span class="ls-pill ls-pill--open">{{ ucwords(str_replace('_', ' ', (string) $enquiry->status)) }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $enquiry->batch?->batch_code ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">No enquiries linked yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Jobs --}}
            <div class="col-lg-6 mb-3">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header d-flex justify-content-between align-items-center" style="cursor:default;">
                        <span>Jobs</span>
                        <span class="ls-pill ls-pill--inactive">{{ $jobs->count() }}</span>
                    </div>
                    <div class="ls-soft-card__body p-0">
                        <div class="ls-table-wrap" style="border:0; border-radius:0; box-shadow:none;">
                            <table class="table ls-table ls-table--dense mb-0">
                                <thead>
                                    <tr>
                                        <th>Job</th>
                                        <th>Status</th>
                                        <th>PO cover</th>
                                        <th class="text-right">Samples</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jobs as $job)
                                        <tr>
                                            <td>{{ $job->batch_code ?: $job->id }}</td>
                                            <td>
                                                @if(filled($job->status))
                                                    <span class="ls-pill ls-pill--open">{{ $job->status }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @switch($job->po_status)
                                                    @case('covered') <span class="cpo-status cpo-status--active">Covered</span> @break
                                                    @case('awaiting_po') <span class="cpo-status cpo-status--exhausted">Awaiting PO</span> @break
                                                    @case('not_required') <span class="text-muted">Partly covered</span> @break
                                                    @case('cancelled') <span class="text-muted">Cancelled (no PO)</span> @break
                                                    @default <span class="text-muted">—</span>
                                                @endswitch
                                            </td>
                                            <td class="text-right">{{ $job->samples_count ?? 0 }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No jobs linked yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5 mb-3">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header" style="cursor:default;"><span>Commercial links</span></div>
                    <div class="ls-soft-card__body">
                        <dl class="cpo-review">
                            <dt>Customer</dt>
                            <dd>{{ $po->customer?->name ?? '—' }}</dd>
                            <dt>Quotation</dt>
                            <dd>
                                @if($po->quotation)
                                    <a href="{{ route('add-qoute-details-view', ['id' => $po->quotation->id]) }}">
                                        {{ $po->quotation->quote_number ?: $po->quotation->id }}
                                    </a>
                                @else
                                    —
                                @endif
                            </dd>
                            <dt>Currency</dt>
                            <dd>{{ $currencyCode ?? '—' }}</dd>
                            @if(! $po->isBlanket())
                                <dt>TRF / request</dt>
                                <dd>
                                    @if($po->enquiry)
                                        <a href="{{ $po->enquiry->staffViewUrl() }}">{{ $trfLabel ?: 'Open request' }}</a>
                                    @else
                                        —
                                    @endif
                                </dd>
                            @endif
                            <dt>Expiry reminder</dt>
                            <dd>{{ (int) ($po->expiry_notice_days ?? 0) }} days before</dd>
                            @if($po->closed_at)
                                <dt>{{ $po->status?->label() ?? 'Closed' }} on</dt>
                                <dd>{{ $po->closed_at->format('d M Y H:i') }}</dd>
                            @endif
                            @if($po->notes)
                                <dt>Notes</dt>
                                <dd style="white-space: pre-line;">{{ $po->notes }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-3">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header d-flex justify-content-between align-items-center" style="cursor:default;">
                        <span>PO file</span>
                        @if($po->fileExists())
                            <a href="{{ route('billing.customer-purchase-orders.download', $po->id) }}" class="ls-btn ls-btn--primary" style="height:auto;padding:0.25rem 0.65rem;font-size:0.75rem;">
                                <i class="mdi mdi-download"></i> Download
                            </a>
                        @endif
                    </div>
                    <div class="ls-soft-card__body">
                        @if($po->fileExists())
                            <div class="d-flex align-items-center mb-3" style="gap:12px;">
                                <div class="ls-pill ls-pill--open" style="width:42px;height:42px;justify-content:center;border-radius:10px;">
                                    <i class="mdi {{ $po->isPdf() ? 'mdi-file-pdf-box' : ($po->isImage() ? 'mdi-file-image' : 'mdi-file-document-outline') }}" style="font-size:1.25rem;"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="ls-table__stack-primary" style="color:var(--ls-ink);">{{ $po->file_name }}</div>
                                    <div class="ls-table__stack-secondary">
                                        @if($po->size)
                                            {{ number_format($po->size / 1024, 1) }} KB
                                        @endif
                                        @if($po->mime)
                                            · {{ $po->mime }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($po->isPdf())
                                <iframe
                                    src="{{ route('billing.customer-purchase-orders.download', ['id' => $po->id, 'inline' => 1]) }}"
                                    title="Purchase order PDF"
                                    class="w-100 border rounded"
                                    style="min-height: 520px; background: #f8fafc; border-color: var(--ls-border, #e2e8f0) !important;"
                                ></iframe>
                            @elseif($po->isImage())
                                <div class="text-center">
                                    <img
                                        src="{{ route('billing.customer-purchase-orders.download', ['id' => $po->id, 'inline' => 1]) }}"
                                        alt="{{ $po->file_name }}"
                                        class="img-fluid rounded border"
                                        style="max-height: 520px;"
                                    >
                                </div>
                            @else
                                <div class="p-3 border rounded" style="border-style:dashed !important; background:#f8fafc;">
                                    <p class="text-muted mb-2">Preview is not available for this file type.</p>
                                    <a href="{{ route('billing.customer-purchase-orders.download', ['id' => $po->id, 'inline' => 1]) }}" target="_blank" class="ls-btn ls-btn--secondary-fill">
                                        Open file
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <i class="mdi mdi-file-hidden text-muted" style="font-size:2rem;"></i>
                                <h6 class="text-muted mt-2 mb-1">No file uploaded</h6>
                                <p class="text-muted small mb-0">This PO was recorded without an attachment.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($samples->isNotEmpty())
            <div class="ls-soft-card is-expanded mb-3">
                <div class="ls-soft-card__header d-flex justify-content-between align-items-center" style="cursor:default;">
                    <span>Samples</span>
                    <span class="ls-pill ls-pill--inactive">{{ $samples->count() }}</span>
                </div>
                <div class="ls-soft-card__body p-0">
                    <div class="ls-table-wrap" style="border:0; border-radius:0; box-shadow:none;">
                        <table class="table ls-table ls-table--dense mb-0">
                            <thead>
                                <tr>
                                    <th>Sample code</th>
                                    <th>Customer sample ID</th>
                                    <th>Material status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($samples as $sample)
                                    <tr>
                                        <td>{{ $sample->sample_code ?? $sample->id }}</td>
                                        <td>{{ filled($sample->customer_sample_id ?? null) ? $sample->customer_sample_id : '—' }}</td>
                                        <td>
                                            @if(filled($sample->material_status ?? null))
                                                <span class="ls-pill ls-pill--open">{{ $sample->material_status }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if($amendable && $canClose)
            <div class="text-right mb-3">
                <button type="button" class="btn btn-sm btn-link text-danger" wire:click="openFinalise('cancel')">
                    <i class="mdi mdi-cancel"></i> Cancel this PO (recorded in error)
                </button>
            </div>
        @endif

        @include('livewire.billing.partials.customer-purchase-order-amendment-modals')
    @endif

    @include('livewire.billing.partials.customer-purchase-order-header-styles')
    @include('livewire.billing.partials.customer-purchase-order-ledger-styles')
</div>
