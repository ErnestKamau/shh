<?php

namespace App\Livewire\Billing;

use App\Enums\Commercial\PurchaseOrderInvoicingMode;
use App\Enums\Commercial\PurchaseOrderInvoicingPeriod;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Http\Requests\Commercial\StoreCustomerPurchaseOrderRequest;
use App\Livewire\Concerns\ValidatesWithFormRequest;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\CRM\CRMCustomer;
use App\Models\Currency;
use App\QuotationHeader;
use App\Services\Commercial\CustomerPurchaseOrderRegistryService;
use App\Services\Commercial\QuotationPurchaseOrderLineMapper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Registry wizard for blanket customer POs: customer and source quotation, lines, terms, review.
 */
class CustomerPurchaseOrderCreate extends Component
{
    use ValidatesWithFormRequest;
    use WithFileUploads;
    use WithToastNotifications;

    public const STEP_CUSTOMER = 1;

    public const STEP_LINES = 2;

    public const STEP_TERMS = 3;

    public const STEP_REVIEW = 4;

    public int $step = self::STEP_CUSTOMER;

    /**
     * @var array{
     *     po_number: string,
     *     customer_id: string,
     *     quotation_header_id: string,
     *     currency_id: string,
     *     valid_from: string,
     *     valid_to: string,
     *     invoicing_mode: string,
     *     invoicing_period: string,
     *     expiry_notice_days: int|string,
     *     notes: string
     * }
     */
    public array $form = [
        'po_number' => '',
        'customer_id' => '',
        'quotation_header_id' => '',
        'currency_id' => '',
        'valid_from' => '',
        'valid_to' => '',
        'invoicing_mode' => 'per_job',
        'invoicing_period' => '',
        'expiry_notice_days' => 30,
        'notes' => '',
    ];

    /**
     * @var list<array<string, mixed>>
     */
    public array $lines = [];

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $file = null;

    public string $customerSearch = '';

    public function mount(?string $customerId = null): void
    {
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_CREATE);

        $this->form['valid_from'] = now()->toDateString();
        $this->form['valid_to'] = now()->addYear()->subDay()->toDateString();
        $this->form['expiry_notice_days'] = (int) config('purchase_orders.default_expiry_notice_days', 30);

        $customerId ??= request()->query('customer');
        if (is_string($customerId) && Str::isUuid($customerId) && CRMCustomer::query()->whereKey($customerId)->exists()) {
            $this->selectCustomer($customerId);
        }
    }

    public function selectCustomer(string $customerId): void
    {
        $customer = CRMCustomer::query()->find($customerId, ['id', 'name']);
        if ($customer === null) {
            return;
        }

        $this->form['customer_id'] = (string) $customer->id;
        $this->customerSearch = (string) $customer->name;
        $this->form['quotation_header_id'] = '';
        $this->lines = [];
        $this->resetErrorBag('form.customer_id');
    }

    public function clearCustomer(): void
    {
        $this->form['customer_id'] = '';
        $this->form['quotation_header_id'] = '';
        $this->customerSearch = '';
        $this->lines = [];
    }

    public function updatedFormQuotationHeaderId(string $quotationId): void
    {
        if ($quotationId === '') {
            return;
        }

        $quotation = QuotationHeader::query()
            ->where('crm_customer_id', $this->form['customer_id'])
            ->find($quotationId);

        if ($quotation === null) {
            $this->form['quotation_header_id'] = '';

            return;
        }

        $this->lines = app(QuotationPurchaseOrderLineMapper::class)->draftsForQuotation($quotation);
        if (filled($quotation->currency_id)) {
            $this->form['currency_id'] = (string) $quotation->currency_id;
        }
        $this->resetErrorBag('lines');
    }

    public function addBlankLine(): void
    {
        $this->lines[] = [
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

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->resetErrorBag();
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = max(self::STEP_CUSTOMER, $step);
        }
    }

    public function nextStep(): void
    {
        $this->validateStep($this->step);
        $this->step = min(self::STEP_REVIEW, $this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = max(self::STEP_CUSTOMER, $this->step - 1);
    }

    public function save(CustomerPurchaseOrderRegistryService $registry): void
    {
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_CREATE);

        foreach ([self::STEP_CUSTOMER, self::STEP_LINES, self::STEP_TERMS] as $step) {
            try {
                $this->validateStep($step);
            } catch (ValidationException $exception) {
                $this->step = $step;

                throw $exception;
            }
        }

        $validated = $this->validateWithFormRequest(StoreCustomerPurchaseOrderRequest::class, $this->validationData(), 'form.', ['lines', 'file']);

        try {
            $po = $registry->createBlanket(
                collect($validated)->except(['lines', 'file'])->all(),
                $this->linesForService(),
                $this->file,
            );
        } catch (ValidationException $exception) {
            $this->step = isset($exception->errors()['po_number']) || isset($exception->errors()['quotation_header_id'])
                ? self::STEP_CUSTOMER
                : self::STEP_LINES;
            $this->rethrowWithPrefix($exception, 'form.', ['lines', 'file']);
        }

        session()->flash('success', 'Purchase order '.$po->po_number.' created.');

        $this->redirectRoute('billing.customer-purchase-orders.show', ['id' => $po->id]);
    }

    /**
     * @return Collection<int, CRMCustomer>
     */
    public function getCustomerOptionsProperty(): Collection
    {
        $term = trim($this->customerSearch);
        if ($this->form['customer_id'] !== '' || mb_strlen($term) < 2) {
            return new Collection;
        }

        return CRMCustomer::query()
            ->where('name', 'ilike', '%'.$term.'%')
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name']);
    }

    public function getSelectedCustomerProperty(): ?CRMCustomer
    {
        return $this->form['customer_id'] !== ''
            ? CRMCustomer::query()->find($this->form['customer_id'], ['id', 'name'])
            : null;
    }

    /**
     * @return Collection<int, QuotationHeader>
     */
    public function getQuotationOptionsProperty(): Collection
    {
        if ($this->form['customer_id'] === '') {
            return new Collection;
        }

        return app(QuotationPurchaseOrderLineMapper::class)->quotationsForCustomer($this->form['customer_id']);
    }

    /**
     * Customer POs already using this number (legacy per-enquiry records or other blanket POs).
     *
     * @return Collection<int, CustomerPurchaseOrder>
     */
    public function getMatchingPoNumbersProperty(): Collection
    {
        $number = trim((string) $this->form['po_number']);
        if ($number === '' || $this->form['customer_id'] === '') {
            return new Collection;
        }

        return CustomerPurchaseOrder::query()
            ->where('customer_id', $this->form['customer_id'])
            ->whereRaw('LOWER(po_number) = ?', [Str::lower($number)])
            ->where('status', '!=', PurchaseOrderStatus::Cancelled->value)
            ->limit(5)
            ->get(['id', 'po_number', 'po_type', 'status', 'recorded_at']);
    }

    /**
     * Line indexes whose price differs from the source quotation's gross price.
     *
     * @return array<int, float>
     */
    public function getPriceWarningsProperty(): array
    {
        $warnings = [];
        foreach ($this->lines as $index => $line) {
            $quoted = $line['quoted_unit_price_gross'] ?? null;
            if ($quoted === null || ! is_numeric($line['unit_price_gross'] ?? null)) {
                continue;
            }

            if (round((float) $line['unit_price_gross'], 2) !== round((float) $quoted, 2)) {
                $warnings[$index] = (float) $quoted;
            }
        }

        return $warnings;
    }

    /**
     * @return array{quantity: int, value: float}
     */
    public function getTotalsProperty(): array
    {
        $quantity = 0;
        $value = 0.0;
        foreach ($this->lines as $line) {
            $qty = (int) ($line['ordered_qty'] ?? 0);
            $quantity += $qty;
            $value += $qty * (float) ($line['unit_price_gross'] ?? 0);
        }

        return ['quantity' => $quantity, 'value' => round($value, 2)];
    }

    public function getCurrencyCodeProperty(): ?string
    {
        if ($this->form['currency_id'] === '') {
            return null;
        }

        return Currency::query()->whereKey($this->form['currency_id'])->value('code');
    }

    public function render()
    {
        return view('livewire.billing.customer-purchase-order-create', [
            'invoicingModes' => PurchaseOrderInvoicingMode::cases(),
            'invoicingPeriods' => PurchaseOrderInvoicingPeriod::cases(),
            'currencies' => Currency::query()->orderBy('code')->get(['id', 'code', 'description']),
        ]);
    }

    private function validateStep(int $step): void
    {
        $keys = match ($step) {
            self::STEP_CUSTOMER => ['po_number', 'customer_id', 'quotation_header_id'],
            self::STEP_LINES => ['lines'],
            self::STEP_TERMS => ['currency_id', 'valid_from', 'valid_to', 'invoicing_mode', 'invoicing_period', 'expiry_notice_days', 'notes', 'file'],
            default => null,
        };

        if ($keys === null) {
            return;
        }

        $this->validateWithFormRequest(StoreCustomerPurchaseOrderRequest::class, $this->validationData(), 'form.', ['lines', 'file'], $keys);

        if ($step === self::STEP_LINES) {
            foreach ($this->lines as $index => $line) {
                $threshold = $line['notify_remaining_qty'] ?? null;
                if ($threshold !== null && $threshold !== '' && (int) $threshold >= (int) $line['ordered_qty']) {
                    throw ValidationException::withMessages([
                        "lines.{$index}.notify_remaining_qty" => 'The alert level must be below the ordered quantity.',
                    ]);
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validationData(): array
    {
        $data = $this->form;
        foreach (['quotation_header_id', 'currency_id', 'invoicing_period', 'notes'] as $optional) {
            if (($data[$optional] ?? '') === '') {
                $data[$optional] = null;
            }
        }

        $data['lines'] = array_map(static function (array $line): array {
            $line['notify_remaining_qty'] = ($line['notify_remaining_qty'] ?? '') === '' ? null : $line['notify_remaining_qty'];
            $line['analysis_type_ids'] = array_values((array) ($line['analysis_type_ids'] ?? []));

            return $line;
        }, $this->lines);
        $data['file'] = $this->file;

        return $data;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function linesForService(): array
    {
        return array_map(static fn (array $line): array => [
            'description' => (string) $line['description'],
            'ordered_qty' => (int) $line['ordered_qty'],
            'unit_price_gross' => (float) $line['unit_price_gross'],
            'sample_type_id' => $line['sample_type_id'] ?? null,
            'analysis_type_ids' => array_values((array) ($line['analysis_type_ids'] ?? [])),
            'is_package' => (bool) ($line['is_package'] ?? false),
            'quotation_detail_id' => $line['quotation_detail_id'] ?? null,
            'notify_remaining_qty' => ($line['notify_remaining_qty'] ?? '') === '' ? null : (int) $line['notify_remaining_qty'],
        ], array_values($this->lines));
    }
}
