<div class="container-fluid invoice-show-page">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show shadow-sm" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    @if(!$invoice)
        <div class="alert alert-danger shadow-sm">Invoice not found.</div>
    @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 page-header-card">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                            <div class="mb-3 mb-lg-0">
                                <div class="page-header-main d-flex flex-wrap align-items-center gap-2">
                                    <h2 class="mb-0 page-header-title">
                                        <i class="mdi mdi-file-document-outline text-primary mr-2"></i>
                                        {{ $invoice->invoice_number }}
                                    </h2>
                                    <span class="status-chip {{ $invoice->zoho_so_confirmed ? 'status-chip--confirmed' : 'status-chip--draft' }}">
                                        {{ $invoice->status_label }}
                                    </span>
                                </div>
                                <p class="text-muted mb-0 page-header-subtitle">
                                    Draft invoice for {{ $invoice->crmCustomer->name ?? 'Unknown customer' }}
                                </p>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('billing.invoices') }}" class="btn btn-outline-secondary page-action-btn">
                                    <i class="mdi mdi-arrow-left"></i> Back
                                </a>
                                <a href="{{ route('print-invoice', ['id' => $invoice->id]) }}" target="_blank" class="btn btn-primary page-action-btn">
                                    <i class="mdi mdi-printer"></i> Print
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <div class="metric-label">Total Amount</div>
                        <div class="metric-value">{{ number_format($summary['total'], 2) }}</div>
                        <div class="metric-meta">{{ $invoice->currency_label }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <div class="metric-label">Tax</div>
                        <div class="metric-value">{{ number_format($summary['tax'], 2) }}</div>
                        <div class="metric-meta">Subtotal {{ number_format($summary['subtotal'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <div class="metric-label">Linked Batches</div>
                        <div class="metric-value">{{ $summary['batch_count'] }}</div>
                        <div class="metric-meta">{{ $summary['line_count'] }} line items</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <div class="metric-label">Due Date</div>
                        <div class="metric-value">
                            {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '—' }}
                        </div>
                        <div class="metric-meta">
                            Created {{ $invoice->created_at->format('d M Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm overview-card h-100">
                    <div class="overview-card__hero">
                        <div class="overview-card__avatar">
                            <i class="mdi mdi-domain"></i>
                        </div>
                        <div class="overview-card__identity">
                            <span class="overview-card__eyebrow">Bill to</span>
                            <h5 class="overview-card__customer mb-1">{{ $invoice->crmCustomer->name ?? 'Unknown customer' }}</h5>
                            <p class="overview-card__ref mb-0">
                                {{ $invoice->reference_number ? 'Ref: '.$invoice->reference_number : 'No reference number' }}
                            </p>
                        </div>
                    </div>

                    <div class="overview-card__body">
                        <div class="overview-detail">
                            <div class="overview-detail__icon"><i class="mdi mdi-cash"></i></div>
                            <div>
                                <span class="overview-detail__label">Currency</span>
                                <span class="overview-detail__value">{{ $invoice->currency_label }}</span>
                            </div>
                        </div>
                        <div class="overview-detail">
                            <div class="overview-detail__icon"><i class="mdi mdi-account-circle-outline"></i></div>
                            <div>
                                <span class="overview-detail__label">Created by</span>
                                <span class="overview-detail__value">{{ $invoice->created_by_user?->name ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="overview-detail">
                            <div class="overview-detail__icon"><i class="mdi mdi-calendar-clock"></i></div>
                            <div>
                                <span class="overview-detail__label">Invoice date</span>
                                <span class="overview-detail__value">{{ $invoice->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                        <div class="overview-detail">
                            <div class="overview-detail__icon"><i class="mdi mdi-calendar-alert"></i></div>
                            <div>
                                <span class="overview-detail__label">Due date</span>
                                <span class="overview-detail__value">
                                    {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="overview-card__actions">
                        <span class="overview-actions__title">Send to customer</span>
                        <div class="overview-actions__grid">
                            <button type="button"
                                    wire:click="sendInvoiceToCustomer"
                                    wire:loading.attr="disabled"
                                    wire:target="sendInvoiceToCustomer"
                                    class="overview-action-btn overview-action-btn--primary {{ $canSendToCustomer ? '' : 'is-disabled' }}"
                                    @if(!$canSendToCustomer) disabled title="Upload invoice PDF first" @endif>
                                <i class="mdi mdi-email-send-outline"></i>
                                <span>Email invoice</span>
                            </button>
                            <a href="{{ route('print-invoice', ['id' => $invoice->id]) }}"
                               target="_blank"
                               class="overview-action-btn overview-action-btn--secondary">
                                <i class="mdi mdi-file-pdf-box"></i>
                                <span>Print / PDF</span>
                            </a>
                            <button type="button" class="overview-action-btn overview-action-btn--muted" disabled title="Awaiting integration">
                                <i class="mdi mdi-message-text-outline"></i>
                                <span>SMS customer</span>
                            </button>
                            <button type="button" class="overview-action-btn overview-action-btn--muted" disabled title="Awaiting integration">
                                <i class="mdi mdi-share-variant-outline"></i>
                                <span>Customer portal</span>
                            </button>
                        </div>
                        @unless($canSendToCustomer)
                            <p class="overview-actions__hint mb-0">
                                <i class="mdi mdi-information-outline"></i>
                                Upload the invoice PDF to enable email delivery.
                            </p>
                        @endunless
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm inner-card mb-4">
                    <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Linked Batches</h5>
                            <p class="text-muted mb-0 small">Sample batches tied to this invoice</p>
                        </div>
                        <span class="badge badge-light">{{ $summary['batch_count'] }}</span>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover modern-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Batch Code</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Created</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invoice->batches as $batch)
                                        <tr>
                                            <td><strong>{{ $batch->batch_code }}</strong></td>
                                            <td>
                                                <span class="mini-status-chip">{{ $batch->status ?? '—' }}</span>
                                            </td>
                                            <td>{{ $batch->reference_number ?? '—' }}</td>
                                            <td class="text-muted">{{ $batch->created_at?->format('Y-m-d') }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('view-batch-details', ['batch' => $batch->id]) }}"
                                                   class="btn btn-sm rm-act-btn rm-act-btn--view"
                                                   title="View batch">
                                                    <i class="mdi mdi-open-in-new"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No batches linked to this invoice.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm inner-card">
                    <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Line Items</h5>
                            <p class="text-muted mb-0 small">Analysis types and parameters</p>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover modern-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Analysis Type</th>
                                        <th>Parameters</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Tax</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->details as $detail)
                                        <tr>
                                            <td>
                                                <strong>{{ $detail->analysis_type_name }}</strong>
                                                @if($detail->analysisType?->code)
                                                    <br><small class="text-muted">{{ $detail->analysisType->code }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="parameters-label">{{ $detail->parameters_label }}</span>
                                            </td>
                                            <td class="text-center">{{ $detail->quantity }}</td>
                                            <td class="text-end">{{ number_format((float) $detail->selling_price, 2) }}</td>
                                            <td class="text-end">{{ number_format((float) $detail->tax_amount, 2) }}</td>
                                            <td class="text-end"><strong>{{ number_format((float) $detail->total, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end text-muted">Subtotal</th>
                                        <th class="text-end">{{ number_format($summary['subtotal'], 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end text-muted">Tax</th>
                                        <th class="text-end">{{ number_format($summary['tax'], 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Total</th>
                                        <th class="text-end">{{ number_format($summary['total'], 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm workspace-card">
            <div class="card-body p-0">
                <div class="workspace-tabs px-3 px-lg-4 pt-3 pt-lg-4">
                    <button type="button"
                            class="workspace-tab {{ $activeTab === 'payments' ? 'active' : '' }}"
                            wire:click="setActiveTab('payments')">
                        <i class="mdi mdi-cash-multiple mr-1"></i>
                        Payments
                        <span class="tab-count">{{ $payments->count() }}</span>
                    </button>
                    <button type="button"
                            class="workspace-tab {{ $activeTab === 'attachments' ? 'active' : '' }}"
                            wire:click="setActiveTab('attachments')">
                        <i class="mdi mdi-paperclip mr-1"></i>
                        Attachments
                        <span class="tab-count">{{ $attachments->count() }}</span>
                    </button>
                    <button type="button"
                            class="workspace-tab {{ $activeTab === 'notes' ? 'active' : '' }}"
                            wire:click="setActiveTab('notes')">
                        <i class="mdi mdi-note-text-outline mr-1"></i>
                        Notes
                        <span class="tab-count">{{ $notes->count() }}</span>
                    </button>
                </div>

                <div class="p-3 p-lg-4">
                    @if($activeTab === 'payments')
                        <div class="payments-toolbar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h6 class="mb-1">Payment records</h6>
                                <p class="text-muted mb-0 small">
                                    Remaining balance:
                                    <strong>{{ number_format($remainingInvoiceAmount, 2) }} {{ $invoice->currency_label }}</strong>
                                </p>
                            </div>
                            <button type="button"
                                    class="btn btn-primary btn-sm payments-raise-btn"
                                    wire:click="openRaiseControlModal"
                                    @if($remainingInvoiceAmount <= 0) disabled @endif>
                                <i class="mdi mdi-barcode-scan"></i> Raise Control No
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover modern-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Reference / Control Number</th>
                                        <th>Received By</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        @php
                                            $controlRef = trim(implode(' / ', array_filter([
                                                $payment->ref_no,
                                                $payment->transaction_no,
                                            ])));
                                        @endphp
                                        <tr>
                                            <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                                            <td>{{ $payment->payment_method ?? '—' }}</td>
                                            <td>
                                                @if($controlRef !== '')
                                                    <span class="control-ref-chip">{{ $controlRef }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $payment->receivername }}</td>
                                            <td class="text-end">{{ $payment->amount ?? '—' }}</td>
                                            <td class="text-end">{{ $payment->balance ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <i class="mdi mdi-cash-remove d-block mb-2" style="font-size: 2rem;"></i>
                                                No payment records for this invoice yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if($activeTab === 'attachments')
                        <div class="row">
                            <div class="col-lg-5 mb-4 mb-lg-0">
                                <div class="invoice-side-form h-100">
                                    <div class="invoice-side-form__header">
                                        <div class="invoice-side-form__icon invoice-side-form__icon--attachment">
                                            <i class="mdi mdi-paperclip"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Add attachment</h6>
                                            <p class="text-muted mb-0 small">Upload a document for this invoice</p>
                                        </div>
                                    </div>
                                    <form wire:submit.prevent="addAttachment" class="invoice-side-form__body">
                                        <div class="invoice-field mb-3">
                                            <label class="invoice-field__label" for="attachment-title">Title</label>
                                            <input type="text"
                                                   id="attachment-title"
                                                   wire:model="attachmentTitle"
                                                   class="invoice-field__input @error('attachmentTitle') is-invalid @enderror"
                                                   placeholder="e.g. Signed invoice PDF">
                                            @error('attachmentTitle')
                                                <div class="invoice-field__error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="invoice-field mb-3">
                                            <label class="invoice-field__label" for="attachment-description">Description <span class="text-muted">(optional)</span></label>
                                            <textarea id="attachment-description"
                                                      wire:model="attachmentDescription"
                                                      rows="2"
                                                      class="invoice-field__input invoice-field__textarea"
                                                      placeholder="Brief description of the file"></textarea>
                                        </div>
                                        <div class="invoice-field mb-3">
                                            <label class="invoice-field__label">File</label>
                                            <div class="invoice-dropzone"
                                                 x-data="{ isDragging: false }"
                                                 x-on:dragover.prevent="isDragging = true"
                                                 x-on:dragleave.prevent="isDragging = false"
                                                 x-on:drop.prevent="isDragging = false; if ($event.dataTransfer.files.length) { $wire.upload('attachmentFile', $event.dataTransfer.files[0]); }"
                                                 x-bind:class="{ 'invoice-dropzone--dragging': isDragging }">
                                                <input type="file"
                                                       id="attachment-file-input"
                                                       wire:model="attachmentFile"
                                                       class="invoice-dropzone__input"
                                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.zip">
                                                @if($attachmentFile)
                                                    <div class="invoice-dropzone__selected">
                                                        <div class="invoice-dropzone__file-icon">
                                                            <i class="mdi mdi-file-document-outline"></i>
                                                        </div>
                                                        <div class="invoice-dropzone__file-meta">
                                                            <strong>{{ $attachmentFile->getClientOriginalName() }}</strong>
                                                            <span>{{ number_format($attachmentFile->getSize() / 1024, 1) }} KB</span>
                                                            <label for="attachment-file-input" class="invoice-dropzone__change">Change file</label>
                                                        </div>
                                                        <button type="button"
                                                                class="invoice-dropzone__clear"
                                                                wire:click="clearAttachmentFile"
                                                                title="Remove file">
                                                            <i class="mdi mdi-close"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <label for="attachment-file-input" class="invoice-dropzone__prompt mb-0">
                                                        <span class="invoice-dropzone__icon">
                                                            <i class="mdi mdi-cloud-upload-outline"></i>
                                                        </span>
                                                        <span class="invoice-dropzone__title">Drag & drop file here</span>
                                                        <span class="invoice-dropzone__hint">or click to browse from your device</span>
                                                        <span class="invoice-dropzone__formats">PDF, Word, Excel, images — max 10 MB</span>
                                                    </label>
                                                @endif
                                                <div wire:loading.flex wire:target="attachmentFile" class="invoice-dropzone__loading">
                                                    <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                                    Processing file…
                                                </div>
                                            </div>
                                            @error('attachmentFile')
                                                <div class="invoice-field__error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit"
                                                class="invoice-side-form__submit"
                                                wire:loading.attr="disabled"
                                                wire:target="addAttachment,attachmentFile">
                                            <span wire:loading.remove wire:target="addAttachment,attachmentFile">
                                                <i class="mdi mdi-upload"></i> Upload attachment
                                            </span>
                                            <span wire:loading wire:target="addAttachment,attachmentFile">
                                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                                Uploading…
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="table-responsive">
                                    <table class="table table-hover modern-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Uploaded By</th>
                                                <th>Date</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($attachments as $attachment)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $attachment->title }}</strong>
                                                        @if(!empty($attachment->description))
                                                            <br><small class="text-muted">{{ $attachment->description }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $attachment->uploader_name }}</td>
                                                    <td class="text-muted">
                                                        {{ $attachment->created_at ? \Carbon\Carbon::parse($attachment->created_at)->format('Y-m-d') : '—' }}
                                                    </td>
                                                    <td class="text-end">
                                                        @if(!empty($attachment->file))
                                                            <a href="{{ $attachment->file }}" target="_blank" class="btn btn-sm rm-act-btn rm-act-btn--view" title="Open">
                                                                <i class="mdi mdi-download"></i>
                                                            </a>
                                                        @endif
                                                        @if(empty($attachment->is_upload_url))
                                                            <button type="button"
                                                                    wire:click="deleteAttachment('{{ $attachment->id }}')"
                                                                    wire:confirm="Remove this attachment?"
                                                                    class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                                    title="Delete">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-5">No attachments yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($activeTab === 'notes')
                        <div class="row">
                            <div class="col-lg-5 mb-4 mb-lg-0">
                                <div class="invoice-side-form h-100">
                                    <div class="invoice-side-form__header">
                                        <div class="invoice-side-form__icon invoice-side-form__icon--note">
                                            <i class="mdi mdi-note-text-outline"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Add note</h6>
                                            <p class="text-muted mb-0 small">Record billing comments or follow-ups</p>
                                        </div>
                                    </div>
                                    <form wire:submit.prevent="addNote" class="invoice-side-form__body">
                                        <div class="invoice-field mb-3">
                                            <label class="invoice-field__label" for="note-type">Note type</label>
                                            <div class="invoice-select-wrap">
                                                <select id="note-type" wire:model="noteType" class="invoice-field__input invoice-field__select">
                                                    <option value="General">General</option>
                                                    <option value="Billing">Billing</option>
                                                    <option value="Follow-up">Follow-up</option>
                                                    <option value="Internal">Internal</option>
                                                </select>
                                                <i class="mdi mdi-chevron-down invoice-select-wrap__chevron"></i>
                                            </div>
                                        </div>
                                        <div class="invoice-field mb-3">
                                            <label class="invoice-field__label" for="note-description">Note</label>
                                            <textarea id="note-description"
                                                      wire:model="noteDescription"
                                                      rows="5"
                                                      class="invoice-field__input invoice-field__textarea invoice-field__textarea--note @error('noteDescription') is-invalid @enderror"
                                                      placeholder="Enter your note here…"></textarea>
                                            @error('noteDescription')
                                                <div class="invoice-field__error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit"
                                                class="invoice-side-form__submit invoice-side-form__submit--note"
                                                wire:loading.attr="disabled"
                                                wire:target="addNote">
                                            <span wire:loading.remove wire:target="addNote">
                                                <i class="mdi mdi-plus-circle-outline"></i> Add note
                                            </span>
                                            <span wire:loading wire:target="addNote">
                                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                                Saving…
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="notes-feed">
                                    @forelse($notes as $note)
                                        <div class="note-card">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <span class="note-type-badge">{{ $note->type }}</span>
                                                    <span class="text-muted small ml-2">{{ $note->creator_name }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-muted small">{{ $note->created_at?->format('d M Y H:i') }}</span>
                                                    <button type="button"
                                                            wire:click="deleteNote('{{ $note->id }}')"
                                                            wire:confirm="Delete this note?"
                                                            class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                            title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <p class="mb-0 note-body">{{ $note->description }}</p>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-5">
                                            <i class="mdi mdi-note-outline d-block mb-2" style="font-size: 2rem;"></i>
                                            No notes recorded for this invoice.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($showRaiseControlModal)
        <div class="modal fade show d-block invoice-modal-overlay" tabindex="-1" wire:click.self="closeRaiseControlModal">
            <div class="modal-dialog modal-dialog-centered" wire:click.stop>
                <div class="modal-content invoice-modal">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold">
                            <i class="mdi mdi-barcode-scan text-primary mr-2"></i>
                            Raise Control Number
                        </h5>
                        <button type="button" class="close" wire:click="closeRaiseControlModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-2">
                        <div class="raise-control-info mb-3">
                            <i class="mdi mdi-information-outline raise-control-info__icon"></i>
                            <p class="mb-0">
                                This will raise a new payment record and trigger a new control number generation from the <strong>GEPG System</strong>.
                            </p>
                        </div>

                        <form wire:submit.prevent="submitRaiseControl">
                            <div class="form-group mb-3">
                                <label class="soft-label">Amount ({{ $invoice->currency_label }})</label>
                                <input type="number"
                                       step="0.01"
                                       min="0.01"
                                       max="{{ $remainingInvoiceAmount }}"
                                       wire:model="controlAmount"
                                       class="form-control modern-input @error('controlAmount') is-invalid @enderror"
                                       placeholder="0.00">
                                @error('controlAmount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-1">
                                    Maximum allowed: {{ number_format($remainingInvoiceAmount, 2) }} (remaining invoice balance)
                                </small>
                            </div>

                            <div class="gepg-awaiting-banner mb-3">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                <span>Awaiting GEPG integration</span>
                            </div>

                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <button type="button" class="btn btn-light btn-sm" wire:click="closeRaiseControlModal">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="btn btn-primary btn-sm"
                                        wire:loading.attr="disabled"
                                        wire:target="submitRaiseControl">
                                    <i class="mdi mdi-barcode-scan"></i> Raise Control No
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .invoice-show-page {
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --blue-600: #2563eb;
            --surface: #ffffff;
        }

        .invoice-show-page .page-header-card,
        .invoice-show-page .metric-card,
        .invoice-show-page .inner-card,
        .invoice-show-page .workspace-card {
            border-radius: 15px;
        }

        .invoice-show-page .page-header-title {
            color: var(--slate-900);
            font-weight: 700;
        }

        .invoice-show-page .page-header-subtitle {
            font-size: 0.95rem;
        }

        .invoice-show-page .status-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .invoice-show-page .status-chip--draft {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .invoice-show-page .status-chip--confirmed {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .invoice-show-page .page-action-btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.45rem 1rem;
        }

        .invoice-show-page .metric-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--slate-500);
            margin-bottom: 6px;
        }

        .invoice-show-page .metric-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--slate-900);
            line-height: 1.2;
        }

        .invoice-show-page .metric-meta {
            font-size: 12px;
            color: var(--slate-500);
            margin-top: 4px;
        }

        .invoice-show-page .overview-card {
            border-radius: 16px;
            overflow: hidden;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .invoice-show-page .overview-card__hero {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 20px 20px 16px;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 55%, #ffffff 100%);
            border-bottom: 1px solid #e8eef5;
        }

        .invoice-show-page .overview-card__avatar {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #2563eb;
            color: #fff;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
        }

        .invoice-show-page .overview-card__eyebrow {
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .invoice-show-page .overview-card__customer {
            color: var(--slate-900);
            font-weight: 700;
            font-size: 1.05rem;
        }

        .invoice-show-page .overview-card__ref {
            font-size: 12px;
            color: var(--slate-500);
        }

        .invoice-show-page .overview-card__body {
            padding: 16px 20px 8px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .invoice-show-page .overview-detail {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #eef2f7;
        }

        .invoice-show-page .overview-detail__icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .invoice-show-page .overview-detail__label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--slate-500);
            margin-bottom: 2px;
        }

        .invoice-show-page .overview-detail__value {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-900);
        }

        .invoice-show-page .overview-card__actions {
            padding: 14px 20px 20px;
            border-top: 1px solid #eef2f7;
            background: #fff;
        }

        .invoice-show-page .overview-actions__title {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--slate-500);
            margin-bottom: 10px;
        }

        .invoice-show-page .overview-actions__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .invoice-show-page .overview-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }

        .invoice-show-page .overview-action-btn--primary {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .invoice-show-page .overview-action-btn--primary:hover:not(:disabled) {
            background: #1d4ed8;
            color: #fff;
        }

        .invoice-show-page .overview-action-btn--secondary {
            background: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .invoice-show-page .overview-action-btn--secondary:hover {
            background: #dcfce7;
            color: #15803d;
        }

        .invoice-show-page .overview-action-btn--muted {
            background: #f8fafc;
            color: #94a3b8;
            border-color: #e2e8f0;
            cursor: not-allowed;
            opacity: 0.85;
        }

        .invoice-show-page .overview-action-btn.is-disabled,
        .invoice-show-page .overview-action-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .invoice-show-page .overview-actions__hint {
            margin-top: 10px;
            font-size: 11px;
            color: #64748b;
        }

        .invoice-show-page .payments-raise-btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .invoice-show-page .control-ref-chip {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .invoice-show-page .invoice-modal-overlay {
            background: rgba(15, 23, 42, 0.52) !important;
            backdrop-filter: blur(4px);
        }

        .invoice-show-page .invoice-modal {
            border-radius: 16px;
            border: 0;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
        }

        .invoice-show-page .raise-control-info {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 12px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            font-size: 14px;
        }

        .invoice-show-page .raise-control-info__icon {
            font-size: 22px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .invoice-show-page .gepg-awaiting-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #92400e;
            font-weight: 600;
            font-size: 13px;
        }

        .invoice-show-page .gepg-awaiting-banner i {
            font-size: 20px;
            color: #d97706;
        }

        .invoice-show-page .workspace-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            border-bottom: 1px solid var(--slate-200);
        }

        .invoice-show-page .workspace-tab {
            border: 0;
            background: #e2e8f0;
            color: var(--slate-700);
            padding: 12px 18px;
            border-radius: 14px 14px 0 0;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .invoice-show-page .workspace-tab.active {
            background: var(--surface);
            color: var(--blue-600);
            box-shadow: 0 -6px 18px rgba(37, 99, 235, 0.08);
        }

        .invoice-show-page .tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            margin-left: 6px;
            padding: 0 6px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            font-size: 11px;
        }

        .invoice-show-page .workspace-tab.active .tab-count {
            background: rgba(37, 99, 235, 0.18);
        }

        .invoice-show-page .modern-table thead th {
            border-top: 0;
            background: #f8fafc;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 11px;
            font-weight: 700;
        }

        .invoice-show-page .modern-table td,
        .invoice-show-page .modern-table th {
            vertical-align: middle;
            border-color: #eef2f7;
        }

        .invoice-show-page .parameters-label {
            font-size: 13px;
            color: var(--slate-700);
            line-height: 1.45;
        }

        .invoice-show-page .mini-status-chip {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            color: var(--slate-700);
            font-size: 12px;
            font-weight: 600;
        }

        .invoice-show-page .soft-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .invoice-show-page .modern-input {
            border-radius: 10px;
            border: 1px solid var(--slate-200);
        }

        .invoice-show-page .inner-panel {
            border-radius: 12px;
            border-color: #eef2f7 !important;
            background: #fafbfc;
        }

        .invoice-show-page .invoice-side-form {
            border-radius: 16px;
            border: 1px solid #e8eef5;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .invoice-show-page .invoice-side-form__header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid #eef2f7;
            background: #fff;
        }

        .invoice-show-page .invoice-side-form__icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .invoice-show-page .invoice-side-form__icon--attachment {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .invoice-show-page .invoice-side-form__icon--note {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .invoice-show-page .invoice-side-form__body {
            padding: 20px;
        }

        .invoice-show-page .invoice-field__label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--slate-500);
            margin-bottom: 8px;
        }

        .invoice-show-page .invoice-field__input {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            color: var(--slate-900);
            background: #fff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .invoice-show-page .invoice-field__input:focus {
            outline: none;
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .invoice-show-page .invoice-field__input.is-invalid {
            border-color: #fca5a5;
        }

        .invoice-show-page .invoice-field__textarea {
            resize: vertical;
            min-height: 72px;
        }

        .invoice-show-page .invoice-field__textarea--note {
            min-height: 120px;
            line-height: 1.5;
        }

        .invoice-show-page .invoice-field__error {
            margin-top: 6px;
            font-size: 12px;
            color: #dc2626;
        }

        .invoice-show-page .invoice-select-wrap {
            position: relative;
        }

        .invoice-show-page .invoice-field__select {
            appearance: none;
            padding-right: 36px;
        }

        .invoice-show-page .invoice-select-wrap__chevron {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            font-size: 18px;
        }

        .invoice-show-page .invoice-dropzone {
            position: relative;
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            background: #f8fafc;
            transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .invoice-show-page .invoice-dropzone--dragging {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .invoice-show-page .invoice-dropzone__input {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }

        .invoice-show-page .invoice-dropzone__prompt {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 28px 20px;
            cursor: pointer;
            width: 100%;
        }

        .invoice-show-page .invoice-dropzone__icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #2563eb;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
        }

        .invoice-show-page .invoice-dropzone__title {
            font-size: 14px;
            font-weight: 700;
            color: var(--slate-900);
            margin-bottom: 4px;
        }

        .invoice-show-page .invoice-dropzone__hint {
            font-size: 13px;
            color: var(--slate-500);
            margin-bottom: 8px;
        }

        .invoice-show-page .invoice-dropzone__formats {
            font-size: 11px;
            color: #94a3b8;
        }

        .invoice-show-page .invoice-dropzone__selected {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
        }

        .invoice-show-page .invoice-dropzone__file-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .invoice-show-page .invoice-dropzone__file-meta {
            flex: 1;
            min-width: 0;
        }

        .invoice-show-page .invoice-dropzone__file-meta strong {
            display: block;
            font-size: 13px;
            color: var(--slate-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .invoice-show-page .invoice-dropzone__file-meta span {
            font-size: 12px;
            color: var(--slate-500);
        }

        .invoice-show-page .invoice-dropzone__clear {
            border: 0;
            background: #f1f5f9;
            color: #64748b;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .invoice-show-page .invoice-dropzone__clear:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .invoice-show-page .invoice-dropzone__loading {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            font-size: 12px;
            color: var(--slate-500);
            border-top: 1px solid #e2e8f0;
        }

        .invoice-show-page .invoice-dropzone__change {
            display: inline-block;
            margin-top: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            cursor: pointer;
        }

        .invoice-show-page .invoice-dropzone__change:hover {
            text-decoration: underline;
        }

        .invoice-show-page .invoice-side-form__submit {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 11px 16px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.28);
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }

        .invoice-show-page .invoice-side-form__submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.32);
        }

        .invoice-show-page .invoice-side-form__submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .invoice-show-page .invoice-side-form__submit--note {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            box-shadow: 0 6px 16px rgba(22, 163, 74, 0.28);
        }

        .invoice-show-page .invoice-side-form__submit--note:hover:not(:disabled) {
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.32);
        }

        .invoice-show-page .notes-feed {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .invoice-show-page .note-card {
            border: 1px solid #eef2f7;
            border-radius: 12px;
            padding: 14px 16px;
            background: #fff;
        }

        .invoice-show-page .note-type-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 700;
        }

        .invoice-show-page .note-body {
            color: var(--slate-700);
            white-space: pre-wrap;
        }

        .invoice-show-page .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            font-size: 12px;
        }

        .invoice-show-page .rm-act-btn--view {
            border: 1px solid #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .invoice-show-page .rm-act-btn--view:hover {
            background: #dcfce7;
        }

        .invoice-show-page .rm-act-btn--delete {
            border: 1px solid #fecdd3;
            color: #e11d48;
            background: #fff5f7;
        }

        .invoice-show-page .rm-act-btn--delete:hover {
            background: #ffe4e6;
        }

        @media (max-width: 767.98px) {
            .invoice-show-page .overview-actions__grid {
                grid-template-columns: 1fr;
            }

            .invoice-show-page .workspace-tab {
                width: 100%;
                border-radius: 12px;
                text-align: left;
            }
        }
    </style>
</div>
