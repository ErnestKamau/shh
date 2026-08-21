<?php

namespace App\Livewire\Sampleworkflow;

use App\InventorySubCategories;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\SampleAnalysisStage;
use App\SampleDate;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use App\Models\System\SystemConfiguration;
use App\Models\SubmissionForm;
use App\User;
use App\Models\SubmissionFormInstance;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\SampleWorkflowDecontaminationLog;
use App\Models\Sampleworkflow\SampleWorkflowDecontaminationLogItem;
use App\Services\Commercial\CommercialEnquiryFromFormService;
use App\Services\Commercial\EnquiryAccountSettingsService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Livewire\Sampleworkflow\ProcessEnquiryWizard;
use App\Services\SubmissionForm\SubmissionFormIntrayService;
use App\Lab;
use App\LabDecontaminationArea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Support\VarcharUuidSql;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use Throwable;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithPagination;

class WorkflowBoard extends Component
{
    use AppliesCaseInsensitiveSearch;
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
            'sample_integrity_check' => 'Sample Integrity & Acceptance Check',
            'accepted' => 'Accepted',
            'in_additional_info' => 'Request Additional Info',
            // 'complete' => 'Complete Requests',
            'sub_contracting' => 'Sub-contracting',
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
     * Synced to `?tab=` so breadcrumb deep-links stay accurate when switching tabs.
     */
    public string $workflowSubTab = 'requests';

    public string $subcontractingDispatchStatus = SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING;

    /**
     * Keep the active receiving / review sub-tab in the browser URL.
     *
     * @return array<string, array{as: string, history: bool, except: string}>
     */
    protected function queryString(): array
    {
        return [
            'workflowSubTab' => [
                'as' => 'tab',
                'history' => true,
                'except' => $this->defaultWorkflowSubTab(),
            ],
        ];
    }

    /**
     * Default sub-tab for the current workflow status (omitted from the URL via queryString except).
     */
    protected function defaultWorkflowSubTab(): string
    {
        $status = $this->status !== ''
            ? $this->status
            : rawurldecode((string) (request()->route('status') ?? ''));

        if ($status === 'Samples Receiving') {
            return 'submitted';
        }

        if ($status === 'Samples Request Review') {
            return 'in_review';
        }

        return 'requests';
    }

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
    public string $amendmentFilter = 'all';
    
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
     * Collapsible filters panel for request tables (closed by default).
     *
     * @deprecated Panel open/close is Alpine-only; kept for snapshot compatibility.
     */
    public bool $showFiltersPanel = false;

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

    public bool $showQuotationAcceptanceModal = false;

    /** When true, modal only captures PO for an already-accepted quotation. */
    public bool $quotationAcceptancePoOnly = false;

    public ?string $quotationAcceptanceEnquiryId = null;

    public string $quotationAcceptanceSignerName = '';

    public string $quotationAcceptanceSignature = '';

    public ?string $quotationAcceptanceContactId = null;

    /** Contact the currently held signature belongs to, so re-sent updates don't reset the pad. */
    #[Locked]
    public string $quotationAcceptanceAppliedContactId = '';

    /** @var list<array{id: string, label: string}> */
    public array $quotationAcceptanceContactOptions = [];

    public string $clientPoNumber = '';

    public string $poRuleType = 'walk_in';

    public bool $poRequiresPo = false;

    public string $poRuleMessage = '';


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

    /** @var array<string, string> */
    public array $subcontractingDispatchStatuses = [
        SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING => 'Awaiting dispatch',
        SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED => 'Dispatched & assigned',
    ];

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
            // Legacy In Review folds into Integrity Accept; received → Ready for Reception rename path.
            if ($requestedTab === 'in_review') {
                $requestedTab = 'sample_integrity_check';
            }
            if ($requestedTab === 'received') {
                $requestedTab = 'ready_for_reception';
            }
            $this->workflowSubTab = in_array($requestedTab, array_keys(self::receivingRequestTabs()), true)
                ? $requestedTab
                : $this->defaultWorkflowSubTab();
        } elseif ($this->status === 'Samples Request Review') {
            $legacyTabMap = ['requests' => 'in_review', 'received' => 'accepted'];
            $requestedTab = $legacyTabMap[$requestedTab] ?? $requestedTab;
            $this->workflowSubTab = in_array($requestedTab, array_keys(self::requestReviewTabs()), true)
                ? $requestedTab
                : $this->defaultWorkflowSubTab();
        } else {
            $this->workflowSubTab = in_array($requestedTab, ['requests', 'received'], true)
                ? $requestedTab
                : $this->defaultWorkflowSubTab();
        }

        $this->allFilter = $this->defaultAllSamplesFilter();
        $this->finishedFilter = $this->defaultFinishedSamplesFilter();

        $this->hydrateFiltersFromRequest();
        $this->submissionFormAttachmentTypeId = $this->resolveSubmissionFormAttachmentTypeId();

        $this->backfillDispatchedSubcontractJobs();
    }

    protected function backfillDispatchedSubcontractJobs(): void
    {
        if (! Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_status')
            || ! Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_date')) {
            return;
        }

        $dispatchedWithoutJob = SampleSubmissionRequest::query()
            ->whereIn('subcontracting_dispatch_status', SampleSubmissionRequest::subcontractDispatchCompletedStatuses())
            ->where(function ($query): void {
                $query->whereNull('sample_header_id');
            })
            ->orderByDesc('subcontracting_dispatch_date')
            ->orderByDesc('updated_at')
            ->limit(250)
            ->get();

        if ($dispatchedWithoutJob->isEmpty()) {
            return;
        }

        foreach ($dispatchedWithoutJob as $enquiry) {
            try {
                $instance = $enquiry->resolveLinkedFormInstance();
                if (! $instance) {
                    continue;
                }

                $rawSubmissionFormInstanceId = (string) ($enquiry->submission_form_instance_id ?? '');
                $normalizedSubmissionFormInstanceId = trim($rawSubmissionFormInstanceId);

                if ($rawSubmissionFormInstanceId !== $normalizedSubmissionFormInstanceId) {
                    $enquiry->submission_form_instance_id = $normalizedSubmissionFormInstanceId !== ''
                        ? $normalizedSubmissionFormInstanceId
                        : null;
                    $enquiry->save();
                }

                $instance->loadMissing('batches');

                $hasExistingJob = ! empty($enquiry->sample_header_id)
                    || ! empty($instance->analysisAcceptanceForms()->value('sample_header_id'));

                if ($hasExistingJob) {
                    $linkedBatchId = (string) ($enquiry->sample_header_id
                        ?: $instance->analysisAcceptanceForms()->value('sample_header_id')
                        ?: optional($instance->batches->first())->id
                    );

                    if ($linkedBatchId !== '') {
                        $batch = SampleHeader::query()->find($linkedBatchId);
                        if ($batch) {
                            $batch->status = 'Samples In Lab';
                            $batch->prelim_batch_status = null;
                            $batch->in_lab_date = $batch->in_lab_date ?: now()->format('Y-m-d');
                            $batch->save();

                            if (! $enquiry->sample_header_id) {
                                $enquiry->sample_header_id = $batch->id;
                                $enquiry->status = SampleSubmissionRequest::STATUS_ACCEPTED;
                                $enquiry->save();
                            }
                        }
                    }

                    continue;
                }

                $existingBatch = $instance->batches->first();
                if ($existingBatch) {
                    $existingBatch->status = 'Samples In Lab';
                    $existingBatch->prelim_batch_status = null;
                    $existingBatch->in_lab_date = $existingBatch->in_lab_date ?: now()->format('Y-m-d');
                    $existingBatch->save();

                    $enquiry->sample_header_id = $existingBatch->id;
                    $enquiry->status = SampleSubmissionRequest::STATUS_ACCEPTED;
                    $enquiry->save();

                    continue;
                }

                $prefill = app(AcceptanceFormPricingService::class)->buildPrefillFromSelection(
                    (string) $enquiry->id,
                    (string) $instance->id,
                );

                $lines = $prefill['lines'] ?? [];
                if (! is_array($lines) || $lines === []) {
                    continue;
                }

                $signerName = (string) (Auth::user()?->name ?? 'System Backfill');
                $createdBy = Auth::id() ? (string) Auth::id() : null;

                app(AcceptanceFormService::class)->acceptWithStaffSignature(
                    (string) $instance->id,
                    (string) $enquiry->id,
                    [
                        'crm_customer_id' => $prefill['customer_id'] ?? $enquiry->crm_customer_id,
                        'customer_name' => $prefill['customer_name'] ?? ($instance->crmCustomer?->name ?? ''),
                        'request_date' => $prefill['request_date'] ?? now()->format('Y-m-d'),
                        'number_of_samples' => (int) ($prefill['number_of_samples'] ?? max(1, (int) ($enquiry->number_of_samples ?? 1))),
                        'mode_of_work' => $prefill['mode_of_work'] ?? 'Normal',
                        'date_of_sampling' => $prefill['date_of_sampling'] ?? null,
                    ],
                    $lines,
                    $signerName,
                    'subcontract-dispatch-backfill-signature',
                    now()->toDateString(),
                    $createdBy,
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }

    public function openPendingReceiveRequestIfNeeded(): void
    {
        // Intentionally no-op: the Test Request modal must only open when the user
        // presses Receive Request, never automatically on Samples Receiving load.
        $this->openReceiveRequestPending = false;
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
            // Defer full user/customer lists — form-only stages only need sample types for filters.
            // Customers are queried on demand in filteredCustomers; users load when assign modals need them.
            $this->users = collect();
            $this->clients = collect();
            $this->customers = collect();
            $this->sampletypes = Cache::remember(
                'workflow-board:active-sample-types',
                now()->addMinutes(10),
                fn () => SampleType::where('active', 1)->orderBy('name')->get()
            );

            return;
        }

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

        $this->users = User::query()
            ->where('active', 1)
            ->where(function ($query): void {
                $query->where('is_client', 0)
                    ->orWhereNull('is_client');
            })
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
        if (! isset($this->users) || $this->users->isEmpty()) {
            $this->users = User::query()
                ->where('active', 1)
                ->where(function ($query): void {
                    $query->where('is_client', 0)
                        ->orWhereNull('is_client');
                })
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        if (! isset($this->clients) || $this->clients->isEmpty()) {
            $this->clients = CRMCustomer::where('active', 1)->orderBy('name')->get();
            $this->customers = $this->clients;
        }

        if (! isset($this->sampletypes)) {
            $this->sampletypes = SampleType::where('active', 1)->orderBy('name')->get();
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

        // Mirror legacy All Samples query params into Livewire board filters.
        $customerId = $this->allFilter['customer_id'] ?? 'All';
        if ($customerId && $customerId !== 'All') {
            $this->customerFilter = (int) $customerId;
        }

        $sampleTypeId = $this->allFilter['sample_type_id'] ?? 'All';
        if ($sampleTypeId && $sampleTypeId !== 'All') {
            $this->sampleTypeFilter = (int) $sampleTypeId;
        }

        if (! empty($this->allFilter['receipt_date_from'])) {
            $this->receiptDateFrom = (string) $this->allFilter['receipt_date_from'];
        }

        if (! empty($this->allFilter['receipt_date_to'])) {
            $this->receiptDateTo = (string) $this->allFilter['receipt_date_to'];
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
            fn (string $key) => ! in_array($key, ['interzone_transfers', 'ready_for_reception', 'sample_integrity_check', 'sub_contracting', 'accepted'], true)
        ));
    }

    /**
     * @deprecated Prefer ready_for_reception; In Review tab was removed.
     */
    public static function legacyInReviewTabKey(): string
    {
        return 'ready_for_reception';
    }

    /**
     * Base query for submission form instances on the Samples Receiving board.
     */
    public static function receivingSubmissionFormsQuery(?array $statuses = null): \Illuminate\Database\Eloquent\Builder
    {
        static $templateFormIdsCache = null;
        static $linkedInstanceIdsCache = null;

        if ($templateFormIdsCache === null) {
            $templateFormIdsCache = SubmissionForm::query()
                ->where('form_type', 'template')
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();
        }

        $templateFormIds = $templateFormIdsCache;

        if ($templateFormIds === []) {
            return SubmissionFormInstance::query()->where('id', '00000000-0000-0000-0000-000000000000');
        }

        if ($linkedInstanceIdsCache === null) {
            $linkedInstanceIdsCache = SampleHeader::query()
                ->whereNotIn('status', ['Samples En-Route', 'Samples Receiving', 'Samples Reception'])
                ->pluck('submission_form_instance_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();
        }

        $linkedInstanceIds = $linkedInstanceIdsCache;

        $query = SubmissionFormInstance::query()
            ->where(function ($templateQuery) use ($templateFormIds): void {
                foreach ($templateFormIds as $index => $templateFormId) {
                    if ($index === 0) {
                        $templateQuery->where('submission_form_id', $templateFormId);

                        continue;
                    }

                    $templateQuery->orWhere('submission_form_id', $templateFormId);
                }
            });

        if ($linkedInstanceIds !== []) {
            $query->whereNotIn('id', $linkedInstanceIds);
        }

        $query->whereIn('status', $statuses ?? self::receivingRequestStatusKeys());

        return $query;
    }

    /**
     * Sidebar badge: commercial pipeline + analyst review queue (formerly Samples Request Review).
     */
    public static function sidebarReceivingRequestCount(): int
    {
        $pipeline = (int) self::receivingSubmissionFormsQuery(['submitted', 'in_additional_info'])->count();

        return $pipeline;
    }

    /**
     * @deprecated Request Review stage is folded into Samples Receiving → Ready for Reception / Accepted.
     */
    public static function sidebarRequestReviewCount(): int
    {
        return self::sidebarReceivingRequestCount();
    }

    /**
     * True when Accept / Reject run on Receiving → Ready for Reception.
     */
    public function isReceivingAnalystReviewTab(): bool
    {
        return $this->isSamplesReceiving() && $this->workflowSubTab === 'ready_for_reception';
    }

    /**
     * True for Receiving → Ready for Reception or Accepted (accept/reject outcomes UI).
     */
    public function isReceivingReviewOutcomeTab(): bool
    {
        return $this->isSamplesReceiving()
            && in_array($this->workflowSubTab, ['ready_for_reception', 'sample_integrity_check', 'accepted'], true);
    }

    protected function receivingSubmissionFormsBaseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return static::receivingSubmissionFormsQuery();
    }

    /**
     * Submission forms awaiting Receive Samples (Ready for Reception handoff into Integrity).
     * Subcontract dispatch is no longer a blocker here — it is enforced at Integrity Accept.
     */
    protected function readyForPhysicalReceptionSubmissionFormsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->receivingSubmissionFormsBaseQuery()
            ->whereDoesntHave('batches', function ($batchQuery) {
                $batchQuery->whereNotIn('status', ['Samples En-Route', 'Samples Receiving', 'Samples Reception']);
            })
            ->whereDoesntHave('analysisAcceptanceForms', function ($acceptanceQuery): void {
                $acceptanceQuery->where('status', \App\Models\Sampleworkflow\AnalysisAcceptanceForm::STATUS_COMPLETED);
            });

        $query->where(function ($outer): void {
            $outer->where(function ($ready): void {
                $ready->whereIn('status', ['submitted', 'Submitted'])
                    ->whereHas('sampleSubmissionRequest', function ($enquiryQuery): void {
                        $enquiryQuery->where('status', SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION);
                    });
            });
        });

        return $query;
    }

    /**
     * Submission forms in Sample Integrity & Acceptance Check (after Receive Samples handoff).
     * Subcontracted work may still be pending dispatch; Accept is not blocked by that.
     */
    protected function sampleIntegrityCheckSubmissionFormsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->receivingSubmissionFormsBaseQuery()
            ->whereDoesntHave('batches', function ($batchQuery) {
                $batchQuery->whereNotIn('status', ['Samples En-Route', 'Samples Receiving', 'Samples Reception']);
            })
            ->whereDoesntHave('analysisAcceptanceForms', function ($acceptanceQuery): void {
                $acceptanceQuery->where('status', \App\Models\Sampleworkflow\AnalysisAcceptanceForm::STATUS_COMPLETED);
            })
            ->where(function ($outer): void {
                $outer->where(function ($integrity): void {
                    $integrity->whereIn('status', ['submitted', 'Submitted', 'received', 'in_review', 'In Review'])
                        ->whereHas('sampleSubmissionRequest', function ($enquiryQuery): void {
                            $enquiryQuery->where('status', SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK);
                        });
                })->orWhere(function ($legacy): void {
                    // Former In Review queue (physical check-in already done) lands here for Accept.
                    $legacy->whereIn('status', ['in_review', 'In Review', 'received'])
                        ->where(function ($legacyInner): void {
                            $legacyInner->whereDoesntHave('sampleSubmissionRequest')
                                ->orWhereHas('sampleSubmissionRequest', function ($enquiryQuery): void {
                                    $enquiryQuery->where('status', SampleSubmissionRequest::STATUS_IN_REVIEW);
                                });
                        });
                });
            });
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
                SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK,
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

    /**
     * Sub-contracting queue: requests with at least one subcontracted parameter
     * (master flag or Integrity Check `subcontracted_parameter_keys`).
     *
     * Membership is driven by subcontracting_dispatch_status, not by AmSpec
     * acceptance. Awaiting includes post-accept enquiries (STATUS_ACCEPTED /
     * received_at_lab = accepted at AmSpec, NOT received by a subcontract lab)
     * and does not exclude instances whose batch is already in Samples In Lab.
     */
    protected function subcontractingSubmissionFormsQuery(?string $dispatchStatusOverride = null): \Illuminate\Database\Eloquent\Builder
    {
        $driver = DB::connection()->getDriverName();
        $hasTestRequestFormInstanceId = false /* legacy test_request_form_instance_id removed */;

        if (! Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_status')) {
            return SubmissionFormInstance::query()->where('id', '00000000-0000-0000-0000-000000000000');
        }

        $queueEnquiryStatuses = SampleSubmissionRequest::subcontractingQueueEnquiryStatuses();
        $dispatchStatus = in_array($dispatchStatusOverride, [
            SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING,
            SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED,
        ], true)
            ? $dispatchStatusOverride
            : $this->normalizedSubcontractingDispatchStatus();
        $isDispatchedFilter = $dispatchStatus === SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED;
        $instanceStatuses = ['submitted', 'Submitted', 'received', 'approved', 'in_review', 'In Review'];
        $completedDispatchStatuses = SampleSubmissionRequest::subcontractDispatchCompletedStatuses();

        // Wide template-instance base for both Awaiting and Dispatched so AmSpec-accepted
        // batches already in Samples In Lab remain visible while subcontract dispatch is pending.
        $baseQuery = SubmissionFormInstance::query()->whereHas('submissionForm', function ($formQuery): void {
            $formQuery->where('form_type', 'template');
        });

        return $baseQuery
            ->whereIn('status', $instanceStatuses)
            ->where(function ($instanceQuery) use ($driver, $hasTestRequestFormInstanceId, $queueEnquiryStatuses, $isDispatchedFilter, $completedDispatchStatuses): void {
                if ($isDispatchedFilter) {
                    $instanceQuery->whereHas('sampleSubmissionRequest', function ($enquiryQuery) use ($completedDispatchStatuses): void {
                        $enquiryQuery->whereIn('subcontracting_dispatch_status', $completedDispatchStatuses);
                    })->orWhereExists(function ($fallbackQuery) use ($driver, $hasTestRequestFormInstanceId, $completedDispatchStatuses): void {
                        $fallbackQuery->selectRaw('1')
                            ->from('sample_submission_requests as ssr')
                            ->whereIn('ssr.subcontracting_dispatch_status', $completedDispatchStatuses)
                            ->where(function ($linkQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                                $this->constrainSubcontractingEnquiryInstanceLink(
                                    $linkQuery,
                                    $driver,
                                    $hasTestRequestFormInstanceId
                                );
                            });
                    });

                    return;
                }

                $instanceQuery
                    ->whereHas('sampleSubmissionRequest', function ($enquiryQuery) use ($queueEnquiryStatuses): void {
                        $enquiryQuery
                            ->whereIn('status', $queueEnquiryStatuses)
                            ->whereSubcontractDispatchPending();
                    })
                    ->orWhereExists(function ($fallbackQuery) use ($driver, $hasTestRequestFormInstanceId, $queueEnquiryStatuses): void {
                        $fallbackQuery->selectRaw('1')
                            ->from('sample_submission_requests as ssr')
                            ->whereIn('ssr.status', $queueEnquiryStatuses)
                            ->where(function ($pendingDispatch): void {
                                $pendingDispatch
                                    ->whereNull('ssr.subcontracting_dispatch_status')
                                    ->orWhere('ssr.subcontracting_dispatch_status', '')
                                    ->orWhere(
                                        'ssr.subcontracting_dispatch_status',
                                        SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING
                                    );
                            })
                            ->where(function ($linkQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                                $this->constrainSubcontractingEnquiryInstanceLink(
                                    $linkQuery,
                                    $driver,
                                    $hasTestRequestFormInstanceId
                                );
                            })
                            ->where(function ($matchQuery) use ($driver): void {
                                $this->constrainSubcontractedWorkExists($matchQuery, $driver);
                            });
                    });
            })
            ->where(function ($dispatchQuery) use ($dispatchStatus, $driver, $hasTestRequestFormInstanceId): void {
                if ($dispatchStatus === SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED) {
                    $completedStatuses = SampleSubmissionRequest::subcontractDispatchCompletedStatuses();
                    $dispatchQuery->whereHas('sampleSubmissionRequest', function ($enquiryQuery) use ($completedStatuses): void {
                        $enquiryQuery->whereIn('subcontracting_dispatch_status', $completedStatuses);
                    })->orWhereExists(function ($fallbackQuery) use ($driver, $hasTestRequestFormInstanceId, $completedStatuses): void {
                        $fallbackQuery->selectRaw('1')
                            ->from('sample_submission_requests as ssr')
                            ->whereIn('ssr.subcontracting_dispatch_status', $completedStatuses)
                            ->where(function ($linkQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                                $this->constrainSubcontractingEnquiryInstanceLink(
                                    $linkQuery,
                                    $driver,
                                    $hasTestRequestFormInstanceId
                                );
                            });
                    });

                    return;
                }

                $dispatchQuery->where(function ($awaitingQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                    $awaitingQuery->whereHas('sampleSubmissionRequest', function ($enquiryQuery): void {
                        $enquiryQuery->where(function ($statusQuery): void {
                            $statusQuery
                                ->whereNull('subcontracting_dispatch_status')
                                ->orWhere('subcontracting_dispatch_status', '')
                                ->orWhere('subcontracting_dispatch_status', SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING);
                        });
                    })->orWhereExists(function ($fallbackQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                        $fallbackQuery->selectRaw('1')
                            ->from('sample_submission_requests as ssr')
                            ->where(function ($statusQuery): void {
                                $statusQuery
                                    ->whereNull('ssr.subcontracting_dispatch_status')
                                    ->orWhere('ssr.subcontracting_dispatch_status', '')
                                    ->orWhere('ssr.subcontracting_dispatch_status', SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING);
                            })
                            ->where(function ($linkQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                                $this->constrainSubcontractingEnquiryInstanceLink(
                                    $linkQuery,
                                    $driver,
                                    $hasTestRequestFormInstanceId
                                );
                            });
                    });
                });
            });
    }

    /**
     * Link sample_submission_requests (ssr) to submission_form_instances for subcontract queries.
     */
    protected function constrainSubcontractingEnquiryInstanceLink(
        \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $linkQuery,
        string $driver,
        bool $hasTestRequestFormInstanceId,
    ): void {
        $linkQuery
            ->whereRaw(VarcharUuidSql::equals('ssr.submission_form_instance_id', 'submission_form_instances.id'))
            ->when($hasTestRequestFormInstanceId, function ($query): void {
                $query->orWhereRaw(VarcharUuidSql::equals('ssr.test_request_form_instance_id', 'submission_form_instances.id'));
            })
            ->orWhere(function ($portalLink) use ($driver): void {
                $portalLink->when($driver === 'pgsql', function ($query): void {
                    $query->whereRaw('ssr.id::text = submission_form_instances.portal_request_id');
                }, function ($query): void {
                    $query->whereColumn('ssr.id', 'submission_form_instances.portal_request_id');
                });
            })
            ->orWhere(function ($targetLink) use ($driver): void {
                $targetLink->whereRaw("LOWER(COALESCE(submission_form_instances.target_record_type, '')) IN ('sample_submission_request', 'sample_submission_requests')")
                    ->when($driver === 'pgsql', function ($query): void {
                        $query->whereRaw('ssr.id::text = submission_form_instances.target_record_id::text');
                    }, function ($query): void {
                        $query->whereColumn('ssr.id', 'submission_form_instances.target_record_id');
                    });
            });
    }

    /**
     * SQL match for subcontracted work on sample_submission_requests as ssr (fallback exists).
     */
    protected function constrainSubcontractedWorkExists(
        \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $matchQuery,
        string $driver,
    ): void {
        $matchQuery
            ->whereExists(function ($analysisExists): void {
                $analysisExists->selectRaw('1')
                    ->from('sample_submission_request_requested_analyses as ra')
                    ->join('analysis_elements as ae', 'ae.id', '=', 'ra.analysis_element_id')
                    ->whereColumn('ra.sample_submission_request_id', 'ssr.id')
                    ->where('ae.sub_contracted', 1);
            })
            ->orWhere(function ($configExists) use ($driver): void {
                if ($driver !== 'pgsql') {
                    $configExists->whereNotNull('ssr.enquiry_sample_configuration')
                        ->where('ssr.enquiry_sample_configuration', '!=', '[]')
                        ->where('ssr.enquiry_sample_configuration', 'like', '%subcontracted_parameter_keys%')
                        ->where('ssr.enquiry_sample_configuration', 'not like', '%"subcontracted_parameter_keys":[]%')
                        ->where('ssr.enquiry_sample_configuration', 'not like', '%"subcontracted_parameter_keys": []%');

                    return;
                }

                $configExists->whereRaw(<<<'SQL'
EXISTS (
    SELECT 1
    FROM jsonb_array_elements(COALESCE(ssr.enquiry_sample_configuration::jsonb, '[]'::jsonb)) AS cfg
    WHERE jsonb_typeof(COALESCE(cfg->'subcontracted_parameter_keys', '[]'::jsonb)) = 'array'
      AND jsonb_array_length(COALESCE(cfg->'subcontracted_parameter_keys', '[]'::jsonb)) > 0
)
SQL);
            })
            ->orWhere(function ($jsonSelectionQuery) use ($driver): void {
                if ($driver !== 'pgsql') {
                    $jsonSelectionQuery->whereRaw('1 = 0');

                    return;
                }

                $jsonSelectionQuery->whereExists(function ($jsonExists): void {
                    $jsonExists->selectRaw('1')
                        ->from('analysis_elements as ae')
                        ->where('ae.sub_contracted', 1)
                        ->where(function ($selectedIds): void {
                            $selectedIds
                                ->whereRaw("ae.id::text IN (SELECT jsonb_array_elements_text(COALESCE(ssr.parameter_ids::jsonb, '[]'::jsonb)))")
                                ->orWhereRaw("ae.id::text IN (SELECT elem->>'analysis_element_id' FROM jsonb_array_elements(COALESCE(ssr.sample_lines::jsonb, '[]'::jsonb)) AS elem WHERE COALESCE(elem->>'analysis_element_id', '') <> '')");
                        });
                });
            });
    }

    /**
     * Dispatch-state counts for the Sub-contracting tab.
     *
     * @return array<string, int>
     */
    public function getSubcontractingDispatchCountsProperty(): array
    {
        if (! $this->isSamplesReceiving()) {
            return [
                SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING => 0,
                SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED => 0,
            ];
        }

        $awaitingQuery = $this->subcontractingSubmissionFormsQuery(SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING);
        $this->applyReceivingSubmissionFormFilters($awaitingQuery);

        $dispatchedQuery = $this->subcontractingSubmissionFormsQuery(SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED);
        $this->applyReceivingSubmissionFormFilters($dispatchedQuery);

        return [
            SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING => (int) $awaitingQuery->count(),
            SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED => (int) $dispatchedQuery->count(),
        ];
    }

    public function setSubcontractingDispatchStatus(string $status): void
    {
        $this->subcontractingDispatchStatus = in_array($status, [
            SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING,
            SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED,
        ], true)
            ? $status
            : SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING;

        $this->selectedFormInstanceIds = [];
        $this->resetPage('forms_page');
    }

    protected function normalizedSubcontractingDispatchStatus(): string
    {
        return in_array($this->subcontractingDispatchStatus, [
            SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING,
            SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED,
        ], true)
            ? $this->subcontractingDispatchStatus
            : SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING;
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
            $this->applySubmissionFormSampleTypeFilter($query, (string) $this->sampleTypeFilter);
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
                $this->applyCaseInsensitiveSearch($q, ['form_number', 'title'], (string) $search);
                $q->orWhereHas('submissionForm', function ($q) use ($search) {
                    $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search);
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

        $cacheKey = $this->receivingCountsCacheKey('tab-counts');

        return Cache::remember($cacheKey, now()->addSeconds(20), function (): array {
            $counts = [];
            foreach ($this->receivingRequestTabKeys() as $tabKey) {
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
                if ($tabKey === 'sample_integrity_check') {
                    $integrityQuery = $this->sampleIntegrityCheckSubmissionFormsQuery();
                    $this->applyReceivingSubmissionFormFilters($integrityQuery);
                    $counts[$tabKey] = (int) $integrityQuery->count();
                    continue;
                }
                if ($tabKey === 'sub_contracting') {
                    $subcontractingQuery = $this->subcontractingSubmissionFormsQuery();
                    $this->applyReceivingSubmissionFormFilters($subcontractingQuery);
                    $counts[$tabKey] = (int) $subcontractingQuery->count();
                    continue;
                }
                if ($tabKey === 'accepted') {
                    $acceptedQuery = SubmissionFormInstance::query()->requestReviewAccepted();
                    $this->applyReceivingSubmissionFormFilters($acceptedQuery);
                    $counts[$tabKey] = (int) $acceptedQuery->count();
                    continue;
                }
                $fallbackQuery = $this->receivingSubmissionFormsBaseQuery()->where('status', $tabKey);
                $this->applyReceivingSubmissionFormFilters($fallbackQuery);
                $counts[$tabKey] = (int) $fallbackQuery->count();
            }

            return $counts;
        });
    }

    /**
     * Cache key for receiving KPI / tab counts (invalidated when queue data changes).
     */
    protected function receivingCountsCacheKey(string $suffix): string
    {
        $companyId = (string) (Auth::user()?->company_id ?? 'none');
        $version = (int) Cache::get("workflow-board:receiving:{$companyId}:ver", 0);

        $filterFingerprint = md5(json_encode([
            $this->submissionFormsSearch,
            $this->receiptDateFrom,
            $this->receiptDateTo,
            $this->submissionFormsPriority,
            $this->natureOfSampleFilter,
            $this->customerFilter,
            $this->sampleTypeFilter,
            $this->submissionFormsStatus,
            $this->subcontractingDispatchStatus,
        ]));

        return "workflow-board:receiving:{$companyId}:v{$version}:{$filterFingerprint}:{$suffix}";
    }

    protected function bustReceivingCountsCache(): void
    {
        $companyId = (string) (Auth::user()?->company_id ?? 'none');
        $versionKey = "workflow-board:receiving:{$companyId}:ver";
        Cache::put($versionKey, ((int) Cache::get($versionKey, 0)) + 1, now()->addDays(7));
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
            $this->applySubmissionFormSampleTypeFilter($query, (string) $this->sampleTypeFilter);
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
                $this->applyCaseInsensitiveSearch($q, ['form_number', 'title'], (string) $search);
                $q->orWhereHas('submissionForm', function ($q) use ($search) {
                    $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search);
                });
            });
        }
    }

    private function applySubmissionFormSampleTypeFilter($query, string $sampleTypeId): void
    {
        $categoryId = SampleType::query()->whereKey($sampleTypeId)->value('sample_type_category');

        $query->where(function ($builder) use ($sampleTypeId, $categoryId): void {
            $builder->where('selected_sample_type_id', $sampleTypeId);

            if ($categoryId !== null) {
                $builder->orWhereHas('submissionForm.sampleTypeCategories', function ($categoryQuery) use ($categoryId): void {
                    $categoryQuery->where('sample_type_categories.id', (int) $categoryId);
                });
            }

            $builder->orWhereHas('values', function ($valueQuery) use ($sampleTypeId): void {
                $valueQuery->whereHas('element', function ($elementQuery): void {
                    $elementQuery->whereIn('name', ['sample_type_id', 'sample_type']);
                })->where('value', 'like', '%'.$sampleTypeId.'%');
            });
        });
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
                'submissionForm.sampleTypeCategories',
                'submittedBy',
                'batches.samples',
                'crmCustomer',
                'sampleSubmissionRequest.currentQuotation',
                'sampleSubmissionRequest.requestedAnalyses',
                'activePendingIntray',
            ];

            if ($this->workflowSubTab === 'sub_contracting') {
                $eagerLoads[] = 'sampleSubmissionRequest.subcontractingDispatchAssignments.lab';
                $eagerLoads[] = 'sampleSubmissionRequest.subcontractingDispatchAssignments.analysisElement.analyte';
            }

            if (in_array($this->workflowSubTab, ['ready_for_reception', 'sample_integrity_check', 'accepted'], true)) {
                $eagerLoads[] = 'analysisAcceptanceForms';
                $eagerLoads[] = 'workflowForms';
            }

            $query = match ($this->workflowSubTab) {
                'ready_for_reception' => $this->readyForPhysicalReceptionSubmissionFormsQuery(),
                'sample_integrity_check' => $this->sampleIntegrityCheckSubmissionFormsQuery(),
                'submitted' => $this->submittedCommercialPipelineSubmissionFormsQuery(),
                'sub_contracting' => $this->subcontractingSubmissionFormsQuery(),
                'accepted' => SubmissionFormInstance::query()->requestReviewAccepted(),
                default => $this->receivingSubmissionFormsBaseQuery()->where('status', $tabStatus),
            };

            $query->with($eagerLoads)
                ->select('submission_form_instances.*')
                ->latest();

            $this->applyReceivingSubmissionFormFilters($query);

            return $query->paginate($this->submissionFormsPerPage, ['*'], 'forms_page');
        }

        if ($this->isSamplesRequestReview()) {
            $driver = DB::connection()->getDriverName();

            $query = $this->requestReviewSubmissionFormsBaseQuery()
                ->with([
                    'submissionForm.sampleTypeCategories',
                    'submittedBy',
                    'batches.batch_attachments',
                    'crmCustomer',
                    'sampleSubmissionRequest.currentQuotation',
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
                'submissionForm.sampleTypeCategories',
                'submittedBy',
                'batches',
                'crmCustomer',
            'sampleSubmissionRequest.currentQuotation',
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
            $hasTestRequestFormInstanceId = false /* legacy test_request_form_instance_id removed */;

            $query->where(function ($samplesInLabQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                $samplesInLabQuery->where('status', 'approved')
                    ->orWhere(function ($dispatchedQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                        $dispatchedQuery
                            ->whereIn('status', ['submitted', 'Submitted', 'received', 'in_review', 'In Review', 'approved'])
                            ->where(function ($dispatchLinkQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                                $dispatchLinkQuery->whereHas('sampleSubmissionRequest', function ($enquiryQuery): void {
                                    $enquiryQuery->whereIn(
                                        'subcontracting_dispatch_status',
                                        SampleSubmissionRequest::subcontractDispatchCompletedStatuses()
                                    );
                                })->orWhereExists(function ($fallbackQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                                    $fallbackQuery->selectRaw('1')
                                        ->from('sample_submission_requests as ssr')
                                        ->whereIn(
                                            'ssr.subcontracting_dispatch_status',
                                            SampleSubmissionRequest::subcontractDispatchCompletedStatuses()
                                        )
                                        ->where(function ($linkQuery) use ($driver, $hasTestRequestFormInstanceId): void {
                                            $linkQuery
                                                ->whereRaw(VarcharUuidSql::equals('ssr.submission_form_instance_id', 'submission_form_instances.id'))
                                                ->when($hasTestRequestFormInstanceId, function ($query): void {
                                                    $query->orWhereRaw(VarcharUuidSql::equals('ssr.test_request_form_instance_id', 'submission_form_instances.id'));
                                                })
                                                ->orWhere(function ($portalLink) use ($driver): void {
                                                    $portalLink->when($driver === 'pgsql', function ($query): void {
                                                        $query->whereRaw('ssr.id::text = submission_form_instances.portal_request_id');
                                                    }, function ($query): void {
                                                        $query->whereColumn('ssr.id', 'submission_form_instances.portal_request_id');
                                                    });
                                                })
                                                ->orWhere(function ($targetLink) use ($driver): void {
                                                    $targetLink->whereRaw("LOWER(COALESCE(submission_form_instances.target_record_type, '')) IN ('sample_submission_request', 'sample_submission_requests')")
                                                        ->when($driver === 'pgsql', function ($query): void {
                                                            $query->whereRaw('ssr.id::text = submission_form_instances.target_record_id::text');
                                                        }, function ($query): void {
                                                            $query->whereColumn('ssr.id', 'submission_form_instances.target_record_id');
                                                        });
                                                });
                                        });
                                });
                            });
                    });
            });
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
            $this->applySubmissionFormSampleTypeFilter($query, (string) $this->sampleTypeFilter);
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
                $this->applyCaseInsensitiveSearch($q, ['form_number', 'title'], (string) $search);
                $q->orWhereHas('submissionForm', function ($q) use ($search) {
                    $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search);
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
            'samples.lab',
            'lab',
            'client',
            'sample_type',
            'invoice',
            'submissionFormInstance.submissionForm',
            'batch_attachments',
            'sampleSubmissionRequest.requestedAnalyses',
            'sampleSubmissionRequest.supportingDocumentTemplates',
            'sampleSubmissionRequest.supportingDocumentInstances.template',
        ])->where('isactive', 1)
            ->where('status', '!=', \App\Services\ShelfLife\ShelfLifeStudyBootstrapService::BATCH_STATUS);
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

        if (! empty($this->search)) {
            $query->where(function ($q): void {
                $this->applyCaseInsensitiveSearch($q, ['batch_code'], (string) $this->search);
                $q->orWhereHas('samples', function ($sampleQuery): void {
                    $this->applyCaseInsensitiveSearch($sampleQuery, ['sample_code'], (string) $this->search);
                });
            });
        }

        if ($this->customerFilter) {
            $query->where('crm_customer_id', $this->customerFilter);
        }

        if ($this->sampleTypeFilter) {
            $query->where('sample_type_id', $this->sampleTypeFilter);
        }

        if ($this->receiptDateFrom) {
            $query->whereDate('receipt_date', '>=', $this->receiptDateFrom);
        }

        if ($this->receiptDateTo) {
            $query->whereDate('receipt_date', '<=', $this->receiptDateTo);
        }

        $this->applyAmendmentFilter($query);

        if (! empty($this->allFilter['tat_date_from'])) {
            $tatQuery = SampleDate::query()
                ->where('name', 'Target Date')
                ->whereDate('date', '>=', $this->allFilter['tat_date_from']);

            if (! empty($this->allFilter['tat_date_to'])) {
                $tatQuery->whereDate('date', '<=', $this->allFilter['tat_date_to']);
            }

            $tatBatchIds = $tatQuery->pluck('sample_header_id')->toArray();
            $query->whereIn('id', $tatBatchIds ?: [0]);
        } elseif (! empty($this->allFilter['tat_date_to'])) {
            $tatBatchIds = SampleDate::query()
                ->where('name', 'Target Date')
                ->whereDate('date', '<=', $this->allFilter['tat_date_to'])
                ->pluck('sample_header_id')
                ->toArray();
            $query->whereIn('id', $tatBatchIds ?: [0]);
        }

        $scheduleSent = $this->allFilter['schedule_sent'] ?? 'All';
        if ($scheduleSent === 'sent') {
            $query->where('schedule_analysis_sent', 1);
        } elseif ($scheduleSent === 'not_sent') {
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
            $query->where(function ($q) {
                $this->applyCaseInsensitiveSearch($q, ['batch_code'], (string) $this->search);
                $q->orWhereHas('samples', function ($sampleQuery) {
                    $this->applyCaseInsensitiveSearch($sampleQuery, ['sample_code'], (string) $this->search);
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

        $this->applyAmendmentFilter($query);

        // Apply submission form filters to batches for certain stages
        if ($this->status === 'Samples In Lab' || ($this->isReceivingStage() && $this->workflowSubTab === 'requests')) {
            if ($this->submissionFormsSearch) {
                $search = $this->submissionFormsSearch;
                $query->whereHas('submissionFormInstance', function ($q) use ($search) {
                    $this->applyCaseInsensitiveSearch($q, ['form_number', 'title'], (string) $search);
                    $q->orWhereHas('submissionForm', function ($q) use ($search) {
                        $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search);
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
        $this->amendmentFilter = 'all';
        $this->customerSearch = '';
        $this->showCustomerDropdown = false;
        $this->customerPage = 1;
        $this->submissionFormsSearch = '';
        $this->submissionFormsStatus = '';
        $this->submissionFormsPriority = '';
        $this->natureOfSampleFilter = '';
        $this->allFilter = $this->defaultAllSamplesFilter();
        $this->resetPage('batches_page');
        $this->resetPage('forms_page');
    }

    protected function applyAmendmentFilter($query): void
    {
        if ($this->amendmentFilter === 'in_amendment') {
            $query->where(function ($inner): void {
                $inner->where('in_ammendment_proccess', 1)
                    ->orWhere('in_ammendment_process', 1);
            });

            return;
        }

        if ($this->amendmentFilter === 'amended') {
            $query->where(function ($inner): void {
                $inner->where('is_amendment', '>', 1)
                    ->orWhere('in_ammendment_proccess', 1)
                    ->orWhere('in_ammendment_process', 1);
            });
        }
    }

    public function updatingAmendmentFilter(): void
    {
        $this->resetPage('batches_page');
    }

    public function updatingSearch(): void
    {
        $this->resetPage('batches_page');
    }

    public function updatingReceiptDateFrom(): void
    {
        $this->resetPage('batches_page');
        $this->resetPage('forms_page');
    }

    public function updatingReceiptDateTo(): void
    {
        $this->resetPage('batches_page');
        $this->resetPage('forms_page');
    }

    public function updatingCustomerFilter(): void
    {
        $this->resetPage('batches_page');
        $this->resetPage('forms_page');
    }

    public function updatingSampleTypeFilter(): void
    {
        $this->resetPage('batches_page');
        $this->resetPage('forms_page');
    }

    public function updatedAllFilter(): void
    {
        $this->resetPage('batches_page');
    }

    public function toggleFiltersPanel(): void
    {
        $this->showFiltersPanel = ! $this->showFiltersPanel;
    }

    public function toggleAdvancedFilters(): void
    {
        $this->showAdvancedFilters = ! $this->showAdvancedFilters;
    }

    /**
     * Count of active request-table filters (for the Filters toggle badge).
     */
    public function getActiveRequestFilterCountProperty(): int
    {
        $count = 0;

        if (trim($this->submissionFormsSearch) !== '') {
            $count++;
        }
        if (trim((string) $this->search) !== '') {
            $count++;
        }
        if ($this->receiptDateFrom) {
            $count++;
        }
        if ($this->receiptDateTo) {
            $count++;
        }
        if ($this->submissionFormsPriority !== '') {
            $count++;
        }
        if ($this->natureOfSampleFilter !== '') {
            $count++;
        }
        if ($this->customerFilter) {
            $count++;
        }
        if ($this->sampleTypeFilter) {
            $count++;
        }
        if ($this->submissionFormsStatus !== '') {
            $count++;
        }
        if ($this->amendmentFilter !== 'all') {
            $count++;
        }
        if (! empty($this->allFilter['tat_date_from'] ?? '')) {
            $count++;
        }
        if (! empty($this->allFilter['tat_date_to'] ?? '')) {
            $count++;
        }
        if (($this->allFilter['schedule_sent'] ?? 'All') !== 'All') {
            $count++;
        }

        return $count;
    }

    public function setWorkflowSubTab(string $tab): void
    {
        $tab = strtolower(trim($tab));

        if ($this->isSamplesReceiving()) {
            if (in_array($tab, ['received', 'in_review'], true)) {
                $tab = 'ready_for_reception';
            }
            $this->workflowSubTab = in_array($tab, $this->receivingRequestTabKeys(), true)
                ? $tab
                : $this->defaultWorkflowSubTab();
            $this->selectedFormInstanceIds = [];
            $this->resetPage('forms_page');
        } elseif ($this->isSamplesRequestReview()) {
            $this->workflowSubTab = in_array($tab, $this->requestReviewTabKeys(), true)
                ? $tab
                : $this->defaultWorkflowSubTab();
            $this->selectedFormInstanceIds = [];
            $this->resetPage('forms_page');
        } else {
            $this->workflowSubTab = in_array($tab, ['requests', 'received'], true)
                ? $tab
                : $this->defaultWorkflowSubTab();
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
        if (! in_array($this->status, ['Samples Reception', 'Samples En-Route'], true)) {
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
     * Physical check-ins logged today (audit action "received").
     */
    public function getReceivingTodayCheckInCountProperty(): int
    {
        if ($this->status !== 'Samples Receiving') {
            return 0;
        }

        $instanceIds = $this->receivingSubmissionFormsBaseQuery()->pluck('id');

        if ($instanceIds->isEmpty()) {
            return 0;
        }

        return (int) \App\Models\SubmissionFormAuditLog::query()
            ->where('action', 'received')
            ->whereIn('submission_form_instance_id', $instanceIds)
            ->whereDate('created_at', today())
            ->pluck('submission_form_instance_id')
            ->unique()
            ->count();
    }

    /**
     * Dashboard KPIs for the Samples Receiving board.
     *
     * @return array{sub_contracting: int, submitted: int, ready_for_reception: int, sample_integrity_check: int, accepted: int, todays_check_ins: int}
     */
    public function getReceivingDashboardStatsProperty(): array
    {
        if ($this->status !== 'Samples Receiving') {
            return [
                'sub_contracting' => 0,
                'submitted' => 0,
                'ready_for_reception' => 0,
                'sample_integrity_check' => 0,
                'accepted' => 0,
                'todays_check_ins' => 0,
            ];
        }

        $cacheKey = $this->receivingCountsCacheKey('dashboard-stats');

        return Cache::remember($cacheKey, now()->addSeconds(20), function (): array {
            $tabCounts = $this->receivingRequestTabCounts;

            return [
                'sub_contracting' => (int) (
                    ($this->subcontractingDispatchCounts[SampleSubmissionRequest::SUBCONTRACT_DISPATCH_AWAITING] ?? 0)
                    + ($this->subcontractingDispatchCounts[SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED] ?? 0)
                ),
                'submitted' => (int) ($tabCounts['submitted'] ?? 0),
                'ready_for_reception' => (int) ($tabCounts['ready_for_reception'] ?? 0),
                'sample_integrity_check' => (int) ($tabCounts['sample_integrity_check'] ?? 0),
                'accepted' => (int) ($tabCounts['accepted'] ?? 0),
                'todays_check_ins' => $this->receivingTodayCheckInCount,
            ];
        });
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
            $query->where(function ($q): void {
                $this->applyCaseInsensitiveSearch($q, ['unique_identification', 'reference_number'], (string) $this->search);
                $q->orWhereHas('customer', function ($c): void {
                    $this->applyCaseInsensitiveSearch($c, ['name'], (string) $this->search);
                });
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
            ->whereIn('status', $statuses, 'and', false)
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
            'currentQuotation',
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
            $query->where(function ($q) {
                $this->applyCaseInsensitiveSearch($q, ['unique_identification'], (string) $this->search);
                $q->orWhereHas('customer', function ($c) {
                    $this->applyCaseInsensitiveSearch($c, ['name'], (string) $this->search);
                });
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
        $query = CRMCustomer::query()
            ->where('active', 1)
            ->orderBy('name');

        if (! empty($this->customerSearch)) {
            $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->customerSearch);
        }

        $totalToShow = $this->customerPage * $this->customerPerPage;

        return $query->limit($totalToShow)->get();
    }
    
    /**
     * Check if there are more customers to load.
     */
    public function getHasMoreCustomersProperty()
    {
        $query = CRMCustomer::query()->where('active', 1);

        if (! empty($this->customerSearch)) {
            $this->applyCaseInsensitiveSearch($query, ['name'], (string) $this->customerSearch);
        }

        $totalLoaded = $this->customerPage * $this->customerPerPage;

        return $query->count() > $totalLoaded;
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
        if (! $this->customerFilter) {
            return null;
        }

        return CRMCustomer::query()->find($this->customerFilter);
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
    #[Renderless]
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
                $this->workflowNotify('error', (string) collect($blockedReasons)->unique()->first());

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
        )->to(\App\Livewire\Sampleworkflow\ReceiveSampleRequest::class);
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function openPoCaptureFromInstances(array $ids = []): void
    {
        $this->syncSelectedFormInstanceIds($ids !== [] ? $ids : $this->selectedFormInstanceIds);
        $enquiryId = $this->resolveEnquiryIdFromSelection($this->selectedFormInstanceIds);

        if ($enquiryId === null) {
            $this->workflowNotify('error', 'Select a request with an accepted quotation to record PO and move to Ready for Reception.');

            return;
        }

        $this->openPoCaptureModal($enquiryId);
    }

    /**
     * @param  array<int, string>  $ids  Checked instance IDs from the browser (deferred wire:model may not be synced yet).
     */
    public function recordWalkInAcceptanceFromInstances(array $ids = []): void
    {
        $this->syncSelectedFormInstanceIds($ids !== [] ? $ids : $this->selectedFormInstanceIds);

        if ($this->selectedFormInstanceIds === []) {
            $this->workflowNotify('error', 'Select a non-portal request with a sent quotation to record acceptance.');

            return;
        }

        $enquiryId = $this->resolveEnquiryIdFromSelection($this->selectedFormInstanceIds);

        if ($enquiryId === null) {
            $this->workflowNotify('error', 'Select a non-portal request with a sent quotation to record acceptance.');

            return;
        }

        $enquiry = SampleSubmissionRequest::query()->find($enquiryId);

        if ($enquiry === null) {
            $this->workflowNotify('error', 'Enquiry not found.');

            return;
        }

        if (strtolower((string) ($enquiry->source_channel ?? '')) === 'portal') {
            $this->workflowNotify('error', 'Portal requests are accepted by the customer in the portal. Use Request Review acceptance for non-portal sources only.');

            return;
        }

        $this->openQuotationAcceptanceModal($enquiryId);
    }

    public function openQuotationAcceptanceModal(string $enquiryId): void
    {
        $enquiry = SampleSubmissionRequest::query()->with(['customer', 'contact'])->find($enquiryId);
        if ($enquiry === null || $enquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_SENT) {
            $this->workflowNotify('error', 'Quotation can only be accepted when the enquiry is Quotation Sent.');

            return;
        }

        $customerId = trim((string) ($enquiry->crm_customer_id ?? ''));
        if ($customerId === '') {
            $this->workflowNotify('error', 'This enquiry has no CRM customer linked. Link a customer before recording quotation acceptance.');

            return;
        }

        $this->quotationAcceptancePoOnly = false;
        $this->quotationAcceptanceEnquiryId = $enquiryId;
        $this->quotationAcceptanceContactId = $enquiry->crm_customer_contact_id
            ? (string) $enquiry->crm_customer_contact_id
            : null;
        $this->quotationAcceptanceContactOptions = app(\App\Services\Sampleworkflow\CustomerContactVerificationService::class)
            ->activeContactsForCustomer($customerId);
        $this->hydrateQuotationAcceptancePoRules($enquiry);
        $this->applyQuotationAcceptanceContact($this->quotationAcceptanceContactId);
        $this->showQuotationAcceptanceModal = true;
        $this->dispatch(
            'quotation-acceptance-modal-opened',
            signature: $this->quotationAcceptanceSignature,
        );
    }

    public function closeQuotationAcceptanceModal(): void
    {
        $this->showQuotationAcceptanceModal = false;
        $this->quotationAcceptancePoOnly = false;
        $this->quotationAcceptanceEnquiryId = null;
        $this->quotationAcceptanceSignerName = '';
        $this->quotationAcceptanceSignature = '';
        $this->quotationAcceptanceContactId = null;
        $this->quotationAcceptanceAppliedContactId = '';
        $this->quotationAcceptanceContactOptions = [];
        $this->clientPoNumber = '';
        $this->poRuleType = 'walk_in';
        $this->poRequiresPo = false;
        $this->poRuleMessage = '';
    }

    public function updatedQuotationAcceptanceContactId(?string $contactId): void
    {
        if ((string) $contactId === $this->quotationAcceptanceAppliedContactId) {
            return;
        }

        $this->applyQuotationAcceptanceContact($contactId);
        $this->dispatch(
            'quotation-acceptance-signature-changed',
            signature: $this->quotationAcceptanceSignature,
        );
    }

    public function submitQuotationAcceptanceSignature(?string $signature = null): void
    {
        if ($signature !== null && str_starts_with($signature, 'data:image/')) {
            $this->quotationAcceptanceSignature = $signature;
        }

        if ($this->quotationAcceptanceEnquiryId === null) {
            return;
        }

        if ($this->quotationAcceptancePoOnly) {
            $this->submitPoAndReadyForReception();

            return;
        }

        $this->validate([
            'quotationAcceptanceContactId' => ['required', 'string'],
            'quotationAcceptanceSignature' => ['required', 'string'],
        ], [
            'quotationAcceptanceContactId.required' => 'Select the customer contact.',
            'quotationAcceptanceSignature.required' => 'Provide the customer signature.',
        ]);

        $signerName = $this->resolveQuotationAcceptanceSignerName($this->quotationAcceptanceContactId);
        if ($signerName === '') {
            $this->addError('quotationAcceptanceContactId', 'Select a valid customer contact.');

            return;
        }
        $this->quotationAcceptanceSignerName = $signerName;

        $enquiry = SampleSubmissionRequest::query()->with('customer')->find($this->quotationAcceptanceEnquiryId);
        if ($enquiry === null) {
            $this->closeQuotationAcceptanceModal();

            return;
        }

        try {
            app(EnquiryAccountSettingsService::class)->validateAcceptPayload(
                $enquiry->customer,
                [
                    'client_po_number' => $this->clientPoNumber,
                ],
            );

            $accepted = app(\App\Services\Commercial\QuotationFromEnquiryService::class)->recordWalkInAcceptance(
                $enquiry,
                null,
                false,
                [
                    'signature' => $this->quotationAcceptanceSignature,
                    'signer_name' => $this->quotationAcceptanceSignerName,
                    'contact_id' => $this->quotationAcceptanceContactId,
                ],
            );

            app(EnquiryReceptionReadinessService::class)->markReadyForReception(
                $accepted,
                (string) ($accepted->accepted_quotation_header_id ?? $accepted->current_quotation_header_id ?? ''),
                [
                    'client_po_number' => $this->clientPoNumber,
                ],
            );

            $this->selectedFormInstanceIds = [];
            $this->closeQuotationAcceptanceModal();
            $this->workflowNotify('success', 'Quotation accepted. Request is ready for physical reception.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
        } catch (\Throwable $exception) {
            $this->workflowNotify('error', $exception->getMessage());
        }
    }

    /**
     * @param  array<int, string>  $ids
     */
    #[Renderless]
    public function openProcessEnquiryModal(array $ids = []): void
    {
        $this->syncSelectedFormInstanceIds($ids !== [] ? $ids : $this->selectedFormInstanceIds);
        $enquiryId = $this->resolveEnquiryIdFromSelection($this->selectedFormInstanceIds);

        if ($enquiryId === null && $this->selectedFormInstanceIds !== []) {
            $enquiryId = $this->createCommercialEnquiryFromSelection($this->selectedFormInstanceIds);
        }

        if ($enquiryId === null) {
            $this->workflowNotify('error', 'Could not open enquiry processing. Select a submitted test request with customer details.');

            return;
        }

        $this->dispatch('process-enquiry-open', enquiryId: $enquiryId)
            ->to(ProcessEnquiryWizard::class);
    }

    /**
     * @param  array<int, string>  $ids
     */
    #[Renderless]
    public function openProcessEnquiryFromInstances(array $ids = []): void
    {
        $this->openProcessEnquiryModal($ids);
    }

    #[Renderless]
    public function openProcessEnquiryByEnquiryId(string $enquiryId): void
    {
        $enquiry = SampleSubmissionRequest::query()->find($enquiryId);

        if ($enquiry === null) {
            session()->flash('error', 'Enquiry not found.');

            return;
        }

        $this->dispatch('process-enquiry-open', enquiryId: $enquiry->id)
            ->to(ProcessEnquiryWizard::class);
    }

    #[Renderless]
    public function openReviewQuotationByEnquiryId(string $enquiryId): void
    {
        $enquiry = SampleSubmissionRequest::query()->find($enquiryId);

        if (
            $enquiry === null
            || $enquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW
            || empty($enquiry->current_quotation_header_id)
        ) {
            session()->flash('error', 'Select a request with a quotation under review.');

            return;
        }

        $this->dispatch('process-enquiry-open', enquiryId: $enquiry->id)
            ->to(ProcessEnquiryWizard::class);
    }

    /**
     * @param  array<int, string>  $ids
     */
    #[Renderless]
    public function openReviewQuotationFromInstances(array $ids = []): void
    {
        $enquiryId = $this->resolveReviewQuotationEnquiryIdFromSelection($ids);

        if ($enquiryId === null) {
            session()->flash('error', 'Select a request with a quotation under review.');

            return;
        }

        $this->openReviewQuotationByEnquiryId($enquiryId);
    }

    public function openPoCaptureModal(string $enquiryId): void
    {
        $enquiry = SampleSubmissionRequest::query()->with(['customer', 'contact'])->find($enquiryId);
        if ($enquiry === null || $enquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED) {
            $this->workflowNotify('error', 'PO can only be recorded for accepted quotations.');

            return;
        }

        $this->quotationAcceptancePoOnly = true;
        $this->quotationAcceptanceEnquiryId = $enquiryId;
        $this->quotationAcceptanceSignerName = '';
        $this->quotationAcceptanceSignature = '';
        $this->quotationAcceptanceContactId = null;
        $this->quotationAcceptanceAppliedContactId = '';
        $this->quotationAcceptanceContactOptions = [];
        $this->hydrateQuotationAcceptancePoRules($enquiry);
        $this->showQuotationAcceptanceModal = true;
        $this->dispatch('quotation-acceptance-modal-opened', signature: '');
    }

    public function closePoCaptureModal(): void
    {
        $this->closeQuotationAcceptanceModal();
    }

    public function submitPoAndReadyForReception(): void
    {
        if ($this->quotationAcceptanceEnquiryId === null) {
            return;
        }

        $enquiry = SampleSubmissionRequest::query()->with('customer')->find($this->quotationAcceptanceEnquiryId);
        if ($enquiry === null) {
            $this->closeQuotationAcceptanceModal();

            return;
        }

        try {
            app(EnquiryAccountSettingsService::class)->validateAcceptPayload(
                $enquiry->customer,
                [
                    'client_po_number' => $this->clientPoNumber,
                ],
            );

            app(EnquiryReceptionReadinessService::class)->markReadyForReception(
                $enquiry,
                (string) ($enquiry->accepted_quotation_header_id ?? $enquiry->current_quotation_header_id ?? ''),
                [
                    'client_po_number' => $this->clientPoNumber,
                ],
            );

            $this->closeQuotationAcceptanceModal();
            $this->workflowNotify('success', 'PO recorded. Request is ready for physical reception.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
        } catch (Throwable $exception) {
            $this->workflowNotify('error', $exception->getMessage());
        }
    }

    protected function hydrateQuotationAcceptancePoRules(SampleSubmissionRequest $enquiry): void
    {
        $rules = app(EnquiryAccountSettingsService::class)->poRulesForCustomer($enquiry->customer);
        $this->poRuleType = (string) ($rules['type'] ?? 'walk_in');
        $this->poRequiresPo = (bool) ($rules['requires_po'] ?? false);
        $this->poRuleMessage = $this->resolvePoRuleMessage();
        $this->clientPoNumber = (string) ($enquiry->client_po_number ?? '');
    }

    protected function applyQuotationAcceptanceContact(?string $contactId): void
    {
        $contactId = filled($contactId) ? (string) $contactId : null;
        $this->quotationAcceptanceContactId = $contactId;
        $this->quotationAcceptanceAppliedContactId = (string) $contactId;
        $this->quotationAcceptanceSignerName = $this->resolveQuotationAcceptanceSignerName($contactId);
        $this->quotationAcceptanceSignature = '';

        if ($contactId === null) {
            return;
        }

        $contact = CustomerContact::query()->find($contactId);
        if ($contact !== null && $contact->hasSignatureImage()) {
            $this->quotationAcceptanceSignature = $contact->signatureDataUri();
        }
    }

    protected function resolveQuotationAcceptanceSignerName(?string $contactId): string
    {
        if (! filled($contactId)) {
            return '';
        }

        $contact = CustomerContact::query()->find((string) $contactId);
        if ($contact === null) {
            return '';
        }

        return trim(implode(' ', array_filter([
            $contact->first_name,
            $contact->middle_name,
            $contact->last_name,
        ]))) ?: (string) ($contact->email ?? '');
    }

    protected function resolvePoRuleMessage(): string
    {
        if ($this->poRequiresPo) {
            return 'This customer account requires a purchase order number before the request can be marked ready.';
        }

        return 'This customer may proceed without a PO number.';
    }

    protected function workflowNotify(string $type, string $message): void
    {
        $this->dispatch('notify', type: $type, message: $message);
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
                ->findMany($this->selectedFormInstanceIds)
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
    protected function resolveReviewQuotationEnquiryIdFromSelection(array $ids): ?string
    {
        if ($ids === []) {
            return null;
        }

        $this->syncSelectedFormInstanceIds($ids);

        $enquiryId = SubmissionFormInstance::query()
            ->findMany($this->selectedFormInstanceIds)
            ->map(fn (SubmissionFormInstance $instance) => $instance->sampleSubmissionRequest)
            ->filter(fn (?SampleSubmissionRequest $enquiry): bool => $enquiry !== null
                && $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW
                && ! empty($enquiry->current_quotation_header_id))
            ->map(fn (SampleSubmissionRequest $enquiry): string => (string) $enquiry->id)
            ->first();

        if ($enquiryId !== null) {
            return $enquiryId;
        }

        return SampleSubmissionRequest::query()
            ->findMany($ids)
            ->first(function (SampleSubmissionRequest $enquiry): bool {
                return $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW
                    && ! empty($enquiry->current_quotation_header_id);
            })?->id;
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
            ->with(['submissionForm', 'values.element', 'crmCustomer', 'sampleSubmissionRequest'])
            ->find($instanceId);

        if ($instance === null) {
            return null;
        }

        $enquiry = app(CommercialEnquiryFromFormService::class)->syncFromSubmittedInstance($instance);

        return $enquiry?->id !== null ? (string) $enquiry->id : null;
    }

    public function onProcessEnquiryCompleted(): void
    {
        $this->bustReceivingCountsCache();
    }

    public function handleProcessEnquiryCompleted(): void
    {
        $this->onProcessEnquiryCompleted();
    }

    public function onReceiveCompleted(array $sfiIds = [], bool $keepModalOpen = false): void
    {
        $this->bustReceivingCountsCache();
        $this->selectedFormInstanceIds = [];
        $this->receiveFormSummaries = [];

        if (! $keepModalOpen) {
            $this->dispatch('hide-receive-sample-modal');
        }

        if ($this->isSamplesReceiving() && $this->workflowSubTab !== 'ready_for_reception') {
            $this->setWorkflowSubTab('ready_for_reception');
        }

        if ($sfiIds !== [] && ! $keepModalOpen) {
            $this->dispatch('open-test-request-pdf', url: route('test-request-form.pdf', $sfiIds[0]));
        }

        if ($keepModalOpen) {
            $this->dispatch(
                'direct-registration-carousel-state',
                ready: true,
                pane: 'register',
            );
        }
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
    #[Renderless]
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
     * @param  array<int, string>|string  $ids
     */
    #[Renderless]
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
        $this->bustReceivingCountsCache();
        $this->selectedFormInstanceIds = [];
        $this->dispatch('hide-analyst-review-modal');
    }

    /**
     * @param  array<int, string>|string  $ids
     */
    #[Renderless]
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
        $this->bustReceivingCountsCache();
        $this->selectedFormInstanceIds = [];
        $this->dispatch('hide-request-additional-info-modal');
    }

    /**
     * Resume selected Request Additional Info items back to Ready for Reception.
     *
     * @param  array<int, string>|string  $ids
     */
    public function resumeAdditionalInfoToReception(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids);

        if ($this->selectedFormInstanceIds === []) {
            $this->workflowNotify('error', 'Select at least one request to resume.');

            return;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->workflowNotify('error', 'You must be signed in to resume reception.');

            return;
        }

        $processed = 0;
        $skipped = 0;

        $instances = SubmissionFormInstance::query()
            ->whereIn('id', $this->selectedFormInstanceIds)
            ->where('status', 'in_additional_info')
            ->get();

        foreach ($instances as $instance) {
            if ($instance->resumeFromAdditionalInfo($user)) {
                $processed++;
            } else {
                $skipped++;
            }
        }

        $this->bustReceivingCountsCache();
        $this->selectedFormInstanceIds = [];

        if ($processed === 0) {
            $this->workflowNotify('error', 'No requests were resumed. They may already be in another status.');

            return;
        }

        $message = $processed === 1
            ? '1 request returned to Ready for Reception.'
            : "{$processed} requests returned to Ready for Reception.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        $this->workflowNotify('success', $message);
        $this->setWorkflowSubTab('ready_for_reception');
    }

    /**
     * @param  array<int, string>|string  $ids
     */
    #[Renderless]
    public function openSubcontractDispatchModal(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids);

        if (count($this->selectedFormInstanceIds) !== 1) {
            $this->workflowNotify('error', 'Select exactly one subcontracting request before dispatching.');

            return;
        }

        $summaries = $this->buildReceiveFormSummaries($this->selectedFormInstanceIds);

        $this->dispatch('subcontract-dispatch-modal-open',
            instanceIds: $this->selectedFormInstanceIds,
            summaries: $summaries,
        );
    }

    public function onSubcontractDispatchCompleted(): void
    {
        $this->bustReceivingCountsCache();
        $this->selectedFormInstanceIds = [];
        $this->dispatch('hide-subcontract-dispatch-modal');
    }

    #[Renderless]
    public function openOfflinePaperTrfCapture(): void
    {
        $this->dispatch('open-offline-paper-trf')->to(ReceiveSampleRequest::class);
    }

    #[Renderless]
    public function openDirectRegistration(): void
    {
        $this->openOfflinePaperTrfCapture();
    }

    #[Renderless]
    public function openAcceptSampleWizardFromSelection(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids !== [] ? $ids : $this->selectedFormInstanceIds);

        if (count($this->selectedFormInstanceIds) !== 1) {
            $this->dispatch('notify', type: 'error', message: 'Please select exactly one submission request or form row before continuing.');

            return;
        }

        $mode = $this->workflowSubTab === 'sample_integrity_check'
            ? 'accept_register'
            : 'receive_only';

        $this->dispatch(
            'open-acceptance-wizard',
            submissionFormInstanceId: $this->selectedFormInstanceIds[0],
            submissionRequestId: $this->resolveSubmissionRequestIdForFormInstance($this->selectedFormInstanceIds[0]),
            mode: $mode,
        )->to(AcceptanceFormWizard::class);
    }

    #[Renderless]
    public function openRejectSampleWizardFromSelection(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids !== [] ? $ids : $this->selectedFormInstanceIds);

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
    #[Renderless]
    public function openAcceptSampleWizard(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids);

        if ($this->selectedFormInstanceIds === [] || count($this->selectedFormInstanceIds) > 1) {
            $this->dispatch('notify', type: 'error', message: 'Please select exactly one submission request or form row before continuing.');

            return;
        }

        $mode = $this->workflowSubTab === 'sample_integrity_check'
            ? 'accept_register'
            : 'receive_only';

        $this->dispatch(
            'open-acceptance-wizard',
            submissionFormInstanceId: $this->selectedFormInstanceIds[0],
            submissionRequestId: $this->resolveSubmissionRequestIdForFormInstance($this->selectedFormInstanceIds[0]),
            mode: $mode,
        )->to(AcceptanceFormWizard::class);
    }

    public function openSampleIntegrityCheckPageFromSelection(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids !== [] ? $ids : $this->selectedFormInstanceIds);

        if (count($this->selectedFormInstanceIds) !== 1) {
            $this->workflowNotify(
                'error',
                'Please select exactly one submission request or form row.'
            );

            return;
        }

        $instance = SubmissionFormInstance::query()
            ->with('submissionForm')
            ->find($this->selectedFormInstanceIds[0]);

        if ($instance === null || $instance->submission_form_id === null || $instance->submission_form_id === '') {
            $this->workflowNotify(
                'error',
                'Unable to open Sample Integrity Check for the selected request.'
            );

            return;
        }

        $this->redirectRoute('submission-forms.instances.sample-integrity-check', [
            'submissionForm' => $instance->submission_form_id,
            'instance' => $instance->id,
        ]);
    }

    /**
     * Clone the selected submission request into a new submitted request (Samples Receiving).
     *
     * @param  array<int, string>|string  $ids
     */
    public function cloneSelectedRequest(array|string $ids = []): void
    {
        if (is_string($ids)) {
            $ids = $ids !== '' ? [$ids] : [];
        }

        $this->syncSelectedFormInstanceIds($ids !== [] ? $ids : $this->selectedFormInstanceIds);

        if (count($this->selectedFormInstanceIds) !== 1) {
            $this->workflowNotify(
                'error',
                'Please select exactly one request to clone.'
            );

            return;
        }

        $source = SubmissionFormInstance::query()
            ->with(['values', 'submissionForm'])
            ->find($this->selectedFormInstanceIds[0]);

        if ($source === null) {
            $this->workflowNotify('error', 'The selected request could not be found.');

            return;
        }

        try {
            $clone = app(\App\Services\SubmissionForm\SubmissionFormInstanceCloneService::class)
                ->cloneRequest($source);

            $this->bustReceivingCountsCache();
            $this->selectedFormInstanceIds = [];

            $label = $clone->getDocumentControlNumber()
                ?? $clone->form_number
                ?? 'new request';

            $this->workflowNotify(
                'success',
                'Request cloned as '.$label.'.'
            );
        } catch (Throwable $exception) {
            report($exception);
            $this->workflowNotify(
                'error',
                $exception->getMessage() !== ''
                    ? $exception->getMessage()
                    : 'Unable to clone the selected request.'
            );
        }
    }

    /**
     * @param  array<int, string>|string  $ids
     */
    #[Renderless]
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
            ->with(['sampleSubmissionRequest'])
            ->find($formInstanceId);

        if ($instance === null) {
            return null;
        }

        $enquiryId = $instance->sampleSubmissionRequest?->id;

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
        $this->bustReceivingCountsCache();
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
        $this->decontaminationLabId = Lab::defaultLabId() ?? '';
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

    /**
     * Signed whole-day offset from today to the batch target date (start-of-day).
     * Positive = days remaining, negative = days overdue, zero = due today.
     */
    public static function statusDaysUntilTarget(mixed $targetDate): ?int
    {
        if ($targetDate === null || trim((string) $targetDate) === '') {
            return null;
        }

        try {
            $target = Carbon::parse($targetDate)->startOfDay();
            $today = Carbon::now()->startOfDay();

            return (int) $today->diffInDays($target, false);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function formatStatusDaysLabel(?int $statusDays): string
    {
        if ($statusDays === null) {
            return '—';
        }

        if ($statusDays < 0) {
            return abs($statusDays).' day'.(abs($statusDays) === 1 ? '' : 's').' overdue';
        }

        if ($statusDays === 0) {
            return 'Due today';
        }

        return $statusDays.' day'.($statusDays === 1 ? '' : 's').' left';
    }

    /**
     * Format receipt as Y-m-d, appending radio_active_levels when it looks like a clock time.
     */
    public static function formatReceiptDateTime(mixed $receiptDate, mixed $receiptTime = null): string
    {
        $dateOnly = self::formatDateOnly($receiptDate);
        if ($dateOnly === 'N/A') {
            return 'N/A';
        }

        $time = trim((string) ($receiptTime ?? ''));
        if ($time === '' || ! preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $time)) {
            return $dateOnly;
        }

        try {
            $normalizedTime = Carbon::parse($time)->format('H:i');
        } catch (\Throwable) {
            return $dateOnly;
        }

        return $dateOnly.' '.$normalizedTime;
    }

    public static function formatDateOnly(mixed $date): string
    {
        if ($date === null || trim((string) $date) === '') {
            return 'N/A';
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            return trim((string) $date);
        }
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function getAssignableUsersForBatchAssignmentProperty(): array
    {
        $this->ensureReferenceDataLoaded();

        return $this->users
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
            'subcontract-dispatch-completed' => 'onSubcontractDispatchCompleted',
            'process-enquiry-completed' => 'handleProcessEnquiryCompleted',
        ];
    }

    public function render()
    {
        // Reference data is loaded here (not in mount) so it is never written into
        // the Livewire encrypted snapshot, which would bloat every request/response.
        $this->loadReferenceData();

        $isFormOnlyStage = $this->isSamplesReceiving() || $this->isSamplesRequestReview();

        $batches = collect();
        $tatTodayBatches = collect();

        if (! $isFormOnlyStage) {
            $batches = $this->batches;

            // Compute once to avoid running the query twice (tatTodayCount calls tatTodayBatches).
            $tatTodayBatches = $this->tatTodayBatches;
        }

        $natureOfSampleOptions = Cache::remember(
            'workflow-board:nature-of-sample-options',
            now()->addMinutes(10),
            function () {
                return \App\Models\System\SystemConfigurationsType::where('configuration_type', '=', 'Nature of Sample', 'and')
                    ->with('configurations')
                    ->first()
                    ?->configurations
                    ?->sortBy('key')
                    ?->values()
                    ?? collect();
            }
        );

        return view('livewire.sampleworkflow.workflow-board', [
            'batches' => $batches,
            'natureOfSampleOptions' => $natureOfSampleOptions,
            'labsections' => $this->labsections,
            'analysts' => $this->analysts,
            'users' => $this->users,
            'zoho_items' => $this->zohoItems,
            'clients' => $this->clients,
            'sampletypes' => $this->sampletypes,
            'customers' => $this->customers,
            'submissionFormAttachmentTypeId' => $this->submissionFormAttachmentTypeId,
            'portalSubmissions' => $isFormOnlyStage ? null : $this->portalSubmissions,
            'commercialEnquiries' => $this->commercialEnquiries,
            'samplesReceptionStats' => $this->samplesReceptionStats,
            'receivingDashboardStats' => $this->receivingDashboardStats,
            'receivingRequestTabs' => self::receivingRequestTabs(),
            'receivingRequestTabCounts' => $this->receivingRequestTabCounts,
            'requestReviewTabs' => self::requestReviewTabs(),
            'requestReviewTabCounts' => $this->requestReviewTabCounts,
            // Unused in the blade today — skip query work on form-only stages.
            'myPendingIntrayForms' => collect(),
            'myPendingIntrayCount' => 0,
            'assignableUsersForIntray' => [],
            'assignableUsersForBatchAssignment' => $isFormOnlyStage ? [] : $this->assignableUsersForBatchAssignment,
            'subcontractingDispatchStatuses' => $this->subcontractingDispatchStatuses,
            'tatTodayBatches' => $tatTodayBatches,
            'tatTodayCount' => $tatTodayBatches->count(),
        ]);
    }
}
