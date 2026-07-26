<?php

namespace App\Livewire\Billing;

use App\QuotationHeader;
use App\QuotationDetails;
use App\QuotationHeaderView;
use App\InvoicableItem;
use App\AnalysisType;
use App\SampleType;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\ModulePreConfigs;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class QuotationManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties
    public $search = '';
    public $customerFilter = '';
    public $stageFilter = '';
    public $quotationTypeFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    // View properties
    public $selectedQuotationId = null;
    public $showQuotationDetails = false;

    // Modal properties
    public $showCreateModal = false;
    public $quotationForm = [];
    
    // Message properties
    public $message = '';
    public $messageType = 'success';

    // Dropdown states
    public $showCustomerDropdown = false;
    public $customerSearch = '';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->resetQuotationForm();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCustomerFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStageFilter(): void
    {
        $this->resetPage();
    }

    public function getQuotationsProperty()
    {
        $query = QuotationHeaderView::with('preparedBy')->where('is_draft', 0);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('quote_number', 'like', "%{$this->search}%");
            });
        }

        if ($this->customerFilter) {
            $query->where('crm_customer_id', $this->customerFilter);
        }

        if ($this->stageFilter) {
            $query->where('status', $this->stageFilter);
        }

        if ($this->quotationTypeFilter) {
            $query->where('quotation_type', $this->quotationTypeFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function getDraftsProperty()
    {
        return QuotationHeaderView::with('preparedBy')->where('is_draft', 1)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getCustomersProperty()
    {
        return CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    public function getQuotationStagesProperty()
    {
        return [
            'Quote In Preparation',
            'Quote Complete'
        ];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->customerFilter = '';
        $this->stageFilter = '';
        $this->quotationTypeFilter = '';
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function viewQuotation($quotationId): void
    {
        $this->selectedQuotationId = $quotationId;
        $this->showQuotationDetails = true;
    }

    public function closeQuotationDetails(): void
    {
        $this->selectedQuotationId = null;
        $this->showQuotationDetails = false;
    }

    public function getSelectedQuotationProperty()
    {
        if ($this->selectedQuotationId) {
            return QuotationHeader::with([
                'customer',
                'contact',
                'details.invoicableItem',
                'details.sampletype'
            ])->find($this->selectedQuotationId);
        }
        return null;
    }

    public function showCreateQuotationModal(): void
    {
        $this->resetQuotationForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetQuotationForm();
    }

    public function resetQuotationForm(): void
    {
        $this->quotationForm = [
            'customer_id' => null,
            'contact_id' => null,
            'quotation_type' => 'Analysis',
            'quote_date' => now()->format('Y-m-d'),
            'expiring_date' => now()->addDays(30)->format('Y-m-d'),
        ];
    }

    public function deleteQuotation($quotationId): void
    {
        try {
            DB::beginTransaction();
            
            $quotation = QuotationHeader::findOrFail($quotationId);
            $quotation->details()->delete();
            $quotation->delete();
            
            DB::commit();
            $this->showMessage('Quotation deleted successfully!', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showMessage('Error deleting quotation: ' . $e->getMessage(), 'danger');
        }
    }

    public function cloneQuotation($quotationId): void
    {
        // Redirect to controller method
        return redirect()->route('clone_quotation', ['id' => $quotationId]);
    }

    public function convertToBatch($quotationId): void
    {
        // This would call the existing convertQuoteToBatch method
        $this->showMessage('Convert to batch functionality to be implemented', 'info');
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
        return view('livewire.billing.quotation-manager', [
            'quotations' => $this->quotations,
            'drafts' => $this->drafts,
            'customers' => $this->customers,
            'quotationStages' => $this->quotationStages,
            'selectedQuotation' => $this->selectedQuotation,
        ]);
    }
}
