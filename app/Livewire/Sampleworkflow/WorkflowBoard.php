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
use App\Models\Sampleworkflow\SampleWorkflowDecontaminationLog;
use App\Models\Sampleworkflow\SampleWorkflowDecontaminationLogItem;
use App\Services\Commercial\CommercialEnquiryFromFormService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Livewire\Sampleworkflow\ProcessEnquiryWizard;
use App\Services\SubmissionForm\SubmissionFormIntrayService;
use App\Lab;
use App\LabDecontaminationArea;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class WorkflowBoard extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    /**
     * Samples Receiving request status tabs (workflowSubTab keys).
     *
     * @return array<string, string>
     */
    public static function receivingRequestTabs(): array
    {
        return [
            'submitted' => 'Submitted Requests',
            'ready_for_reception' => 'Ready for Reception',
            'received' => 'Received Request',
            'in_review' => 'In Review',
            'in_additional_info' => 'Request Additional Info',
            'complete' => 'Complete Requests',
            // 'interzone_transfers' => 'Interzone Transfers',
        ];
    }

    /**
     * Samples Request Review sub-tabs (workflowSubTab keys).
     *
     * @return array<string, string>
     */
    public static function requestReviewTabs(): array
    {
        return [
            'in_review' => 'In review',
            'accepted' => 'Accepted',
        ];
    }
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
     * Pagination for submission form instance tables.
     */
    public int $submissionFormsPerPage = 25;

    public array $submissionFormsPerPageOptions = [10, 25, 50, 100, 200, 500];

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
    public string $natureOfSampleFilter = '';
    
    /**
     * Expandable advanced filters (Samples Receiving layout).
     */
    public bool $showAdvancedFilters = false;

    /**
     * Customer dropdown state.
     */
    public bool $showCustomerDropdown = false;
    public string $customerSearch = '';
    public int $customerPage = 1;
    public int $customerPerPage = 50;
    
    public ?int $submissionFormAttachmentTypeId = null;

    /**
     * Selected submission form instances (receive, move to tray, request review).
     *
     * @var array<int, string>
     */
    public array $selectedFormInstanceIds = [];

    /**
     * Display metadata for the receive-sample modal.
     *
     * @var array<int, array{id: string, label: string, customer: string}>
     */
    public array $receiveFormSummaries = [];

    public bool $showPoCaptureModal = false;

    public ?string $poCaptureEnquiryId = null;

    public string $clientPoNumber = '';

    public bool $poSkipped = false;

    public string $advancePaymentReference = '';


    /**
     * @var array<int, array{id: string, label: string, customer: string}>
     */
    public array $intrayFormSummaries = [];

    /**
     * Selected instances that already have a pending intray (for reassignment UI).
     *
     * @var array<int, array{id: string, label: string, assignee_name: string}>
     */
    public array $pendingIntrayAssignments = [];

    public bool $showMyIntrayPanel = true;

    public bool $showDecontaminationModal = false;

    public bool $openReceiveRequestPending = false;

    public string $decontaminationDate = '';

    public string $decontaminationOfficer = '';

    public string $decontaminationLabId = '';

    /** @var array<string, string> */
    public array $decontaminationSwabbing = [];

    /**
     * Reference datasets for dropdowns/selects.
     * Kept protected so Livewire never serialises them into the snapshot.
     * Populated once per request inside render().
     */
    protected Collection $labsections;
    protected Collection $analysts;
    protected Collection $users;
    protected Collection $zohoItems;
    protected Collection $clients;
    protected Collection $sampletypes;
    protected Collection $customers;

    /**
     * Mount the component with the requested status and filters.
     */
    public function mount(string $status = null, array $initialFilters = []): void
    {
        $workflowStages = getSampleWorflowStages();
        $this->status = $status ?: ($workflowStages[0] ?? 'Samples Reception');
        $this->initialFilters = $initialFilters;

        $requestedTab = strtolower((string) ($this->initialFilters['tab'] ?? request()->query('tab', 'requests')));
        if ($this->status === 'Samples Receiving') {
            $defaultTab = 'submitted';
            $this->workflowSubTab = in_array($requestedTab, array_keys(self::receivingRequestTabs()), true)
                ? $requestedTab
                : $defaultTab;
        } elseif ($this->status === 'Samples Request Review') {
            $legacyTabMap = ['requests' => 'in_review', 'received' => 'accepted'];
            $requestedTab = $legacyTabMap[$requestedTab] ?? $requestedTab;
            $this->workflowSubTab = in_array($requestedTab, array_keys(self::requestReviewTabs()), true)
                ? $requestedTab
                : 'in_review';
        } else {
            $this->workflowSubTab = in_array($requestedTab, ['requests', 'received'], true) ? $requestedTab : 'requests';
        }

        $this->allFilter = $this->defaultAllSamplesFilter();
        $this->finishedFilter = $this->defaultFinishedSamplesFilter();

        $this->hydrateFiltersFromRequest();
        $this->submissionFormAttachmentTypeId = $this->resolveSubmissionFormAttachmentTypeId();

        if ($this->status === 'Samples Receiving' && request()->boolean('open_receive_request')) {
            $this->workflowSubTab = 'submitted';
            $this->openReceiveRequestPending = true;
        }
    }

    public function openPendingReceiveRequestIfNeeded(): void
    {
        if (! $this->openReceiveRequestPending) {
            return;
        }

        $this->openReceiveRequestPending = false;
        $this->openReceiveModal([]);
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

    public function updatingNatureOfSampleFilter(): void
    {
        $this->resetPage('forms_page');
    }

    public function updatingSubmissionFormsPerPage(): void
    {
        $this->resetPage('forms_page');
    }
    
    /**
     * Load shared datasets used across the workflow UI.
     * Called once per render(), not in mount(), so the collections are never
     * included in Livewire's serialised snapshot.
     *
     * Heavy batch-stage data (labsections, analysts, zohoItems) is skipped for
     * form-centric stages (Samples Receiving, Samples Request Review) where those
     * dropdowns are never rendered, saving 3 queries per request.
     */
    protected function loadReferenceData(): void
    {
        $isFormOnlyStage = $this->isSamplesReceiving() || $this->isSamplesRequestReview();

        if ($isFormOnlyStage) {
            $this->labsections = collect();
            $this->zohoItems = collect();
            $this->analysts = collect();
        } else {
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
        }

        $this->users = User::query()
            ->where('is_client', 0)
            ->where('active', 1)
            ->whereNull('supplier_id')
            ->orderBy('name')
            ->get();

        $this->clients = CRMCustomer::where('active', 1)->orderBy('name')->get();
        $this->customers = $this->clients;
        $this->sampletypes = SampleType::where('active', 1)->orderBy('name')->get();
    }

    /**
     * Reference collections are populated in render(); action methods may need them earlier.
     */
    protected function ensureReferenceDataLoaded(): void
    {
        if (! isset($this->users)) {
            $this->loadReferenceData();
        }
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
        if ($this->status === 'Samples Receiving') {
            return true;
        }

        if ($this->isReceivingStage()) {
            return $this->workflowSubTab === 'requests';
        }

        if ($this->status === 'Samples Request Review') {
            return $this->workflowSubTab === 'in_review';
        }

        return false;
    }

    public function isSamplesReceiving(): bool
    {
        return $this->status === 'Samples Receiving';
    }

    public function isSamplesRequestReview(): bool
    {
        return $this->status === 'Samples Request Review';
    }

    /**
     * @return array<int, string>
     */
    protected function requestReviewTabKeys(): array
    {
        return array_keys(self::requestReviewTabs());
    }

    /**
     * @return array<int, string>
     */
    protected function receivingRequestTabKeys(): array
    {
        return array_keys(self::receivingRequestTabs());
    }

    /**
     * Submission form status keys for Samples Receiving (excludes interzone tab).
     *
     * @return array<int, string>
     */
    public static function receivingRequestStatusKeys(): array
    {
        return array_values(array_filter(
            array_keys(self::receivingRequestTabs()),
            fn (string $key) => ! in_array($key, ['interzone_transfers', 'ready_for_reception'], true)
        ));
    }

    /**
     * Base query for submission form instances on the Samples Receiving board.
     */
    public static function receivingSubmissionFormsQuery(?array $statuses = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = SubmissionFormInstance::query()
            ->whereHas('submissionForm', function ($formQuery) {
                $formQuery->where('form_type', 'template');
            })
            ->whereDoesntHave('batches', function ($bq) {
                $bq->whereNotIn('status', ['Samples En-Route', 'Samples Receiving', 'Samples Reception']);
            });

        $query->whereIn('status', $statuses ?? self::receivingRequestStatusKeys());

        return $query;
    }

    /**
     * Sidebar badge: submitted + received + additional-info requests in receiving.
     */
    public static function sidebarReceivingRequestCount(): int
    {
        return (int) self::receivingSubmissionFormsQuery(['submitted', 'received', 'in_additional_info'])->count();
    }

    /**
     * Sidebar badge: in-review requests plus batches at Samples Request Review.
     */
    public static function sidebarRequestReviewCount(): int
    {
        $inReviewRequests = SubmissionFormInstance::query()
            ->whereHas('submissionForm', function ($formQuery) {
                $formQuery->where('form_type', 'template');
            })
            ->whereIn('status', ['in_review', 'In Review'])
            ->count();

        $batchesInReview = SampleHeader::query()
            ->where('isactive', 1)
            ->where(function ($inner) {
                $inner->where('status', 'Samples Request Review')
                    ->orWhere('prelim_batch_status', 'Samples Request Review');
            })
            ->count();

        return $inReviewRequests + $batchesInReview;
    }

    protected function receivingSubmissionFormsBaseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return static::receivingSubmissionFormsQuery();
    }

    /**
     * Submission forms awaiting physical check-in (quotation accepted / ready for reception).
     */
    protected function readyForPhysicalReceptionSubmissionFormsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->receivingSubmissionFormsBaseQuery()
            ->whereIn('status', ['submitted', 'Submitted'])
            ->whereDoesntHave('batches', function ($batchQuery) {
                $batchQuery->whereNotIn('status', ['Samples En-Route', 'Samples Receiving', 'Samples Reception']);
            });

        $query->whereHas('sampleSubmissionRequest', function ($enquiryQuery): void {
            $enquiryQuery->where('status', SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION);
        });

        return $query;
    }

    /**
     * Submitted Requests tab: instance-based commercial pipeline before physical reception.
     */
    protected function submittedCommercialPipelineSubmissionFormsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $statuses = array_values(array_filter(
            SampleSubmissionRequest::COMMERCIAL_PIPELINE_STATUSES,
            fn (string $status): bool => ! in_array($status, [
                SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            ], true)
        ));

        return $this->receivingSubmissionFormsBaseQuery()
            ->whereIn('status', ['submitted', 'Submitted'])
            ->where(function ($query) use ($statuses): void {
                $query->whereHas('sampleSubmissionRequest', function ($enquiryQuery) use ($statuses): void {
                    $enquiryQuery
                        ->whereIn('status', $statuses)
                        ->whereNull('sample_header_id');
                })->orWhere(function ($orphanQuery): void {
                    $orphanQuery
                        ->whereDoesntHave('sampleSubmissionRequest')
                        ->whereDoesntHave('batches');
                });
            });
    }

    protected function applyReceivingSubmissionFormFilters(\Illuminate\Database\Eloquent\Builder $query): void
    {
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

        if ($this->natureOfSampleFilter) {
            $filter = $this->natureOfSampleFilter;
            $instanceIds = \App\Models\SubmissionFormInstanceValue::query()
                ->whereHas('element', fn ($q) => $q->where('name', 'nature_of_sample'))
                ->get()
                ->filter(fn ($v) => $v->value === $filter)
                ->pluck('submission_form_instance_id');
            $query->whereIn('id', $instanceIds);
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
    }

    /**
     * Per-tab record counts for Samples Receiving status tabs.
     *
     * @return array<string, int>
     */
    public function getReceivingRequestTabCountsProperty(): array
    {
        if (! $this->isSamplesReceiving()) {
            return [];
        }

        $query = $this->receivingSubmissionFormsBaseQuery();
        $this->applyReceivingSubmissionFormFilters($query);

        $rows = $query->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->all();

        $counts = [];
        foreach ($this->receivingRequestTabKeys() as $tabKey) {
            // if ($tabKey === 'interzone_transfers') {
            //     $counts[$tabKey] = \App\Models\Sampleworkflow\InterzoneTransfer::query()->count();
            //     continue;
            // }
            if ($tabKey === 'submitted') {
                $submittedQuery = $this->submittedCommercialPipelineSubmissionFormsQuery();
                $this->applyReceivingSubmissionFormFilters($submittedQuery);
                $counts[$tabKey] = (int) $submittedQuery->count();
                continue;
            }
            if ($tabKey === 'ready_for_reception') {
                $readyQuery = $this->readyForPhysicalReceptionSubmissionFormsQuery();
                $this->applyReceivingSubmissionFormFilters($readyQuery);
                $counts[$tabKey] = (int) $readyQuery->count();
                continue;
            }
            $counts[$tabKey] = (int) ($rows[$tabKey] ?? 0);
        }

        return $counts;
    }

    /**
     * Per-tab record counts for Samples Request Review.
     *
     * @return array<string, int>
     */
    public function getRequestReviewTabCountsProperty(): array
    {
        if (! $this->isSamplesRequestReview()) {
            return [];
        }

        $inReviewQuery = $this->requestReviewSubmissionFormsBaseQuery();
        $this->applyRequestReviewSubmissionFormFilters($inReviewQuery);
        $inReviewQuery->requestReviewInReview();

        $acceptedQuery = $this->requestReviewSubmissionFormsBaseQuery();
        $this->applyRequestReviewSubmissionFormFilters($acceptedQuery);
        $acceptedQuery->requestReviewAccepted();

        return [
            'in_review' => $inReviewQuery->count(),
            'accepted'  => $acceptedQuery->count(),
        ];
    }

    protected function requestReviewSubmissionFormsBaseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return SubmissionFormInstance::query()->inRequestReviewQueue();
    }

    protected function applyRequestReviewSubmissionFormFilters(\Illuminate\Database\Eloquent\Builder $query): void
    {
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

        if ($this->submissionFormsStatus) {
            $query->where('status', $this->submissionFormsStatus);
        }

        if ($this->natureOfSampleFilter) {
            $filter = $this->natureOfSampleFilter;
            $instanceIds = \App\Models\SubmissionFormInstanceValue::query()
                ->whereHas('element', fn ($q) => $q->where('name', 'nature_of_sample'))
                ->get()
                ->filter(fn ($v) => $v->value === $filter)
                ->pluck('submission_form_instance_id');
            $query->whereIn('id', $instanceIds);
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

        if ($this->isSamplesReceiving()) {
            $tabStatus = in_array($this->workflowSubTab, $this->receivingRequestTabKeys(), true)
                ? $this->workflowSubTab
                : 'submitted';

            $eagerLoads = [
                'submissionForm.sampleTypes',
                'submittedBy',
                'batches',
                'crmCustomer',
                'sampleSubmissionRequest',
                'testRequestFormInstance',
                'values.element',
                'latestIntray.toUser',
                'latestIntray.fromUser',
                'activePendingIntray',
            ];

            $query = match ($this->workflowSubTab) {
                'ready_for_reception' => $this->readyForPhysicalReceptionSubmissionFormsQuery(),
                'submitted' => $this->submittedCommercialPipelineSubmissionFormsQuery(),
                default => $this->receivingSubmissionFormsBaseQuery()->where('status', $tabStatus),
            };

            $query->with($eagerLoads)->select('submission_form_instances.*');

            $query->selectSub(function ($subQuery) use ($driver) {
                    $subQuery->from('submission_form_instances as attachment_instances')
                        ->selectRaw('count(*)')
                        ->whereRaw('attachment_instances.portal_request_id = submission_form_instances.id' . ($driver === 'pgsql' ? '::text' : ''));
                }, 'attachment_count')
                ->latest();

            $this->applyReceivingSubmissionFormFilters($query);

            return $query->paginate($this->submissionFormsPerPage, ['*'], 'forms_page');
        }

        if ($this->isSamplesRequestReview()) {
            $driver = DB::connection()->getDriverName();

            $query = $this->requestReviewSubmissionFormsBaseQuery()
                ->with([
                    'submissionForm.sampleTypes',
                    'submittedBy',
                    'batches.batch_attachments',
                    'crmCustomer',
                    'values.element',
                    'workflowForms',
                    'analysisAcceptanceForms',
                    'latestIntray.toUser',
                    'latestIntray.fromUser',
                    'activePendingIntray',
                ])
                ->select('submission_form_instances.*')
                ->selectSub(function ($subQuery) use ($driver) {
                    $subQuery->from('submission_form_instances as attachment_instances')
                        ->selectRaw('count(*)')
                        ->whereRaw('attachment_instances.portal_request_id = submission_form_instances.id' . ($driver === 'pgsql' ? '::text' : ''));
                }, 'attachment_count')
                ->latest();

            if ($this->workflowSubTab === 'accepted') {
                $query->requestReviewAccepted();
            } else {
                $query->requestReviewInReview();
            }

            $this->applyRequestReviewSubmissionFormFilters($query);

            return $query->paginate($this->submissionFormsPerPage, ['*'], 'forms_page');
        }

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
                $query->whereIn('status', ['submitted', 'Submitted', 'pending_reception', 'received_at_lab']);
                $query->whereDoesntHave('batches', function ($bq) {
                    $bq->whereNotIn('status', ['Samples En-Route', 'Samples Receiving', 'Samples Reception']);
                });
            } elseif ($this->status === 'Samples Request Review') {
                $query->whereIn('status', ['submitted', 'Submitted', 'in_review', 'In Review', 'pending_reception', 'received_at_lab']);
                $query->whereDoesntHave('batches');
            }
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

        return $query->paginate($this->submissionFormsPerPage, ['*'], 'forms_page');
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

            if ($this->workflowSubTab === 'accepted') {
                $query->whereHas('submissionFormInstance', function ($formQuery) {
                    $formQuery->requestReviewAccepted();
                });
            } else {
                $query->where(function ($inner) {
                    $inner->whereDoesntHave('submissionFormInstance')
                        ->orWhereHas('submissionFormInstance', function ($formQuery) {
                            $formQuery->requestReviewInReview();
                        });
                });
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
        $this->natureOfSampleFilter = '';
    }

    public function toggleAdvancedFilters(): void
    {
        $this->showAdvancedFilters = ! $this->showAdvancedFilters;
    }

    public function setWorkflowSubTab(string $tab): void
    {
        $tab = strtolower(trim($tab));

        if ($this->isSamplesReceiving()) {
            $this->workflowSubTab = in_array($tab, $this->receivingRequestTabKeys(), true) ? $tab : 'submitted';
            $this->selectedFormInstanceIds = [];
            $this->resetPage('forms_page');
        } elseif ($this->isSamplesRequestReview()) {
            $this->workflowSubTab = in_array($tab, $this->requestReviewTabKeys(), true) ? $tab : 'in_review';
            $this->selectedFormInstanceIds = [];
            $this->resetPage('forms_page');
        } else {
            $this->workflowSubTab = in_array($tab, ['requests', 'received'], true) ? $tab : 'requests';
            $this->resetPage('batches_page');
            $this->resetPage('portal_submissions_page');
        }

        $this->showAdvancedFilters = false;
    }

    /**
     * Batches at or past target date (due today or overdue) for the TAT Today modal.
     *
     * @return Collection<int, object{id: int|string, batch_code: string, status: string, tat_date: string, is_late: bool, is_today: bool}>
     */
    public function getTatTodayBatchesProperty(): Collection
    {
        $today = Carbon::today();
        $cutoff = Carbon::now()->addDay();

        return SampleDate::query()
            ->join('sample_headers as s', 's.id', '=', 'sample_dates.sample_header_id')
            ->where('sample_dates.date', '<=', $cutoff)
            ->whereIn('s.status', [
                'Samples En-Route',
                'Samples Reception',
                'Samples Request Review',
                'Samples In Lab',
                'Sample Verification',
                'Sample Approval',
            ])
            ->where('sample_dates.name', 'Target Date')
            ->selectRaw('s.id, s.batch_code, s.status, sample_dates.date as tat_date')
            ->orderByDesc('sample_dates.date')
            ->get()
            ->map(function ($row) use ($today) {
                $tatDate = Carbon::parse($row->tat_date);

                return (object) [
                    'id' => $row->id,
                    'batch_code' => $row->batch_code,
                    'status' => $row->status,
                    'tat_date' => $tatDate->format('Y-m-d'),
                    'is_late' => $tatDate->lt($today),
                    'is_today' => $tatDate->isSameDay($today),
                ];
            });
    }

    public function getTatTodayCountProperty(): int
    {
        return $this->tatTodayBatches->count();
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
        $portalRequestQuery->where(function ($q) {
            $q->whereNull('sample_header_id')
                ->orWhereHas('batch', function ($bq) {
                    $bq->whereIn('status', ['Samples En-Route', 'Samples Receiving', 'Samples Reception']);
                });
        });
        $portalRequestQuery->whereIn('status', [
            'submitted',
            'Submitted',
            'booking_date_approved',
            'booking_date_rescheduled',
            'in_review',
            'In Review',
            'pending_reception',
            'received_at_lab',
        ]);

        /** @var \Illuminate\Database\Eloquent\Builder $portalFormQuery */
        $portalFormQuery = SubmissionFormInstance::query();
        $portalFormQuery->whereNotNull('crm_customer_id');
        $portalFormQuery->whereHas('submissionForm', function ($formQuery) {
            $formQuery->where('form_type', 'template');
        });
        $portalFormQuery->whereDoesntHave('batches', function ($bq) {
            $bq->whereNotIn('status', ['Samples En-Route', 'Samples Receiving', 'Samples Reception']);
        });

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
                    ->whereIn('status', ['submitted', 'Submitted', 'pending_reception', 'received_at_lab'])
                    ->count()
                + (clone $portalRequestQuery)
                    ->whereIn('status', ['submitted', 'Submitted', 'booking_date_approved', 'booking_date_rescheduled', 'pending_reception', 'received_at_lab'])
                    ->count(),
            'sent_to_request_review' => (clone $portalFormQuery)
                    ->where('status', 'in_review')
                    ->count()
                + (clone $portalRequestQuery)
                    ->where('status', 'in_review')
                    ->count(),
            'waiting_for_delivery' => (clone $portalRequestQuery)
                ->whereIn('status', ['submitted', 'Submitted', 'booking_date_approved', 'booking_date_rescheduled', 'pending_reception', 'received_at_lab'])
                ->count(),
        ];
    }

    /**
     * Commercial enquiry queue for Phase 1 (portal / walk-in LSR).
     */
    public function getCommercialEnquiriesProperty()
    {
        if (! $this->isSamplesReceiving() || $this->workflowSubTab !== 'submitted') {
            return null;
        }

        $query = $this->commercialEnquiriesBaseQuery()
            ->with(['customer', 'contact', 'currentQuotation', 'submissionFormInstance.submissionForm'])
            ->orderByDesc('created_at');

        if (! empty($this->search)) {
            $search = '%'.$this->search.'%';
            $query->where(function ($q) use ($search): void {
                $q->where('unique_identification', 'like', $search)
                    ->orWhere('reference_number', 'like', $search)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $search));
            });
        }

        return $query->paginate($this->batchesPerPage, ['*'], 'commercial_enquiries_page');
    }

    protected function commercialEnquiriesBaseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $excludedStatuses = [
            SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
        ];

        $statuses = array_values(array_filter(
            SampleSubmissionRequest::COMMERCIAL_PIPELINE_STATUSES,
            fn (string $status): bool => ! in_array($status, $excludedStatuses, true)
        ));

        return SampleSubmissionRequest::query()
            ->whereIn('status', $statuses)
            ->whereNull('sample_header_id');
    }

    /**
     * Computed list of portal submission requests for the Sample Receiving Requests tab.
     * Shows SampleSubmissionRequest records that have been submitted but not yet assigned to a batch.
     */
    public function getPortalSubmissionsProperty()
    {
        if ($this->isSamplesReceiving() || ! $this->isReceivingFormsTab()) {
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
                    'Submitted',
                    'booking_date_approved',
                    'booking_date_rescheduled',
                    'in_review',
                    'In Review',
                    'pending_reception',
                    'received_at_lab',
                ])->orWhereHas('supportingDocumentInstances', function ($docQuery) {
                    $docQuery->whereIn('status', ['submitted', 'Submitted', 'in_review', 'In Review', 'approved', 'Approved']);
                });
            })
            ->where(function ($q) {
                $q->whereNull('sample_header_id')
                    ->orWhereHas('batch', function ($bq) {
                        $bq->whereIn('status', ['Samples En-Route', 'Samples Receiving', 'Samples Reception']);
                    });
            })
            ->orderByDesc('submitted_by_date');

        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('unique_identification', 'like', $search)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $search));
            });
        }

        $results = $query->paginate($this->batchesPerPage, ['*'], 'portal_submissions_page');

        \Illuminate\Support\Facades\Log::info('LIMS: Samples Reception querying for portal submissions (Requests).', [
            'status' => $this->status,
            'sub_tab' => $this->workflowSubTab,
            'results_count' => $results->total(),
        ]);

        return $results;
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

    /**
     * @param  array<int, string>  $ids  Checked instance IDs from the browser (deferred wire:model may not be synced yet).
     */
    public function openReceiveModal(array $ids = []): void
    {
        $this->syncSelectedFormInstanceIds($ids);

        if ($this->selectedFormInstanceIds !== []) {
            $checkInService = app(\App\Services\Sampleworkflow\SampleReceivingCheckInService::class);
            $blockedReasons = [];

            foreach ($this->selectedFormInstanceIds as $instanceId) {
                $instance = SubmissionFormInstance::query()->find($instanceId);
                if ($instance === null) {
                    continue;
                }

                if (! $checkInService->canReceiveInstance($instance)) {
                    $reason = $checkInService->receiveBlockReason($instance);
                    if ($reason !== null) {
                        $blockedReasons[] = $reason;
                    }
                }
            }

            if ($blockedReasons !== []) {
                session()->flash('error', collect($blockedReasons)->unique()->first());

                return;
            }
        }

        $summaries = [];
        if ($this->selectedFormInstanceIds !== []) {
            $summaries = $this->buildReceiveFormSummaries($this->selectedFormInstanceIds);
            $this->receiveFormSummaries = $summaries;
        } else {
            $this->receiveFormSummaries = [];
        }

        $this->dispatch('receive-modal-open',
            instanceIds: $this->selectedFormInstanceIds,
            summaries: $summaries,
        );
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function openPoCaptureFromInstances(array $ids = []): void
    {
        $enquiryId = $this->resolveEnquiryIdFromSelection($ids);

        if ($enquiryId === null) {
            session()->flash('error', 'Select a request with an accepted quotation to record PO and move to Ready for Reception.');

            return;
        }

        $this->openPoCaptureModal($enquiryId);
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function recordWalkInAcceptanceFromInstances(array $ids = []): void
    {
        $enquiryId = $this->resolveEnquiryIdFromSelection($ids);

        if ($enquiryId === null) {
            session()->flash('error', 'Select a walk-in request with a sent quotation to record acceptance.');

            return;
        }

        $enquiry = SampleSubmissionRequest::query()->find($enquiryId);

        if ($enquiry === null) {
            session()->flash('error', 'Enquiry not found.');

            return;
        }

        if (strtolower((string) ($enquiry->source_channel ?? '')) !== 'walk_in') {
            session()->flash('error', 'Walk-in acceptance only applies to in-person enquiries.');

            return;
        }

        try {
            app(\App\Services\Commercial\QuotationFromEnquiryService::class)->recordWalkInAcceptance($enquiry);
            session()->flash('message', 'Quotation accepted. Record the customer PO to move this request to Ready for Reception.');
        } catch (\Throwable $exception) {
            session()->flash('error', $exception->getMessage());
        }
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function openProcessEnquiryModal(array $ids = []): void
    {
        $enquiryId = $this->resolveEnquiryIdFromSelection($ids);

        if ($enquiryId === null && $ids !== []) {
            $enquiryId = $this->createCommercialEnquiryFromSelection($ids);
        }

        if ($enquiryId === null) {
            session()->flash('error', 'Could not open enquiry processing. Select a submitted test request with customer details.');

            return;
        }

        $this->dispatch('process-enquiry-open', enquiryId: $enquiryId)
            ->to(ProcessEnquiryWizard::class);
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function openProcessEnquiryFromInstances(array $ids = []): void
    {
        $this->openProcessEnquiryModal($ids);
    }

    public function openPoCaptureModal(string $enquiryId): void
    {
        $enquiry = SampleSubmissionRequest::query()->find($enquiryId);
        if ($enquiry === null || $enquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED) {
            session()->flash('error', 'PO can only be recorded for accepted quotations.');

            return;
        }

        $this->poCaptureEnquiryId = $enquiryId;
        $this->clientPoNumber = (string) ($enquiry->client_po_number ?? '');
        $this->poSkipped = (bool) $enquiry->po_skipped;
        $this->advancePaymentReference = (string) ($enquiry->advance_payment_reference ?? '');
        $this->showPoCaptureModal = true;
    }

    public function closePoCaptureModal(): void
    {
        $this->showPoCaptureModal = false;
        $this->poCaptureEnquiryId = null;
        $this->clientPoNumber = '';
        $this->poSkipped = false;
        $this->advancePaymentReference = '';
    }

    public function submitPoAndReadyForReception(): void
    {
        if ($this->poCaptureEnquiryId === null) {
            return;
        }

        $enquiry = SampleSubmissionRequest::query()->find($this->poCaptureEnquiryId);
        if ($enquiry === null) {
            $this->closePoCaptureModal();

            return;
        }

        try {
            app(EnquiryReceptionReadinessService::class)->markReadyForReception(
                $enquiry,
                (string) ($enquiry->accepted_quotation_header_id ?? $enquiry->current_quotation_header_id ?? ''),
                [
                    'client_po_number' => $this->clientPoNumber,
                    'po_skipped' => $this->poSkipped,
                    'advance_payment_reference' => $this->advancePaymentReference,
                ],
            );

            $this->closePoCaptureModal();
            session()->flash('message', 'PO recorded. Request is ready for physical reception.');
        } catch (Throwable $exception) {
            session()->flash('error', $exception->getMessage());
        }
    }

    /**
     * @param  array<int, string>  $ids
     */
    protected function resolveEnquiryIdFromSelection(array $ids): ?string
    {
        if ($ids !== []) {
            $firstId = trim((string) ($ids[0] ?? ''));
            if ($firstId === '') {
                return null;
            }

            if (SampleSubmissionRequest::query()->whereKey($firstId)->exists()) {
                return $firstId;
            }

            $this->syncSelectedFormInstanceIds($ids);

            $enquiryId = SubmissionFormInstance::query()
                ->whereIn('id', $this->selectedFormInstanceIds)
                ->with('sampleSubmissionRequest')
                ->get()
                ->map(fn (SubmissionFormInstance $instance) => $instance->sampleSubmissionRequest?->id)
                ->filter()
                ->first();

            return $enquiryId !== null ? (string) $enquiryId : null;
        }

        return null;
    }

    /**
     * @param  array<int, string>  $ids
     */
    protected function createCommercialEnquiryFromSelection(array $ids): ?string
    {
        $this->syncSelectedFormInstanceIds($ids);

        $instanceId = trim((string) ($this->selectedFormInstanceIds[0] ?? ''));
        if ($instanceId === '') {
            return null;
        }

        $instance = SubmissionFormInstance::query()
            ->with(['submissionForm', 'values.element', 'crmCustomer', 'testRequestFormInstance', 'sampleSubmissionRequest'])
            ->find($instanceId);

        if ($instance === null) {
            return null;
        }

        $enquiry = app(CommercialEnquiryFromFormService::class)->syncFromSubmittedInstance($instance);

        return $enquiry?->id !== null ? (string) $enquiry->id : null;
    }

    public function onProcessEnquiryCompleted(): void
    {
        // Livewire will re-render lists on next request; nothing else required.
    }

    #[On('process-enquiry-completed')]
    public function handleProcessEnquiryCompleted(): void
    {
        $this->onProcessEnquiryCompleted();
    }

    public function onReceiveCompleted(): void
    {
        $this->selectedFormInstanceIds = [];
        $this->receiveFormSummaries = [];
        $this->dispatch('hide-receive-sample-modal');
    }

    /**
     * @param  array<int, string>  $ids
     * @return array<int, array{id: string, label: string, customer: string}>
     */
    protected function buildReceiveFormSummaries(array $ids): array
    {
        return SubmissionFormInstance::query()
            ->with(['crmCustomer', 'submittedBy', 'submissionForm', 'sampleSubmissionRequest'])
            ->whereIn('id', $ids)
            ->get()
            ->map(function (SubmissionFormInstance $instance): array {
                return [
                    'id' => $instance->id,
                    'label' => (string) ($instance->canonicalFormNumber() ?: 'Pending'),
                    'customer' => (string) (
                        $instance->crmCustomer->name
                        ?? $instance->submittedBy->name
                        ?? ''
                    ),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $ids
     * @return array<int, array{id: string, label: string, assignee_name: string}>
     */
    protected function buildPendingIntrayAssignments(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return SubmissionFormInstance::query()
            ->with(['activePendingIntray.toUser'])
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn (SubmissionFormInstance $instance) => $instance->activePendingIntray !== null)
            ->map(function (SubmissionFormInstance $instance): array {
                return [
                    'id' => (string) $instance->id,
                    'label' => (string) ($instance->canonicalFormNumber() ?: 'Pending'),
                    'assignee_name' => (string) ($instance->activePendingIntray?->toUser?->name ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>|string  $ids
     */
    public function openMoveToIntrayModal(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids);

        if ($this->selectedFormInstanceIds === []) {
            return;
        }

        $summaries = $this->buildReceiveFormSummaries($this->selectedFormInstanceIds);
        $pending = $this->buildPendingIntrayAssignments($this->selectedFormInstanceIds);
        $this->intrayFormSummaries = $summaries;
        $this->pendingIntrayAssignments = $pending;

        $this->dispatch('intray-modal-open',
            instanceIds: $this->selectedFormInstanceIds,
            summaries: $summaries,
            assignableUsers: $this->assignableUsersForIntray,
            pendingIntrayAssignments: $pending,
        );
    }

    /**
     * @param  array<int, string>  $ids
     */
    /**
     * @param  array<int, string>|string  $ids
     */
    public function openRequestReviewModal(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids);

        if ($this->selectedFormInstanceIds === []) {
            return;
        }

        $summaries = $this->buildReceiveFormSummaries($this->selectedFormInstanceIds);

        $this->dispatch('analyst-review-modal-open',
            instanceIds: $this->selectedFormInstanceIds,
            summaries: $summaries,
        );
    }

    public function onAnalystReviewCompleted(): void
    {
        $this->selectedFormInstanceIds = [];
        $this->dispatch('hide-analyst-review-modal');
    }

    /**
     * @param  array<int, string>|string  $ids
     */
    public function openRequestAdditionalInfoModal(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids);

        if ($this->selectedFormInstanceIds === []) {
            return;
        }

        $summaries = $this->buildReceiveFormSummaries($this->selectedFormInstanceIds);

        $this->dispatch('request-additional-info-modal-open',
            instanceIds: $this->selectedFormInstanceIds,
            summaries: $summaries,
        );
    }

    public function onRequestAdditionalInfoCompleted(): void
    {
        $this->selectedFormInstanceIds = [];
        $this->dispatch('hide-request-additional-info-modal');
    }

    public function openAcceptSampleWizardFromSelection(): void
    {
        if (count($this->selectedFormInstanceIds) !== 1) {
            $this->dispatch('notify', type: 'error', message: 'Please select exactly one submission request or form row before accepting.');

            return;
        }

        $this->dispatch(
            'open-acceptance-wizard',
            submissionFormInstanceId: $this->selectedFormInstanceIds[0],
            submissionRequestId: $this->resolveSubmissionRequestIdForFormInstance($this->selectedFormInstanceIds[0]),
        )->to(AcceptanceFormWizard::class);
    }

    public function openRejectSampleWizardFromSelection(): void
    {
        if (count($this->selectedFormInstanceIds) !== 1) {
            $this->dispatch('notify', type: 'error', message: 'Please select exactly one submission request or form row before rejecting.');

            return;
        }

        $this->dispatch(
            'open-rejection-wizard',
            submissionFormInstanceId: $this->selectedFormInstanceIds[0],
            submissionRequestId: $this->resolveSubmissionRequestIdForFormInstance($this->selectedFormInstanceIds[0]),
        )->to(SampleRejectionWizard::class);
    }

    /**
     * @param  array<int, string>|string  $ids
     */
    public function openAcceptSampleWizard(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids);

        if ($this->selectedFormInstanceIds === [] || count($this->selectedFormInstanceIds) > 1) {
            $this->dispatch('notify', type: 'error', message: 'Please select exactly one submission request or form row before accepting.');

            return;
        }

        $this->dispatch(
            'open-acceptance-wizard',
            submissionFormInstanceId: $this->selectedFormInstanceIds[0],
            submissionRequestId: $this->resolveSubmissionRequestIdForFormInstance($this->selectedFormInstanceIds[0]),
        )->to(AcceptanceFormWizard::class);
    }

    /**
     * @param  array<int, string>|string  $ids
     */
    public function openRejectSampleWizard(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids);

        if ($this->selectedFormInstanceIds === [] || count($this->selectedFormInstanceIds) > 1) {
            $this->dispatch('notify', type: 'error', message: 'Please select exactly one submission request or form row before rejecting.');

            return;
        }

        $this->dispatch(
            'open-rejection-wizard',
            submissionFormInstanceId: $this->selectedFormInstanceIds[0],
            submissionRequestId: $this->resolveSubmissionRequestIdForFormInstance($this->selectedFormInstanceIds[0]),
        )->to(SampleRejectionWizard::class);
    }

    protected function resolveSubmissionRequestIdForFormInstance(string $formInstanceId): ?string
    {
        $formInstanceId = trim($formInstanceId);
        if ($formInstanceId === '') {
            return null;
        }

        $instance = SubmissionFormInstance::query()
            ->with(['sampleSubmissionRequest', 'testRequestFormInstance.sampleSubmissionRequest'])
            ->find($formInstanceId);

        if ($instance === null) {
            return null;
        }

        $enquiryId = $instance->sampleSubmissionRequest?->id
            ?? $instance->testRequestFormInstance?->sample_submission_request_id
            ?? $instance->testRequestFormInstance?->sampleSubmissionRequest?->id;

        if ($enquiryId !== null) {
            return (string) $enquiryId;
        }

        $linkedId = SampleSubmissionRequest::query()
            ->where('submission_form_instance_id', $instance->id)
            ->value('id');

        return $linkedId !== null ? (string) $linkedId : null;
    }

    /**
     * @param  array<int, string>  $ids
     */
    protected function syncSelectedFormInstanceIds(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $this->selectedFormInstanceIds = array_values(array_unique(array_filter(
            array_map(static fn ($id): string => trim((string) $id), $ids),
            static fn (string $id): bool => $id !== '',
        )));
    }

    public function onIntrayMoveCompleted(): void
    {
        $this->selectedFormInstanceIds = [];
        $this->intrayFormSummaries = [];
        $this->pendingIntrayAssignments = [];
        $this->dispatch('hide-move-to-intray-modal');
    }

    public function openDecontaminationModal(): void
    {
        $this->resetDecontaminationForm();
        $this->showDecontaminationModal = true;
    }

    public function closeDecontaminationModal(): void
    {
        $this->showDecontaminationModal = false;
        $this->resetValidation();
    }

    public function updatedDecontaminationLabId(): void
    {
        $this->decontaminationSwabbing = [];
        $this->resetValidation();
    }

    public function getDecontaminationLabsProperty(): Collection
    {
        return Lab::query()
            ->where('active', 1)
            ->whereHas('decontaminationAreas')
            ->with(['decontaminationAreas.labSection'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<string, Collection<int, LabDecontaminationArea>>
     */
    public function getSelectedLabDecontaminationAreasGroupedProperty(): Collection
    {
        if ($this->decontaminationLabId === '') {
            return collect();
        }

        $lab = $this->decontaminationLabs->firstWhere('id', $this->decontaminationLabId);
        if (! $lab) {
            return collect();
        }

        return $lab->decontaminationAreas
            ->sortBy(fn (LabDecontaminationArea $area) => strtolower((string) $area->name))
            ->groupBy(function (LabDecontaminationArea $area): string {
                if ($area->labSection) {
                    return trim(($area->labSection->code ? $area->labSection->code.' - ' : '').$area->labSection->name);
                }

                return 'Unassigned Section';
            });
    }

    public function saveDecontaminationLog(): void
    {
        $areas = $this->selectedLabDecontaminationAreasGrouped
            ->flatten(1)
            ->values();

        $rules = [
            'decontaminationDate' => ['required', 'date'],
            'decontaminationOfficer' => ['required', 'string', 'max:255'],
            'decontaminationLabId' => ['required', 'uuid', 'exists:labs,id'],
        ];

        foreach ($areas as $area) {
            $rules['decontaminationSwabbing.'.$area->id] = ['required', 'in:yes,no'];
        }

        $this->validate($rules);

        DB::transaction(function () use ($areas): void {
            $log = SampleWorkflowDecontaminationLog::create([
                'log_date' => $this->decontaminationDate,
                'officer_name' => trim($this->decontaminationOfficer),
                'lab_id' => $this->decontaminationLabId,
                'status' => 'saved',
                'company_id' => getUserCompany(),
                'created_by' => Auth::id(),
            ]);

            foreach ($areas as $area) {
                SampleWorkflowDecontaminationLogItem::create([
                    'decontamination_log_id' => $log->id,
                    'lab_section_id' => $area->lab_section_id,
                    'lab_decontamination_area_id' => $area->id,
                    'swabbing' => ($this->decontaminationSwabbing[$area->id] ?? 'no') === 'yes',
                ]);
            }
        });

        session()->flash('success', 'Decontamination samples log saved successfully.');
        $this->showDecontaminationModal = false;
        $this->resetDecontaminationForm();
    }

    protected function resetDecontaminationForm(): void
    {
        $this->decontaminationDate = now()->toDateString();
        $this->decontaminationOfficer = Auth::user()?->name ?? '';
        $this->decontaminationLabId = '';
        $this->decontaminationSwabbing = [];
        $this->resetValidation();
    }

    public function completeIntray(string $instanceId): void
    {
        $user = Auth::user();
        if ($user === null) {
            session()->flash('error', 'You must be signed in to complete intray tasks.');

            return;
        }

        try {
            app(SubmissionFormIntrayService::class)->completePending($instanceId, $user);
            session()->flash('success', 'Intray task marked as complete.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            session()->flash('error', collect($exception->errors())->flatten()->first() ?? 'Unable to complete intray task.');
        }
    }

    public function toggleMyIntrayPanel(): void
    {
        $this->showMyIntrayPanel = ! $this->showMyIntrayPanel;
    }

    public function getMyPendingIntrayFormsProperty(): \Illuminate\Support\Collection
    {
        if ((! $this->isSamplesReceiving() && ! $this->isSamplesRequestReview()) || ! Auth::check()) {
            return collect();
        }

        return app(SubmissionFormIntrayService::class)->getPendingForUser((string) Auth::id());
    }

    public function getMyPendingIntrayCountProperty(): int
    {
        return $this->myPendingIntrayForms->count();
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function getAssignableUsersForIntrayProperty(): array
    {
        $this->ensureReferenceDataLoaded();

        $currentUserId = Auth::id();

        return $this->users
            ->filter(fn ($user) => (string) $user->id !== (string) $currentUserId)
            ->map(fn ($user) => [
                'id' => (string) $user->id,
                'name' => (string) $user->name,
            ])
            ->values()
            ->all();
    }

    protected function getListeners(): array
    {
        return [
            'receive-completed' => 'onReceiveCompleted',
            'intray-move-completed' => 'onIntrayMoveCompleted',
            'request-additional-info-completed' => 'onRequestAdditionalInfoCompleted',
            'analyst-review-completed' => 'onAnalystReviewCompleted',
            'interzone-transfer-completed' => '$refresh',
            'process-enquiry-completed' => '$refresh',
        ];
    }

    public function render()
    {
        // Reference data is loaded here (not in mount) so it is never written into
        // the Livewire encrypted snapshot, which would bloat every request/response.
        $this->loadReferenceData();

        // Compute once to avoid running the query twice (tatTodayCount calls tatTodayBatches).
        $tatTodayBatches = $this->tatTodayBatches;

        $natureOfSampleOptions = \App\Models\System\SystemConfigurationsType::where('configuration_type', 'Nature of Sample')
            ->with('configurations')
            ->first()
            ?->configurations
            ?->sortBy('key')
            ?->values()
            ?? collect();

        return view('livewire.sampleworkflow.workflow-board', [
            'batches' => $this->batches,
            'natureOfSampleOptions' => $natureOfSampleOptions,
            'labsections' => $this->labsections,
            'analysts' => $this->analysts,
            'users' => $this->users,
            'zoho_items' => $this->zohoItems,
            'clients' => $this->clients,
            'sampletypes' => $this->sampletypes,
            'customers' => $this->customers,
            'submissionFormAttachmentTypeId' => $this->submissionFormAttachmentTypeId,
            'portalSubmissions' => $this->portalSubmissions,
            'commercialEnquiries' => $this->commercialEnquiries,
            'samplesReceptionStats' => $this->samplesReceptionStats,
            'receivingRequestTabs' => self::receivingRequestTabs(),
            'receivingRequestTabCounts' => $this->receivingRequestTabCounts,
            'requestReviewTabs' => self::requestReviewTabs(),
            'requestReviewTabCounts' => $this->requestReviewTabCounts,
            'myPendingIntrayForms' => $this->myPendingIntrayForms,
            'myPendingIntrayCount' => $this->myPendingIntrayCount,
            'assignableUsersForIntray' => $this->assignableUsersForIntray,
            'tatTodayBatches' => $tatTodayBatches,
            'tatTodayCount' => $tatTodayBatches->count(),
        ]);
    }
}
