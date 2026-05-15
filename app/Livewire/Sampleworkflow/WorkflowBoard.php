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
use App\Models\SubmissionFormInstance;
use App\Models\SampleSubmissionRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;
use Livewire\Component;
use Livewire\WithPagination;

class WorkflowBoard extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';
    /**
     * Current workflow status tab.
     */
    public string $status = '';

    /**
     * Sub-tab for status pages that split Requests vs Received.
     */
    public string $workflowSubTab = 'requests';

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
     * Pagination settings for the batches table.
     */
    public int $batchesPerPage = 10;

    public array $batchesPerPageOptions = [10, 20, 50, 100, 200, 500];

    /**
     * Livewire search and filters for all statuses.
     */
    public string $search = '';
    public ?string $receiptDateFrom = null;
    public ?string $receiptDateTo = null;
    public ?int $customerFilter = null;
    public ?int $sampleTypeFilter = null;
    
    /**
     * Submission form filters (for Samples Reception).
     */
    public string $submissionFormsSearch = '';
    public string $submissionFormsStatus = '';
    public string $submissionFormsPriority = '';
    
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

    public ?int $submissionFormAttachmentTypeId = null;

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

        $requestedTab = strtolower((string) ($this->initialFilters['tab'] ?? request()->query('tab', 'requests')));
        $this->workflowSubTab = in_array($requestedTab, ['requests', 'received'], true) ? $requestedTab : 'requests';

        $this->allFilter = $this->defaultAllSamplesFilter();
        $this->finishedFilter = $this->defaultFinishedSamplesFilter();

        $this->hydrateFiltersFromRequest();
        $this->loadReferenceData();
        $this->submissionFormAttachmentTypeId = $this->resolveSubmissionFormAttachmentTypeId();
        
        // Mark initial load as complete after a short delay
        $this->dispatch('initial-load-complete');
    }

    /**
     * Reset pagination when submission form filters are updated.
     */
    public function updatingSubmissionFormsSearch(): void
    {
        $this->resetPage('forms_page');
    }

    public function updatingSubmissionFormsStatus(): void
    {
        $this->resetPage('forms_page');
    }

    public function updatingSubmissionFormsPriority(): void
    {
        $this->resetPage('forms_page');
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
        $this->zohoItems = InventorySubCategories::orderBy('name', 'asc')->get();

        $analystRoleNames = ['laboratory analyst', 'analyst'];
        $driver = DB::connection()->getDriverName();
        $userIdColumn = $driver === 'pgsql' ? DB::raw('id::text') : 'id';

        $analystUserIds = DB::table('spatie_model_has_roles as smr')
            ->join('spatie_roles as sr', 'sr.id', '=', 'smr.role_id')
            ->where('smr.model_type', User::class)
            ->where('sr.guard_name', 'web')
            ->where(function ($query) use ($analystRoleNames) {
                foreach ($analystRoleNames as $roleName) {
                    $query->orWhereRaw('LOWER(sr.name) = ?', [$roleName]);
                }
            })
            ->pluck('smr.model_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->analysts = empty($analystUserIds)
            ? collect()
            : User::query()
                ->where('active', 1)
                ->where('is_support_staff', 0)
                ->whereIn($userIdColumn, $analystUserIds)
                ->orderBy('name')
                ->get();

        $usersQuery = User::query()
            ->where('is_client', 0)
            ->where('active', 1);

        $usersQuery->whereNull('supplier_id');

        $this->users = $usersQuery
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

    /**
     * Reset batches pagination when per-page value changes.
     */
    public function updatedBatchesPerPage($value): void
    {
        $value = (int) $value;

        if (! in_array($value, $this->batchesPerPageOptions, true)) {
            $value = 10;
        }

        $this->batchesPerPage = $value;
        $this->resetPage('batches_page');
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

    protected function isReceivingFormsTab(): bool
    {
        if ($this->isReceivingStage()) {
            return $this->workflowSubTab === 'requests';
        }

        if ($this->status === 'Samples Request Review') {
            return $this->workflowSubTab === 'requests';
        }

        return false;
    }

    public function isReceivingStage(): bool
    {
        return in_array($this->status, ['Samples En-Route', 'Samples Receiving', 'Samples Reception'], true);
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

    /**
     * Computed list of submission form instances for the Samples Reception tab.
     */
    public function getSubmissionFormsProperty()
    {
        $driver = DB::connection()->getDriverName();
        $query = SubmissionFormInstance::with([
                'submissionForm.sampleTypes',
                'submittedBy',
                'batches',
                'crmCustomer',
            'values.element',
            ])
            ->select('submission_form_instances.*')
            ->whereIn('status', ['submitted', 'in_review', 'approved', 'rejected'])
            ->whereHas('submissionForm', function ($formQuery) {
                $formQuery->where('form_type', 'template');
            })
            ->selectSub(function ($subQuery) use ($driver) {
                $subQuery->from('submission_form_instances as attachment_instances')
                    ->selectRaw('count(*)')
                    ->whereRaw('attachment_instances.portal_request_id = submission_form_instances.id' . ($driver === 'pgsql' ? '::text' : ''));
            }, 'attachment_count')
            ->latest();

        if ($this->isReceivingFormsTab()) {
            if ($this->isReceivingStage()) {
                $query->where('status', 'submitted');
                $query->whereDoesntHave('batches');
            } elseif ($this->status === 'Samples Request Review') {
                $query->whereIn('status', ['submitted', 'in_review']);
                $query->whereDoesntHave('batches');
            }
        }

        if ($this->status === 'Samples Request Review' && $this->workflowSubTab === 'received') {
            $query->whereIn('status', ['approved', 'rejected']);
        }

        if ($this->status === 'Samples In Lab') {
            $query->where('status', 'approved');
        }

        if ($this->submissionFormsStatus) {
            $query->where('status', $this->submissionFormsStatus);
        }

        if ($this->submissionFormsPriority) {
            $query->withPriority($this->submissionFormsPriority);
        }

        if ($this->customerFilter) {
            $query->where('crm_customer_id', $this->customerFilter);
        }

        if ($this->sampleTypeFilter) {
            $query->whereHas('submissionForm.sampleTypes', function ($q) {
                $q->where('sample_types.id', $this->sampleTypeFilter);
            });
        }

        if ($this->receiptDateFrom) {
            $query->whereDate('submitted_at', '>=', $this->receiptDateFrom);
        }

        if ($this->receiptDateTo) {
            $query->whereDate('submitted_at', '<=', $this->receiptDateTo);
        }

        if ($this->submissionFormsSearch) {
            $search = $this->submissionFormsSearch;
            $query->where(function ($q) use ($search) {
                $q->where('form_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('submissionForm', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate(10, ['*'], 'forms_page');
    }

    /**
     * Delete a submission form instance.
     */
    public function deleteSubmissionForm(string $id): void
    {
        if (!Str::isUuid($id)) {
            session()->flash('error', 'Invalid submission form identifier.');
            return;
        }

        $instance = SubmissionFormInstance::findOrFail($id);

        // Only allow deleting drafts
        if ($instance->status !== 'draft') {
            session()->flash('error', 'Only draft submissions can be deleted.');
            return;
        }

        $instance->delete();
        session()->flash('message', 'Draft submission deleted successfully.');
    }



    protected function baseBatchQuery()
    {
        return SampleHeader::with([
            'samples',
            'client',
            'sample_type',
            'invoice',
            'submissionFormInstance.submissionForm',
            'batch_attachments',
            'sampleSubmissionRequest.requestedAnalyses',
            'sampleSubmissionRequest.supportingDocumentTemplates',
            'sampleSubmissionRequest.supportingDocumentInstances.template',
        ])->where('isactive', 1);
    }

    protected function resolveSubmissionFormAttachmentTypeId(): ?int
    {
        $id = SystemConfiguration::query()->where('key', 'attachment_type')
            ->where('value', 'Submission Form')
            ->value('id');

        if ($id === null) {
            $id = SystemConfiguration::query()->where('key', 'attachment_type')->value('id');
        }

        return $id !== null ? (int) $id : null;
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

        return $query->paginate($this->batchesPerPage, ['*'], 'batches_page');
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

        return $query->paginate($this->batchesPerPage, ['*'], 'batches_page');
    }

    protected function getStatusBatches()
    {
        $query = $this->baseBatchQuery()->orderBy('receipt_date', 'desc');

        if ($this->status === 'Schedule of Analysis') {
            $query->where('status', 'Samples In Lab')
                ->where('schedule_sent', '<', 1);
        } elseif ($this->isReceivingStage()) {
            if ($this->workflowSubTab === 'received') {
                $query->where(function ($inner) {
                    $inner->where('status', 'Samples Reception')
                        ->where(function ($q) {
                            $q->whereNotNull('in_lab_date')
                                ->orWhere('is_amendment', 1);
                        });
                });
            } else {
                $query->where(function ($inner) {
                    $inner->whereIn('status', ['Samples En-Route', 'Samples Reception'])
                        ->where(function ($q) {
                            $q->whereNull('in_lab_date')
                                ->where('is_amendment', 0);
                        });
                });
            }
        } elseif ($this->status === 'Samples Request Review') {
            $query->where(function ($inner) {
                $inner->where('status', 'Samples Request Review')
                    ->orWhere('prelim_batch_status', 'Samples Request Review');
            });

            if ($this->workflowSubTab === 'received') {
                $query->whereNotNull('in_lab_date');
            } else {
                $query->whereNull('in_lab_date');
            }
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

        // Apply submission form filters to batches for certain stages
        if ($this->status === 'Samples In Lab' || ($this->isReceivingStage() && $this->workflowSubTab === 'requests')) {
            if ($this->submissionFormsSearch) {
                $search = $this->submissionFormsSearch;
                $query->whereHas('submissionFormInstance', function ($q) use ($search) {
                    $q->where('form_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('submissionForm', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            }

            if ($this->submissionFormsStatus) {
                $status = $this->submissionFormsStatus;
                $query->whereHas('submissionFormInstance', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            }

            if ($this->submissionFormsPriority) {
                $priority = $this->submissionFormsPriority;
                $query->whereHas('submissionFormInstance', function ($q) use ($priority) {
                    $q->withPriority($priority);
                });
            }
        }

        return $query->paginate($this->batchesPerPage, ['*'], 'batches_page');
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
        $this->submissionFormsSearch = '';
        $this->submissionFormsStatus = '';
        $this->submissionFormsPriority = '';
    }

    public function setWorkflowSubTab(string $tab): void
    {
        $tab = strtolower(trim($tab));
        $this->workflowSubTab = in_array($tab, ['requests', 'received'], true) ? $tab : 'requests';
        $this->resetPage('batches_page');
        $this->resetPage('portal_submissions_page');
    }

    /**
     * Summary metrics for Samples Reception dashboard widgets.
     */
    public function getSamplesReceptionStatsProperty(): array
    {
        if (!in_array($this->status, ['Samples Reception', 'Samples En-Route', 'Samples Receiving'], true)) {
            return [
                'customers_requested' => 0,
                'portal_submitted' => 0,
                'sent_to_request_review' => 0,
                'waiting_for_delivery' => 0,
            ];
        }

        /** @var \Illuminate\Database\Eloquent\Builder $portalRequestQuery */
        $portalRequestQuery = SampleSubmissionRequest::query();
        $portalRequestQuery->whereNull('sample_header_id');
        $portalRequestQuery->whereIn('status', [
            'submitted',
            'booking_date_approved',
            'booking_date_rescheduled',
            'in_review',
        ]);

        /** @var \Illuminate\Database\Eloquent\Builder $portalFormQuery */
        $portalFormQuery = SubmissionFormInstance::query();
        $portalFormQuery->whereNotNull('crm_customer_id');
        $portalFormQuery->whereHas('submissionForm', function ($formQuery) {
            $formQuery->where('form_type', 'template');
        });
        $portalFormQuery->whereDoesntHave('batches');

        $requestedCustomerIds = (clone $portalRequestQuery)
            ->whereNotNull('crm_customer_id')
            ->pluck('crm_customer_id');

        $submittedCustomerIds = (clone $portalFormQuery)
            ->where('status', 'submitted')
            ->whereNotNull('crm_customer_id')
            ->pluck('crm_customer_id');

        return [
            'customers_requested' => $requestedCustomerIds
                ->merge($submittedCustomerIds)
                ->filter()
                ->unique()
                ->count(),
            'portal_submitted' => (clone $portalFormQuery)
                    ->where('status', 'submitted')
                    ->count()
                + (clone $portalRequestQuery)
                    ->whereIn('status', ['submitted', 'booking_date_approved', 'booking_date_rescheduled'])
                    ->count(),
            'sent_to_request_review' => (clone $portalFormQuery)
                    ->where('status', 'in_review')
                    ->count()
                + (clone $portalRequestQuery)
                    ->where('status', 'in_review')
                    ->count(),
            'waiting_for_delivery' => (clone $portalRequestQuery)
                ->whereIn('status', ['submitted', 'booking_date_approved', 'booking_date_rescheduled'])
                ->count(),
        ];
    }

    /**
     * Computed list of portal submission requests for the Sample Receiving Requests tab.
     * Shows SampleSubmissionRequest records that have been submitted but not yet assigned to a batch.
     */
    public function getPortalSubmissionsProperty()
    {
        if (! $this->isReceivingFormsTab()) {
            return null;
        }

        $query = SampleSubmissionRequest::with([
                'customer',
                'supportingDocumentTemplates',
                'supportingDocumentInstances.template',
            'supportingDocumentInstances.values.element',
            ])
            ->where(function ($q) {
                $q->whereIn('status', [
                    'submitted',
                    'booking_date_approved',
                    'booking_date_rescheduled',
                ])->orWhereHas('supportingDocumentInstances', function ($docQuery) {
                    $docQuery->whereIn('status', ['submitted', 'in_review', 'approved']);
                });
            })
            ->whereNull('sample_header_id')
            ->orderByDesc('submitted_by_date');

        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('unique_identification', 'like', $search)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $search));
            });
        }

        if ($this->customerFilter) {
            $query->where('crm_customer_id', $this->customerFilter);
        }

        return $query->paginate($this->batchesPerPage, ['*'], 'portal_submissions_page');
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
            'submissionFormAttachmentTypeId' => $this->submissionFormAttachmentTypeId,
            'portalSubmissions' => $this->portalSubmissions,
            'samplesReceptionStats' => $this->samplesReceptionStats,
        ]);
    }
}
