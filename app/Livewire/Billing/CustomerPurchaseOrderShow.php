<?php

namespace App\Livewire\Billing;

use App\Enums\Commercial\PurchaseOrderInvoicingMode;
use App\Enums\Commercial\PurchaseOrderInvoicingPeriod;
use App\Enums\Commercial\SampleHeaderPoStatus;
use App\Http\Requests\Commercial\AddPurchaseOrderLineRequest;
use App\Http\Requests\Commercial\AdjustPurchaseOrderLineQuantityRequest;
use App\Http\Requests\Commercial\ChangePurchaseOrderValidityRequest;
use App\Http\Requests\Commercial\FinalisePurchaseOrderRequest;
use App\Http\Requests\Commercial\UpdateCustomerPurchaseOrderDetailsRequest;
use App\Invoice;
use App\Livewire\Concerns\ValidatesWithFormRequest;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderAmendment;
use App\Models\Commercial\CustomerPurchaseOrderLedgerEntry;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Models\SampleSubmissionRequest;
use App\QuotationDetails;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Commercial\CustomerPurchaseOrderRegistryService;
use App\Services\Commercial\QuotationPurchaseOrderLineMapper;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomerPurchaseOrderShow extends Component
{
    use ValidatesWithFormRequest;
    use WithFileUploads;
    use WithToastNotifications;

    public const MODAL_ADJUST = 'adjust';

    public const MODAL_ADD_LINE = 'add_line';

    public const MODAL_VALIDITY = 'validity';

    public const MODAL_DETAILS = 'details';

    public const MODAL_FINALISE = 'finalise';

    public string $purchaseOrderId = '';

    public ?CustomerPurchaseOrder $purchaseOrder = null;

    public string $activeModal = '';

    public int $timelineLimit = 30;

    /** @var array{line_id: string, direction: string, quantity: int|string, reason: string} */
    public array $adjustForm = ['line_id' => '', 'direction' => 'top_up', 'quantity' => '', 'reason' => ''];

    /** @var array<string, mixed> */
    public array $lineForm = [];

    public string $lineReason = '';

    public string $lineSourceDetailId = '';

    /** @var array{valid_from: string, valid_to: string, reason: string} */
    public array $validityForm = ['valid_from' => '', 'valid_to' => '', 'reason' => ''];

    /** @var array<string, mixed> */
    public array $detailsForm = [];

    /** @var array<string, int|string|null> */
    public array $lineThresholds = [];

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $detailsFile = null;

    /** @var array{action: string, reason: string} */
    public array $finaliseForm = ['action' => 'close', 'reason' => ''];

    public function mount(string $id): void
    {
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_VIEW);

        $this->purchaseOrderId = $id;
        $this->loadPurchaseOrder();
    }

    protected function loadPurchaseOrder(): void
    {
        $this->purchaseOrder = CustomerPurchaseOrder::query()
            ->with([
                'customer',
                'quotation',
                'currency',
                'uploader',
                'enquiry.submissionFormInstance.submissionForm',
                'enquiry.batch',
                'lines.sampleType',
            ])
            ->findOrFail($this->purchaseOrderId);
    }

    public function openAdjust(string $lineId, string $direction = AdjustPurchaseOrderLineQuantityRequest::DIRECTION_TOP_UP): void
    {
        $this->authorizeAmend();
        $this->resetErrorBag();
        $this->adjustForm = ['line_id' => $lineId, 'direction' => $direction, 'quantity' => '', 'reason' => ''];
        $this->activeModal = self::MODAL_ADJUST;
    }

    public function saveAdjust(CustomerPurchaseOrderRegistryService $registry): void
    {
        $this->authorizeAmend();
        $data = $this->validateWithFormRequest(AdjustPurchaseOrderLineQuantityRequest::class, $this->adjustForm, 'adjustForm.');

        $line = $this->findLine($data['line_id']);
        $delta = $data['direction'] === AdjustPurchaseOrderLineQuantityRequest::DIRECTION_REDUCE
            ? -(int) $data['quantity']
            : (int) $data['quantity'];

        try {
            $registry->adjustLineQuantity($line, $delta, $data['reason']);
        } catch (ValidationException $exception) {
            $this->rethrowWithPrefix($exception, 'adjustForm.');
        }

        $this->afterAmendment($delta > 0 ? "Line {$line->line_no} topped up by {$delta}." : "Line {$line->line_no} reduced by ".abs($delta).'.');
    }

    public function openAddLine(): void
    {
        $this->authorizeAmend();
        $this->resetErrorBag();
        $this->lineForm = $this->blankLine();
        $this->lineReason = '';
        $this->lineSourceDetailId = '';
        $this->activeModal = self::MODAL_ADD_LINE;
    }

    public function updatedLineSourceDetailId(string $detailId): void
    {
        if ($detailId === '' || $this->purchaseOrder?->quotation_header_id === null) {
            $this->lineForm = $this->blankLine();

            return;
        }

        $detail = QuotationDetails::query()
            ->with('sampletype')
            ->where('quotation_header_id', $this->purchaseOrder->quotation_header_id)
            ->find($detailId);

        if ($detail !== null) {
            $this->lineForm = app(QuotationPurchaseOrderLineMapper::class)->draftFromDetail($detail);
        }
    }

    public function saveAddLine(CustomerPurchaseOrderRegistryService $registry): void
    {
        $this->authorizeAmend();

        $line = $this->lineForm;
        $line['notify_remaining_qty'] = ($line['notify_remaining_qty'] ?? '') === '' ? null : $line['notify_remaining_qty'];
        $line['analysis_type_ids'] = array_values((array) ($line['analysis_type_ids'] ?? []));

        $data = $this->validateWithFormRequest(
            AddPurchaseOrderLineRequest::class,
            ['line' => $line, 'reason' => $this->lineReason],
            '',
        );

        try {
            $created = $registry->addLine($this->purchaseOrder, $data['line'], $data['reason']);
        } catch (ValidationException $exception) {
            $this->rethrowWithPrefix($exception, '');
        }

        $this->afterAmendment("Line {$created->line_no} added.");
    }

    public function openValidity(): void
    {
        $this->authorizeAmend();
        $this->resetErrorBag();
        $this->validityForm = [
            'valid_from' => $this->purchaseOrder?->valid_from?->toDateString() ?? '',
            'valid_to' => $this->purchaseOrder?->valid_to?->toDateString() ?? '',
            'reason' => '',
        ];
        $this->activeModal = self::MODAL_VALIDITY;
    }

    public function saveValidity(CustomerPurchaseOrderRegistryService $registry): void
    {
        $this->authorizeAmend();

        $payload = $this->validityForm;
        $payload['valid_from'] = $payload['valid_from'] === '' ? null : $payload['valid_from'];
        $data = $this->validateWithFormRequest(ChangePurchaseOrderValidityRequest::class, $payload, 'validityForm.');

        try {
            $registry->changeValidity($this->purchaseOrder, $data['valid_from'] ?? null, $data['valid_to'], $data['reason']);
        } catch (ValidationException $exception) {
            $this->rethrowWithPrefix($exception, 'validityForm.');
        }

        $this->afterAmendment('Validity updated.');
    }

    public function openDetails(): void
    {
        $this->authorizeAmend();
        $this->resetErrorBag();

        $po = $this->purchaseOrder;
        $this->detailsForm = [
            'po_number' => (string) $po->po_number,
            'invoicing_mode' => $po->invoicing_mode?->value ?? PurchaseOrderInvoicingMode::PerJob->value,
            'invoicing_period' => (string) ($po->invoicing_period ?? ''),
            'expiry_notice_days' => (int) ($po->expiry_notice_days ?? 30),
            'notes' => (string) ($po->notes ?? ''),
            'reason' => '',
        ];
        $this->lineThresholds = $po->lines
            ->mapWithKeys(fn (CustomerPurchaseOrderLine $line): array => [(string) $line->id => $line->notify_remaining_qty])
            ->all();
        $this->detailsFile = null;
        $this->activeModal = self::MODAL_DETAILS;
    }

    public function saveDetails(CustomerPurchaseOrderRegistryService $registry): void
    {
        $this->authorizeAmend();

        $payload = $this->detailsForm;
        $payload['invoicing_period'] = ($payload['invoicing_period'] ?? '') === '' ? null : $payload['invoicing_period'];
        $payload['notes'] = ($payload['notes'] ?? '') === '' ? null : $payload['notes'];
        $payload['file'] = $this->detailsFile;
        $payload['line_thresholds'] = array_map(static fn ($value) => $value === '' ? null : $value, $this->lineThresholds);

        $data = $this->validateWithFormRequest(
            UpdateCustomerPurchaseOrderDetailsRequest::class,
            $payload,
            'detailsForm.',
            ['file', 'line_thresholds'],
        );

        try {
            $amendment = $registry->updateDetails(
                $this->purchaseOrder,
                collect($data)->only(['po_number', 'invoicing_mode', 'invoicing_period', 'expiry_notice_days', 'notes'])->all(),
                $payload['line_thresholds'],
                $this->detailsFile,
                $data['reason'],
            );
        } catch (ValidationException $exception) {
            $this->rethrowWithPrefix($exception, 'detailsForm.', ['lineThresholds']);
        }

        if ($amendment === null) {
            $this->toastInfo('Nothing changed.');
            $this->closeModal();

            return;
        }

        $this->afterAmendment('Purchase order details updated.');
    }

    public function openFinalise(string $action = FinalisePurchaseOrderRequest::ACTION_CLOSE): void
    {
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_CLOSE);
        $this->resetErrorBag();
        $this->finaliseForm = ['action' => $action, 'reason' => ''];
        $this->activeModal = self::MODAL_FINALISE;
    }

    public function saveFinalise(CustomerPurchaseOrderRegistryService $registry): void
    {
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_CLOSE);
        $data = $this->validateWithFormRequest(FinalisePurchaseOrderRequest::class, $this->finaliseForm, 'finaliseForm.');

        try {
            if ($data['action'] === FinalisePurchaseOrderRequest::ACTION_CANCEL) {
                $registry->cancel($this->purchaseOrder, $data['reason']);
            } else {
                $registry->close($this->purchaseOrder, $data['reason']);
            }
        } catch (ValidationException $exception) {
            $this->rethrowWithPrefix($exception, 'finaliseForm.');
        }

        $this->afterAmendment($data['action'] === FinalisePurchaseOrderRequest::ACTION_CANCEL ? 'Purchase order cancelled.' : 'Purchase order closed.');
    }

    public function closeModal(): void
    {
        $this->activeModal = '';
        $this->detailsFile = null;
        $this->resetErrorBag();
    }

    public function loadMoreTimeline(): void
    {
        $this->timelineLimit += 30;
    }

    /**
     * @return EloquentCollection<int, CustomerPurchaseOrderAmendment>
     */
    public function getAmendmentsProperty(): EloquentCollection
    {
        return CustomerPurchaseOrderAmendment::query()
            ->with(['creator:id,name', 'line:id,line_no,description'])
            ->where('customer_purchase_order_id', $this->purchaseOrderId)
            ->latest()
            ->get();
    }

    /**
     * @return EloquentCollection<int, CustomerPurchaseOrderLedgerEntry>
     */
    public function getTimelineProperty(): EloquentCollection
    {
        return CustomerPurchaseOrderLedgerEntry::query()
            ->with(['creator:id,name', 'line:id,line_no,description'])
            ->where('customer_purchase_order_id', $this->purchaseOrderId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($this->timelineLimit)
            ->get();
    }

    public function getTimelineTotalProperty(): int
    {
        return CustomerPurchaseOrderLedgerEntry::query()
            ->where('customer_purchase_order_id', $this->purchaseOrderId)
            ->count();
    }

    /**
     * Labels and links for the enquiries, jobs and invoices referenced by the visible timeline.
     *
     * @return array{enquiries: Collection<string, SampleSubmissionRequest>, jobs: Collection<string, SampleHeader>, invoices: Collection<string, Invoice>}
     */
    public function getTimelineReferencesProperty(): array
    {
        $entries = $this->timeline;
        $ids = static fn (string $column): array => $entries->pluck($column)->filter()->unique()->values()->all();

        return [
            'enquiries' => SampleSubmissionRequest::query()
                ->with('submissionFormInstance.submissionForm')
                ->whereIn('id', $ids('enquiry_id'))
                ->get()
                ->keyBy(fn ($enquiry): string => (string) $enquiry->id),
            'jobs' => SampleHeader::query()
                ->whereIn('id', $ids('sample_header_id'))
                ->get(['id', 'batch_code'])
                ->keyBy(fn ($job): string => (string) $job->id),
            'invoices' => Invoice::query()
                ->whereIn('id', $ids('invoice_id'))
                ->get(['id', 'invoice_number'])
                ->keyBy(fn ($invoice): string => (string) $invoice->id),
        ];
    }

    /**
     * Enquiries drawing from this PO: blanket bindings plus the legacy one-to-one enquiry.
     *
     * @return EloquentCollection<int, SampleSubmissionRequest>
     */
    public function getEnquiriesProperty(): EloquentCollection
    {
        $po = $this->purchaseOrder;

        return SampleSubmissionRequest::query()
            ->with(['submissionFormInstance.submissionForm', 'batch'])
            ->where(function ($query) use ($po): void {
                $query->where('customer_purchase_order_id', $po->id);
                if (filled($po->enquiry_id)) {
                    $query->orWhere('id', $po->enquiry_id);
                }
            })
            ->latest()
            ->limit(100)
            ->get();
    }

    /**
     * @return Collection<int, SampleHeader>
     */
    public function getJobsProperty(): Collection
    {
        $po = $this->purchaseOrder;
        if ($po === null) {
            return collect();
        }

        $jobs = SampleHeader::query()
            ->withCount('samples')
            ->where('customer_purchase_order_id', $po->id)
            ->latest()
            ->limit(200)
            ->get();

        $enquiry = $po->enquiry;
        if ($enquiry !== null && ! $po->isBlanket()) {
            if ($enquiry->batch !== null) {
                $jobs->push($enquiry->batch->loadCount('samples'));
            }

            $quotationId = $po->quotation_header_id
                ?? $enquiry->accepted_quotation_header_id
                ?? $enquiry->current_quotation_header_id;

            if (filled($quotationId)) {
                $jobs = $jobs->merge(
                    SampleHeader::query()->withCount('samples')->where('quote_id', (string) $quotationId)->get()
                );
            }
        }

        return $jobs->unique('id')->values();
    }

    /**
     * @return Collection<int, SampleHeader>
     */
    public function getHeldJobsProperty(): Collection
    {
        return $this->jobs
            ->filter(fn (SampleHeader $job): bool => (string) $job->po_status === SampleHeaderPoStatus::AwaitingPo->value)
            ->values();
    }

    /**
     * Samples are only listed for single-enquiry POs; blanket POs can cover thousands.
     *
     * @return Collection<int, SampleDetails>
     */
    public function getSamplesProperty(): Collection
    {
        if ($this->purchaseOrder === null || $this->purchaseOrder->isBlanket()) {
            return collect();
        }

        $jobIds = $this->jobs->pluck('id')->all();
        if ($jobIds === []) {
            return collect();
        }

        return SampleDetails::query()->whereIn('sample_header_id', $jobIds)->limit(500)->get();
    }

    /**
     * Source-quotation items not yet on the PO, offered when adding a line.
     *
     * @return EloquentCollection<int, QuotationDetails>
     */
    public function getQuotationDetailOptionsProperty(): EloquentCollection
    {
        $po = $this->purchaseOrder;
        if ($po === null || ! filled($po->quotation_header_id)) {
            return new EloquentCollection;
        }

        $used = $po->lines->pluck('quotation_detail_id')->filter()->all();

        return QuotationDetails::query()
            ->where('quotation_header_id', $po->quotation_header_id)
            ->whereNotIn('id', $used)
            ->orderBy('item_name')
            ->get(['id', 'item_name', 'description', 'quantity', 'unit_price', 'tax']);
    }

    /**
     * @return array{ordered: int, reserved: int, committed: int, invoiced: int, remaining: int}
     */
    public function getTotalsProperty(): array
    {
        $lines = $this->purchaseOrder?->lines ?? collect();

        return [
            'ordered' => (int) $lines->sum('ordered_qty'),
            'reserved' => (int) $lines->sum('reserved_qty'),
            'committed' => (int) $lines->sum('committed_qty'),
            'invoiced' => (int) $lines->sum('invoiced_qty'),
            'remaining' => (int) $lines->sum('remaining_qty'),
        ];
    }

    public function render()
    {
        return view('livewire.billing.customer-purchase-order-show', [
            'jobs' => $this->jobs,
            'samples' => $this->samples,
            'totals' => $this->totals,
            'canAmend' => (bool) auth()->user()?->can(CustomerPurchaseOrder::PERMISSION_AMEND),
            'canClose' => (bool) auth()->user()?->can(CustomerPurchaseOrder::PERMISSION_CLOSE),
            'invoicingModes' => PurchaseOrderInvoicingMode::cases(),
            'invoicingPeriods' => PurchaseOrderInvoicingPeriod::cases(),
        ]);
    }

    private function afterAmendment(string $message): void
    {
        $this->closeModal();
        $this->loadPurchaseOrder();
        $this->toastSuccess($message);
    }

    private function authorizeAmend(): void
    {
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_AMEND);
    }

    private function findLine(string $lineId): CustomerPurchaseOrderLine
    {
        return CustomerPurchaseOrderLine::query()
            ->where('customer_purchase_order_id', $this->purchaseOrderId)
            ->findOrFail($lineId);
    }

    /**
     * @return array<string, mixed>
     */
    private function blankLine(): array
    {
        return [
            'quotation_detail_id' => null,
            'description' => '',
            'sample_type_id' => null,
            'sample_type_name' => null,
            'analysis_type_ids' => [],
            'is_package' => false,
            'ordered_qty' => 1,
            'unit_price_gross' => 0,
            'quoted_unit_price_gross' => null,
            'notify_remaining_qty' => null,
        ];
    }
}
