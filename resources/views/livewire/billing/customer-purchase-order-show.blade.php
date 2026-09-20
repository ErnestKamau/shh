@php
    $po = $purchaseOrder;
    $enquiryStatus = filled($po?->enquiry?->status)
        ? str_replace('_', ' ', (string) $po->enquiry->status)
        : null;
    $trfLabel = $po?->enquiry?->submissionFormInstance?->form_number
        ?: ($po?->enquiry?->unique_identification ?: null);
@endphp

<div class="cpo-ls-show ls-quotation-shell">
    @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')

    @if($po)
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
                        @if($po->po_skipped)
                            <span class="cpo-stat-pill cpo-stat-pill--skip">Skipped</span>
                        @else
                            <span class="cpo-stat-pill cpo-stat-pill--ok">Recorded</span>
                        @endif
                        @if($po->fileExists())
                            <span class="cpo-stat-pill cpo-stat-pill--file">
                                <i class="mdi mdi-paperclip"></i> File attached
                            </span>
                        @endif
                    </div>

                    <p class="cpo-burgundy-header__meta">
                        <strong>{{ $po->customer?->name ?? 'Unknown customer' }}</strong>
                        <span class="mx-1">·</span>
                        Recorded {{ optional($po->recorded_at)->format('d M Y H:i') ?? '—' }}
                        @if($po->uploader)
                            <span class="mx-1">·</span>
                            by {{ $po->uploader->name }}
                        @endif
                    </p>
                </div>

                <div class="cpo-burgundy-header__actions">
                    @if($po->fileExists())
                        <a href="{{ route('billing.customer-purchase-orders.download', $po->id) }}" class="cpo-header-btn cpo-header-btn--light">
                            <i class="mdi mdi-download"></i> Download file
                        </a>
                    @endif
                    <a href="{{ route('billing.customer-purchase-orders') }}" class="cpo-header-btn cpo-header-btn--ghost">
                        Back to list
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3 col-6 mb-2">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header" style="cursor:default;">
                        <span>PO number</span>
                    </div>
                    <div class="ls-soft-card__body">
                        <div class="ls-table__stack-primary" style="font-size:1.05rem;">
                            {{ $po->po_skipped ? '—' : ($po->po_number ?: '—') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header" style="cursor:default;">
                        <span>Jobs</span>
                    </div>
                    <div class="ls-soft-card__body">
                        <div class="ls-table__stack-primary" style="font-size:1.05rem;">{{ $jobs->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header" style="cursor:default;">
                        <span>Samples</span>
                    </div>
                    <div class="ls-soft-card__body">
                        <div class="ls-table__stack-primary" style="font-size:1.05rem;">{{ $samples->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header" style="cursor:default;">
                        <span>Attachment</span>
                    </div>
                    <div class="ls-soft-card__body">
                        @if($po->fileExists())
                            <span class="ls-pill ls-pill--open">Yes</span>
                        @else
                            <span class="ls-pill ls-pill--inactive">None</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5 mb-3">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header" style="cursor:default;">
                        <span>Commercial links</span>
                    </div>
                    <div class="ls-soft-card__body">
                        <div class="ls-field mb-3">
                            <label class="ls-field__label">Customer</label>
                            <div class="ls-field__control">
                                <div class="ls-field__input" style="background:#f8fafc;">{{ $po->customer?->name ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="ls-field mb-3">
                            <label class="ls-field__label">Quotation</label>
                            <div>
                                @if($po->quotation)
                                    <a href="{{ route('add-qoute-details-view', ['id' => $po->quotation->id]) }}">
                                        {{ $po->quotation->quote_number ?: $po->quotation->id }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="ls-field mb-3">
                            <label class="ls-field__label">TRF / Request</label>
                            <div>
                                @if($po->enquiry)
                                    <a href="{{ $po->enquiry->staffViewUrl() }}">
                                        {{ $trfLabel ?: 'Open request' }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="ls-field mb-0">
                            <label class="ls-field__label">Enquiry status</label>
                            <div>
                                @if($enquiryStatus)
                                    <span class="ls-pill ls-pill--open">{{ ucwords($enquiryStatus) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
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
                                    <a
                                        href="{{ route('billing.customer-purchase-orders.download', ['id' => $po->id, 'inline' => 1]) }}"
                                        target="_blank"
                                        class="ls-btn ls-btn--secondary-fill"
                                    >
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

        <div class="row">
            <div class="col-lg-5 mb-3">
                <div class="ls-soft-card is-expanded h-100">
                    <div class="ls-soft-card__header d-flex justify-content-between align-items-center" style="cursor:default;">
                        <span>Jobs (batches)</span>
                        <span class="ls-pill ls-pill--inactive">{{ $jobs->count() }}</span>
                    </div>
                    <div class="ls-soft-card__body p-0">
                        <div class="ls-table-wrap" style="border:0; border-radius:0; box-shadow:none;">
                            <table class="table ls-table ls-table--dense mb-0">
                                <thead>
                                    <tr>
                                        <th>Batch</th>
                                        <th>Status</th>
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
                                            <td class="text-right">{{ $job->samples?->count() ?? 0 }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No jobs linked yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-3">
                <div class="ls-soft-card is-expanded h-100">
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
                                    @forelse($samples as $sample)
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
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No samples linked yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.billing.partials.customer-purchase-order-header-styles')
</div>
