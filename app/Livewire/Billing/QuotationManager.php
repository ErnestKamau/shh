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
use App\Services\Commercial\EnquiryFromQuotationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Throwable;

class QuotationManager extends Component
{
    use AuthorizesRequests;
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

    public bool $showCreateEnquiryModal = false;

    public ?string $selectedEnquiryQuotationId = null;

    /** @var array<string, mixed> */
    public array $enquiryForm = [];

    public string $enquiryCreationToken = '';
    
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

    /**
     * @return array{all: int, Quote In Preparation: int, Quote Complete: int}
     */
    public function getStageCountsProperty(): array
    {
        return [
            'all' => QuotationHeaderView::query()->where('is_draft', 0)->count(),
            'Quote In Preparation' => QuotationHeaderView::query()
                ->where('is_draft', 0)
                ->where('status', 'Quote In Preparation')
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

    public function getEnquirySourceQuotationProperty(): ?QuotationHeader
    {
        if ($this->selectedEnquiryQuotationId === null) {
            return null;
        }

        return QuotationHeader::query()
            ->with(['customer', 'details'])
            ->find($this->selectedEnquiryQuotationId);
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

    public function cloneQuotation($quotationId)
    {
        return redirect()->route('clone_quotation', ['id' => $quotationId]);
    }

    public function openCreateEnquiryModal(string $quotationId): void
    {
        $this->authorize('laboratory.components.quotation.add');
        $this->resetValidation();

        try {
            $quotation = QuotationHeader::query()
                ->with(['details', 'customer'])
                ->findOrFail($quotationId);
            $service = app(EnquiryFromQuotationService::class);
            $lines = $service->eligibleQuotationLines($quotation);

            $this->selectedEnquiryQuotationId = (string) $quotation->id;
            $this->enquiryCreationToken = (string) Str::uuid();
            $this->enquiryForm = [
                'creation_intent' => EnquiryFromQuotationService::INTENT_PREPARE,
                'number_of_samples' => $service->inferPhysicalSampleCount($lines),
                'reference_number' => '',
                'client_po_number' => '',
                'po_skipped' => false,
                'date_expected' => '',
                'sample_description' => '',
                'enquiry_notes' => '',
            ];
            $this->showCreateEnquiryModal = true;
        } catch (Throwable $exception) {
            $this->showMessage($exception->getMessage(), 'danger');
        }
    }

    public function closeCreateEnquiryModal(): void
    {
        $this->showCreateEnquiryModal = false;
        $this->selectedEnquiryQuotationId = null;
        $this->enquiryCreationToken = '';
        $this->enquiryForm = [];
        $this->resetValidation();
    }

    public function createEnquiryFromQuotation()
    {
        $this->authorize('laboratory.components.quotation.add');

        $validated = $this->validate([
            'selectedEnquiryQuotationId' => ['required', 'uuid'],
            'enquiryCreationToken' => ['required', 'uuid'],
            'enquiryForm.creation_intent' => ['required', 'in:'.implode(',', EnquiryFromQuotationService::creationIntents())],
            'enquiryForm.number_of_samples' => ['required', 'integer', 'min:1', 'max:10000'],
            'enquiryForm.reference_number' => ['nullable', 'string', 'max:255'],
            'enquiryForm.client_po_number' => ['nullable', 'string', 'max:255'],
            'enquiryForm.po_skipped' => ['nullable', 'boolean'],
            'enquiryForm.date_expected' => ['nullable', 'date'],
            'enquiryForm.sample_description' => ['nullable', 'string', 'max:5000'],
            'enquiryForm.enquiry_notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'enquiryForm.number_of_samples.required' => 'Confirm the number of physical samples.',
            'enquiryForm.number_of_samples.min' => 'At least one physical sample is required.',
        ]);

        try {
            $quotation = QuotationHeader::query()
                ->findOrFail($validated['selectedEnquiryQuotationId']);
            $enquiry = app(EnquiryFromQuotationService::class)->create(
                $quotation,
                [
                    'number_of_samples' => (int) $validated['enquiryForm']['number_of_samples'],
                    'reference_number' => $validated['enquiryForm']['reference_number'] ?? null,
                    'date_expected' => $validated['enquiryForm']['date_expected'] ?? null,
                    'sample_description' => $validated['enquiryForm']['sample_description'] ?? null,
                    'enquiry_notes' => $validated['enquiryForm']['enquiry_notes'] ?? null,
                    'creation_intent' => $validated['enquiryForm']['creation_intent'],
                    'client_po_number' => $validated['enquiryForm']['client_po_number'] ?? null,
                    'po_skipped' => (bool) ($validated['enquiryForm']['po_skipped'] ?? false),
                ],
                $validated['enquiryCreationToken'],
            );

            $this->closeCreateEnquiryModal();

            $instance = $enquiry->submissionFormInstance;
            if ($instance !== null && $instance->submission_form_id !== null) {
                return redirect()->route('submission-forms.instances.fill', [
                    'submissionForm' => $instance->submission_form_id,
                    'instance' => $instance->id,
                ]);
            }

            $this->showMessage(
                'Enquiry '.($enquiry->reference_number ?: $enquiry->formatted_number)
                .' was created from quotation '.$quotation->quote_number.'.',
                'success',
            );
        } catch (Throwable $exception) {
            $this->showMessage($exception->getMessage(), 'danger');
        }

        return null;
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
            'stageCounts' => $this->stageCounts,
        ]);
    }
}
