<?php

namespace App\Livewire\Billing;

use App\Invoice;
use App\InvoiceDetails;
use App\InvoicableItem;
use App\Models\CRM\CRMCustomer;
use App\ModulePreConfigs;
use App\SampleHeader;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties
    public $search = '';
    public $customerFilter = '';
    public $currencyFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $dateFilter = 'created_at'; // created_at or due_date
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    // View properties
    public $selectedInvoiceId = null;
    public $showInvoiceDetails = false;

    // Message properties
    public $message = '';
    public $messageType = 'success';

    public function mount(): void
    {
        // Set default date range to current month
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

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function getInvoicesProperty()
    {
        $query = Invoice::with(['crmCustomer', 'currencyinfo', 'details']);

        if ($this->search) {
            $query->where(function($q) {
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

        // Date filtering
        if ($this->startDate && $this->endDate) {
            if ($this->dateFilter === 'due_date') {
                $query->whereBetween('due_date', [$this->startDate, $this->endDate]);
            } else {
                $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
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
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->dateFilter = 'created_at';
        $this->resetPage();
    }

    public function viewInvoice($invoiceId): void
    {
        $this->selectedInvoiceId = $invoiceId;
        $this->showInvoiceDetails = true;
    }

    public function closeInvoiceDetails(): void
    {
        $this->selectedInvoiceId = null;
        $this->showInvoiceDetails = false;
    }

    public function getSelectedInvoiceProperty()
    {
        if ($this->selectedInvoiceId) {
            return Invoice::with(['crmCustomer', 'currencyinfo', 'details.invoicableItem', 'details.analysisType'])
                ->find($this->selectedInvoiceId);
        }
        return null;
    }

    public function generateInvoiceFromBatch($batchId): void
    {
        try {
            // Call the existing controller method
            $controller = new \App\Http\Controllers\Invoice\InvoiceController();
            $response = $controller->generateinvoice($batchId);
            
            $this->showMessage('Invoice generated successfully!', 'success');
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            $this->showMessage('Error generating invoice: ' . $e->getMessage(), 'danger');
        }
    }

    public function printInvoice($invoiceId)
    {
        // Redirect to print route
        return $this->redirect(route('print-invoice', ['id' => $invoiceId]));
    }

    public function emailInvoice($invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Check if invoice has upload URL
        if (!$invoice->upload_url) {
            $this->showMessage('Please upload the invoice PDF first.', 'danger');
            return;
        }

        // Email logic would go here
        $this->showMessage('Email functionality to be implemented', 'info');
    }

    public function showMessage($message, $type = 'success'): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.billing.invoice-manager', [
            'invoices' => $this->invoices,
            'customers' => $this->customers,
            'currencies' => $this->currencies,
            'selectedInvoice' => $this->selectedInvoice,
        ]);
    }
}
