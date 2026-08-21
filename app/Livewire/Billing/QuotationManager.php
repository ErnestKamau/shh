<?php

namespace App\Livewire\Billing;

use App\QuotationHeader;
use App\QuotationHeaderView;
use App\SampleAnalysisStage;
use App\Models\CRM\CRMCustomer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $embedded = false;

    public bool $filtersOpen = false;

    public string $search = '';

    public string $customerFilter = '';

    public string $stageFilter = '';

    public string $quotationTypeFilter = '';

    public string $labSectionFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 25;

    /** @var list<int> */
    public array $perPageOptions = [10, 25, 50, 100];

    public $selectedQuotationId = null;

    public bool $showQuotationDetails = false;

    public bool $showCreateModal = false;

    /** @var array<string, mixed> */
    public array $quotationForm = [];

    public string $message = '';

    public string $messageType = 'success';

    public bool $showCustomerDropdown = false;

    public string $customerSearch = '';

    /** @var list<string> */
    private const SORTABLE = [
        'quote_number',
        'customer',
        'status',
        'quote_date',
        'expiring_date',
        'total_amount',
        'created_at',
        'prepared_by_name',
    ];

    public function mount(?string $initialStage = null): void
    {
        $this->startDate = '';
        $this->endDate = '';
        $this->resetQuotationForm();

        $fromRequest = request()->query('stage_filter');
        $stage = $initialStage ?? (is_string($fromRequest) ? $fromRequest : null);
        $this->applyStageFilter($stage);
    }

    #[On('set-quotation-stage')]
    public function onSetQuotationStage(string $stage = ''): void
    {
        $this->applyStageFilter($stage);
    }

    public function setStageFilter(string $stage = ''): void
    {
        $this->applyStageFilter($stage);
    }

    public function toggleFilters(): void
    {
        $this->filtersOpen = ! $this->filtersOpen;
    }

    public function closeFilters(): void
    {
        $this->filtersOpen = false;
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = in_array($field, ['quote_date', 'expiring_date', 'created_at', 'total_amount'], true)
                ? 'desc'
                : 'asc';
        }

        $this->resetPage();
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

    public function updatedQuotationTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedLabSectionFilter(): void
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

    public function updatedSortField(): void
    {
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }

    public function getQuotationsProperty(): LengthAwarePaginator
    {
        if ($this->stageFilter === 'approval-settings') {
            return new LengthAwarePaginator([], 0, (int) $this->perPage, 1, [
                'path' => request()->url(),
                'pageName' => 'page',
            ]);
        }

        $query = QuotationHeaderView::with(['preparedBy', 'labSections'])->where('is_draft', 0);

        if ($this->search !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term): void {
                $q->where('quote_number', 'like', $term)
                    ->orWhere('customer', 'like', $term)
                    ->orWhere('contact_first', 'like', $term)
                    ->orWhere('contact_last', 'like', $term)
                    ->orWhere('prepared_by_name', 'like', $term);
            });
        }

        if ($this->customerFilter !== '') {
            $query->where('crm_customer_id', $this->customerFilter);
        }

        if ($this->stageFilter !== '') {
            $query->where('status', $this->stageFilter);
        }

        if ($this->quotationTypeFilter !== '') {
            $query->where('quotation_type', $this->quotationTypeFilter);
        }

        if ($this->labSectionFilter !== '') {
            $labSectionId = (string) $this->labSectionFilter;
            $query->whereHas('labSections', function ($q) use ($labSectionId): void {
                $q->where('sample_analysis_stages.id', $labSectionId);
            });
        }

        if ($this->startDate !== '' && $this->endDate !== '') {
            $query->whereBetween('quote_date', [$this->startDate, $this->endDate]);
        } elseif ($this->startDate !== '') {
            $query->whereDate('quote_date', '>=', $this->startDate);
        } elseif ($this->endDate !== '') {
            $query->whereDate('quote_date', '<=', $this->endDate);
        }

        $field = in_array($this->sortField, self::SORTABLE, true) ? $this->sortField : 'created_at';
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($field, $direction)->paginate($this->perPage);
    }

    public function getDraftsProperty()
    {
        return QuotationHeaderView::with('preparedBy')
            ->where('is_draft', 1)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();
    }

    public function getCustomersProperty()
    {
        return CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    public function getLabSectionsProperty()
    {
        return SampleAnalysisStage::query()
            ->where('active', 1)
            ->where(function ($query): void {
                $query->where('is_sample_stage', 0)->orWhereNull('is_sample_stage');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /**
     * @return list<string>
     */
    public function getQuotationStagesProperty(): array
    {
        return [
            'Quote In Preparation',
            'Quote In Approval',
            'Quote Complete',
        ];
    }

    /**
     * @return array{all: int, Quote In Preparation: int, Quote In Approval: int, Quote Complete: int}
     */
    public function getStageCountsProperty(): array
    {
        return [
            'all' => QuotationHeaderView::query()->where('is_draft', 0)->count(),
            'Quote In Preparation' => QuotationHeaderView::query()
                ->where('is_draft', 0)
                ->where('status', 'Quote In Preparation')
                ->count(),
            'Quote In Approval' => QuotationHeaderView::query()
                ->where('is_draft', 0)
                ->where('status', 'Quote In Approval')
                ->count(),
            'Quote Complete' => QuotationHeaderView::query()
                ->where('is_draft', 0)
                ->where('status', 'Quote Complete')
                ->count(),
        ];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->customerFilter = '';
        $this->quotationTypeFilter = '';
        $this->labSectionFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
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
                'details.sampletype',
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
            $quotation->labSections()->detach();
            $quotation->details()->delete();
            $quotation->delete();

            DB::commit();
            $this->showMessage('Quotation deleted successfully!', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showMessage('Error deleting quotation: '.$e->getMessage(), 'danger');
        }
    }

    public function cloneQuotation(string $quotationId)
    {
        return redirect()->route('clone_quotation', ['id' => $quotationId]);
    }

    public function openCreateEnquiryWizard(string $quotationId): void
    {
        $this->authorize('laboratory.components.quotation.add');
        $this->dispatch('open-create-enquiry-from-quotation', quotationId: $quotationId);
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

    public function render()
    {
        return view('livewire.billing.quotation-manager', [
            'quotations' => $this->quotations,
            'drafts' => $this->drafts,
            'customers' => $this->customers,
            'labSections' => $this->labSections,
            'quotationStages' => $this->quotationStages,
            'selectedQuotation' => $this->selectedQuotation,
            'stageCounts' => $this->stageCounts,
        ]);
    }

    private function applyStageFilter(?string $stage): void
    {
        if ($stage === null || $stage === '' || $stage === 'All Quotations' || $stage === 'all') {
            $this->stageFilter = '';
        } elseif ($stage === 'approval-settings' || in_array($stage, $this->quotationStages, true)) {
            $this->stageFilter = $stage;
        } else {
            return;
        }

        $this->resetPage();
    }
}
