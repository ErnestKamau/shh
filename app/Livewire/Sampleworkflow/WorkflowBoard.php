<?php

namespace App\Livewire\Sampleworkflow;

use App\InventorySubCategories;
use App\Models\CRM\CRMCustomer;
use App\SampleAnalysisStage;
use App\SampleDate;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use App\Models\System\SystemConfiguration;
use App\User;
use Illuminate\Support\Collection;
use Livewire\Component;

class WorkflowBoard extends Component
{
    /**
     * Current workflow status tab.
     */
    public string $status = '';

    /**
     * Initial filters passed from the controller (query string).
     */
    public array $initialFilters = [];

    /**
     * Filters for the “All Samples” tab.
     */
    public array $allFilter = [];

    /**
     * Filters for the "Finished Sample" tab.
     */
    public array $finishedFilter = [];

    /**
     * Livewire search and filters for all statuses.
     */
    public string $search = '';
    public ?string $receiptDateFrom = null;
    public ?string $receiptDateTo = null;
    public ?int $customerFilter = null;
    public ?int $sampleTypeFilter = null;
    
    /**
     * Customer dropdown state.
     */
    public bool $showCustomerDropdown = false;
    public string $customerSearch = '';
    public int $customerPage = 1;
    public int $customerPerPage = 50;
    
    /**
     * Track if component has finished initial load.
     */
    public bool $initialLoadComplete = false;

    /**
     * Shared datasets required by the legacy modals/forms.
     */
    public Collection $labsections;
    public Collection $analysts;
    public Collection $users;
    public Collection $zohoItems;
    public Collection $clients;
    public Collection $sampletypes;
    public Collection $customers;

    /**
     * Mount the component with the requested status and filters.
     */
    public function mount(string $status = null, array $initialFilters = []): void
    {
        $workflowStages = getSampleWorflowStages();
        $this->status = $status ?: ($workflowStages[0] ?? 'Samples Reception');
        $this->initialFilters = $initialFilters;

        $this->allFilter = $this->defaultAllSamplesFilter();
        $this->finishedFilter = $this->defaultFinishedSamplesFilter();

        $this->hydrateFiltersFromRequest();
        $this->loadReferenceData();
        
        // Mark initial load as complete after a short delay
        $this->dispatch('initial-load-complete');
    }
    
    /**
     * Mark initial load as complete.
     */
    public function markInitialLoadComplete(): void
    {
        $this->initialLoadComplete = true;
    }

    /**
     * Load shared datasets used across the workflow UI.
     */
    protected function loadReferenceData(): void
    {
        $this->labsections = SampleAnalysisStage::where('active', 1)->orderBy('name')->get();
        $this->zohoItems = InventorySubCategories::orderBy('name')->get();

        $analystRoleId = SystemConfiguration::where('key', 'analyst_role_id')->value('value');
        $this->analysts = User::query()
            ->orderBy('users.name')
            ->join('user_roles as ur', 'ur.user_id', '=', 'users.id')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->when($analystRoleId, fn ($query) => $query->where('r.id', $analystRoleId))
            ->where('users.active', 1)
            ->where('users.is_support_staff', 0)
            ->select('users.*')
            ->get();

        $this->users = User::where('is_client', 0)
            ->where('supplier_id', 0)
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        $this->clients = CRMCustomer::where('active', 1)->orderBy('name')->get();
        $this->sampletypes = SampleType::where('active', 1)->orderBy('name')->get();
        $this->customers = CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    /**
     * Ensure filters respect query-string defaults.
     */
    protected function hydrateFiltersFromRequest(): void
    {
        foreach ($this->allFilter as $key => $value) {
            if (array_key_exists($key, $this->initialFilters)) {
                $this->allFilter[$key] = $this->trimFilterValue($this->initialFilters[$key]);
            }
        }

        foreach ($this->finishedFilter as $key => $value) {
            if (array_key_exists($key, $this->initialFilters)) {
                $this->finishedFilter[$key] = $this->trimFilterValue($this->initialFilters[$key]);
            }
        }

        if ($this->finishedFilterHasValues()) {
            $this->finishedFilter['has_filter'] = 1;
        }
    }

    protected function trimFilterValue($value)
    {
        return is_string($value) ? trim($value) : $value;
    }

    protected function finishedFilterHasValues(): bool
    {
        return (bool) (
            $this->finishedFilter['receipt_from'] ||
            $this->finishedFilter['receipt_to'] ||
            $this->finishedFilter['customer_id'] ||
            $this->finishedFilter['sample_codes']
        );
    }

    protected function defaultAllSamplesFilter(): array
    {
        return [
            'customer_id' => 'All',
            'sample_type_id' => 'All',
            'receipt_date_from' => '',
            'receipt_date_to' => '',
            'tat_date_from' => '',
            'tat_date_to' => '',
            'schedule_sent' => 'All',
        ];
    }

    protected function defaultFinishedSamplesFilter(): array
    {
        return [
            'receipt_from' => '',
            'receipt_to' => '',
            'customer_id' => '',
            'sample_codes' => '',
            'has_filter' => 0,
        ];
    }

    /**
     * Computed list of batches for the active status.
     */
    public function getBatchesProperty()
    {
        return match ($this->status) {
            'Finished Sample' => $this->finishedFilter['has_filter'] ? $this->getFinishedSampleBatches() : collect([]),
            'All Samples' => $this->getAllSampleBatches(),
            default => $this->getStatusBatches(),
        };
    }

    protected function baseBatchQuery()
    {
        return SampleHeader::with([
            'samples',
            'client',
            'sample_type',
            'invoice',
        ])->where('isactive', 1);
    }

    protected function getAllSampleBatches()
    {
        $query = $this->baseBatchQuery()->orderBy('receipt_date', 'desc');

        if ($this->allFilter['customer_id'] && $this->allFilter['customer_id'] !== 'All') {
            $query->where('crm_customer_id', $this->allFilter['customer_id']);
        }

        if ($this->allFilter['sample_type_id'] && $this->allFilter['sample_type_id'] !== 'All') {
            $query->where('sample_type_id', $this->allFilter['sample_type_id']);
        }

        if ($this->allFilter['receipt_date_from']) {
            $query->whereDate('receipt_date', '>=', $this->allFilter['receipt_date_from']);
        }

        if ($this->allFilter['receipt_date_to']) {
            $query->whereDate('receipt_date', '<=', $this->allFilter['receipt_date_to']);
        }

        if ($this->allFilter['tat_date_from']) {
            $tatQuery = SampleDate::query()
                ->where('name', 'Target Date')
                ->whereDate('date', '>=', $this->allFilter['tat_date_from']);

            if ($this->allFilter['tat_date_to']) {
                $tatQuery->whereDate('date', '<=', $this->allFilter['tat_date_to']);
            }

            $tatBatchIds = $tatQuery->pluck('sample_header_id')->toArray();
            $query->whereIn('id', $tatBatchIds ?: [0]);
        } elseif ($this->allFilter['tat_date_to']) {
            $tatBatchIds = SampleDate::query()
                ->where('name', 'Target Date')
                ->whereDate('date', '<=', $this->allFilter['tat_date_to'])
                ->pluck('sample_header_id')
                ->toArray();
            $query->whereIn('id', $tatBatchIds ?: [0]);
        }

        if ($this->allFilter['schedule_sent'] === 'sent') {
            $query->where('schedule_analysis_sent', 1);
        } elseif ($this->allFilter['schedule_sent'] === 'not_sent') {
            $query->where('schedule_analysis_sent', 0);
        }

        return $query->get();
    }

    protected function getFinishedSampleBatches()
    {
        $query = $this->baseBatchQuery()
            ->where('status', 'Finished Sample')
            ->orderBy('receipt_date', 'desc');

        if ($this->finishedFilter['sample_codes']) {
            $codes = $this->normalizeSampleCodes($this->finishedFilter['sample_codes']);
            if (!empty($codes)) {
                $sampleBatchIds = SampleDetails::whereIn('sample_code', $codes)
                    ->pluck('sample_header_id')
                    ->toArray();
                $query->whereIn('id', $sampleBatchIds ?: [0]);
            }
        }

        if ($this->finishedFilter['receipt_from']) {
            $query->whereDate('receipt_date', '>=', $this->finishedFilter['receipt_from']);
        }

        if ($this->finishedFilter['receipt_to']) {
            $query->whereDate('receipt_date', '<=', $this->finishedFilter['receipt_to']);
        }

        if ($this->finishedFilter['customer_id']) {
            $query->where('crm_customer_id', $this->finishedFilter['customer_id']);
        }

        return $query->get();
    }

    protected function getStatusBatches()
    {
        $query = $this->baseBatchQuery()->orderBy('receipt_date', 'desc');

        if ($this->status === 'Schedule of Analysis') {
            $query->where('status', 'Samples In Lab')
                ->where('schedule_sent', '<', 1);
        } else {
            $query->where(function ($inner) {
                $inner->where('status', $this->status)
                    ->orWhere('prelim_batch_status', $this->status);
            });
        }

        // Apply search filter (batch_code and sample_code)
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('batch_code', 'like', $searchTerm)
                    ->orWhereHas('samples', function ($sampleQuery) use ($searchTerm) {
                        $sampleQuery->where('sample_code', 'like', $searchTerm);
                    });
            });
        }

        // Apply receipt date filters
        if ($this->receiptDateFrom) {
            $query->whereDate('receipt_date', '>=', $this->receiptDateFrom);
        }

        if ($this->receiptDateTo) {
            $query->whereDate('receipt_date', '<=', $this->receiptDateTo);
        }

        // Apply customer filter
        if ($this->customerFilter) {
            $query->where('crm_customer_id', $this->customerFilter);
        }

        // Apply sample type filter
        if ($this->sampleTypeFilter) {
            $query->where('sample_type_id', $this->sampleTypeFilter);
        }

        return $query->get();
    }

    protected function normalizeSampleCodes(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($code) => trim($code))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->receiptDateFrom = null;
        $this->receiptDateTo = null;
        $this->customerFilter = null;
        $this->sampleTypeFilter = null;
        $this->customerSearch = '';
        $this->showCustomerDropdown = false;
        $this->customerPage = 1;
    }
    
    /**
     * Get filtered customers for dropdown with pagination.
     * Returns all customers up to the current page (accumulated).
     */
    public function getFilteredCustomersProperty()
    {
        $customers = $this->clients;
        
        // Apply search filter if provided
        if (!empty($this->customerSearch)) {
            $customers = $customers->filter(function($customer) {
                return stripos($customer->name ?? '', $this->customerSearch) !== false;
            });
        }
        
        // Return all customers up to current page (for infinite scroll accumulation)
        $totalToShow = $this->customerPage * $this->customerPerPage;
        return $customers->take($totalToShow);
    }
    
    /**
     * Check if there are more customers to load.
     */
    public function getHasMoreCustomersProperty()
    {
        $customers = $this->clients;
        
        if (!empty($this->customerSearch)) {
            $customers = $customers->filter(function($customer) {
                return stripos($customer->name ?? '', $this->customerSearch) !== false;
            });
        }
        
        $totalLoaded = $this->customerPage * $this->customerPerPage;
        return $customers->count() > $totalLoaded;
    }
    
    /**
     * Load more customers (for infinite scroll).
     */
    public function loadMoreCustomers(): void
    {
        if ($this->hasMoreCustomers) {
            $this->customerPage++;
        }
    }
    
    /**
     * Get selected customer name.
     */
    public function getSelectedCustomerProperty()
    {
        if (!$this->customerFilter) {
            return null;
        }
        
        return $this->clients->firstWhere('id', $this->customerFilter);
    }
    
    /**
     * Select a customer from dropdown.
     */
    public function selectCustomer(int $customerId): void
    {
        $this->customerFilter = $customerId;
        $this->customerSearch = ''; // Clear search input, only show badge
        $this->showCustomerDropdown = false;
        $this->customerPage = 1; // Reset pagination
    }
    
    /**
     * Reset customer pagination when search changes.
     */
    public function updatedCustomerSearch(): void
    {
        $this->customerPage = 1; // Reset to first page when searching
    }

    public function render()
    {
        return view('livewire.sampleworkflow.workflow-board', [
            'batches' => $this->batches,
            'labsections' => $this->labsections,
            'analysts' => $this->analysts,
            'users' => $this->users,
            'zoho_items' => $this->zohoItems,
            'clients' => $this->clients,
            'sampletypes' => $this->sampletypes,
            'customers' => $this->customers,
        ]);
    }
}
