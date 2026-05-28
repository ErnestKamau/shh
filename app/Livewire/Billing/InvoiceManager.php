<?php

namespace App\Livewire\Billing;

use App\Invoice;
use App\Models\CRM\CRMCustomer;
use App\ModulePreConfigs;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $customerFilter = '';

    public string $currencyFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $dateFilter = 'created_at';

    public string $paymentStatusTab = 'all';

    public int $perPage = 25;

    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];

    public string $message = '';

    public string $messageType = 'success';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCurrencyFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function setPaymentStatusTab(string $tab): void
    {
        if (! in_array($tab, ['all', 'unpaid', 'partially_paid', 'completely_paid'], true)) {
            return;
        }

        $this->paymentStatusTab = $tab;
        $this->resetPage();
    }

    public function getInvoicesProperty()
    {
        return $this->filteredInvoiceQuery()
            ->with(['crmCustomer', 'currencyinfo', 'pricelist.currency'])
            ->select('customer_invoice.*')
            ->selectRaw($this->lineItemsTotalSubquery().' as line_items_total')
            ->selectRaw($this->lineItemsTaxSubquery().' as line_items_tax')
            ->selectRaw($this->paidTotalSubquery().' as paid_total')
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    /** @return array<string, int> */
    public function getPaymentTabCountsProperty(): array
    {
        return [
            'all' => $this->filteredInvoiceQuery()->count(),
            'unpaid' => $this->filteredInvoiceQuery('unpaid')->count(),
            'partially_paid' => $this->filteredInvoiceQuery('partially_paid')->count(),
            'completely_paid' => $this->filteredInvoiceQuery('completely_paid')->count(),
        ];
    }

    public function getCustomersProperty()
    {
        return CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    public function getCurrenciesProperty()
    {
        return ModulePreConfigs::where('type', 'Currency')->orderBy('name')->get();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->customerFilter = '';
        $this->currencyFilter = '';
        $this->paymentStatusTab = 'all';
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->dateFilter = 'created_at';
        $this->resetPage();
    }

    public function generateInvoiceFromBatch($batchId): void
    {
        try {
            $controller = new \App\Http\Controllers\Invoice\InvoiceController;
            $controller->generateinvoice($batchId);

            $this->showMessage('Invoice generated successfully!', 'success');
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            $this->showMessage('Error generating invoice: '.$e->getMessage(), 'danger');
        }
    }

    public function printInvoice($invoiceId)
    {
        return $this->redirect(route('print-invoice', ['id' => $invoiceId]));
    }

    public function emailInvoice($invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        if (! $invoice->upload_url) {
            $this->showMessage('Please upload the invoice PDF first.', 'danger');

            return;
        }

        $this->showMessage('Email functionality to be implemented', 'info');
    }

    public function showMessage(string $message, string $type = 'success'): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    protected function filteredInvoiceQuery(?string $paymentTab = null): Builder
    {
        $query = Invoice::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                    ->orWhere('reference_number', 'like', "%{$this->search}%");
            });
        }

        if ($this->customerFilter) {
            $query->where('customer_id', $this->customerFilter);
        }

        if ($this->currencyFilter) {
            $query->where('currency_id', $this->currencyFilter);
        }

        if ($this->startDate && $this->endDate) {
            if ($this->dateFilter === 'due_date') {
                $query->whereBetween('due_date', [$this->startDate, $this->endDate]);
            } else {
                $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
            }
        }

        $tab = $paymentTab ?? $this->paymentStatusTab;

        if ($tab !== 'all') {
            $this->applyPaymentStatusFilter($query, $tab);
        }

        return $query;
    }

    protected function lineItemsTotalSubquery(): string
    {
        return '(SELECT COALESCE(SUM(CAST(invoice_details.total AS NUMERIC)), 0) FROM invoice_details WHERE invoice_details.invoice_id = customer_invoice.id)';
    }

    protected function lineItemsTaxSubquery(): string
    {
        return '(SELECT COALESCE(SUM(CAST(invoice_details.tax_amount AS NUMERIC)), 0) FROM invoice_details WHERE invoice_details.invoice_id = customer_invoice.id)';
    }

    protected function paidTotalSubquery(): string
    {
        return "(SELECT COALESCE(SUM(CAST(NULLIF(invoice_payment_details.amount, '') AS NUMERIC)), 0) FROM invoice_payment_details WHERE invoice_payment_details.invoice_id = customer_invoice.id AND invoice_payment_details.is_delete = false)";
    }

    protected function applyPaymentStatusFilter(Builder $query, string $tab): void
    {
        $invoiceTotalSql = $this->lineItemsTotalSubquery();
        $paidTotalSql = $this->paidTotalSubquery();

        match ($tab) {
            'unpaid' => $query->whereRaw("{$paidTotalSql} <= 0"),
            'partially_paid' => $query->whereRaw("{$paidTotalSql} > 0")
                ->whereRaw("{$paidTotalSql} < {$invoiceTotalSql}"),
            'completely_paid' => $query->whereRaw("{$invoiceTotalSql} > 0")
                ->whereRaw("{$paidTotalSql} >= {$invoiceTotalSql}"),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.billing.invoice-manager', [
            'invoices' => $this->invoices,
            'customers' => $this->customers,
            'currencies' => $this->currencies,
            'paymentTabCounts' => $this->paymentTabCounts,
        ]);
    }
}
