<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\SamplingSchedule;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormSection;
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\CRM\CRMCustomerService;
use App\Services\CRM\CustomerContactPrefillService;
use App\Services\Planner\SamplingScheduleCollectionProgress;
use App\Services\Planner\SamplingScheduleTrfSync;
use App\Services\Sampleworkflow\ReceivingLabMetadataService;
use App\Services\Sampleworkflow\WalkInParameterCatalogService;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use App\Services\Sampleworkflow\SampleReceivingCheckInService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Services\SubmissionForm\SubmissionFormSubmissionService;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use App\Support\TrfToast;
use App\Services\SubmissionForm\TrfDocumentCodeForSampleType;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ReceiveSampleRequest extends Component
{
    use AppliesCaseInsensitiveSearch;
    /** @var array<int, string> */
    public array $selectedFormInstanceIds = [];

    /** @var array<int, array{id: string, label: string, customer: string}> */
    public array $selectedFormSummaries = [];

    /** @var list<array<string, mixed>> */
    public array $checkInContexts = [];

    /** @var array<string, array<string, string>> */
    public array $checkInTrfFields = [];

    public string $remarks = '';

    // Dynamic SubmissionForm TRF capture state
    public ?string $selectedSampleTypeId = null;
    public ?string $selectedSampleTypeCategoryId = null;

    public array $formData = [];

    public $sampleTypes = [];

    /** @var array<int, string> */
    public array $lastGeneratedSfiIds = [];

    public bool $showWalkInAddContactModal = false;

    public bool $showWalkInAddCustomerModal = false;

    public string $walkInNewCustomerName = '';

    public string $walkInNewCustomerEmail = '';

    public string $walkInNewCustomerPhone = '';

    public string $walkInNewCustomerAddress = '';

    public bool $showWalkInAddPointModal = false;

    public bool $showWalkInAddUnitModal = false;

    public string $walkInNewUnitName = '';

    public string $walkInNewContactName = '';

    public string $walkInNewContactUnitId = '';

    public string $walkInNewContactEmail = '';

    public string $walkInNewContactPhone = '';

    public string $walkInNewPointName = '';

    public string $walkInNewPointUnitId = '';

    public string $walkInSamplePointTargetField = 'sampling_location';

    public ?int $walkInSamplePointTargetRowIndex = null;

    public int $walkInActiveStepIndex = 0;

    public ?string $lastSelectedSampleTypeId = null;

    /** When true, render tablet RFT page chrome instead of modal chrome. */
    public bool $pageMode = false;

    /** When true, only the wizard is shown (dedicated fill page). */
    public bool $wizardOnly = false;

    /** When true, use System Planner routes/wording and schedule-linked submit. */
    public bool $plannerMode = false;

    public ?string $initialSampleTypeId = null;

    public ?string $initialSubmissionFormId = null;

    public ?string $selectedSubmissionFormId = null;

    public ?string $initialScheduleId = null;

    public ?string $selectedScheduleId = null;

    /** Resolved CRM customer for walk-in contact/point actions (avoids fragile name-only matching). */
    public ?string $selectedCrmCustomerId = null;

    /** Request origin for staff capture: walk_in (default) or offline direct registration. */
    public string $intakeChannel = CommercialEnquirySyncService::SOURCE_WALK_IN;

    /** Direct Registration modal stages: select_trf | fill_trf | post_save */
    public string $directRegistrationStage = 'select_trf';

    /** Direct Registration carousel pane: register | receive */
    public string $directRegistrationPane = 'register';

    public bool $directRegistrationCarouselReady = false;

    public string $lastSavedTrfName = '';

    /** Open Drafts / Today tab on the RFT list page (or pending/filled for planner). */
    public string $rftInstancesTab = 'today';

    public string $rftInstancesSearch = '';

    public bool $showPhysicalConfirmModal = false;

    public bool $showHiddenRftForms = false;

    /**
     * Safe handler when client-side code probes $wire.toJSON during serialization.
     *
     * @return array<string, mixed>
     */
    public function toJSON(mixed $value = null): array
    {
        return [];
    }

    public function mount(
        array $selectedFormInstanceIds = [],
        array $selectedFormSummaries = [],
        bool $pageMode = false,
        bool $wizardOnly = false,
        bool $plannerMode = false,
        ?string $initialSampleTypeId = null,
        ?string $initialScheduleId = null,
        ?string $initialSubmissionFormId = null,
    ): void {
        $this->pageMode = $pageMode;
        $this->wizardOnly = $wizardOnly;
        $this->plannerMode = $plannerMode;
        $this->initialSampleTypeId = $initialSampleTypeId;
        $this->initialScheduleId = $initialScheduleId;
        $this->initialSubmissionFormId = $initialSubmissionFormId;
        $this->selectedFormInstanceIds = array_values(array_filter($selectedFormInstanceIds));
        $this->selectedFormSummaries = $selectedFormSummaries;

        if ($this->plannerMode) {
            $this->rftInstancesTab = 'pending';
        }

        $this->sampleTypes = \App\SampleType::orderBy('name')->get();

        if ($this->initialSubmissionFormId) {
            $this->selectedSubmissionFormId = (string) $this->initialSubmissionFormId;
            $form = SubmissionForm::query()
                ->with(['sections.elementHolders.elements'])
                ->find($this->selectedSubmissionFormId);
            if ($form !== null) {
                $this->initializeFormDataFromSubmissionForm($form);
            }
        } elseif ($this->initialSampleTypeId) {
            $this->selectedSampleTypeId = (string) $this->initialSampleTypeId;
            $this->lastSelectedSampleTypeId = (string) $this->initialSampleTypeId;
            $this->initializeFormDataForSampleType((string) $this->initialSampleTypeId);
        }

        if ($this->plannerMode && $this->initialScheduleId) {
            if ($this->wizardOnly) {
                $this->applyScheduleSelection((string) $this->initialScheduleId, forceSampleType: false);
            } else {
                // Hub: keep the schedule selected so "Fill form" stays linked to it.
                $this->selectedScheduleId = (string) $this->initialScheduleId;
            }
        }

        // Auto-load form data if viewing an existing request
        if (! empty($this->selectedFormInstanceIds)) {
            $this->loadExistingFormData();
        }

        $this->refreshCheckInContexts();
    }

    private function loadExistingFormData(): void
    {
        if (empty($this->selectedFormInstanceIds)) {
            return;
        }

        $firstInstance = SubmissionFormInstance::with(['crmCustomer', 'submissionForm.sampleTypeCategories', 'batches.sampleType', 'values.element'])->find($this->selectedFormInstanceIds[0]);
        
        if (!$firstInstance) {
            return;
        }

        // Try to determine sample type from submission form categories
        $sampleType = null;
        if ($firstInstance->submissionForm) {
            $sampleType = app(\App\Services\SubmissionForm\PortalTestRequestFormSampleTypeResolver::class)
                ->resolveForForm($firstInstance->submissionForm)
                ->first();
        }

        // If no sample type from submission form, try from batches
        if (!$sampleType && $firstInstance->batches->first()) {
            $sampleType = $firstInstance->batches->first()->sampleType;
        }

        // If we found a sample type, set it and manually load the form data
        if ($sampleType) {
            $this->selectedSampleTypeId = $sampleType->id;
            $this->lastSelectedSampleTypeId = (string) $sampleType->id;
            $this->initializeFormDataForSampleType($sampleType->id);
            $this->loadFormDataFromInstance($firstInstance);
        }
    }

    private function initializeFormDataForSampleType(string $sampleTypeId): void
    {
        $submissionForm = $this->resolveSubmissionFormForSampleType($sampleTypeId);
        if ($submissionForm === null) {
            $this->formData = [];
            $this->selectedCrmCustomerId = null;

            return;
        }

        $this->initializeFormDataFromSubmissionForm($submissionForm);
    }

    private function loadFormDataFromInstance(SubmissionFormInstance $instance): void
    {
        $normalizer = app(SubmissionFormValueNormalizer::class);
        $existingFormData = $normalizer->valuesMapFromInstance($instance);

        if ($existingFormData !== []) {
            foreach ($existingFormData as $key => $val) {
                if (array_key_exists($key, $this->formData)) {
                    $this->formData[$key] = $val;
                }
            }
        }

        if (isset($this->formData['extra_sampling_equipment'])) {
            $this->formData['extra_sampling_equipment'] = $this->decodeExtraSamplingEquipmentRows(
                $this->formData['extra_sampling_equipment']
            );
        }

        $this->ensureAdditionalDetailsFormField(max(1, $this->schemaRowCount()));
        $this->hydrateSamplingEquipmentIdRows();

        if ($instance->crmCustomer) {
            $this->applyCustomerPrefillFromCrm($instance->crmCustomer, onlyEmpty: true);
        }
    }

    private function resolveSubmissionFormForSampleType(string $sampleTypeId): ?SubmissionForm
    {
        $form = app(PortalSubmissionFormAccess::class)->testRequestFormForSampleType($sampleTypeId);
        if ($form === null) {
            return null;
        }

        return $form->loadMissing(['sections.elementHolders.elements']);
    }

    private function limsCompanyId(): ?string
    {
        $candidates = array_values(array_filter([
            function_exists('getUserCompany') ? getUserCompany() : null,
            auth()->user()?->company_id,
            function_exists('currentCompanyForFeatures') ? currentCompanyForFeatures()?->id : null,
        ], static fn ($id): bool => filled($id)));

        foreach ($candidates as $candidate) {
            $companyId = (string) $candidate;
            $hasTrf = SubmissionForm::query()
                ->forCompany($companyId)
                ->where('is_active', true)
                ->where('is_published', true)
                ->where('form_type', 'template')
                ->where(function ($q): void {
                    $q->where('document_code', 'like', 'TRF%')
                        ->orWhereRaw('lower(name) like ?', ['%test request form%']);
                })
                ->exists();

            if ($hasTrf) {
                return $companyId;
            }
        }

        // Last resort for Brazil local DBs: use the brl company that owns seeded TRFs.
        $brazilId = \App\Company::query()
            ->whereRaw('LOWER(TRIM(code)) = ?', ['brl'])
            ->value('id');

        return $brazilId !== null ? (string) $brazilId : ($candidates[0] ?? null);
    }

    private function resolveSubmissionFormForSampleTypeCategory(string $categoryId): ?SubmissionForm
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('submission_form_sample_type_categories')) {
            return null;
        }

        return SubmissionForm::query()
            ->forCompany($this->limsCompanyId())
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('form_type', 'template')
            ->where(function ($q): void {
                $q->where('document_code', 'like', 'TRF%')
                    ->orWhereRaw('lower(name) like ?', ['%test request form%']);
            })
            ->whereHas('sampleTypeCategories', function ($query) use ($categoryId): void {
                $query->where('sample_type_categories.id', $categoryId);
            })
            ->with(['sections.elementHolders.elements'])
            ->orderBy('name')
            ->first();
    }

    private function initializeFormDataFromSubmissionForm(SubmissionForm $submissionForm): void
    {
        $this->formData = [];
        $schemaHelper = app(SubmissionFormSchemaHelper::class);

        foreach ($schemaHelper->uniqueSections($submissionForm) as $section) {
            if (SubmissionFormSchemaHelper::isBuilderHiddenSection($section)) {
                continue;
            }

            $elements = $section->elementHolders->flatMap->elements->sortBy('sort_order')
                ->reject(fn (SubmissionFormElement $element): bool => SubmissionFormSchemaHelper::shouldOmitFromFillForm($element, $section));

            if (($section->section_type ?? '') === 'rows_section' || $this->walkInSectionUsesSampleCards($section)) {
                foreach ($elements as $element) {
                    $this->formData[$element->name] = [$this->defaultValueForElement($element)];
                }
                $this->ensureWalkInCanonicalQtyFields(1);
                $this->ensureWalkInSampleTypeField(1);

                continue;
            }

            foreach ($elements as $element) {
                $this->formData[$element->name] = $this->defaultValueForElement($element);
            }
        }

        $this->ensureWaterTrfSyntheticFormFields($submissionForm);
        $this->ensureWasteWaterTrfSyntheticFormFields($submissionForm);
        $this->ensureAdditionalDetailsFormField(1);
        $this->hydrateSamplingEquipmentIdRows();
    }

    public function getWalkInSectionsProperty(): Collection
    {
        $form = $this->submissionForm;

        if ($form === null) {
            return collect();
        }

        $sections = app(SubmissionFormSchemaHelper::class)->uniqueSections($form)
            ->reject(fn ($section) => ($section->title ?? '') === 'TRF storage')
            ->reject(fn ($section) => SubmissionFormSchemaHelper::isBuilderHiddenSection($section))
            ->reject(fn ($section) => SubmissionFormSchemaHelper::isSampleCollectionSectionTitle($section->title ?? null));

        return $sections->values();
    }

    /** @return list<array{index: int, key: string, label: string, title: string, is_rows: bool}> */
    public function getWalkInWizardStepsProperty(): array
    {
        return $this->walkInSections
            ->values()
            ->map(fn (SubmissionFormSection $section, int $index): array => [
                'index' => $index,
                'key' => (string) $section->id,
                'label' => $this->walkInStepShortLabel((string) ($section->title ?? 'Step')),
                'title' => (string) ($section->title ?? ''),
                'is_rows' => $this->walkInSectionUsesSampleCards($section),
            ])
            ->all();
    }

    public function getWalkInWizardProgressProperty(): int
    {
        $total = max(1, $this->walkInSections->count());

        // Step-based progress: step 1 of 5 => 20%, step 2 => 40%, etc.
        return (int) round((($this->walkInActiveStepIndex + 1) / $total) * 100);
    }

    public function getWalkInIsFirstStepProperty(): bool
    {
        return $this->walkInActiveStepIndex <= 0;
    }

    public function getWalkInIsLastStepProperty(): bool
    {
        $maxIndex = max(0, $this->walkInSections->count() - 1);

        return $this->walkInActiveStepIndex >= $maxIndex;
    }

    public function getWalkInTotalStepsProperty(): int
    {
        return $this->walkInSections->count();
    }

    /**
     * Wizard is ready when a TRF is resolved (by sample type or direct form id).
     */
    public function getIsWalkInCaptureReadyProperty(): bool
    {
        if ($this->submissionForm === null) {
            return false;
        }

        return filled($this->selectedSampleTypeId) || filled($this->selectedSubmissionFormId);
    }

    /**
     * TRF cards for the Request For Testing page (UI only).
     *
     * Walk-in RFT shows category-bound TRFs first, then unlinked templates.
     * Planner mode still lists by sample type.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getFormTypeCardsProperty(): Collection
    {
        $linkedFormIds = [];

        if ($this->plannerMode) {
            return collect($this->sampleTypes)
                ->map(function ($sampleType) use (&$linkedFormIds): ?array {
                    $form = $this->resolveSubmissionFormForSampleType((string) $sampleType->id);
                    if ($form === null) {
                        return null;
                    }

                    if (! $this->shouldIncludeFormOnRft($form)) {
                        return null;
                    }

                    $linkedFormIds[(string) $form->id] = true;

                    return [
                        'submission_form_id' => (string) $form->id,
                        'sample_type_id' => (string) $sampleType->id,
                        'requires_inline_sample_type' => false,
                        'is_hidden_from_rft' => (bool) ($form->is_hidden_from_rft ?? false),
                        'name' => (string) $sampleType->name,
                        'sample_type_name' => (string) $sampleType->name,
                        'document_code' => $form->document_code,
                        'sections_count' => $this->wizardStepCountForForm($form),
                        'description' => filled($form->description)
                            ? (string) $form->description
                            : 'Fill a sampling form for '.$sampleType->name.'.',
                        'icon' => 'mdi-file-document-edit-outline',
                        'view_url' => route('submission-forms.show', ['submissionForm' => $form, 'from' => 'rft']),
                        'edit_url' => route('submission-forms.builder', ['submissionForm' => $form, 'from' => 'rft']),
                        'details_url' => route('submission-forms.edit', ['submissionForm' => $form, 'from' => 'rft']),
                        'start_action' => 'sampleType',
                    ];
                })
                ->filter()
                ->values();
        }

        // One card per category-bound TRF — vial line shows category name(s).
        $categoryBoundCards = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('submission_form_sample_type_categories')) {
            $categoryBoundForms = SubmissionForm::query()
                ->forCompany($this->limsCompanyId())
                ->where('is_active', true)
                ->where('is_published', true)
                ->where('form_type', 'template')
                ->where(function ($q): void {
                    $q->where('document_code', 'like', 'TRF%')
                        ->orWhereRaw('lower(name) like ?', ['%test request form%']);
                })
                ->whereHas('sampleTypeCategories')
                ->when(
                    ! $this->showHiddenRftForms,
                    fn ($q) => $q->where(function ($hq): void {
                        $hq->where('is_hidden_from_rft', false)->orWhereNull('is_hidden_from_rft');
                    }),
                )
                ->with(['sampleTypeCategories'])
                ->orderBy('name')
                ->get();

            $categoryBoundCards = $categoryBoundForms
                ->map(function (SubmissionForm $form) use (&$linkedFormIds): array {
                    $form->loadMissing(['sections.elementHolders.elements']);
                    $linkedFormIds[(string) $form->id] = true;

                    $categoryNames = $form->sampleTypeCategories
                        ->pluck('sample_type_category')
                        ->filter()
                        ->unique()
                        ->values()
                        ->implode(', ');

                    $cardTitle = (string) $form->name;

                    return [
                        'submission_form_id' => (string) $form->id,
                        'sample_type_id' => null,
                        'requires_inline_sample_type' => true,
                        'is_hidden_from_rft' => (bool) ($form->is_hidden_from_rft ?? false),
                        'name' => $cardTitle,
                        'sample_type_name' => $categoryNames !== '' ? $categoryNames : 'Select in form',
                        'document_code' => $form->document_code,
                        'sections_count' => $this->wizardStepCountForForm($form),
                        'description' => filled($form->description)
                            ? (string) $form->description
                            : 'Capture a test request for '.$cardTitle.'.',
                        'icon' => 'mdi-file-document-edit-outline',
                        'view_url' => route('submission-forms.show', ['submissionForm' => $form, 'from' => 'rft']),
                        'edit_url' => route('submission-forms.builder', ['submissionForm' => $form, 'from' => 'rft']),
                        'details_url' => route('submission-forms.edit', ['submissionForm' => $form, 'from' => 'rft']),
                        'start_action' => 'form',
                    ];
                })
                ->values();
        }

        $unlinkedCards = SubmissionForm::query()
            ->forCompany($this->limsCompanyId())
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('form_type', 'template')
            ->where(function ($query): void {
                $query->where('document_code', 'like', 'TRF%')
                    ->orWhereRaw('lower(name) like ?', ['%test request form%']);
            })
            ->when(
                \Illuminate\Support\Facades\Schema::hasTable('submission_form_sample_type_categories'),
                fn ($q) => $q->whereDoesntHave('sampleTypeCategories')
            )
            ->when(
                ! $this->showHiddenRftForms,
                fn ($query) => $query->where(function ($hiddenQuery): void {
                    $hiddenQuery->where('is_hidden_from_rft', false)->orWhereNull('is_hidden_from_rft');
                }),
            )
            ->orderBy('name')
            ->get()
            ->reject(fn (SubmissionForm $form): bool => isset($linkedFormIds[(string) $form->id]))
            ->map(function (SubmissionForm $form): array {
                $form->loadMissing(['sections.elementHolders.elements']);

                return [
                    'submission_form_id' => (string) $form->id,
                    'sample_type_id' => null,
                    'requires_inline_sample_type' => true,
                    'is_hidden_from_rft' => (bool) ($form->is_hidden_from_rft ?? false),
                    'name' => (string) $form->name,
                    'sample_type_name' => 'Select in form',
                    'document_code' => $form->document_code,
                    'sections_count' => $this->wizardStepCountForForm($form),
                    'description' => filled($form->description)
                        ? (string) $form->description
                        : 'TRF with no linked sample type — choose sample type in the form.',
                    'icon' => 'mdi-file-document-edit-outline',
                    'view_url' => route('submission-forms.show', ['submissionForm' => $form, 'from' => 'rft']),
                    'edit_url' => route('submission-forms.builder', ['submissionForm' => $form, 'from' => 'rft']),
                    'details_url' => route('submission-forms.edit', ['submissionForm' => $form, 'from' => 'rft']),
                    'start_action' => 'form',
                ];
            })
            ->values();

        return $categoryBoundCards->concat($unlinkedCards)->values();
    }

    private function shouldIncludeFormOnRft(SubmissionForm $form): bool
    {
        if ($this->showHiddenRftForms) {
            return true;
        }

        return ! (bool) ($form->is_hidden_from_rft ?? false);
    }

    public function toggleFormHiddenFromRft(string $submissionFormId): void
    {
        $form = SubmissionForm::query()->find($submissionFormId);
        if ($form === null || ! $form->isTestRequestTemplate()) {
            TrfToast::dispatch($this, 'error', 'Test request form not found.');

            return;
        }

        $form->is_hidden_from_rft = ! (bool) $form->is_hidden_from_rft;
        $form->save();

        TrfToast::dispatch(
            $this,
            'success',
            $form->is_hidden_from_rft
                ? 'Form hidden from Request For Testing.'
                : 'Form visible on Request For Testing again.',
            'Form visibility updated',
            'glass',
        );
    }

    public function toggleShowHiddenRftForms(): void
    {
        $this->showHiddenRftForms = ! $this->showHiddenRftForms;
    }

    public function getHasHiddenRftFormsProperty(): bool
    {
        if ($this->plannerMode) {
            return false;
        }

        return SubmissionForm::query()
            ->forCompany($this->limsCompanyId())
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('form_type', 'template')
            ->where('is_hidden_from_rft', true)
            ->where(function ($query): void {
                $query->where('document_code', 'like', 'TRF%')
                    ->orWhereRaw('lower(name) like ?', ['%test request form%']);
            })
            ->exists();
    }

    /**
     * Wizard step count shown on form cards (matches the stepper, not raw section rows).
     */
    private function wizardStepCountForForm(SubmissionForm $form): int
    {
        return max(1, app(SubmissionFormSchemaHelper::class)->uniqueSections($form)
            ->reject(fn ($section) => ($section->title ?? '') === 'TRF storage')
            ->count());
    }

    /**
     * Draft / today walk-in submissions for the RFT list page.
     *
     * @return Collection<int, SubmissionFormInstance>
     */
    public function getRftInstancesProperty(): Collection
    {
        if (! $this->pageMode || $this->wizardOnly || $this->plannerMode) {
            return collect();
        }

        $formIds = collect($this->formTypeCards)
            ->pluck('submission_form_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($formIds === []) {
            return collect();
        }

        $query = SubmissionFormInstance::query()
            ->with(['submissionForm'])
            ->whereIn('submission_form_id', $formIds)
            ->orderByDesc('updated_at');

        if ($this->rftInstancesTab === 'today') {
            $query->where(function ($q): void {
                $q->whereDate('created_at', today())
                    ->orWhereDate('submitted_at', today());
            });
        } else {
            $query->where('status', 'draft');
        }

        $search = trim($this->rftInstancesSearch);
        if ($search !== '') {
            $this->applyCaseInsensitiveSearch($query, ['form_number', 'title'], $search);
        }

        return $query->limit(25)->get();
    }

    /**
     * Pending / recently filled sampling schedules for the planner fill page.
     *
     * @return Collection<int, SamplingSchedule>
     */
    public function getPlannerSchedulesProperty(): Collection
    {
        if (! $this->pageMode || $this->wizardOnly || ! $this->plannerMode) {
            return collect();
        }

        $query = SamplingSchedule::query()
            ->visibleTo()
            ->with(['client', 'sample_type', 'submissionFormInstances.values.element'])
            ->orderByDesc('sampling_datetime');

        if ($this->rftInstancesTab === 'filled') {
            $query->where('is_collected', true)
                ->where(function ($q): void {
                    $q->whereDate('updated_at', today())
                        ->orWhereDate('sampling_datetime', today());
                });
        } else {
            // Pending + partial (not yet fully collected)
            $query->where(function ($q): void {
                $q->where('is_collected', false)->orWhereNull('is_collected');
            });
        }

        $search = trim($this->rftInstancesSearch);
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $this->applyCaseInsensitiveSearch($q, ['title', 'location'], $search);
                $q->orWhereHas('client', function ($customerQuery) use ($search): void {
                    $this->applyCaseInsensitiveSearch($customerQuery, ['name'], $search);
                });
            });
        }

        return $query->limit(25)->get();
    }

    /**
     * Schedules available to link on the planner wizard for the selected sample type.
     *
     * @return Collection<int, SamplingSchedule>
     */
    public function getPlannerScheduleOptionsProperty(): Collection
    {
        if (! $this->plannerMode || ! $this->selectedSampleTypeId) {
            return collect();
        }

        $sampleTypeId = (string) $this->selectedSampleTypeId;

        return SamplingSchedule::query()
            ->visibleTo()
            ->with(['client'])
            ->where(function ($q): void {
                $q->where('is_collected', false)->orWhereNull('is_collected');
            })
            ->orderBy('sampling_datetime')
            ->limit(100)
            ->get()
            ->filter(fn (SamplingSchedule $schedule): bool => $schedule->isCompatibleWithSampleType($sampleTypeId))
            ->sortBy([
                fn (SamplingSchedule $schedule): int => $schedule->matchesSampleType($sampleTypeId) ? 0 : 1,
                fn (SamplingSchedule $schedule): int => optional($schedule->sampling_datetime)?->timestamp ?? PHP_INT_MAX,
            ])
            ->take(50)
            ->values();
    }

    private function findPendingScheduleForSampleType(string $sampleTypeId): ?SamplingSchedule
    {
        $candidates = SamplingSchedule::query()
            ->visibleTo()
            ->where(function ($q): void {
                $q->where('is_collected', false)->orWhereNull('is_collected');
            })
            ->orderBy('sampling_datetime')
            ->limit(100)
            ->get();

        return $candidates->first(fn (SamplingSchedule $schedule): bool => $schedule->matchesSampleType($sampleTypeId))
            ?? $candidates->first(fn (SamplingSchedule $schedule): bool => $schedule->isCompatibleWithSampleType($sampleTypeId));
    }

    public function setRftInstancesTab(string $tab): void
    {
        if ($this->plannerMode) {
            $this->rftInstancesTab = in_array($tab, ['pending', 'filled'], true) ? $tab : 'pending';

            return;
        }

        $this->rftInstancesTab = in_array($tab, ['open', 'today'], true) ? $tab : 'open';
    }

    public function clearRftInstanceFilters(): void
    {
        $this->rftInstancesSearch = '';
        $this->rftInstancesTab = $this->plannerMode ? 'pending' : 'today';
    }

    public function deleteRftDraft(string $instanceId): void
    {
        $instance = SubmissionFormInstance::query()->find($instanceId);
        if ($instance === null || $instance->status !== 'draft') {
            return;
        }

        $instance->delete();
    }

    public function startWalkInForSampleType(string $sampleTypeId): void
    {
        if ($this->pageMode && ! $this->wizardOnly) {
            if ($this->plannerMode) {
                $scheduleId = trim((string) ($this->selectedScheduleId ?: $this->initialScheduleId ?: ''));

                if ($scheduleId === '') {
                    $pendingSchedule = $this->findPendingScheduleForSampleType((string) $sampleTypeId);
                    if ($pendingSchedule === null) {
                        TrfToast::dispatch(
                            $this,
                            'error',
                            'Select a pending schedule first (use Fill on the schedule). Sampling forms must be linked to a schedule.',
                        );

                        return;
                    }
                    $scheduleId = (string) $pendingSchedule->id;
                }

                $this->redirect(
                    route('system-planner.fill-sampling-forms.fill', [
                        'sampleType' => $sampleTypeId,
                        'schedule' => $scheduleId,
                    ]),
                    navigate: false,
                );

                return;
            }

            $this->redirect(
                route('sample-workflow.request-for-testing.fill', ['sampleType' => $sampleTypeId]),
                navigate: false,
            );

            return;
        }

        $this->selectedSampleTypeId = $sampleTypeId;
        $this->selectedSubmissionFormId = null;

        if ($this->isOfflineIntake()) {
            $this->directRegistrationStage = 'fill_trf';
            $this->directRegistrationPane = 'register';
            $this->walkInActiveStepIndex = 0;
        }
    }

    public function startWalkInForForm(string $submissionFormId): void
    {
        if ($this->plannerMode) {
            return;
        }

        if ($this->pageMode && ! $this->wizardOnly) {
            $this->redirect(
                route('sample-workflow.request-for-testing.fill-form', ['submissionForm' => $submissionFormId]),
                navigate: false,
            );

            return;
        }

        $this->selectedSubmissionFormId = $submissionFormId;
        $this->selectedSampleTypeId = null;
        $this->selectedSampleTypeCategoryId = null;
        $form = SubmissionForm::query()
            ->with(['sections.elementHolders.elements'])
            ->find($submissionFormId);
        if ($form !== null) {
            $this->initializeFormDataFromSubmissionForm($form);
        }

        if ($this->isOfflineIntake()) {
            $this->directRegistrationStage = 'fill_trf';
            $this->directRegistrationPane = 'register';
            $this->walkInActiveStepIndex = 0;
        }
    }

    public function startFillForSchedule(string $scheduleId): void
    {
        if (! $this->plannerMode) {
            return;
        }

        $schedule = SamplingSchedule::query()->visibleTo()->findOrFail($scheduleId);
        $sampleTypeId = $this->resolveSampleTypeIdFromSchedule($schedule);
        if ($sampleTypeId === null) {
            $this->redirect(
                route('system-planner.fill-sampling-forms', ['schedule' => $schedule->id]),
                navigate: false,
            );

            return;
        }

        $this->redirect(
            route('system-planner.fill-sampling-forms.fill', [
                'sampleType' => $sampleTypeId,
                'schedule' => $schedule->id,
            ]),
            navigate: false,
        );
    }

    public function clearSelectedSampleType(): void
    {
        if ($this->wizardOnly) {
            $hubRoute = $this->plannerMode
                ? 'system-planner.fill-sampling-forms'
                : 'sample-workflow.request-for-testing';
            $this->redirect(route($hubRoute), navigate: false);

            return;
        }

        $this->selectedSampleTypeId = null;
        $this->selectedSampleTypeCategoryId = null;
        $this->lastSelectedSampleTypeId = null;
        $this->selectedSubmissionFormId = null;
        $this->selectedScheduleId = null;
        $this->formData = [];
        $this->selectedCrmCustomerId = null;
        $this->walkInActiveStepIndex = 0;
    }

    public function updatedSelectedScheduleId(?string $value): void
    {
        if (! $this->plannerMode) {
            return;
        }

        if ($value === null || trim($value) === '') {
            $this->selectedScheduleId = null;

            return;
        }

        $this->applyScheduleSelection((string) $value, forceSampleType: true);
    }

    private function applyScheduleSelection(string $scheduleId, bool $forceSampleType = false): void
    {
        $schedule = SamplingSchedule::query()->visibleTo()->find($scheduleId);
        if ($schedule === null) {
            $this->selectedScheduleId = null;

            return;
        }

        $this->selectedScheduleId = (string) $schedule->id;

        $sampleTypeId = $this->resolveSampleTypeIdFromSchedule($schedule);
        if ($sampleTypeId !== null && ($forceSampleType || ! $this->selectedSampleTypeId)) {
            $this->selectedSampleTypeId = $sampleTypeId;
            $this->lastSelectedSampleTypeId = $sampleTypeId;
            $this->initializeFormDataForSampleType($sampleTypeId);
        }

        $submissionForm = $this->submissionForm;
        if ($submissionForm === null || ! $this->selectedSampleTypeId) {
            return;
        }

        $this->formData = app(SamplingScheduleTrfSync::class)->mergeIntoFormData(
            $this->formData,
            $schedule,
            (string) $this->selectedSampleTypeId,
            $submissionForm,
        );

        $this->dispatch('submission-form-reinit-signatures');
        $this->dispatch('trf-reinit-signatures');
        $this->dispatch('trf-reset-all-parameter-selects');
    }

    private function resolveSampleTypeIdFromSchedule(SamplingSchedule $schedule): ?string
    {
        if (! empty($schedule->sample_type_id)) {
            return (string) $schedule->sample_type_id;
        }

        foreach ((array) ($schedule->sample_details ?? []) as $entry) {
            if (! empty($entry['sample_type_id'])) {
                return (string) $entry['sample_type_id'];
            }
        }

        return null;
    }

    public function toggleWalkInParameter(int $rowIndex, string $paramName): void
    {
        $current = $this->formData['parameters'][$rowIndex] ?? [];
        if (! is_array($current)) {
            $current = $current !== '' && $current !== null ? [(string) $current] : [];
        }

        $paramName = trim($paramName);
        if ($paramName === '') {
            return;
        }

        if (in_array($paramName, $current, true)) {
            $current = array_values(array_filter($current, fn ($value) => (string) $value !== $paramName));
        } else {
            $current[] = $paramName;
        }

        $this->formData['parameters'][$rowIndex] = array_values($current);
    }

    public function selectAllWalkInParameters(int $rowIndex): void
    {
        $this->formData['parameters'][$rowIndex] = array_values(array_map(
            static fn (array $option): string => (string) $option['id'],
            $this->walkInParameterPickerState($rowIndex)['options']
        ));
    }

    public function clearWalkInParameters(int $rowIndex): void
    {
        $this->formData['parameters'][$rowIndex] = [];
    }

    /**
     * @param  list<string|int|float>  $parameters
     * @return array{rowIndex: int, selected: list<string>, analysisTypeIds: list<string>, count: int}
     */
    public function setWalkInParameters(int $rowIndex, array $parameters): array
    {
        $effectiveRow = ($rowIndex < 0 || ! $this->walkInUsesIndexedSampleRows()) ? null : $rowIndex;
        $rowCount = max($this->schemaRowCount(), ($effectiveRow ?? 0) + 1);
        $this->ensureWalkInIndexedRowField('parameters', $rowCount);
        $this->ensureWalkInIndexedRowField('analysis_type_id', $rowCount);

        $groups = $this->parameterGroupsForRow($effectiveRow);
        $catalog = $this->walkInParameterCatalog();
        $incoming = $this->flattenWalkInScalarList($parameters);
        $normalized = $catalog->normalizeSelection($incoming, $groups);

        if ($normalized === [] && $incoming !== []) {
            $normalized = $catalog->filterKnownParameterIds($incoming);
        }

        if ($effectiveRow === null) {
            $this->formData['parameters'] = $normalized;
            if (array_key_exists('parameter', $this->formData)) {
                $this->formData['parameter'] = $normalized;
            }
            $this->syncDerivedAnalysisTypesForRow(null, $normalized);
        } else {
            $this->formData['parameters'][$effectiveRow] = $normalized;
            $this->syncDerivedAnalysisTypesForRow($effectiveRow, $normalized);
        }

        $analysisTypeIds = $effectiveRow === null
            ? $this->flattenWalkInScalarList($this->formData['analysis_type_id'] ?? [])
            : $this->flattenWalkInScalarList($this->formData['analysis_type_id'][$effectiveRow] ?? []);

        $paramNames = collect($normalized)
            ->map(function (string $id) use ($groups): string {
                foreach ($groups as $group) {
                    foreach ($group['tests'] ?? [] as $test) {
                        if ((string) ($test['id'] ?? '') === $id) {
                            return (string) ($test['name'] ?? $id);
                        }
                    }
                }

                return $id;
            })
            ->values()
            ->all();

        $analysisTypeNames = $analysisTypeIds === []
            ? []
            : \App\AnalysisType::query()
                ->whereIn('id', $analysisTypeIds)
                ->orderBy('name')
                ->pluck('name')
                ->map(static fn ($name): string => (string) $name)
                ->values()
                ->all();

        $result = [
            'rowIndex' => $effectiveRow ?? -1,
            'selected' => $normalized,
            'paramNames' => $paramNames,
            'analysisTypeIds' => $analysisTypeIds,
            'analysisTypeNames' => $analysisTypeNames,
            'analysisLabel' => $analysisTypeNames !== []
                ? implode(', ', array_slice($analysisTypeNames, 0, 2)).(count($analysisTypeNames) > 2 ? '…' : '')
                : '',
            'count' => count($normalized),
            'preview' => implode(', ', array_slice($paramNames, 0, 2)),
        ];

        $this->dispatchWalkInTestsSelectionSync($effectiveRow ?? -1, $normalized, $paramNames);

        return $result;
    }

    /**
     * @param  list<string>  $selected
     */
    private function dispatchWalkInTestsSelectionSync(int $rowIndex, array $selected, array $paramNames = []): void
    {
        $this->dispatch(
            'walk-in-params-row-reset',
            rowIndex: $rowIndex,
            selected: array_values($selected),
            paramNames: array_values($paramNames),
        );
    }

    /**
     * Sync Alpine multi sample-type picker into formData.
     *
     * @param  list<string|int|float>  $sampleTypeIds
     */
    public function setWalkInSampleTypes(string $wireKey, array $sampleTypeIds): void
    {
        $relative = preg_replace('/^formData\./', '', $wireKey) ?: 'sample_type_id';
        $ids = $this->normalizeSampleTypeIdList($sampleTypeIds);

        data_set($this->formData, $relative, $ids);

        $rowIndex = null;
        if (preg_match('/^(?:sample_type_id|sample_type)\.(\d+)$/', $relative, $matches)) {
            $rowIndex = (int) $matches[1];
        }

        $this->applySampleTypeSelectionFromIds($ids, $rowIndex);
        $this->skipRender();
    }

    /**
     * Sync analysis-type checkbox toggle into formData and refresh test options.
     */
    public function toggleWalkInAnalysisType(string $wireKey, string $analysisTypeId): void
    {
        $relative = preg_replace('/^formData\./', '', $wireKey) ?: 'analysis_type_id';
        $id = trim((string) $analysisTypeId);
        if ($id === '') {
            return;
        }

        $current = data_get($this->formData, $relative, []);
        if (! is_array($current)) {
            $current = filled($current) ? [(string) $current] : [];
        }

        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($value): string => (string) $value,
            $current
        ), static fn (string $value): bool => $value !== '')));

        if (in_array($id, $ids, true)) {
            $ids = array_values(array_filter($ids, static fn (string $value): bool => $value !== $id));
        } else {
            $ids[] = $id;
        }

        data_set($this->formData, $relative, $ids);

        $rowIndex = null;
        if (preg_match('/^(?:analysis_type_id|analysis_type|analysis_types)\.(\d+)$/', $relative, $matches)) {
            $rowIndex = (int) $matches[1];
        }

        $this->refreshWalkInParametersForAnalysisChange($rowIndex);
        $this->dispatchWalkInCatalogOptionsRefresh($rowIndex, ['parameters']);
    }

    /**
     * Sync Alpine multi analysis-type picker into formData.
     *
     * @param  list<string|int|float>  $analysisTypeIds
     */
    public function setWalkInAnalysisTypes(string $wireKey, array $analysisTypeIds): void
    {
        $relative = preg_replace('/^formData\./', '', $wireKey) ?: 'analysis_type_id';
        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($value): string => (string) $value,
            $analysisTypeIds
        ), static fn (string $id): bool => $id !== '')));

        data_set($this->formData, $relative, $ids);

        $rowIndex = null;
        if (preg_match('/^(?:analysis_type_id|analysis_type|analysis_types)\.(\d+)$/', $relative, $matches)) {
            $rowIndex = (int) $matches[1];
        }

        $this->refreshWalkInParametersForAnalysisChange($rowIndex);
        $this->dispatchWalkInCatalogOptionsRefresh($rowIndex, ['parameters']);
    }

    /**
     * Rebuild parameter options for the row after analysis types change,
     * keeping only still-valid selections (no auto-select-all).
     */
    private function refreshWalkInParametersForAnalysisChange(?int $rowIndex): void
    {
        $state = $this->walkInParameterPickerState($rowIndex);
        $groups = $state['groups'];
        $currentSelected = [];

        if ($rowIndex !== null) {
            $currentSelected = $this->formData['parameters'][$rowIndex] ?? [];
        } else {
            foreach (['parameter', 'parameters'] as $paramKey) {
                if (array_key_exists($paramKey, $this->formData)) {
                    $currentSelected = $this->formData[$paramKey];
                    break;
                }
            }
        }

        if (! is_array($currentSelected)) {
            $currentSelected = filled($currentSelected) ? [(string) $currentSelected] : [];
        }

        $selected = $this->normalizeWalkInParameterSelection(
            array_values(array_map(static fn ($value): string => (string) $value, $currentSelected)),
            $groups
        );

        if ($rowIndex !== null) {
            $this->formData['parameters'][$rowIndex] = $selected;

            return;
        }

        foreach (['parameter', 'parameters'] as $paramKey) {
            if (array_key_exists($paramKey, $this->formData)) {
                $this->formData[$paramKey] = $selected;
            }
        }

        if (! array_key_exists('parameters', $this->formData) && ! array_key_exists('parameter', $this->formData)) {
            $this->formData['parameters'] = $selected;
        }
    }

    /**
     * @return array{sample_type_id: list<array{value: string, label: string}>, analysis_type_id: list<array{value: string, label: string}>, parameters: list<array{value: string, label: string, meta?: string, meta_method?: string, meta_lab?: string}>}
     */
    private function buildWalkInRowSelectOptions(?int $rowIndex): array
    {
        $sampleTypeOptions = $this->walkInSampleTypeOptions
            ->map(static fn ($sampleType): array => [
                'value' => (string) $sampleType->id,
                'label' => (string) $sampleType->name,
            ])
            ->values()
            ->all();

        $analysisTypeOptions = $this->analysisTypesForRow($rowIndex)
            ->map(function ($analysisType): array {
                $sampleTypeName = trim((string) (optional($analysisType->sample_type)->name ?? ''));
                $label = (string) $analysisType->name;
                if ($sampleTypeName !== '') {
                    $label .= ' ('.$sampleTypeName.')';
                }

                return [
                    'value' => (string) $analysisType->id,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();

        return [
            'sample_type_id' => $sampleTypeOptions,
            'analysis_type_id' => $analysisTypeOptions,
            'parameters' => $this->walkInParameterSelectOptions($rowIndex),
        ];
    }

    /**
     * @param  list<string>  $cleared
     */
    private function dispatchWalkInCatalogOptionsRefresh(?int $rowIndex, array $cleared = []): void
    {
        $this->dispatch(
            'walk-in-catalog-options-refreshed',
            rowIndex: $rowIndex ?? -1,
            options: $this->buildWalkInRowSelectOptions($rowIndex),
            cleared: $cleared,
        );

        $this->skipRender();
    }

    private function walkInUsesIndexedSampleRows(): bool
    {
        return $this->walkInSections->contains(
            fn ($section): bool => $this->walkInSectionUsesSampleCards($section)
        );
    }

    /**
     * Sample cards for rows_section TRFs, and for unlinked/manual forms that
     * put sample-line fields in a regular section (no rows_section yet).
     */
    public function walkInSectionUsesSampleCards(SubmissionFormSection $section): bool
    {
        return SubmissionFormSchemaHelper::sectionUsesSampleCards($section);
    }

    public function goToWalkInStep(int $index): void
    {
        $maxIndex = max(0, $this->walkInSections->count() - 1);
        if ($index < 0 || $index > $maxIndex) {
            return;
        }

        if ($index <= $this->walkInActiveStepIndex) {
            $this->walkInActiveStepIndex = $index;
            $this->dispatchWalkInTrfStepHooks();

            return;
        }

        for ($step = $this->walkInActiveStepIndex; $step < $index; $step++) {
            try {
                $this->validateWalkInStep($step);
            } catch (ValidationException $exception) {
                $this->walkInActiveStepIndex = $step;
                $this->dispatchWalkInTrfStepHooks();
                $this->notifyWalkInValidationFailure($exception->validator->errors()->all());
                throw $exception;
            }
        }

        $this->walkInActiveStepIndex = $index;
        $this->dispatchWalkInTrfStepHooks();
    }

    public function nextWalkInStep(): void
    {
        $maxIndex = max(0, $this->walkInSections->count() - 1);
        if ($this->walkInActiveStepIndex >= $maxIndex) {
            return;
        }

        try {
            $this->validateWalkInStep($this->walkInActiveStepIndex);
        } catch (ValidationException $exception) {
            $this->notifyWalkInValidationFailure($exception->validator->errors()->all());
            throw $exception;
        }

        $this->walkInActiveStepIndex++;
        $this->dispatchWalkInTrfStepHooks();
    }

    public function prevWalkInStep(): void
    {
        if ($this->walkInActiveStepIndex <= 0) {
            return;
        }

        $this->walkInActiveStepIndex--;
        $this->dispatchWalkInTrfStepHooks();
    }

    private function walkInStepShortLabel(string $title): string
    {
        return match ($title) {
            'Customer details' => 'Customer',
            'Sample collection data' => 'Collection',
            'Test & sample information' => 'Samples',
            'Miscellaneous' => 'Misc',
            'Submit & sign', 'Submit and sign' => 'Sign',
            default => \Illuminate\Support\Str::limit($title, 14),
        };
    }

    private function dispatchWalkInTrfStepHooks(): void
    {
        $this->dispatch('trf-reinit-signatures');
        $this->dispatch('trf-reinit-parameter-selects');
        $this->dispatch('walk-in-trf-step-changed');
    }

    private function defaultValueForElement(SubmissionFormElement $element): mixed
    {
        $name = (string) ($element->name ?? '');

        if (in_array($name, ['method_of_sampling', 'test_category', 'sample_condition'], true)) {
            return [];
        }

        if ($name === 'extra_sampling_equipment') {
            return [];
        }

        if ($name === 'test_requirements') {
            return $this->usesWasteWaterSampleCardLayout()
                ? $this->defaultWasteWaterTestRequirementsRow()
                : $this->defaultWaterTestRequirementsRow();
        }

        if ($element->element_type === 'checkbox') {
            $options = $element->options ?? [];

            return is_array($options) && $options !== [] ? [] : false;
        }

        if (
            $element->element_type === 'sample_type_select'
            || in_array($name, ['sample_type_id', 'sample_type'], true)
        ) {
            return [];
        }

        if (
            $element->element_type === 'analysis_type_select'
            || in_array($name, ['analysis_type_id', 'analysis_type', 'analysis_types'], true)
        ) {
            return [];
        }

        if ($element->element_type === 'analysis_elements_select' || $name === 'parameters') {
            return [];
        }

        return '';
    }

    /**
     * @return Collection<int, SubmissionFormElement>
     */
    public function uniqueRowElementsForSection(SubmissionFormSection $section): Collection
    {
        $elements = $section->elementHolders->flatMap->elements;
        $seen = [];

        return $elements
            ->sortBy('sort_order')
            ->filter(function (SubmissionFormElement $element) use (&$seen): bool {
                $name = trim((string) ($element->name ?? ''));
                if ($name === '' || isset($seen[$name])) {
                    return false;
                }

                if ($this->isHiddenWalkInRowElement($element)) {
                    return false;
                }

                // Legacy Qty / Unit — always represented as Qty/Unit → sample_quantity (+ unit).
                if (in_array($name, ['number_of_samples', 'sample_quantity_unit', 'additional_details'], true)) {
                    return false;
                }

                $seen[$name] = true;

                return true;
            })
            ->values();
    }

    /**
     * @return list<array{type: string, label: string, class: string, element: SubmissionFormElement, field?: array<string, mixed>}>
     */
    public function walkInRowTableColumns(SubmissionFormSection $section): array
    {
        $columns = [];
        $elements = $this->uniqueRowElementsForSection($section);
        $elementNames = $elements->map(fn ($el) => (string) ($el->name ?? ''))->all();
        $hasSampleQuantity = in_array('sample_quantity', $elementNames, true);
        $qtyUnitAdded = false;

        // Always expose Qty/Unit for sample cards (binds to sample_quantity, never number_of_samples).
        $qtySource = $elements->first(fn ($el) => (string) ($el->name ?? '') === 'sample_quantity')
            ?? $section->elementHolders->flatMap->elements->first(fn ($el) => in_array((string) ($el->name ?? ''), ['sample_quantity', 'number_of_samples'], true));

        if ($qtySource !== null || $hasSampleQuantity) {
            $columns[] = [
                'type' => 'qty_unit',
                'label' => 'Qty / Unit',
                'class' => 'walk-in-trf-col-qty',
                'element' => $qtySource ?? $elements->first(),
            ];
            $qtyUnitAdded = true;
        }

        foreach ($elements as $element) {
            $name = (string) ($element->name ?? '');

            if ($name === 'sample_quantity' || $name === 'sample_quantity_unit' || $name === 'number_of_samples') {
                continue;
            }

            $field = app(\App\Services\Sampleworkflow\WalkInTrfFieldMapper::class)->toField($element);
            $columns[] = [
                'type' => 'field',
                'label' => (string) ($field['label'] ?? $name),
                'class' => $this->walkInRowColumnClass($name),
                'element' => $element,
                'field' => $field,
            ];
        }

        // Forms with sample cards but no qty field in schema still get Qty/Unit.
        if (! $qtyUnitAdded && $this->walkInSectionUsesSampleCards($section)) {
            $fallbackElement = $elements->first() ?? $section->elementHolders->flatMap->elements->first();
            if ($fallbackElement !== null) {
                array_unshift($columns, [
                    'type' => 'qty_unit',
                    'label' => 'Qty / Unit',
                    'class' => 'walk-in-trf-col-qty',
                    'element' => $fallbackElement,
                ]);
            }
        }

        return $columns;
    }

    public function walkInRowColumnClass(string $fieldName): string
    {
        return match ($fieldName) {
            'sample_description' => 'walk-in-trf-col-desc',
            'sampling_point', 'sampling_location', 'location' => 'walk-in-trf-col-location',
            'sampling_point_manual', 'manual_sampling_point' => 'walk-in-trf-col-default',
            'analysis_type_id' => 'walk-in-trf-col-analysis-type',
            'parameters' => 'walk-in-trf-col-parameters',
            'state_of_sample', 'test_category', 'test_requirements' => 'walk-in-trf-col-radio',
            'production_date', 'expiration_date' => 'walk-in-trf-col-date',
            'batch_number' => 'walk-in-trf-col-batch',
            default => str_starts_with($fieldName, 'field_') ? 'walk-in-trf-col-field-data' : 'walk-in-trf-col-default',
        };
    }

    /**
     * Prepare compact sample-card field buckets for Blade (no layout logic in the view).
     *
     * Row 1: Qty/Unit | Sample temp | State of sample
     * Row 2: Batch | Production date | Expiration date
     * Row 3: Sampling Location | Sampling Point | Test category
     * Row 4: Location (water) / Sample type / Analysis type when present
     * Then: Parameters (full), Sample description (full)
     *
     * @param  list<array{type: string, label: string, class: string, element: SubmissionFormElement, field?: array<string, mixed>}>  $tableColumns
     * @param  list<string>  $hiddenFields
     * @return array{
     *     layout_variant: string,
     *     grid_rows: list<array{type: string, cols?: int, label?: string, compact?: bool, columns?: list<array<string, mixed>|null>}>,
     *     catalog_row?: array{sample_type?: array<string, mixed>|null, analysis_type?: array<string, mixed>|null},
     *     parameters_column: array<string, mixed>|null,
     *     description_column: array<string, mixed>|null,
     *     extra_columns: list<array<string, mixed>>
     * }
     */
    public function walkInSampleCardLayout(array $tableColumns, array $hiddenFields = []): array
    {
        $columnName = static function (array $column): string {
            if (($column['type'] ?? '') === 'qty_unit') {
                return 'sample_quantity';
            }

            $fromField = (string) ($column['field']['name'] ?? '');
            if ($fromField !== '') {
                return $fromField;
            }

            return (string) ($column['element']->name ?? '');
        };

        $columnLabel = static function (array $column): string {
            return strtolower(trim((string) ($column['label'] ?? '')));
        };

        /** @var array<string, array<string, mixed>> $byName */
        $byName = [];
        foreach ($tableColumns as $column) {
            $byName[$columnName($column)] = $column;
        }

        $findByNames = static function (array $names) use ($byName): ?array {
            foreach ($names as $name) {
                if (isset($byName[$name])) {
                    return $byName[$name];
                }
            }

            return null;
        };

        $findTemp = static function () use ($findByNames, $tableColumns, $columnName, $columnLabel): ?array {
            $exact = $findByNames([
                'field_sample_temp',
                'sample_temp',
                'sample_temperature',
                'temperature',
                'sample_temp_c',
            ]);
            if ($exact !== null) {
                return $exact;
            }

            foreach ($tableColumns as $column) {
                $name = strtolower($columnName($column));
                $label = $columnLabel($column);
                if (str_contains($name, 'temp') || str_contains($label, 'temp')) {
                    return $column;
                }
            }

            return null;
        };

        $findQty = static function () use ($findByNames, $byName, $tableColumns): ?array {
            $qty = $findByNames(['sample_quantity']);
            if ($qty !== null) {
                return $qty;
            }

            // Some food TRFs still use number_of_samples as Qty.
            if (isset($byName['number_of_samples'])) {
                $column = $byName['number_of_samples'];
                if (($column['type'] ?? '') !== 'qty_unit') {
                    $column['type'] = 'qty_unit';
                    $column['label'] = 'Qty / Unit';
                }

                return $column;
            }

            foreach ($tableColumns as $column) {
                $label = strtolower((string) ($column['label'] ?? ''));
                if ($label === 'qty' || str_starts_with($label, 'qty')) {
                    $column['type'] = 'qty_unit';
                    $column['label'] = 'Qty / Unit';

                    return $column;
                }
            }

            return null;
        };

        $usedNames = [];
        $take = static function (?array $column) use (&$usedNames, $columnName): ?array {
            if ($column === null) {
                return null;
            }

            $name = $columnName($column);
            $usedNames[$name] = true;
            if (($column['type'] ?? '') === 'qty_unit') {
                $usedNames['sample_quantity'] = true;
                $usedNames['sample_quantity_unit'] = true;
                $usedNames['number_of_samples'] = true;
            }

            return $column;
        };

        $sampleTypeColumn = $take($findByNames(['sample_type_id', 'sample_type']));
        if ($sampleTypeColumn === null) {
            $sampleTypeColumn = $take($this->syntheticSampleTypeColumn());
        }
        $take($findByNames(['analysis_type_id', 'analysis_type', 'analysis_types']));

        if ($this->usesWaterSampleCardLayout()) {
            return $this->prependSampleCollectionBlock(
                $this->buildWaterSampleCardLayout(
                    $take,
                    $findQty,
                    $findTemp,
                    $findByNames,
                    $sampleTypeColumn,
                ),
                $take,
                $findByNames,
            );
        }

        if ($this->usesFoodSampleCardLayout()) {
            return $this->prependSampleCollectionBlock(
                $this->buildFoodSampleCardLayout(
                    $take,
                    $findQty,
                    $findByNames,
                    $sampleTypeColumn,
                ),
                $take,
                $findByNames,
            );
        }

        if ($this->usesWasteWaterSampleCardLayout()) {
            return $this->prependSampleCollectionBlock(
                $this->buildWasteWaterSampleCardLayout(
                    $take,
                    $findQty,
                    $findByNames,
                    $sampleTypeColumn,
                    $tableColumns,
                    $hiddenFields,
                    $columnName,
                    $usedNames,
                ),
                $take,
                $findByNames,
                wasteWater: true,
            );
        }

        $gridRows = [
            [
                $take($findQty()),
                $take($findTemp()),
                $take($findByNames(['state_of_sample'])),
            ],
            [
                $take($findByNames(['batch_number'])),
                $take($findByNames(['production_date'])),
                $take($findByNames(['expiration_date'])),
            ],
            [
                $take($findByNames(['sampling_point_manual', 'manual_sampling_point'])),
                $take($findByNames(['test_category', 'test_requirements'])),
                null,
            ],
        ];

        // Sample type belongs only in catalog_row (with Tests) — do not also place it in the grid.
        $locationColumn = $take($findByNames(['location']));
        if ($locationColumn !== null) {
            $gridRows[] = [$locationColumn, null, null];
        }

        $parametersColumn = $take($findByNames(['parameters', 'parameter']));
        if ($parametersColumn !== null) {
            $parametersColumn['label'] = 'Tests';
        }
        $descriptionColumn = $take($findByNames(['sample_description']));

        $extraColumns = [];
        foreach ($tableColumns as $column) {
            $name = $columnName($column);
            if (isset($usedNames[$name]) || in_array($name, $hiddenFields, true)) {
                continue;
            }
            if (SubmissionFormSchemaHelper::isSampleCollectionFieldName($name)) {
                continue;
            }
            $extraColumns[] = $column;
        }

        return $this->prependSampleCollectionBlock([
            'layout_variant' => 'default',
            'grid_rows' => array_map(
                static fn (array $row): array => [
                    'type' => 'fields',
                    'cols' => 3,
                    'columns' => $row,
                ],
                $gridRows
            ),
            'catalog_row' => [
                'sample_type' => $sampleTypeColumn,
                'parameters' => $parametersColumn,
            ],
            'parameters_column' => null,
            'description_column' => $descriptionColumn,
            'extra_columns' => $extraColumns,
        ], $take, $findByNames);
    }

    /**
     * @param  array<string, mixed>  $layout
     * @param  callable(?array): ?array  $take
     * @param  callable(list<string>): ?array  $findByNames
     * @return array<string, mixed>
     */
    private function prependSampleCollectionBlock(
        array $layout,
        callable $take,
        callable $findByNames,
        bool $wasteWater = false,
    ): array {
        $samplingDate = $take($findByNames(['sampling_date']));
        $samplingTime = $take($findByNames(['sampling_time']));
        $dateReceived = $take($findByNames(['date_received']));
        $samplingLocation = $take($findByNames(['sampling_location']));
        if ($samplingLocation !== null) {
            $samplingLocation['label'] = 'Sampling location';
        }
        $transport = $take($findByNames(['transport_condition']));
        if ($transport !== null) {
            $transport['option_cols'] = 1;
        }
        $reason = $take($findByNames(['reason_of_collection']));
        if ($reason !== null) {
            $reason['option_cols'] = 2;
        }
        $apparatus = $take($findByNames(['sampling_apparatus']));
        if ($apparatus !== null) {
            $apparatus['option_cols'] = 2;
        }
        $method = $take($findByNames(['method_of_sampling']));
        if ($method !== null) {
            $method['option_cols'] = 2;
        }
        $thermometer = $take($findByNames(['thermometer_id', 'equipment_id']));
        if ($thermometer !== null) {
            $thermometer['label'] = $wasteWater ? 'Thermometer ID' : 'Equipment ID';
            $thermometer['is_equipment_id'] = ! $wasteWater;
            if ($apparatus !== null) {
                $apparatus['nested'] = $thermometer;
                $thermometer = null;
            }
        }

        $collectionRows = [
            ['type' => 'section', 'label' => 'Sample collection', 'group' => 'collection'],
            [
                'type' => 'fields',
                'cols' => 3,
                'group' => 'collection',
                'columns' => [$samplingDate, $samplingTime, $dateReceived],
            ],
            [
                'type' => 'fields',
                'cols' => 3,
                'group' => 'collection',
                'columns' => [$samplingLocation, $transport, $reason],
            ],
            [
                'type' => 'fields',
                'cols' => 2,
                'group' => 'collection',
                'columns' => [$apparatus, $method],
            ],
        ];

        if ($wasteWater) {
            $collectionRows = array_merge($collectionRows, $this->wasteWaterCollectionExtraRows($take, $findByNames));
        }

        // Drop empty collection rows/columns so missing schema never renders shimmer placeholders.
        $collectionRows = array_values(array_filter(array_map(static function (array $row): ?array {
            if (($row['type'] ?? '') !== 'fields') {
                return $row;
            }

            $columns = array_values(array_filter(
                $row['columns'] ?? [],
                static fn ($column): bool => is_array($column) && isset($column['element'])
            ));

            if ($columns === []) {
                return null;
            }

            $row['columns'] = $columns;
            $row['cols'] = min(3, max(1, count($columns)));

            return $row;
        }, $collectionRows)));

        $hasCollectionFields = collect($collectionRows)->contains(
            static fn (array $row): bool => ($row['type'] ?? '') === 'fields'
        );

        if (! $hasCollectionFields) {
            $layout['has_collection_block'] = false;

            return $layout;
        }

        $sampleRows = $layout['grid_rows'] ?? [];
        $hasSampleContent = $sampleRows !== []
            || ($layout['catalog_row'] ?? null) !== null
            || ($layout['description_column'] ?? null) !== null
            || ($layout['extra_columns'] ?? []) !== [];

        $prefix = $collectionRows;
        if ($hasSampleContent) {
            $prefix[] = ['type' => 'section', 'label' => 'Sample & test information', 'group' => 'sample'];
        }

        $layout['grid_rows'] = array_merge($prefix, $sampleRows);
        $layout['has_collection_block'] = true;

        // Drop collection leftovers from extras.
        $layout['extra_columns'] = array_values(array_filter(
            $layout['extra_columns'] ?? [],
            static fn (array $column): bool => ! SubmissionFormSchemaHelper::isSampleCollectionFieldName(
                (string) (($column['field']['name'] ?? '') !== ''
                    ? $column['field']['name']
                    : ($column['element']->name ?? ''))
            )
        ));

        return $layout;
    }

    /**
     * @param  callable(?array): ?array  $take
     * @param  callable(list<string>): ?array  $findByNames
     * @return list<array<string, mixed>>
     */
    private function wasteWaterCollectionExtraRows(callable $take, callable $findByNames): array
    {
        $desc = $take($findByNames(['sample_sampling_point_description']));
        $phMeter = $take($findByNames(['ph_meter_id']));
        $chlorineMeter = $take($findByNames(['chlorine_meter_id']));
        $apparatusOthers = $take($findByNames(['sampling_apparatus_others']));
        $extraEquipment = $take($findByNames(['extra_sampling_equipment']));
        $technique = $take($findByNames(['sampling_technique']));
        $source = $take($findByNames(['sampling_source']));
        $sampleTypesWw = $take($findByNames(['sample_types_ww']));
        $fieldReq = $take($findByNames(['field_data_requirements']));

        $fieldQty = $take($findByNames(['field_data_quantity']));
        $fieldAppearance = $take($findByNames(['field_data_appearance']));
        $fieldColor = $take($findByNames(['field_data_color']));
        $fieldOdor = $take($findByNames(['field_data_odor']));
        $fieldPh = $take($findByNames(['field_data_ph']));
        $fieldTemp = $take($findByNames(['field_data_temperature']));
        $fieldChlorine = $take($findByNames(['field_data_free_chlorine']));

        return [
            [
                'type' => 'fields',
                'cols' => 1,
                'group' => 'collection',
                'columns' => [$desc],
            ],
            [
                'type' => 'fields',
                'cols' => 3,
                'group' => 'collection',
                'columns' => [$phMeter, $chlorineMeter, $apparatusOthers],
            ],
            [
                'type' => 'fields',
                'cols' => 3,
                'group' => 'collection',
                'columns' => [$technique, $source, $sampleTypesWw],
            ],
            [
                'type' => 'fields',
                'cols' => 2,
                'group' => 'collection',
                'columns' => [$fieldReq, $extraEquipment],
            ],
            [
                'type' => 'fields',
                'cols' => 3,
                'group' => 'collection',
                'compact' => true,
                'columns' => [$fieldQty, $fieldAppearance, $fieldColor],
            ],
            [
                'type' => 'fields',
                'cols' => 3,
                'group' => 'collection',
                'compact' => true,
                'columns' => [$fieldOdor, $fieldPh, $fieldTemp],
            ],
            [
                'type' => 'fields',
                'cols' => 1,
                'group' => 'collection',
                'compact' => true,
                'columns' => [$fieldChlorine],
            ],
        ];
    }

    /**
     * Whether the active walk-in TRF uses the Water collection field pairing layout.
     */
    public function usesWaterCollectionLayout(): bool
    {
        return $this->usesWaterSampleCardLayout();
    }

    private function usesWaterSampleCardLayout(): bool
    {
        if ($this->isWater) {
            return true;
        }

        $documentCode = strtoupper(trim((string) ($this->submissionForm?->document_code ?? '')));

        if ($documentCode === strtoupper(TrfDocumentCodeForSampleType::WATER)) {
            return true;
        }

        return $documentCode !== ''
            && str_contains($documentCode, 'WATER')
            && ! str_contains($documentCode, 'WASTE');
    }

    private function usesFoodSampleCardLayout(): bool
    {
        if ($this->isFood) {
            return true;
        }

        $documentCode = trim((string) ($this->submissionForm?->document_code ?? ''));

        return in_array($documentCode, [
            TrfDocumentCodeForSampleType::FOOD,
            TrfDocumentCodeForSampleType::FOOD_AND_FEED,
        ], true);
    }

    private function usesWasteWaterSampleCardLayout(): bool
    {
        return $this->usesWasteWaterCollectionLayout();
    }

    /**
     * Food TRF sample card: qty/dates, batch/point/type, category/state/condition, analysis, tests, description.
     *
     * @return array{
     *     layout_variant: string,
     *     grid_rows: list<array<string, mixed>>,
     *     analysis_type_column: array<string, mixed>|null,
     *     parameters_column: array<string, mixed>|null,
     *     description_column: array<string, mixed>|null,
     *     extra_columns: list<array<string, mixed>>
     * }
     */
    private function buildFoodSampleCardLayout(
        callable $take,
        callable $findQty,
        callable $findByNames,
        ?array $sampleTypeColumn,
    ): array {
        $qtyColumn = $take($findQty());
        if ($qtyColumn !== null) {
            $qtyColumn['label'] = 'Qty / Unit';
        }

        $productionDateColumn = $take($findByNames(['production_date']));
        $expirationDateColumn = $take($findByNames(['expiration_date']));
        $batchColumn = $take($findByNames(['batch_number']));
        $samplingPointColumn = $take($findByNames(['sampling_point_manual', 'manual_sampling_point']));
        if ($samplingPointColumn !== null) {
            $samplingPointColumn['label'] = 'Sampling Point';
        }

        $stateColumn = $take($findByNames(['state_of_sample']));

        $testCategoryColumn = $take($findByNames(['test_category', 'test_requirements']));
        if ($testCategoryColumn === null) {
            $testCategoryColumn = $take($this->foodTestCategoryFallbackColumn());
        }
        if ($testCategoryColumn !== null) {
            $testCategoryColumn['label'] = 'Test category';
            $testCategoryColumn['option_cols'] = 2;
        }

        $sampleConditionColumn = $take($findByNames(['sample_condition']));
        if ($sampleConditionColumn !== null) {
            $sampleConditionColumn['label'] = 'Sample condition';
            $sampleConditionColumn['option_cols'] = 2;
            $sampleTempColumn = $take($findByNames(['sample_temp', 'field_sample_temp', 'sample_temperature']));
            if ($sampleTempColumn !== null) {
                $sampleConditionColumn['nested'] = $sampleTempColumn;
            }
        } else {
            $take($findByNames(['sample_temp', 'field_sample_temp', 'sample_temperature']));
        }

        $parametersColumn = $take($findByNames(['parameters', 'parameter']));
        if ($parametersColumn !== null) {
            $parametersColumn['label'] = 'Tests';
        }

        $descriptionColumn = $take($findByNames(['sample_description']));

        return [
            'layout_variant' => 'food',
            'grid_rows' => [
                ['type' => 'catalog'],
                [
                    'type' => 'fields',
                    'cols' => 3,
                    'columns' => [$qtyColumn, $productionDateColumn, $expirationDateColumn],
                ],
                [
                    'type' => 'fields',
                    'cols' => 3,
                    'columns' => [$batchColumn, $samplingPointColumn, $descriptionColumn],
                ],
                [
                    'type' => 'fields',
                    'cols' => 3,
                    'columns' => [$testCategoryColumn, $stateColumn, $sampleConditionColumn],
                ],
            ],
            'catalog_row' => [
                'sample_type' => $sampleTypeColumn,
                'parameters' => $parametersColumn,
            ],
            'parameters_column' => null,
            'description_column' => null,
            'extra_columns' => [],
        ];
    }

    /**
     * @return array{type: string, label: string, class: string, element: SubmissionFormElement, field: array<string, mixed>}
     */
    private function foodTestCategoryFallbackColumn(): array
    {
        $element = new SubmissionFormElement([
            'name' => 'test_category',
            'label' => 'Test category',
            'element_type' => 'checkbox',
            'options' => [
                ['value' => 'chemistry', 'label' => 'Chemistry'],
                ['value' => 'microbiology', 'label' => 'Microbiology'],
            ],
        ]);

        return [
            'type' => 'field',
            'label' => 'Test category',
            'class' => 'walk-in-trf-col-radio',
            'element' => $element,
            'field' => app(\App\Services\Sampleworkflow\WalkInTrfFieldMapper::class)->toField($element),
        ];
    }

    /**
     * Whether the active walk-in TRF uses the Food collection field pairing layout.
     */
    public function usesFoodCollectionLayout(): bool
    {
        return $this->usesFoodSampleCardLayout();
    }

    /**
     * Whether the active walk-in TRF uses the Waste Water LWS-036 collection layout.
     * Detect by sample type and/or TRF document code (form may load before sample type is set).
     */
    public function usesWasteWaterCollectionLayout(): bool
    {
        if ((bool) $this->isWasteWater) {
            return true;
        }

        $documentCode = strtoupper(trim((string) ($this->submissionForm?->document_code ?? '')));

        return $documentCode === TrfDocumentCodeForSampleType::WASTE_WATER
            || $documentCode === TrfDocumentCodeForSampleType::WASTE_WATER_LEGACY
            || str_contains($documentCode, 'WASTEWATER')
            || str_contains($documentCode, 'WASTE-WATER')
            || (str_contains($documentCode, 'WASTE') && str_contains($documentCode, '036'));
    }

    /**
     * Food/Water (not Waste Water): Equipment ID multi-select from equipment master.
     */
    public function usesSamplingEquipmentIdPicker(): bool
    {
        if ($this->usesWasteWaterCollectionLayout()) {
            return false;
        }

        if ($this->usesFoodSampleCardLayout() || $this->usesWaterSampleCardLayout()) {
            return true;
        }

        $documentCode = strtoupper(trim((string) ($this->submissionForm?->document_code ?? '')));
        if ($documentCode !== '' && str_contains($documentCode, 'WATER') && ! str_contains($documentCode, 'WASTE')) {
            return true;
        }

        // Any non-WW TRF that still has the thermometer/equipment collection field.
        return array_key_exists('thermometer_id', $this->formData)
            || array_key_exists('Equipment_ID', $this->formData)
            || array_key_exists('equipment_id', $this->formData);
    }

    public function addSamplingEquipmentIdRow(?int $sampleRowIndex = null): void
    {
        if (! $this->usesSamplingEquipmentIdPicker()) {
            return;
        }

        $this->normalizeSamplingEquipmentFormKey();

        if ($sampleRowIndex === null) {
            $rows = $this->samplingEquipmentIdRows();
            $rows[] = '';
            $this->formData['thermometer_id'] = $rows;

            return;
        }

        $rows = $this->samplingEquipmentIdRowsForSample($sampleRowIndex);
        $rows[] = '';
        $this->formData['thermometer_id'][$sampleRowIndex] = $rows;
    }

    public function removeSamplingEquipmentIdRow(int $index, ?int $sampleRowIndex = null): void
    {
        if (! $this->usesSamplingEquipmentIdPicker()) {
            return;
        }

        $this->normalizeSamplingEquipmentFormKey();

        if ($sampleRowIndex === null) {
            $rows = $this->samplingEquipmentIdRows();
            if (! isset($rows[$index]) || count($rows) <= 1) {
                return;
            }

            unset($rows[$index]);
            $this->formData['thermometer_id'] = array_values($rows);

            return;
        }

        $rows = $this->samplingEquipmentIdRowsForSample($sampleRowIndex);
        if (! isset($rows[$index]) || count($rows) <= 1) {
            return;
        }

        unset($rows[$index]);
        $this->formData['thermometer_id'][$sampleRowIndex] = array_values($rows);
    }

    /**
     * @return list<string>
     */
    private function samplingEquipmentIdRows(): array
    {
        $this->normalizeSamplingEquipmentFormKey();
        $resolver = app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class);

        if (is_array($this->formData['thermometer_id'] ?? null)) {
            // Per-sample shape: [0 => ['EQ1'], 1 => ['EQ2']] — do not flatten as batch rows.
            $first = $this->formData['thermometer_id'][0] ?? null;
            if (is_array($first) || $this->walkInUsesIndexedSampleRows()) {
                return $this->samplingEquipmentIdRowsForSample(0);
            }

            $rows = array_map(
                static fn ($id): string => trim((string) $id),
                array_values($this->formData['thermometer_id'])
            );

            return $rows === [] ? [''] : $rows;
        }

        return $resolver->rowsForForm($this->formData['thermometer_id'] ?? null);
    }

    /**
     * @return list<string>
     */
    public function samplingEquipmentIdRowsForSample(int $sampleRowIndex): array
    {
        $this->normalizeSamplingEquipmentFormKey();
        $resolver = app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class);
        $raw = $this->formData['thermometer_id'][$sampleRowIndex] ?? null;

        if (is_array($raw)) {
            $rows = array_map(
                static fn ($id): string => is_array($id)
                    ? trim((string) ($id['id'] ?? $id['equipment_id'] ?? ''))
                    : trim((string) $id),
                array_values($raw)
            );

            return $rows === [] ? [''] : $rows;
        }

        return $resolver->rowsForForm($raw);
    }

    private function hydrateSamplingEquipmentIdRows(): void
    {
        if (! $this->usesSamplingEquipmentIdPicker()) {
            return;
        }

        $this->normalizeSamplingEquipmentFormKey();

        if (! array_key_exists('thermometer_id', $this->formData)) {
            return;
        }

        if ($this->walkInUsesIndexedSampleRows()) {
            $rowCount = max(1, $this->schemaRowCount());
            $normalized = [];
            for ($index = 0; $index < $rowCount; $index++) {
                $normalized[$index] = $this->samplingEquipmentIdRowsForSample($index);
            }
            $this->formData['thermometer_id'] = $normalized;

            return;
        }

        $this->formData['thermometer_id'] = $this->samplingEquipmentIdRows();
    }

    /**
     * Copy all sample-card fields from sample 1 (index 0) onto every sample below it.
     */
    public function copyFirstSampleToAllBelow(): void
    {
        $rowCount = $this->schemaRowCount();
        if ($rowCount <= 1) {
            return;
        }

        $fieldNames = array_values(array_unique(array_merge(
            $this->schemaRowFieldNames(),
            SubmissionFormSchemaHelper::sampleCollectionFieldNames(),
            SubmissionFormSchemaHelper::sampleRowAnchorFieldNames(),
            SubmissionFormSchemaHelper::sampleRowMultiSelectFieldNames(),
            [
                'sample_quantity',
                'sample_quantity_unit',
                'additional_details',
                'thermometer_id',
                'equipment_id',
                'sample_type_id',
                'sample_type',
                'sampling_location',
                'sampling_point',
                'sampling_point_manual',
                'manual_sampling_point',
                'state_of_sample',
            ],
        )));

        foreach ($fieldNames as $fieldName) {
            $this->ensureWalkInIndexedRowFieldForCopy($fieldName, $rowCount);
        }

        for ($targetRowIndex = 1; $targetRowIndex < $rowCount; $targetRowIndex++) {
            foreach ($fieldNames as $fieldName) {
                if (! array_key_exists($fieldName, $this->formData) || ! is_array($this->formData[$fieldName])) {
                    continue;
                }

                if (! array_key_exists(0, $this->formData[$fieldName])) {
                    continue;
                }

                $source = $this->formData[$fieldName][0];
                $this->formData[$fieldName][$targetRowIndex] = is_array($source)
                    ? json_decode(json_encode($source), true)
                    : $source;
            }
        }

        $this->dispatch('trf-reinit-parameter-selects');
        $this->dispatch('trf-sync-copied-sample-fields', rowCount: $rowCount);
        TrfToast::dispatch($this, 'success', 'Sample 1 details copied to all samples below.');
    }

    /**
     * Promote scalar / short row fields to indexed arrays so copy can write every sample slot.
     */
    private function ensureWalkInIndexedRowFieldForCopy(string $field, int $rowCount): void
    {
        if (! array_key_exists($field, $this->formData)) {
            return;
        }

        $existing = $this->formData[$field];
        if (! is_array($existing)) {
            $existing = $existing !== null && $existing !== '' ? [$existing] : [''];
        }

        $emptyValue = in_array($field, [
            'parameters',
            'parameter',
            'analysis_type_id',
            'analysis_type',
            'analysis_types',
            'sample_type_id',
            'sample_type',
            'test_category',
            'test_requirements',
            'sample_condition',
            'method_of_sampling',
            'transport_condition',
            'sampling_apparatus',
            'additional_details',
            'thermometer_id',
            'equipment_id',
            'extra_sampling_equipment',
        ], true) ? [] : '';

        while (count($existing) < $rowCount) {
            $existing[] = is_array($emptyValue) ? [] : $emptyValue;
        }

        $this->formData[$field] = $existing;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function additionalDetailsRowsForSample(int $sampleRowIndex): array
    {
        $this->ensureAdditionalDetailsFormField(max(1, $this->schemaRowCount()));
        $raw = $this->formData['additional_details'][$sampleRowIndex] ?? [];

        return $this->decodeAdditionalDetailsRows($raw);
    }

    public function addAdditionalDetailRow(int $sampleRowIndex): void
    {
        $this->ensureAdditionalDetailsFormField(max($sampleRowIndex + 1, $this->schemaRowCount()));
        $rows = $this->additionalDetailsRowsForSample($sampleRowIndex);
        $rows[] = ['label' => '', 'value' => ''];
        $this->formData['additional_details'][$sampleRowIndex] = $rows;
    }

    public function removeAdditionalDetailRow(int $sampleRowIndex, int $detailIndex): void
    {
        $rows = $this->additionalDetailsRowsForSample($sampleRowIndex);
        if (! isset($rows[$detailIndex])) {
            return;
        }

        unset($rows[$detailIndex]);
        $this->formData['additional_details'][$sampleRowIndex] = array_values($rows);
    }

    private function ensureAdditionalDetailsFormField(int $rowCount): void
    {
        $rowCount = max(1, $rowCount);
        $existing = $this->formData['additional_details'] ?? null;

        if (! is_array($existing)) {
            $existing = [];
        }

        for ($index = 0; $index < $rowCount; $index++) {
            $existing[$index] = $this->decodeAdditionalDetailsRows($existing[$index] ?? []);
        }

        $this->formData['additional_details'] = $existing;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function decodeAdditionalDetailsRows(mixed $value): array
    {
        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($value)) {
            return [];
        }

        $rows = [];
        foreach (array_values($value) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            $detailValue = trim((string) ($row['value'] ?? ''));
            $rows[] = [
                'label' => $label,
                'value' => $detailValue,
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array{label: string, value: string}>  $rows
     */
    private function encodeAdditionalDetailsRows(array $rows): string
    {
        $clean = array_values(array_filter(
            $rows,
            static fn (array $row): bool => trim((string) ($row['label'] ?? '')) !== ''
                || trim((string) ($row['value'] ?? '')) !== ''
        ));

        return $clean === [] ? '' : (string) json_encode($clean);
    }

    /**
     * Canonicalize legacy Equipment_ID / equipment_id form keys onto thermometer_id.
     */
    private function normalizeSamplingEquipmentFormKey(): void
    {
        foreach (['Equipment_ID', 'equipment_id', 'Equipment ID'] as $alias) {
            if (! array_key_exists($alias, $this->formData)) {
                continue;
            }

            if (! array_key_exists('thermometer_id', $this->formData)
                || $this->formData['thermometer_id'] === null
                || $this->formData['thermometer_id'] === ''
                || $this->formData['thermometer_id'] === []) {
                $this->formData['thermometer_id'] = $this->formData[$alias];
            }

            unset($this->formData[$alias]);
        }
    }

    public function addExtraSamplingEquipmentRow(): void
    {
        $rows = $this->formData['extra_sampling_equipment'] ?? [];
        if (! is_array($rows)) {
            $rows = $this->decodeExtraSamplingEquipmentRows($rows);
        }

        $rows[] = ['label' => '', 'id' => ''];
        $this->formData['extra_sampling_equipment'] = $rows;
    }

    public function removeExtraSamplingEquipmentRow(int $index): void
    {
        $rows = $this->formData['extra_sampling_equipment'] ?? [];
        if (! is_array($rows)) {
            $rows = $this->decodeExtraSamplingEquipmentRows($rows);
        }

        if (! isset($rows[$index])) {
            return;
        }

        unset($rows[$index]);
        $this->formData['extra_sampling_equipment'] = array_values($rows);
    }

    /**
     * @return list<array{label: string, id: string}>
     */
    private function decodeExtraSamplingEquipmentRows(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_map(static function ($row): array {
                if (! is_array($row)) {
                    return ['label' => '', 'id' => ''];
                }

                return [
                    'label' => (string) ($row['label'] ?? ''),
                    'id' => (string) ($row['id'] ?? ''),
                ];
            }, $value));
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $this->decodeExtraSamplingEquipmentRows($decoded) : [];
        }

        return [];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, SubmissionFormElement>|\Illuminate\Database\Eloquent\Collection<int, SubmissionFormElement>  $elements
     */
    public function walkInCollectionElementByName($elements, string $name): ?SubmissionFormElement
    {
        foreach ($elements as $element) {
            if ((string) ($element->name ?? '') === $name) {
                return $element;
            }
        }

        return null;
    }

    /**
     * Water TRF sample card: Qty + point, field data block, type/analysis/requirements, parameters, description.
     *
     * @return array{
     *     layout_variant: string,
     *     grid_rows: list<array<string, mixed>>,
     *     parameters_column: array<string, mixed>|null,
     *     description_column: array<string, mixed>|null,
     *     extra_columns: list<array<string, mixed>>
     * }
     */
    private function buildWaterSampleCardLayout(
        callable $take,
        callable $findQty,
        callable $findTemp,
        callable $findByNames,
        ?array $sampleTypeColumn,
    ): array {
        $shortFieldLabel = static function (?array $column, string $defaultLabel): ?array {
            if ($column === null) {
                return null;
            }

            $label = trim((string) ($column['label'] ?? $defaultLabel));
            $label = preg_replace('/^field data\s*[-–—]\s*/iu', '', $label) ?? $label;
            $column['label'] = $label !== '' ? $label : $defaultLabel;

            return $column;
        };

        $qtyColumn = $take($findQty());
        if ($qtyColumn !== null) {
            $qtyColumn['label'] = 'Qty / Unit';
        }

        $samplingPointColumn = $shortFieldLabel(
            $take($findByNames(['sampling_point_manual', 'manual_sampling_point'])),
            'Sampling Point'
        );
        $tempColumn = $shortFieldLabel($take($findTemp()), 'Sample temp (°C)');
        $appearanceColumn = $shortFieldLabel($take($findByNames(['field_appearance'])), 'Appearance');
        $residualChlorineColumn = $shortFieldLabel($take($findByNames(['field_residual_chlorine'])), 'Residual chlorine');
        $odorColumn = $shortFieldLabel($take($findByNames(['field_odor'])), 'Odor');
        $phColumn = $shortFieldLabel($take($findByNames(['field_ph'])), 'pH');

        $testRequirementsColumn = $take($findByNames(['test_requirements', 'test_category']));
        if ($testRequirementsColumn === null) {
            $testRequirementsColumn = $take($this->waterTestRequirementsFallbackColumn());
        }
        if ($testRequirementsColumn !== null) {
            $testRequirementsColumn['label'] = 'Test requirement';
        }

        $parametersColumn = $take($findByNames(['parameters', 'parameter']));
        if ($parametersColumn !== null) {
            $parametersColumn['label'] = 'Tests';
        }

        $descriptionColumn = $take($findByNames(['sample_description']));

        return [
            'layout_variant' => 'water',
            'grid_rows' => [
                [
                    'type' => 'fields',
                    'cols' => 3,
                    'columns' => [$qtyColumn, $samplingPointColumn, $descriptionColumn],
                ],
                [
                    'type' => 'section',
                    'label' => 'Field data',
                ],
                [
                    'type' => 'fields',
                    'cols' => 2,
                    'group' => 'field_data',
                    'columns' => [$tempColumn, $appearanceColumn],
                ],
                [
                    'type' => 'fields',
                    'cols' => 3,
                    'group' => 'field_data',
                    'columns' => [$residualChlorineColumn, $odorColumn, $phColumn],
                ],
                [
                    'type' => 'divider',
                ],
                [
                    'type' => 'fields',
                    'cols' => 1,
                    'columns' => [$testRequirementsColumn],
                ],
            ],
            'catalog_row' => [
                'sample_type' => $sampleTypeColumn,
                'parameters' => $parametersColumn,
            ],
            'parameters_column' => null,
            'description_column' => null,
            'extra_columns' => [],
        ];
    }

    /**
     * Waste Water TRF sample card: qty/point/state, condition/requirements/description, type + tests.
     *
     * @param  callable(?array): ?array  $take
     * @param  callable(): ?array  $findQty
     * @param  callable(list<string>): ?array  $findByNames
     * @param  list<array<string, mixed>>  $tableColumns
     * @param  list<string>  $hiddenFields
     * @param  array<string, bool>  $usedNames
     * @return array{
     *     layout_variant: string,
     *     grid_rows: list<array<string, mixed>>,
     *     catalog_row: array{sample_type?: array<string, mixed>|null, parameters?: array<string, mixed>|null},
     *     parameters_column: array<string, mixed>|null,
     *     description_column: array<string, mixed>|null,
     *     extra_columns: list<array<string, mixed>>
     * }
     */
    private function buildWasteWaterSampleCardLayout(
        callable $take,
        callable $findQty,
        callable $findByNames,
        ?array $sampleTypeColumn,
        array $tableColumns,
        array $hiddenFields,
        callable $columnName,
        array &$usedNames,
    ): array {
        $qtyColumn = $take($findQty());
        if ($qtyColumn !== null) {
            $qtyColumn['label'] = 'Qty / Unit';
        }

        $samplingPointColumn = $take($findByNames(['sampling_point_manual', 'manual_sampling_point']));
        if ($samplingPointColumn !== null) {
            $samplingPointColumn['label'] = 'Sampling Point';
        }

        $stateColumn = $take($findByNames(['state_of_sample']));

        $conditionColumn = $take($findByNames(['sample_condition']));

        $testRequirementsColumn = $take($findByNames(['test_requirements', 'test_category']));
        if ($testRequirementsColumn === null) {
            $testRequirementsColumn = $take($this->wasteWaterTestRequirementsFallbackColumn());
        }
        if ($testRequirementsColumn !== null) {
            $testRequirementsColumn['label'] = 'Test requirements';
        }

        $descriptionColumn = $take($findByNames(['sample_description']));

        $parametersColumn = $take($findByNames(['parameters', 'parameter']));
        if ($parametersColumn !== null) {
            $parametersColumn['label'] = 'Tests';
        }

        $extraColumns = [];
        foreach ($tableColumns as $column) {
            $name = $columnName($column);
            if (isset($usedNames[$name]) || in_array($name, $hiddenFields, true)) {
                continue;
            }
            if (SubmissionFormSchemaHelper::isSampleCollectionFieldName($name)) {
                continue;
            }
            $extraColumns[] = $column;
        }

        return [
            'layout_variant' => 'waste_water',
            'grid_rows' => [
                [
                    'type' => 'fields',
                    'cols' => 3,
                    'columns' => [$qtyColumn, $samplingPointColumn, $stateColumn],
                ],
                [
                    'type' => 'fields',
                    'cols' => 3,
                    'columns' => [$conditionColumn, $testRequirementsColumn, $descriptionColumn],
                ],
            ],
            'catalog_row' => [
                'sample_type' => $sampleTypeColumn,
                'parameters' => $parametersColumn,
            ],
            'parameters_column' => null,
            'description_column' => null,
            'extra_columns' => $extraColumns,
        ];
    }

    /**
     * @return array{analysis_label: string, param_count: int, param_preview: string}
     */
    public function walkInSampleRowSummary(int $rowIndex): array
    {
        $picker = $this->walkInParameterPickerState($rowIndex);
        $paramIds = $picker['selected'];
        $labelById = collect($picker['options'] ?? [])
            ->mapWithKeys(static fn (array $option): array => [
                (string) ($option['id'] ?? '') => (string) ($option['name'] ?? $option['id'] ?? ''),
            ]);

        $paramNames = collect($paramIds)
            ->map(static fn (string $id): string => $labelById->get($id, $id))
            ->filter(static fn (string $name): bool => $name !== '')
            ->values()
            ->all();

        $analysisIds = $this->flattenWalkInScalarList($this->formData['analysis_type_id'][$rowIndex] ?? []);
        $analysisLabel = '';
        if ($analysisIds !== []) {
            $names = \App\AnalysisType::query()
                ->whereIn('id', $analysisIds)
                ->orderBy('name')
                ->pluck('name')
                ->map(static fn ($name): string => (string) $name)
                ->values()
                ->all();
            $analysisLabel = $names !== []
                ? implode(', ', array_slice($names, 0, 2)).(count($names) > 2 ? '…' : '')
                : 'Analysis selected';
        }

        return [
            'analysis_label' => $analysisLabel,
            'param_count' => count($paramIds),
            'param_preview' => implode(', ', array_slice($paramNames, 0, 2)),
        ];
    }

    /**
     * @return array{
     *     selected: list<string>,
     *     options: list<array{id: string, name: string}>,
     *     groups: list<array{analysis_type_id: string, analysis_type: string, sample_type: string, tests: list<array{id: string, name: string}>}>
     * }
     */
    public function walkInParameterPickerState(?int $rowIndex = null): array
    {
        if ($rowIndex === null || ! $this->walkInUsesIndexedSampleRows()) {
            $raw = $this->formData['parameters'] ?? ($this->formData['parameter'] ?? []);
            if (is_array($raw) && $raw !== [] && is_array(reset($raw))) {
                $raw = $raw[0] ?? [];
            }
            $selected = $this->flattenWalkInScalarList($raw);

            $groups = $this->parameterGroupsForRow(null);
            $options = $this->flattenParameterPickerOptions($groups);

            return [
                'selected' => $this->normalizeWalkInParameterSelection($selected, $groups),
                'options' => $options,
                'groups' => $groups,
            ];
        }

        $raw = $this->formData['parameters'][$rowIndex] ?? [];
        $selected = $this->flattenWalkInScalarList($raw);

        $groups = $this->parameterGroupsForRow($rowIndex);
        $options = $this->flattenParameterPickerOptions($groups);

        return [
            'selected' => $this->normalizeWalkInParameterSelection($selected, $groups),
            'options' => $options,
            'groups' => $groups,
        ];
    }

    /**
     * Options for LS multi-columns Tests Select2 (Edit request details shape).
     *
     * @return list<array{value: string, label: string, meta?: string, meta_method?: string, meta_lab?: string}>
     */
    public function walkInParameterSelectOptions(?int $rowIndex = null): array
    {
        $picker = $this->walkInParameterPickerState($rowIndex);
        $base = collect($picker['options'] ?? [])
            ->map(static fn (array $option): array => [
                'value' => (string) ($option['id'] ?? ''),
                'label' => (string) ($option['name'] ?? $option['id'] ?? ''),
            ])
            ->filter(static fn (array $option): bool => $option['value'] !== '')
            ->values()
            ->all();

        return $this->enrichWalkInParameterSelectOptions($base);
    }

    /**
     * @param  list<array{value: string, label: string}>  $options
     * @return list<array{value: string, label: string, meta?: string, meta_method?: string, meta_lab?: string}>
     */
    private function enrichWalkInParameterSelectOptions(array $options): array
    {
        return $this->walkInParameterCatalog()->enrichSelectOptions($options);
    }

    /**
     * @return list<array{analysis_type_id: string, analysis_type: string, sample_type: string, tests: list<array{id: string, name: string}>}>
     */
    private function parameterGroupsForRow(?int $rowIndex = null): array
    {
        $sampleTypeIds = $this->resolveSampleTypeIdsForRow($rowIndex);
        if ($sampleTypeIds === []) {
            return [];
        }

        return $this->walkInParameterCatalog()->groupsForSampleTypes($sampleTypeIds);
    }

    private function walkInParameterCatalog(): WalkInParameterCatalogService
    {
        return app(WalkInParameterCatalogService::class);
    }

    /**
     * @param  list<string>  $parameterIds
     */
    private function syncDerivedAnalysisTypesForRow(?int $rowIndex, array $parameterIds): void
    {
        $derived = $this->walkInParameterCatalog()->deriveAnalysisTypeIds($parameterIds);
        $rowCount = max($this->schemaRowCount(), ($rowIndex ?? 0) + 1);
        $this->ensureWalkInIndexedRowField('analysis_type_id', $rowCount);
        $this->ensureWalkInIndexedRowField('analysis_type', $rowCount);
        $this->ensureWalkInIndexedRowField('analysis_types', $rowCount);

        foreach (['analysis_type_id', 'analysis_type', 'analysis_types'] as $key) {
            if ($rowIndex !== null && is_array($this->formData[$key])) {
                $this->formData[$key][$rowIndex] = $derived;
            } elseif ($rowIndex === null) {
                $this->formData[$key] = $derived;
            }
        }
    }

    /**
     * @param  list<array{tests: list<array{id: string, name: string}>}>  $groups
     * @return list<array{id: string, name: string}>
     */
    private function flattenParameterPickerOptions(array $groups): array
    {
        return $this->walkInParameterCatalog()->flattenGroups($groups);
    }

    /**
     * Accept analysis_element ids, or legacy analyte names (maps into every matching element).
     *
     * @param  list<string>  $selected
     * @param  list<array{tests: list<array{id: string, name: string}>}>  $groups
     * @return list<string>
     */
    private function normalizeWalkInParameterSelection(array $selected, array $groups): array
    {
        return $this->walkInParameterCatalog()->normalizeSelection($selected, $groups);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\ReportingUnit>
     */
    public function getReportingUnitsProperty(): Collection
    {
        return \App\ReportingUnit::query()->where('active', 1)->orderBy('name')->get();
    }

    private function isHiddenWalkInRowElement(SubmissionFormElement $element): bool
    {
        if (SubmissionFormSchemaHelper::shouldOmitFromFillForm($element)) {
            return true;
        }

        $name = strtolower(trim((string) ($element->name ?? '')));
        $label = strtolower(trim((string) ($element->label ?? '')));

        if (in_array($name, ['sampling_point_other', 'sampling_point_others', 'other_sampling_point'], true)) {
            return true;
        }

        return str_contains($label, 'sampling point (other)');
    }

    public function getSubmissionFormProperty(): ?SubmissionForm
    {
        if ($this->selectedSubmissionFormId) {
            return SubmissionForm::query()
                ->with(['sections.elementHolders.elements'])
                ->find($this->selectedSubmissionFormId);
        }

        if (! $this->selectedSampleTypeId) {
            return null;
        }

        return $this->resolveSubmissionFormForSampleType((string) $this->selectedSampleTypeId);
    }

    /**
     * Sample types offered in the walk-in sample type picker.
     * When the resolved form has category bindings the list is restricted to those categories;
     * otherwise all sample types are returned (original behaviour).
     *
     * @return \Illuminate\Support\Collection<int, \App\SampleType>
     */
    public function getWalkInSampleTypeOptionsProperty(): \Illuminate\Support\Collection
    {
        $categoryId = trim((string) ($this->selectedSampleTypeCategoryId ?? ''));
        if ($categoryId !== '') {
            return \App\SampleType::query()
                ->where('active', true)
                ->where('sample_type_category', $categoryId)
                ->orderBy('name')
                ->get();
        }

        $form = $this->submissionForm;

        if ($form !== null && \Illuminate\Support\Facades\Schema::hasTable('submission_form_sample_type_categories')) {
            $form->loadMissing(['sampleTypeCategories']);
            $categoryIds = $form->sampleTypeCategories->pluck('id')->map(fn ($id) => (int) $id)->all();

            if ($categoryIds !== []) {
                return \App\SampleType::query()
                    ->where('active', true)
                    ->whereIn('sample_type_category', $categoryIds)
                    ->orderBy('name')
                    ->get();
            }
        }

        return collect($this->sampleTypes);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\SampleTypeCategory>
     */
    public function getSampleTypeCategoriesProperty(): \Illuminate\Support\Collection
    {
        return \App\SampleTypeCategory::query()
            ->where('active', 1)
            ->orderBy('sample_type_category')
            ->get();
    }

    public function addSchemaRow(string $sectionId): void
    {
        $form = $this->submissionForm;
        if ($form === null) {
            return;
        }

        $section = $form->sections->firstWhere('id', $sectionId);
        if ($section === null || ! $this->walkInSectionUsesSampleCards($section)) {
            return;
        }

        foreach ($this->uniqueRowElementsForSection($section) as $element) {
            $existing = $this->formData[$element->name] ?? [];
            if (! is_array($existing)) {
                $existing = [];
            }
            $existing[] = $this->defaultValueForElement($element);
            $this->formData[$element->name] = $existing;
        }

        $rowCount = $this->schemaRowCount();
        foreach (array_merge(
            SubmissionFormSchemaHelper::sampleCollectionFieldNames(),
            ['sampling_point_manual', 'manual_sampling_point', 'state_of_sample', 'sample_type_id', 'sample_type'],
        ) as $fieldName) {
            $this->ensureWalkInIndexedRowFieldForCopy($fieldName, $rowCount);
        }

        $this->ensureWalkInCanonicalQtyFields($rowCount);
        $this->ensureWalkInSampleTypeField($rowCount);
        $this->ensureAdditionalDetailsFormField($rowCount);
        $this->appendWaterTrfSyntheticRowDefaults();
        $this->appendWasteWaterTrfSyntheticRowDefaults();

        $this->dispatch('trf-reinit-parameter-selects');
    }

    /**
     * Water TRF expects test_requirements even when the live form schema has not been patched yet.
     */
    private function ensureWaterTrfSyntheticFormFields(SubmissionForm $submissionForm): void
    {
        if (trim((string) ($submissionForm->document_code ?? '')) !== TrfDocumentCodeForSampleType::WATER) {
            return;
        }

        if (isset($this->formData['test_requirements'])) {
            return;
        }

        $rowCount = 1;
        foreach ($this->formData as $values) {
            if (is_array($values)) {
                $rowCount = max($rowCount, count($values));
            }
        }

        $this->formData['test_requirements'] = array_fill(0, $rowCount, $this->defaultWaterTestRequirementsRow());
    }

    /**
     * @return array{microbiology: bool, legionella: bool, chemistry: bool}
     */
    private function defaultWaterTestRequirementsRow(): array
    {
        return [
            'microbiology' => false,
            'legionella' => false,
            'chemistry' => false,
        ];
    }

    private function appendWaterTrfSyntheticRowDefaults(): void
    {
        if (! $this->usesWaterSampleCardLayout()) {
            return;
        }

        if (! isset($this->formData['test_requirements']) || ! is_array($this->formData['test_requirements'])) {
            $this->formData['test_requirements'] = [];
        }

        $this->formData['test_requirements'][] = $this->defaultWaterTestRequirementsRow();
    }

    private function ensureWasteWaterTrfSyntheticFormFields(SubmissionForm $submissionForm): void
    {
        $documentCode = strtoupper(trim((string) ($submissionForm->document_code ?? '')));
        $isWasteWaterDocument = $documentCode === TrfDocumentCodeForSampleType::WASTE_WATER
            || $documentCode === TrfDocumentCodeForSampleType::WASTE_WATER_LEGACY
            || str_contains($documentCode, 'WASTEWATER')
            || (str_contains($documentCode, 'WASTE') && str_contains($documentCode, '036'));

        if (! $isWasteWaterDocument) {
            return;
        }

        if (isset($this->formData['test_requirements'])) {
            return;
        }

        $rowCount = 1;
        foreach ($this->formData as $values) {
            if (is_array($values)) {
                $rowCount = max($rowCount, count($values));
            }
        }

        $this->formData['test_requirements'] = array_fill(0, $rowCount, $this->defaultWasteWaterTestRequirementsRow());
    }

    /**
     * @return array{microbiology: bool, chemistry: bool}
     */
    private function defaultWasteWaterTestRequirementsRow(): array
    {
        return [
            'microbiology' => false,
            'chemistry' => false,
        ];
    }

    private function appendWasteWaterTrfSyntheticRowDefaults(): void
    {
        if (! $this->usesWasteWaterSampleCardLayout()) {
            return;
        }

        if (! isset($this->formData['test_requirements']) || ! is_array($this->formData['test_requirements'])) {
            $this->formData['test_requirements'] = [];
        }

        $this->formData['test_requirements'][] = $this->defaultWasteWaterTestRequirementsRow();
    }

    /**
     * @return array{type: string, label: string, class: string, element: SubmissionFormElement, field: array<string, mixed>}
     */
    private function syntheticSampleTypeColumn(): array
    {
        $element = new SubmissionFormElement([
            'name' => 'sample_type_id',
            'label' => 'Sample type',
            'element_type' => 'sample_type_select',
            'required' => true,
        ]);

        return [
            'type' => 'field',
            'label' => 'Sample type',
            'class' => 'walk-in-trf-col-default',
            'element' => $element,
            'field' => [
                'name' => 'sample_type_id',
                'label' => 'Sample type',
                'type' => 'sample_type_select',
                'required' => true,
            ],
        ];
    }

    private function waterTestRequirementsFallbackColumn(): array
    {
        $element = new SubmissionFormElement([
            'name' => 'test_requirements',
            'label' => 'Test requirements',
            'element_type' => 'checkbox',
            'options' => [
                ['value' => 'microbiology', 'label' => 'Microbiology'],
                ['value' => 'legionella', 'label' => 'Legionella'],
                ['value' => 'chemistry', 'label' => 'Chemical Analysis'],
            ],
        ]);

        return [
            'type' => 'field',
            'label' => 'Test requirement',
            'class' => 'walk-in-trf-col-radio',
            'element' => $element,
            'field' => app(\App\Services\Sampleworkflow\WalkInTrfFieldMapper::class)->toField($element),
        ];
    }

    private function wasteWaterTestRequirementsFallbackColumn(): array
    {
        $element = new SubmissionFormElement([
            'name' => 'test_requirements',
            'label' => 'Test requirements',
            'element_type' => 'checkbox',
            'options' => [
                ['value' => 'microbiology', 'label' => 'Microbiology'],
                ['value' => 'chemistry', 'label' => 'Chemistry'],
            ],
        ]);

        return [
            'type' => 'field',
            'label' => 'Test requirements',
            'class' => 'walk-in-trf-col-radio',
            'element' => $element,
            'field' => app(\App\Services\Sampleworkflow\WalkInTrfFieldMapper::class)->toField($element),
        ];
    }

    public function getSelectedSampleTypeProperty()
    {
        if (! $this->selectedSampleTypeId) {
            return null;
        }

        return \App\SampleType::query()
            ->with('sampleTypeCategory')
            ->find($this->selectedSampleTypeId);
    }

    public function getIsFoodProperty()
    {
        $st = $this->selectedSampleType;
        if (! $st) {
            return false;
        }

        $resolver = app(TrfDocumentCodeForSampleType::class);

        return $resolver->isFood($st) || $resolver->isFoodAndFeed($st);
    }

    public function getIsWaterProperty()
    {
        if ($this->isWasteWater) {
            return false;
        }

        $st = $this->selectedSampleType;
        if (! $st) {
            return false;
        }

        return app(TrfDocumentCodeForSampleType::class)->isWater($st);
    }

    public function getIsWasteWaterProperty()
    {
        // Category is the source of truth for Waste Water RFT routing.
        if ($this->selectedSampleTypeCategoryIsWasteWater()) {
            return true;
        }

        $documentCode = strtoupper(trim((string) ($this->submissionForm?->document_code ?? '')));
        if (
            $documentCode === TrfDocumentCodeForSampleType::WASTE_WATER
            || $documentCode === TrfDocumentCodeForSampleType::WASTE_WATER_LEGACY
            || str_contains($documentCode, 'WASTEWATER')
            || (str_contains($documentCode, 'WASTE') && str_contains($documentCode, '036'))
        ) {
            return true;
        }

        $st = $this->selectedSampleType;
        if ($st === null) {
            return false;
        }

        return app(TrfDocumentCodeForSampleType::class)->isWasteWater($st);
    }

    private function selectedSampleTypeCategoryIsWasteWater(): bool
    {
        $categoryId = trim((string) ($this->selectedSampleTypeCategoryId ?? ''));
        if ($categoryId !== '') {
            $categoryName = (string) (\App\SampleTypeCategory::query()
                ->whereKey($categoryId)
                ->value('sample_type_category') ?? '');

            if ($this->isWasteWaterCategoryName($categoryName)) {
                return true;
            }
        }

        $st = $this->selectedSampleType;
        if ($st === null) {
            return false;
        }

        return $this->isWasteWaterCategoryName((string) $st->category());
    }

    private function isWasteWaterCategoryName(string $categoryName): bool
    {
        $normalized = mb_strtolower(trim($categoryName));

        return $normalized === 'waste water'
            || $normalized === 'wastewater'
            || str_contains($normalized, 'waste water');
    }

    public function removeSchemaRow(string $sectionId, int $rowIndex): void
    {
        $form = $this->submissionForm;
        if ($form === null) {
            return;
        }

        $section = $form->sections->firstWhere('id', $sectionId);
        if ($section === null || ! $this->walkInSectionUsesSampleCards($section)) {
            return;
        }

        $this->dispatch('trf-destroy-editors');

        $fieldNames = $section->elementHolders->flatMap->elements
            ->map(fn ($element) => (string) ($element->name ?? ''))
            ->filter()
            ->merge(['sample_quantity', 'sample_quantity_unit', 'additional_details'])
            ->unique()
            ->values();

        foreach ($fieldNames as $name) {
            if (! isset($this->formData[$name]) || ! is_array($this->formData[$name])) {
                continue;
            }

            unset($this->formData[$name][$rowIndex]);
            $this->formData[$name] = array_values($this->formData[$name]);
        }
    }

    public function updatedSelectedFormInstanceIds(): void
    {
        $this->refreshCheckInContexts();
    }

    public function updatedSelectedSampleTypeId($value): void
    {
        $normalizedValue = $value !== null && $value !== '' ? (string) $value : null;
        $lastSelected = $this->lastSelectedSampleTypeId !== null && $this->lastSelectedSampleTypeId !== ''
            ? (string) $this->lastSelectedSampleTypeId
            : null;

        if ($normalizedValue === $lastSelected) {
            return;
        }

        if ($normalizedValue !== null) {
            $this->selectedSampleTypeCategoryId = null;
        }

        // RFT fill by document code: sample types are chosen per sample row only.
        if ($this->pageMode && $this->wizardOnly && filled($this->selectedSubmissionFormId)) {
            $this->lastSelectedSampleTypeId = $normalizedValue;

            return;
        }

        $this->lastSelectedSampleTypeId = $normalizedValue;
        $this->formData = [];
        $this->selectedCrmCustomerId = null;
        $this->walkInActiveStepIndex = 0;

        if ($normalizedValue !== null) {
            $this->initializeFormDataForSampleType($normalizedValue);

            if (! empty($this->selectedFormInstanceIds)) {
                $firstInstance = SubmissionFormInstance::with(['crmCustomer', 'values.element'])->find($this->selectedFormInstanceIds[0]);
                if ($firstInstance) {
                    $this->loadFormDataFromInstance($firstInstance);
                }
            }
        }

        $this->dispatch('submission-form-reinit-signatures');
        $this->dispatch('trf-reinit-signatures');
        $this->dispatch('trf-reset-all-parameter-selects');
    }

    public function updatedSelectedSampleTypeCategoryId($value): void
    {
        $normalizedValue = $value !== null && $value !== '' ? (string) $value : null;

        $this->selectedSampleTypeCategoryId = $normalizedValue;
        $this->selectedSampleTypeId = null;
        $this->lastSelectedSampleTypeId = null;
        $this->selectedSubmissionFormId = null;
        $this->formData = [];
        $this->selectedCrmCustomerId = null;
        $this->walkInActiveStepIndex = 0;

        if ($normalizedValue !== null) {
            $submissionForm = $this->resolveSubmissionFormForSampleTypeCategory($normalizedValue);
            if ($submissionForm !== null) {
                $this->selectedSubmissionFormId = (string) $submissionForm->id;
                $this->initializeFormDataFromSubmissionForm($submissionForm);
            }
        }

        $this->dispatch('submission-form-reinit-signatures');
        $this->dispatch('trf-reinit-signatures');
        $this->dispatch('trf-reset-all-parameter-selects');
    }

    public function updatedSelectedCrmCustomerId(?string $value): void
    {
        $this->handleCustomerFieldUpdated($value);
    }

    public function updatedFormDataCustomerName(?string $value): void
    {
        $this->handleCustomerFieldUpdated($value);
    }

    public function updatedFormDataClientName(?string $value): void
    {
        $this->handleCustomerFieldUpdated($value);
    }

    public function updatedFormDataCustomer(?string $value): void
    {
        $this->handleCustomerFieldUpdated($value);
    }

    public function updatedFormDataClient(?string $value): void
    {
        $this->handleCustomerFieldUpdated($value);
    }

    public function updatedFormDataContactPerson(?string $value): void
    {
        if ($value === null || trim($value) === '') {
            $this->clearContactCommunicationFields();

            return;
        }

        $contact = CustomerContact::query()->find($value);
        if ($contact === null) {
            return;
        }

        $customer = CRMCustomer::query()->find($this->selectedCrmCustomerId ?? $contact->crm_customer_id);
        if ($customer === null) {
            return;
        }

        $communicationFields = app(CustomerContactPrefillService::class)
            ->buildSelectedContactCommunicationFields($contact);

        foreach ($communicationFields as $key => $fieldValue) {
            if (! array_key_exists($key, $this->formData)) {
                continue;
            }

            $this->formData[$key] = $fieldValue;
        }

        $this->applyCustomerRepresentativeFromContact($contact);
    }

    public function updatedFormDataCompanyUnitId(?string $value): void
    {
        $this->formData['contact_person'] = '';
        $this->clearContactCommunicationFields();

        foreach (['sampling_point', 'sampling_location', 'location'] as $fieldName) {
            if (! array_key_exists($fieldName, $this->formData)) {
                continue;
            }

            if (! is_array($this->formData[$fieldName])) {
                $this->formData[$fieldName] = '';

                continue;
            }

            $this->formData[$fieldName] = array_map(static fn (): string => '', $this->formData[$fieldName]);
        }

        $customerId = $this->resolveSelectedCustomerId();
        if ($customerId === null) {
            return;
        }

        $customer = CRMCustomer::query()->find($customerId);
        if ($customer === null) {
            return;
        }

        $this->autoSelectDefaultContactForCustomer($customer);
    }

    public function openWalkInAddCustomerModal(): void
    {
        $this->resetWalkInCustomerModal();
        $this->showWalkInAddCustomerModal = true;
    }

    public function openWalkInAddContactModal(): void
    {
        if ($this->resolveSelectedCustomerId() === null) {
            $this->addError('formData.customer_name', 'Select a customer before adding a contact.');

            return;
        }

        $this->resetWalkInContactModal();
        $this->walkInNewContactUnitId = trim((string) ($this->formData['company_unit_id'] ?? '')) !== ''
            ? trim((string) $this->formData['company_unit_id'])
            : (string) (CRMCompanyUnit::query()
                ->where('crm_customer_id', $this->resolveSelectedCustomerId())
                ->where('active', 1)
                ->orderBy('name')
                ->value('id') ?? '');
        $this->showWalkInAddContactModal = true;
    }

    public function openWalkInAddUnitModal(): void
    {
        if ($this->resolveSelectedCustomerId() === null) {
            $this->addError('formData.customer_name', 'Select a customer before adding a company unit.');

            return;
        }

        $this->resetWalkInUnitModal();
        $this->showWalkInAddUnitModal = true;
    }

    public function openWalkInAddPointModal(string $fieldName = 'sampling_location', ?int $rowIndex = null): void
    {
        $customerId = $this->resolveSelectedCustomerId();
        if ($customerId === null) {
            $this->addError('formData.customer_name', 'Select a customer before adding a sample point.');

            return;
        }

        $this->resetWalkInPointModal();
        $this->walkInSamplePointTargetField = $fieldName !== '' ? $fieldName : 'sampling_location';
        $this->walkInSamplePointTargetRowIndex = $rowIndex;
        $selectedUnitId = trim((string) ($this->formData['company_unit_id'] ?? ''));
        $this->walkInNewPointUnitId = $selectedUnitId !== ''
            ? $selectedUnitId
            : (string) (CRMCompanyUnit::query()
                ->where('crm_customer_id', $customerId)
                ->where('active', 1)
                ->orderBy('name')
                ->value('id') ?? '');
        $this->showWalkInAddPointModal = true;
    }

    public function saveWalkInCustomer(CRMCustomerService $customerService): void
    {
        $this->validate([
            'walkInNewCustomerName' => 'required|string|max:255',
            'walkInNewCustomerEmail' => 'nullable|email|max:255',
            'walkInNewCustomerPhone' => 'nullable|string|max:50',
            'walkInNewCustomerAddress' => 'nullable|string|max:500',
        ]);

        try {
            $customer = $customerService->create([
                'name' => trim($this->walkInNewCustomerName),
                'email' => trim($this->walkInNewCustomerEmail),
                'phone1' => trim($this->walkInNewCustomerPhone),
                'physical_address' => trim($this->walkInNewCustomerAddress),
                'postal_address' => trim($this->walkInNewCustomerAddress),
                'active' => 1,
            ]);
        } catch (\RuntimeException $exception) {
            $this->addError('walkInNewCustomerName', $exception->getMessage());

            return;
        }

        $this->applyCustomerPrefillFromCrm($customer);
        $this->showWalkInAddCustomerModal = false;
        $this->resetWalkInCustomerModal();
    }

    public function saveWalkInContact(): void
    {
        $customerId = $this->resolveSelectedCustomerId();
        if ($customerId === null) {
            $this->addError('walkInNewContactName', 'Select a customer first.');

            return;
        }

        $this->validate([
            'walkInNewContactName' => 'required|string|max:255',
            'walkInNewContactUnitId' => 'required|exists:crm_company_units,id',
            'walkInNewContactEmail' => 'nullable|email|max:255',
            'walkInNewContactPhone' => 'nullable|string|max:50',
        ]);

        $unitId = trim($this->walkInNewContactUnitId);
        $unitBelongsToCustomer = CRMCompanyUnit::query()
            ->whereKey($unitId)
            ->where('crm_customer_id', $customerId)
            ->exists();

        if (! $unitBelongsToCustomer) {
            $this->addError('walkInNewContactUnitId', 'Select a company unit for this client.');

            return;
        }

        $nameParts = preg_split('/\s+/', trim($this->walkInNewContactName)) ?: [];
        $firstName = $nameParts[0] ?? '';
        $lastName = count($nameParts) > 1 ? (string) array_pop($nameParts) : '';
        $middleName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        $contact = new CustomerContact();
        $contact->crm_customer_id = $customerId;
        $contact->company_id = getUserCompany();
        $contact->first_name = $firstName;
        $contact->middle_name = $middleName !== '' ? $middleName : null;
        $contact->last_name = $lastName !== '' ? $lastName : null;
        $contact->email = $this->walkInNewContactEmail !== '' ? $this->walkInNewContactEmail : '';
        $contact->telephone = $this->walkInNewContactPhone !== '' ? $this->walkInNewContactPhone : '-';
        $contact->mobile = $this->walkInNewContactPhone !== '' ? $this->walkInNewContactPhone : null;
        $contact->receive_price_list = 0;
        $contact->receive_invoice = 0;
        $contact->receive_report = 0;
        $contact->active = 1;
        $contact->crm_company_unit_id = $unitId;
        $contact->unit_name = $unitId;
        $contact->save();

        if (array_key_exists('contact_person', $this->formData)) {
            $this->formData['contact_person'] = (string) $contact->id;
        }

        $communicationFields = app(CustomerContactPrefillService::class)
            ->buildSelectedContactCommunicationFields($contact);

        foreach ($communicationFields as $key => $fieldValue) {
            if (! array_key_exists($key, $this->formData)) {
                continue;
            }

            $this->formData[$key] = $fieldValue;
        }

        $this->showWalkInAddContactModal = false;
        $this->resetWalkInContactModal();
    }

    public function saveWalkInCompanyUnit(): void
    {
        $customerId = $this->resolveSelectedCustomerId();
        if ($customerId === null) {
            $this->addError('walkInNewUnitName', 'Select a customer first.');

            return;
        }

        $this->validate([
            'walkInNewUnitName' => 'required|string|max:255',
        ]);

        $unit = new CRMCompanyUnit();
        $unit->name = trim($this->walkInNewUnitName);
        $unit->company_id = getUserCompany();
        $unit->crm_customer_id = $customerId;
        $unit->active = 1;
        $unit->save();

        if (array_key_exists('company_unit_id', $this->formData)) {
            $this->formData['company_unit_id'] = (string) $unit->id;
        }

        $this->showWalkInAddUnitModal = false;
        $this->resetWalkInUnitModal();
    }

    public function saveWalkInSamplePoint(): void
    {
        $customerId = $this->resolveSelectedCustomerId();
        if ($customerId === null) {
            $this->addError('walkInNewPointName', 'Select a customer first.');

            return;
        }

        $this->validate([
            'walkInNewPointName' => 'required|string|max:255',
            'walkInNewPointUnitId' => 'required|exists:crm_company_units,id',
        ]);

        $point = SamplePoint::query()->create([
            'crm_customer_id' => $customerId,
            'crm_company_unit_id' => $this->walkInNewPointUnitId,
            'name' => trim($this->walkInNewPointName),
            'active' => 1,
        ]);

        $this->assignWalkInSamplePointSelection((string) $point->id);

        $this->showWalkInAddPointModal = false;
        $this->resetWalkInPointModal();
    }

    public function closeWalkInAddCustomerModal(): void
    {
        $this->showWalkInAddCustomerModal = false;
        $this->resetWalkInCustomerModal();
    }

    public function closeWalkInAddContactModal(): void
    {
        $this->showWalkInAddContactModal = false;
        $this->resetWalkInContactModal();
    }

    public function closeWalkInAddUnitModal(): void
    {
        $this->showWalkInAddUnitModal = false;
        $this->resetWalkInUnitModal();
    }

    public function closeWalkInAddPointModal(): void
    {
        $this->showWalkInAddPointModal = false;
        $this->resetWalkInPointModal();
    }

    private function resetWalkInCustomerModal(): void
    {
        $this->walkInNewCustomerName = '';
        $this->walkInNewCustomerEmail = '';
        $this->walkInNewCustomerPhone = '';
        $this->walkInNewCustomerAddress = '';
        $this->resetValidation([
            'walkInNewCustomerName',
            'walkInNewCustomerEmail',
            'walkInNewCustomerPhone',
            'walkInNewCustomerAddress',
        ]);
    }

    private function resetWalkInContactModal(): void
    {
        $this->walkInNewContactName = '';
        $this->walkInNewContactUnitId = '';
        $this->walkInNewContactEmail = '';
        $this->walkInNewContactPhone = '';
        $this->resetValidation([
            'walkInNewContactName',
            'walkInNewContactUnitId',
            'walkInNewContactEmail',
            'walkInNewContactPhone',
        ]);
    }

    private function resetWalkInUnitModal(): void
    {
        $this->walkInNewUnitName = '';
        $this->resetValidation([
            'walkInNewUnitName',
        ]);
    }

    private function resetWalkInPointModal(): void
    {
        $this->walkInNewPointName = '';
        $this->walkInNewPointUnitId = '';
        $this->walkInSamplePointTargetField = 'sampling_location';
        $this->walkInSamplePointTargetRowIndex = null;
        $this->resetValidation([
            'walkInNewPointName',
            'walkInNewPointUnitId',
        ]);
    }

    private function assignWalkInSamplePointSelection(string $pointId): void
    {
        $field = $this->walkInSamplePointTargetField !== ''
            ? $this->walkInSamplePointTargetField
            : 'sampling_location';
        $rowIndex = $this->walkInSamplePointTargetRowIndex;

        $value = $pointId;
        if (in_array($field, ['sampling_point_manual', 'manual_sampling_point'], true)) {
            $point = SamplePoint::query()->find($pointId);
            $value = $point !== null
                ? (string) ($point->display_name !== '' ? $point->display_name : $point->name)
                : $pointId;
        }

        if ($rowIndex !== null) {
            if (! isset($this->formData[$field]) || ! is_array($this->formData[$field])) {
                $this->formData[$field] = [];
            }

            $this->formData[$field][$rowIndex] = $value;

            return;
        }

        if (array_key_exists($field, $this->formData)) {
            $this->formData[$field] = $value;

            return;
        }

        if (array_key_exists('sampling_location', $this->formData)) {
            $this->formData['sampling_location'] = $pointId;
        }
    }

    public function resolveSelectedCustomerId(): ?string
    {
        if ($this->selectedCrmCustomerId !== null && trim($this->selectedCrmCustomerId) !== '') {
            $exists = CRMCustomer::query()
                ->whereKey($this->selectedCrmCustomerId)
                ->exists();

            if ($exists) {
                return (string) $this->selectedCrmCustomerId;
            }

            $this->selectedCrmCustomerId = null;
        }

        foreach (['customer_name', 'client_name', 'customer', 'client'] as $key) {
            $customer = $this->findCrmCustomerBySelectedValue($this->formData[$key] ?? null);
            if ($customer !== null) {
                $this->selectedCrmCustomerId = (string) $customer->id;

                return (string) $customer->id;
            }
        }

        return null;
    }

    public function getCustomerContactsProperty(): Collection
    {
        $customerId = $this->resolveSelectedCustomerId();
        if ($customerId === null) {
            return collect();
        }

        $contacts = CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $unitId = trim((string) ($this->formData['company_unit_id'] ?? ''));
        if ($unitId === '') {
            return $contacts;
        }

        return $contacts
            ->filter(fn (CustomerContact $contact): bool => $contact->isLinkedToCompanyUnit($unitId))
            ->values();
    }

    public function getSelectedCompanyUnitNameProperty(): ?string
    {
        $unitId = trim((string) ($this->formData['company_unit_id'] ?? ''));
        if ($unitId === '') {
            return null;
        }

        return CRMCompanyUnit::query()->whereKey($unitId)->value('name');
    }

    public function getCustomerSamplePointsProperty(): Collection
    {
        $customerId = $this->resolveSelectedCustomerId();
        if ($customerId === null) {
            return collect();
        }

        $query = SamplePoint::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1);

        $unitId = trim((string) ($this->formData['company_unit_id'] ?? ''));
        if ($unitId !== '') {
            $query->where('crm_company_unit_id', $unitId);
        }

        return $query->orderBy('name')->get();
    }

    public function getCustomerCompanyUnitsProperty(): Collection
    {
        $customerId = $this->resolveSelectedCustomerId();
        if ($customerId === null) {
            return collect();
        }

        return CRMCompanyUnit::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function updated($propertyName, $value): void
    {
        if (preg_match('/^formData\.(sample_type_id|sample_type)(?:\.(\d+))?$/', $propertyName, $matches)) {
            $rowIndex = isset($matches[2]) ? (int) $matches[2] : null;
            $ids = $this->normalizeSampleTypeIdList($value);
            $this->applySampleTypeSelectionFromIds($ids, $rowIndex);

            return;
        }

        if (preg_match('/^formData\.analysis_type_id\.(\d+)$/', $propertyName, $matches)) {
            $this->refreshWalkInParametersForAnalysisChange((int) $matches[1]);
            $this->dispatchWalkInCatalogOptionsRefresh((int) $matches[1], ['parameters']);

            return;
        }

        $fieldKey = str_replace('formData.', '', $propertyName);

        if (in_array($fieldKey, ['analysis_type', 'analysis_types', 'analysis_type_id'], true)) {
            $this->refreshWalkInParametersForAnalysisChange(null);
            $this->dispatchWalkInCatalogOptionsRefresh(null, ['parameters']);
        }
    }

    /**
     * @param  list<string>  $sampleTypeIds
     */
    private function applySampleTypeSelectionFromIds(array $sampleTypeIds, ?int $rowIndex = null): void
    {
        $first = $sampleTypeIds[0] ?? null;

        // Per-row sample type pickers must not touch form-level selectedSampleTypeId;
        // that triggers updatedSelectedSampleTypeId() and wipes wizard state (scroll jump).
        if ($rowIndex === null) {
            if ($first !== null) {
                $this->selectedSampleTypeId = (string) $first;
                $this->lastSelectedSampleTypeId = (string) $first;
            } elseif (
                filled($this->selectedSubmissionFormId)
                && ! filled($this->initialSampleTypeId)
            ) {
                $this->selectedSampleTypeId = null;
            }
        }

        $this->clearAnalysisSelectionsForRow($rowIndex);
        $this->dispatchWalkInParameterPickerCatalog($rowIndex);
        $this->dispatchWalkInCatalogOptionsRefresh($rowIndex, ['parameters']);
    }

    /**
     * Push the current parameter catalog to Alpine pickers (wire:ignore is not re-rendered).
     */
    private function dispatchWalkInParameterPickerCatalog(?int $rowIndex): void
    {
        $state = $this->walkInParameterPickerState($rowIndex);

        $this->dispatch(
            'walk-in-params-row-reset',
            rowIndex: $rowIndex ?? 0,
            selected: [],
            options: $state['options'],
            groups: $state['groups'],
        );
    }

    private function dispatchWalkInParameterPickerReset(?int $rowIndex): void
    {
        $this->dispatch(
            'walk-in-params-row-reset',
            rowIndex: $rowIndex ?? 0,
            selected: [],
            deferCatalog: true,
        );
    }

    private function clearAnalysisSelectionsForRow(?int $rowIndex = null): void
    {
        foreach (['analysis_type_id', 'analysis_type', 'analysis_types', 'parameter', 'parameters'] as $key) {
            if (! array_key_exists($key, $this->formData)) {
                continue;
            }

            $isParam = in_array($key, ['parameter', 'parameters'], true);
            $isAnalysis = in_array($key, ['analysis_type_id', 'analysis_type', 'analysis_types'], true);

            if ($rowIndex !== null && is_array($this->formData[$key])) {
                $this->formData[$key][$rowIndex] = ($isParam || $isAnalysis) ? [] : '';
            } elseif ($rowIndex === null) {
                $this->formData[$key] = ($isParam || $isAnalysis || is_array($this->formData[$key])) ? [] : '';
            }
        }
    }

    public function getCustomersProperty()
    {
        return \App\Models\CRM\CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    public function getAnalysisTypesProperty()
    {
        return $this->analysisTypesForRow(null);
    }

    /**
     * Analysis types for selected sample type(s) (linked Fill or in-form multi select).
     *
     * @return \Illuminate\Support\Collection<int, \App\AnalysisType>
     */
    public function analysisTypesForRow(?int $rowIndex = null): \Illuminate\Support\Collection
    {
        $sampleTypeIds = $this->resolveSampleTypeIdsForRow($rowIndex);
        if ($sampleTypeIds === []) {
            return new \Illuminate\Database\Eloquent\Collection;
        }

        return \App\AnalysisType::query()
            ->with('sample_type')
            ->whereIn('sample_type_id', $sampleTypeIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<string>
     */
    private function resolveSampleTypeIdsForRow(?int $rowIndex = null): array
    {
        foreach (['sample_type_id', 'sample_type'] as $key) {
            $value = $this->formData[$key] ?? null;
            if ($rowIndex !== null) {
                if (! is_array($value)) {
                    continue;
                }
                $ids = $this->normalizeSampleTypeIdList($value[$rowIndex] ?? null);
                if ($ids !== []) {
                    return $ids;
                }

                continue;
            }

            $ids = $this->normalizeSampleTypeIdList($value);
            if ($ids !== []) {
                return $ids;
            }
        }

        if (filled($this->selectedSampleTypeId)) {
            return [(string) $this->selectedSampleTypeId];
        }

        return [];
    }

    private function resolveSampleTypeIdForRow(?int $rowIndex = null): ?string
    {
        $ids = $this->resolveSampleTypeIdsForRow($rowIndex);

        return $ids[0] ?? null;
    }

    /**
     * @return list<string>
     */
    private function normalizeSampleTypeIdList(mixed $value): array
    {
        $ids = $this->flattenWalkInScalarList($value);

        if ($ids === []) {
            return [];
        }

        return \App\SampleType::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->values()
            ->all();
    }

    /**
     * Flatten nested Livewire row values (e.g. `[['uuid']]` or `[[]]`) into scalar strings.
     *
     * @return list<string>
     */
    private function flattenWalkInScalarList(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (! is_array($value)) {
            $scalar = trim((string) $value);

            return $scalar !== '' ? [$scalar] : [];
        }

        $ids = [];
        foreach ($value as $item) {
            foreach ($this->flattenWalkInScalarList($item) as $scalar) {
                $ids[] = $scalar;
            }
        }

        return array_values(array_unique(array_filter($ids, static fn (string $id): bool => $id !== '')));
    }

    public function getParametersProperty()
    {
        return $this->parametersForRow(null);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Analyte>
     */
    public function parametersForRow(?int $rowIndex = null): \Illuminate\Support\Collection
    {
        $sampleTypeIds = $this->resolveSampleTypeIdsForRow($rowIndex);
        if ($sampleTypeIds === []) {
            return collect();
        }

        $analysisTypeIds = \App\AnalysisType::query()
            ->whereIn('sample_type_id', $sampleTypeIds)
            ->where(function ($query): void {
                $query->where('active', true)->orWhere('active', 1);
            })
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->all();

        if ($analysisTypeIds === []) {
            return collect();
        }

        return \App\Analyte::whereHas('analysis_elements', function ($q) use ($analysisTypeIds): void {
            $q->whereIn('analysis_type_id', $analysisTypeIds)->where('active', 1);
        })->orderBy('name')->get();
    }

    /**
     * @return list<string>
     */
    private function resolveAnalysisTypeIdsForRow(?int $rowIndex = null): array
    {
        $ids = [];

        foreach (['analysis_type_id', 'analysis_type', 'analysis_types'] as $key) {
            $value = $this->formData[$key] ?? null;
            if ($rowIndex !== null) {
                if (! is_array($value)) {
                    continue;
                }
                $cell = $value[$rowIndex] ?? null;
                $ids = array_merge($ids, $this->normalizeAnalysisTypeIdList($cell, $rowIndex));
                continue;
            }

            $ids = array_merge($ids, $this->normalizeAnalysisTypeIdList($value, null));
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * @return list<string>
     */
    private function normalizeAnalysisTypeIdList(mixed $value, ?int $rowIndex = null): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (! is_array($value)) {
            $value = [(string) $value];
        }

        $first = $value === [] ? null : reset($value);
        if (is_array($first)) {
            return [];
        }

        $sampleTypeIds = $this->resolveSampleTypeIdsForRow($rowIndex);
        $raw = array_values(array_unique(array_filter(array_map(
            static fn ($item): string => is_scalar($item) ? (string) $item : '',
            $value
        ), static fn (string $id): bool => $id !== '')));

        if ($raw === []) {
            return [];
        }

        // Accept either IDs or analysis type names (legacy name-based fields).
        $byId = \App\AnalysisType::query()
            ->when($sampleTypeIds !== [], fn ($q) => $q->whereIn('sample_type_id', $sampleTypeIds))
            ->whereIn('id', $raw)
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->all();

        $byName = \App\AnalysisType::query()
            ->when($sampleTypeIds !== [], fn ($q) => $q->whereIn('sample_type_id', $sampleTypeIds))
            ->whereIn('name', $raw)
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->all();

        return array_values(array_unique(array_merge($byId, $byName)));
    }

    /**
     * @param  array<int, string>  $instanceIds
     * @param  array<int, array{id: string, label: string, customer: string}>  $summaries
     */
    public function handleReceiveModalOpen(array $instanceIds, array $summaries): void
    {
        $normalizedInstanceIds = array_values(array_filter($instanceIds));
        $walkInInProgress = $normalizedInstanceIds === []
            && $this->selectedFormInstanceIds === []
            && $this->selectedSampleTypeId !== null
            && $this->walkInActiveStepIndex > 0;

        if (! $walkInInProgress) {
            $this->remarks = '';
            $this->checkInTrfFields = [];
            $this->selectedSampleTypeId = null;
            $this->selectedSampleTypeCategoryId = null;
            $this->lastSelectedSampleTypeId = null;
            $this->formData = [];
            $this->selectedCrmCustomerId = null;
            $this->walkInActiveStepIndex = 0;
            $this->resetValidation();
            if ($normalizedInstanceIds !== []) {
                $this->intakeChannel = CommercialEnquirySyncService::SOURCE_WALK_IN;
            }
        }

        $this->selectedFormInstanceIds = $normalizedInstanceIds;
        $this->selectedFormSummaries = $summaries;
        $this->refreshCheckInContexts();

        if ($this->isPhysicalCheckIn) {
            $this->showPhysicalConfirmModal = true;

            return;
        }

        $this->showPhysicalConfirmModal = false;
        $this->dispatch(
            'show-receive-sample-modal',
            physicalCheckIn: false,
            intakeChannel: $this->intakeChannel,
        );
    }

    public function openOfflinePaperTrfCapture(): void
    {
        $this->openDirectRegistration();
    }

    public function openDirectRegistration(): void
    {
        $this->intakeChannel = CommercialEnquirySyncService::SOURCE_OFFLINE;
        $this->selectedFormInstanceIds = [];
        $this->selectedSampleTypeId = null;
        $this->selectedSampleTypeCategoryId = null;
        $this->selectedSubmissionFormId = null;
        $this->walkInActiveStepIndex = 0;
        $this->directRegistrationStage = 'select_trf';
        $this->directRegistrationPane = 'register';
        $this->directRegistrationCarouselReady = false;
        $this->lastSavedTrfName = '';
        $this->lastGeneratedSfiIds = [];
        $this->handleReceiveModalOpen([], []);
        $this->dispatch(
            'direct-registration-carousel-state',
            ready: false,
            pane: 'register',
        );
    }

    public function isOfflineIntake(): bool
    {
        return $this->intakeChannel === CommercialEnquirySyncService::SOURCE_OFFLINE;
    }

    public function backToDirectRegistrationFormCards(): void
    {
        if (! $this->isOfflineIntake()) {
            return;
        }

        $this->selectedSubmissionFormId = null;
        $this->selectedSampleTypeId = null;
        $this->selectedSampleTypeCategoryId = null;
        $this->formData = [];
        $this->walkInActiveStepIndex = 0;
        $this->directRegistrationStage = 'select_trf';
        $this->directRegistrationPane = 'register';
        $this->resetValidation();
    }

    public function goDirectRegistrationCarousel(mixed $direction = 'right'): void
    {
        if (is_array($direction)) {
            $direction = $direction['direction'] ?? 'right';
        }

        if (! $this->isOfflineIntake() || ! $this->directRegistrationCarouselReady) {
            return;
        }

        $direction = strtolower(trim((string) $direction));

        if ($direction === 'left') {
            $this->directRegistrationPane = 'register';
            $this->dispatch(
                'direct-registration-carousel-state',
                ready: true,
                pane: 'register',
            );

            return;
        }

        if ($direction !== 'right') {
            return;
        }

        $sfiId = $this->lastGeneratedSfiIds[0] ?? null;
        if ($sfiId === null || $sfiId === '') {
            return;
        }

        $instance = SubmissionFormInstance::query()
            ->with('sampleSubmissionRequest')
            ->find($sfiId);
        $submissionRequestId = $instance?->sampleSubmissionRequest?->id
            ?? (filled($instance?->portal_request_id) ? (string) $instance->portal_request_id : null);

        $this->dispatch(
            'open-acceptance-wizard',
            submissionFormInstanceId: (string) $sfiId,
            submissionRequestId: $submissionRequestId,
            mode: 'receive_only',
        )->to(AcceptanceFormWizard::class);

        $this->dispatch('direct-registration-handoff-to-receive');
        $this->dispatch('hide-receive-sample-modal');
    }

    private function walkInSourceChannel(): string
    {
        if ($this->plannerMode) {
            return CommercialEnquirySyncService::SOURCE_SCHEDULED;
        }

        if ($this->isOfflineIntake()) {
            return CommercialEnquirySyncService::SOURCE_OFFLINE;
        }

        return CommercialEnquirySyncService::SOURCE_WALK_IN;
    }

    public function closePhysicalConfirmModal(): void
    {
        $this->showPhysicalConfirmModal = false;
        $this->selectedFormInstanceIds = [];
        $this->selectedFormSummaries = [];
        $this->checkInContexts = [];
        $this->resetValidation();
    }

    public function onHideReceiveSampleModal(): void
    {
        $this->showPhysicalConfirmModal = false;
        if (! $this->isPhysicalCheckIn) {
            $this->intakeChannel = CommercialEnquirySyncService::SOURCE_WALK_IN;
            $this->directRegistrationStage = 'select_trf';
            $this->directRegistrationPane = 'register';
            $this->directRegistrationCarouselReady = false;
            $this->lastSavedTrfName = '';
        }
    }

    protected function getListeners(): array
    {
        return [
            'receive-modal-open' => 'handleReceiveModalOpen',
            'hide-receive-sample-modal' => 'onHideReceiveSampleModal',
            'open-offline-paper-trf' => 'openOfflinePaperTrfCapture',
            'go-direct-registration-carousel' => 'goDirectRegistrationCarousel',
        ];
    }

    public function openRejectWizard(string $instanceId): void
    {
        $this->dispatch('open-rejection-wizard', submissionFormInstanceId: $instanceId);
        $this->dispatch('hide-receive-sample-modal');
    }

    public function confirmReceive(): void
    {
        if ($this->isPhysicalCheckIn) {
            $this->confirmPhysicalCheckIn();

            return;
        }

        $this->confirmWalkInCapture();
    }

    private function confirmPhysicalCheckIn(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            $message = 'You must be signed in to receive samples.';
            $this->addError('selection', $message);
            TrfToast::dispatch($this, 'error', $message);

            return;
        }

        $checkInService = app(SampleReceivingCheckInService::class);
        $processed = 0;
        $skipped = 0;
        $blockedReasons = [];

        DB::transaction(function () use ($user, $checkInService, &$processed, &$skipped, &$blockedReasons): void {
            $instances = SubmissionFormInstance::query()
                ->with(['batches', 'submissionForm', 'sampleSubmissionRequest', 'values.element'])
                ->whereIn('id', $this->selectedFormInstanceIds)
                ->get();

            foreach ($instances as $instance) {
                if (! $checkInService->canReceiveInstance($instance)) {
                    $skipped++;
                    $reason = $checkInService->receiveBlockReason($instance);
                    if ($reason !== null) {
                        $blockedReasons[] = $reason;
                    }

                    continue;
                }

                if ($checkInService->receiveInstance(
                    $instance,
                    $user,
                    $this->remarks !== '' ? $this->remarks : null,
                )) {
                    $metadata = $this->checkInTrfFields[$instance->id] ?? [];
                    if ($metadata !== []) {
                        app(ReceivingLabMetadataService::class)->persistForInstance(
                            $instance,
                            $instance->sampleSubmissionRequest,
                            $metadata,
                        );
                    }
                    $processed++;
                }
            }
        });

        if ($processed === 0) {
            $message = 'No eligible requests were checked in.';
            if ($blockedReasons !== []) {
                $message .= ' '.collect($blockedReasons)->unique()->implode(' ');
            }

            $this->addError('selection', $message);
            TrfToast::dispatch($this, 'error', $message);

            return;
        }

        $message = $processed === 1
            ? '1 request checked in and moved to In Review.'
            : "{$processed} requests checked in and moved to In Review.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        TrfToast::flashMessage('success', $message, 'Checked in', 'celebrate');
        $this->showPhysicalConfirmModal = false;
        $this->dispatch('receive-completed');
        $this->dispatch('hide-receive-sample-modal');
    }

    private function confirmWalkInCapture(): void
    {
        try {
            $this->runWalkInCaptureValidations();
        } catch (ValidationException $exception) {
            $this->dispatchWalkInTrfStepHooks();
            $this->notifyWalkInValidationFailure($exception->validator->errors()->all());
            throw $exception;
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            $this->notifyWalkInValidationFailure();

            return;
        }

        if ($this->plannerMode && ($this->selectedScheduleId === null || trim((string) $this->selectedScheduleId) === '')) {
            $message = 'Open this form from a sampling schedule (or pending schedule) so it can be linked on submit.';
            $this->addError('selectedScheduleId', $message);
            TrfToast::dispatch($this, 'error', $message);

            return;
        }

        $this->prepareLabUseFields();

        $user = Auth::user();
        if (! $user instanceof User) {
            $message = 'You must be signed in to receive samples.';
            $this->addError('selection', $message);
            TrfToast::dispatch($this, 'error', $message);

            return;
        }

        $crmCustomerId = $this->resolveSelectedCustomerId();

        $schedule = null;
        if ($this->plannerMode && $this->selectedScheduleId) {
            $schedule = SamplingSchedule::query()->visibleTo()->find($this->selectedScheduleId);
            if ($schedule === null) {
                $message = 'The selected sampling schedule could not be found.';
                $this->addError('selectedScheduleId', $message);
                TrfToast::dispatch($this, 'error', $message);

                return;
            }

            if ($schedule->crm_customer_id) {
                $crmCustomerId = (string) $schedule->crm_customer_id;
            }

            $submissionFormForPrefill = $this->submissionForm;
            if ($submissionFormForPrefill !== null && $this->selectedSampleTypeId) {
                $this->formData = app(SamplingScheduleTrfSync::class)->mergeIntoFormData(
                    $this->formData,
                    $schedule,
                    (string) $this->selectedSampleTypeId,
                    $submissionFormForPrefill,
                );
            }
        }

        $payload = $this->walkInSubmissionPayload();

        $submissionForm = $this->submissionForm;
        if ($submissionForm === null) {
            return;
        }

        try {
            $instance = app(SubmissionFormSubmissionService::class)->submitWalkInInstance(
                $submissionForm,
                $payload,
                $crmCustomerId !== null ? (string) $crmCustomerId : null,
                filled($this->selectedSampleTypeId) ? (string) $this->selectedSampleTypeId : null,
                $this->walkInSourceChannel(),
                $schedule?->id !== null ? (string) $schedule->id : null,
            );

            if ($schedule !== null) {
                $schedule->refresh();
                $schedule->load('submissionFormInstances.values.element');
                $progress = app(SamplingScheduleCollectionProgress::class)->refresh($schedule);
            }

            $this->rememberAmspecSamplerFromFormData();
        } catch (\Throwable $exception) {
            report($exception);
            $message = $this->walkInSubmitFailureMessage($exception);
            $this->addError('selection', $message);
            TrfToast::dispatch($this, 'error', $message);

            return;
        }

        $this->lastGeneratedSfiIds = [$instance->id];

        $trfName = trim((string) ($submissionForm->name ?? 'TRF'));
        if ($trfName === '') {
            $trfName = 'TRF';
        }
        $this->lastSavedTrfName = $trfName;

        $successMessage = $this->plannerMode
            ? 'Sampling form submitted and linked to the schedule successfully.'
            : ($this->isOfflineIntake()
                ? $trfName.' saved successfully'
                : 'Walk-in test request submitted successfully.');
        if ($this->plannerMode && isset($progress)) {
            $successMessage = $progress['is_complete']
                ? 'Sampling form submitted. All '.$progress['scheduled'].' scheduled sample(s) are now collected.'
                : 'Sampling form submitted. Collection progress: '.$progress['label'].' (partial until complete).';
        }

        TrfToast::flashMessage('success', $successMessage, 'Request saved', 'celebrate');
        $this->dispatch('receive-completed', sfiIds: $this->lastGeneratedSfiIds, keepModalOpen: $this->isOfflineIntake());

        if ($this->pageMode) {
            if ($this->plannerMode) {
                $this->redirect(route('system-planner.fill-sampling-forms'), navigate: false);

                return;
            }

            $this->redirect(
                route('sample-workflow', ['status' => 'Samples Receiving']).($this->isOfflineIntake()
                    ? '?tab=ready_for_reception'
                    : '?tab=submitted'),
                navigate: false
            );

            return;
        }

        if ($this->isOfflineIntake()) {
            $this->directRegistrationStage = 'post_save';
            $this->directRegistrationPane = 'register';
            $this->directRegistrationCarouselReady = true;
            TrfToast::dispatch($this, 'success', $successMessage, 'Request saved', 'celebrate');
            $this->dispatch(
                'direct-registration-carousel-state',
                ready: true,
                pane: 'register',
            );

            return;
        }

        TrfToast::dispatch($this, 'success', $successMessage, 'Request saved', 'celebrate');
        $this->dispatch('hide-receive-sample-modal');
    }

    private function walkInSubmitFailureMessage(\Throwable $exception): string
    {
        unset($exception);

        if ($this->plannerMode) {
            return 'Could not submit the sampling form. Please try again.';
        }

        if ($this->isOfflineIntake()) {
            return 'Could not submit the direct registration. Check the customer and sample rows, then try again.';
        }

        return 'Could not submit the walk-in request. Please try again.';
    }

    private function runWalkInCaptureValidations(): void
    {
        $this->syncSelectedSampleTypeFromFormData();

        if ($this->isOfflineIntake()) {
            $this->validate([
                'selectedSubmissionFormId' => 'required_without:selectedSampleTypeId',
                'selectedSampleTypeId' => 'nullable|exists:sample_types,id',
            ], [
                'selectedSubmissionFormId.required_without' => 'Please choose a test request form to fill.',
            ]);
        } else {
            $this->validate([
                'selectedSampleTypeId' => 'required|exists:sample_types,id',
            ], [
                'selectedSampleTypeId.required' => 'Please select a Sample Type (link the form to a sample type, or add a Sample type field on the form).',
            ]);
        }

        $submissionForm = $this->submissionForm;
        if ($submissionForm === null) {
            $message = 'No active Test Request Form template found for the selected sample type.';
            $this->addError('selectedSampleTypeId', $message);
            TrfToast::dispatch($this, 'error', $message);

            return;
        }

        foreach ($this->walkInSections->values() as $stepIndex => $section) {
            try {
                $this->validateWalkInStep($stepIndex);
            } catch (ValidationException $exception) {
                $this->walkInActiveStepIndex = $stepIndex;
                $this->dispatchWalkInTrfStepHooks();
                throw $exception;
            }
        }
    }

    /**
     * Unlinked TRFs rely on an in-form sample_type_select (usually sample_type_id, multi).
     */
    private function syncSelectedSampleTypeFromFormData(): void
    {
        if ($this->isOfflineIntake() && filled($this->selectedSampleTypeCategoryId)) {
            return;
        }

        if (filled($this->selectedSampleTypeId)) {
            return;
        }

        $ids = $this->resolveSampleTypeIdsForRow(null);
        if ($ids === []) {
            // Rows section: take the first selected id across rows.
            foreach (['sample_type_id', 'sample_type'] as $key) {
                $value = $this->formData[$key] ?? null;
                if (! is_array($value)) {
                    continue;
                }
                foreach ($value as $cell) {
                    $rowIds = $this->normalizeSampleTypeIdList($cell);
                    if ($rowIds !== []) {
                        $ids = $rowIds;
                        break 2;
                    }
                }
            }
        }

        $first = $ids[0] ?? null;
        if ($first !== null) {
            $this->selectedSampleTypeId = $first;
            $this->lastSelectedSampleTypeId = $first;
        }
    }

    private function validateWalkInStep(int $stepIndex): void
    {
        $section = $this->walkInSections->values()->get($stepIndex);
        if ($section === null) {
            return;
        }

        $title = (string) ($section->title ?? '');

        // Only Customer + Test/sample rows must be complete to proceed.
        if ($title === 'Customer details') {
            $this->validateWalkInCustomerInfo();
            if ($this->getErrorBag()->isNotEmpty()) {
                throw ValidationException::withMessages($this->getErrorBag()->toArray());
            }

            return;
        }

        if ($this->walkInSectionUsesSampleCards($section)
            || ($section->section_type ?? '') === 'rows_section') {
            $this->validateWalkInSchemaRows($section);

            return;
        }

        if (in_array($title, ['Submit & sign', 'Submit and sign'], true)) {
            $this->validateWalkInSubmitAndSign();
            if ($this->getErrorBag()->isNotEmpty()) {
                throw ValidationException::withMessages($this->getErrorBag()->toArray());
            }

            return;
        }

        // Collection / Misc may be empty; progress still advances.
    }

    private function validateWalkInSubmitAndSign(): void
    {
        $this->resetValidation(['formData.amspec_sampler_name']);

        $sampledBy = \App\Services\Sampleworkflow\SampledByParty::normalize($this->formData['sampled_by'] ?? null);
        $samplerName = trim((string) ($this->formData['amspec_sampler_name'] ?? ''));

        if ($sampledBy === \App\Services\Sampleworkflow\SampledByParty::COMPANY && $samplerName === '') {
            $this->addError('formData.amspec_sampler_name', 'Sampler name is required when sampled by the company.');
        }
    }

    /**
     * Grows the AmSpec sampler typeahead with whoever was just entered on "Submit & sign".
     */
    private function rememberAmspecSamplerFromFormData(): void
    {
        $sampledBy = \App\Services\Sampleworkflow\SampledByParty::normalize($this->formData['sampled_by'] ?? null);
        if ($sampledBy !== \App\Services\Sampleworkflow\SampledByParty::COMPANY) {
            return;
        }

        $name = trim((string) ($this->formData['amspec_sampler_name'] ?? ''));
        if ($name === '') {
            return;
        }

        \App\Models\AmspecSampler::remember($name, $this->formData['amspec_sampler_employee_id'] ?? null);
    }

    private function validateWalkInCustomerInfo(): void
    {
        $this->resetValidation([
            'formData.customer_name',
            'formData.client_name',
            'formData.customer',
            'formData.client',
            'formData.contact_person',
        ]);

        $customerName = '';

        foreach (['customer_name', 'client_name', 'customer', 'client'] as $key) {
            $candidate = trim((string) ($this->formData[$key] ?? ''));
            if ($candidate !== '') {
                $customerName = $candidate;
                break;
            }
        }

        if ($customerName === '' && $this->resolveSelectedCustomerId() === null) {
            $this->addError(
                'formData.customer_name',
                'Customer name is required in Customer details.',
            );
        }

        if (array_key_exists('contact_person', $this->formData)
            && trim((string) ($this->formData['contact_person'] ?? '')) === '') {
            $this->addError('formData.contact_person', 'Contact person is required in Customer details.');
        }
    }

    /**
     * @param  list<string>|null  $messages
     */
    private function notifyWalkInValidationFailure(?array $messages = null): void
    {
        $messages = array_values(array_filter($messages ?? $this->getErrorBag()->all()));
        if ($messages === []) {
            return;
        }

        if (count($messages) === 1) {
            TrfToast::dispatch($this, 'error', $messages[0]);

            return;
        }

        TrfToast::dispatch(
            $this,
            'error',
            $this->isOfflineIntake()
                ? 'Could not submit paper test request. Please complete the required fields below.'
                : 'Could not submit walk-in request. Please complete the required fields below.',
        );

        foreach (array_slice($messages, 0, 4) as $message) {
            TrfToast::dispatch($this, 'warning', $message, 'Please review', 'pulse');
        }
    }

    /**
     * Walk-in Livewire state uses indexed row fields (e.g. parameters[0]); the normalizer
     * folds those into sample_rows for enquiry sync but processFormData must receive the
     * indexed keys to persist SubmissionFormInstanceValue rows.
     *
     * @return array<string, mixed>
     */
    private function walkInSubmissionPayload(): array
    {
        $this->finalizeWalkInRowSelectionsForSubmit();
        $this->ensureWalkInCanonicalQtyFields($this->schemaRowCount());
        $this->mirrorCanonicalQtyOntoLegacySchemaFields();

        $normalizer = app(SubmissionFormValueNormalizer::class);
        $payload = array_merge(
            $this->formData,
            $normalizer->toRequestPayload($this->formData),
        );

        // Indexed row fields are the source of truth for processFormData persistence.
        foreach ($this->walkInPersistableRowFieldNames() as $name) {
            if (! array_key_exists($name, $this->formData) || ! is_array($this->formData[$name])) {
                continue;
            }

            $payload[$name] = $this->formData[$name];
        }

        if (isset($payload['extra_sampling_equipment']) && is_array($payload['extra_sampling_equipment'])) {
            $payload['extra_sampling_equipment'] = json_encode(array_values(array_filter(
                $payload['extra_sampling_equipment'],
                static function ($row): bool {
                    if (! is_array($row)) {
                        return false;
                    }

                    return trim((string) ($row['label'] ?? '')) !== ''
                        || trim((string) ($row['id'] ?? '')) !== '';
                }
            )));
        }

        if ($this->usesSamplingEquipmentIdPicker()) {
            $this->normalizeSamplingEquipmentFormKey();
            if (array_key_exists('thermometer_id', $payload) || array_key_exists('thermometer_id', $this->formData)) {
                $raw = $payload['thermometer_id'] ?? $this->formData['thermometer_id'] ?? null;
                $resolver = app(\App\Services\Sampleworkflow\TrfSamplingEquipmentResolver::class);

                if (is_array($raw) && $this->walkInUsesIndexedSampleRows()) {
                    $encodedRows = [];
                    foreach (array_values($raw) as $sampleIndex => $sampleValue) {
                        $ids = $this->flattenWalkInScalarList($sampleValue);
                        $encodedRows[$sampleIndex] = $resolver->encodeIds($ids) ?? '';
                    }
                    $payload['thermometer_id'] = $encodedRows;
                } else {
                    $payload['thermometer_id'] = $resolver->encodeIds(
                        $this->flattenWalkInScalarList(is_array($raw) ? $raw : [$raw])
                    ) ?? '';
                }

                unset($payload['Equipment_ID'], $payload['equipment_id'], $payload['Equipment ID']);
            }
        }

        if (isset($payload['additional_details']) && is_array($payload['additional_details'])) {
            $encodedDetails = [];
            foreach (array_values($payload['additional_details']) as $sampleIndex => $rows) {
                $encodedDetails[$sampleIndex] = $this->encodeAdditionalDetailsRows(
                    $this->decodeAdditionalDetailsRows($rows)
                );
            }
            $payload['additional_details'] = $encodedDetails;

            // Keep sample_rows in sync for enquiry/PDF consumers.
            if (isset($payload['sample_rows']) && is_array($payload['sample_rows'])) {
                foreach ($payload['sample_rows'] as $sampleIndex => $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $payload['sample_rows'][$sampleIndex]['additional_details'] = $this->decodeAdditionalDetailsRows(
                        $encodedDetails[$sampleIndex] ?? ''
                    );
                }
            }
        }

        return $payload;
    }

    /**
     * Ensure derived analysis types and indexed parameter rows exist before submit persistence.
     */
    private function finalizeWalkInRowSelectionsForSubmit(): void
    {
        $rowCount = $this->schemaRowCount();
        $this->ensureWalkInIndexedRowField('parameters', $rowCount);
        $this->ensureWalkInIndexedRowField('analysis_type_id', $rowCount);

        for ($index = 0; $index < $rowCount; $index++) {
            $parameterIds = $this->normalizeRowSelectValues($this->formData['parameters'][$index] ?? null);
            if ($parameterIds !== []) {
                $this->syncDerivedAnalysisTypesForRow($index, $parameterIds);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function walkInPersistableRowFieldNames(): array
    {
        $names = array_merge(
            $this->schemaRowFieldNames(),
            ['sample_quantity', 'sample_quantity_unit', 'number_of_samples'],
            ['parameters', 'parameter', 'analysis_type_id', 'analysis_type', 'analysis_types'],
            ['additional_details'],
        );

        if ($this->usesWaterSampleCardLayout()) {
            $names[] = 'test_requirements';
        }

        if ($this->usesWasteWaterSampleCardLayout()) {
            $names[] = 'test_requirements';
        }

        if ($this->isFood) {
            $names[] = 'test_category';
            $names[] = 'sample_condition';
            $names[] = 'sample_temp';
        }

        return array_values(array_unique(array_filter($names)));
    }

    /**
     * If the builder only has legacy number_of_samples, copy Qty into that field for storage
     * while keeping sample_quantity as the canonical walk-in source.
     */
    private function mirrorCanonicalQtyOntoLegacySchemaFields(): void
    {
        $qty = $this->formData['sample_quantity'] ?? null;
        if (! is_array($qty)) {
            return;
        }

        $hasSampleQuantityElement = false;
        $hasNumberOfSamplesElement = false;

        foreach ($this->walkInSections as $section) {
            if (! $this->walkInSectionUsesSampleCards($section)) {
                continue;
            }
            foreach ($section->elementHolders->flatMap->elements as $element) {
                $name = (string) ($element->name ?? '');
                if ($name === 'sample_quantity') {
                    $hasSampleQuantityElement = true;
                }
                if ($name === 'number_of_samples') {
                    $hasNumberOfSamplesElement = true;
                }
            }
        }

        if (! $hasSampleQuantityElement && $hasNumberOfSamplesElement) {
            $this->formData['number_of_samples'] = $qty;
        }
    }

    /**
     * @return list<string>
     */
    private function schemaRowFieldNames(): array
    {
        return $this->walkInSections
            ->filter(fn ($section) => $this->walkInSectionUsesSampleCards($section))
            ->flatMap(fn ($section) => $this->uniqueRowElementsForSection($section))
            ->map(fn ($element) => (string) ($element->name ?? ''))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Qty/Unit always writes sample_quantity (+ unit), never number_of_samples.
     */
    private function ensureWalkInCanonicalQtyFields(int $rowCount): void
    {
        $rowCount = max(1, $rowCount);

        foreach (['sample_quantity', 'sample_quantity_unit'] as $field) {
            $existing = $this->formData[$field] ?? [];
            if (! is_array($existing)) {
                $existing = $existing !== null && $existing !== '' ? [(string) $existing] : [];
            }

            while (count($existing) < $rowCount) {
                $existing[] = '';
            }

            $this->formData[$field] = $existing;
        }
    }

    private function ensureWalkInSampleTypeField(int $rowCount): void
    {
        $this->ensureWalkInIndexedRowField('sample_type_id', $rowCount);

        // Prefill empty row pickers from the form-level sample type so Choose tests can resolve a catalog.
        $fallbackId = filled($this->selectedSampleTypeId)
            ? (string) $this->selectedSampleTypeId
            : (filled($this->initialSampleTypeId) ? (string) $this->initialSampleTypeId : null);

        if ($fallbackId === null) {
            return;
        }

        $existing = $this->formData['sample_type_id'] ?? [];
        if (! is_array($existing)) {
            return;
        }

        $changed = false;
        foreach ($existing as $index => $value) {
            if ($this->normalizeSampleTypeIdList($value) !== []) {
                continue;
            }
            $existing[$index] = [$fallbackId];
            $changed = true;
        }

        if ($changed) {
            $this->formData['sample_type_id'] = $existing;
        }
    }

    /**
     * Ensure an indexed row field (e.g. parameters.0) exists for every sample row.
     */
    private function ensureWalkInIndexedRowField(string $field, int $rowCount): void
    {
        $rowCount = max(1, $rowCount);
        $existing = $this->formData[$field] ?? [];
        if (! is_array($existing)) {
            $existing = filled($existing) ? [(string) $existing] : [];
        }

        $emptyValue = in_array($field, ['parameters', 'parameter', 'analysis_type_id', 'analysis_type', 'analysis_types', 'sample_type_id', 'sample_type'], true)
            ? []
            : '';

        while (count($existing) < $rowCount) {
            $existing[] = $emptyValue;
        }

        $this->formData[$field] = $existing;
    }

    private function schemaRowCount(): int
    {
        $count = 0;
        foreach (array_merge($this->schemaRowFieldNames(), ['sample_quantity', 'sample_quantity_unit', 'sample_type_id']) as $name) {
            if (isset($this->formData[$name]) && is_array($this->formData[$name])) {
                $count = max($count, count($this->formData[$name]));
            }
        }

        return max(1, $count);
    }

    /**
     * @return list<string>
     */
    private function normalizeRowSelectValues(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(
                static fn ($item): string => trim((string) $item),
                $value,
            ), static fn (string $token): bool => $token !== ''));
        }

        $string = trim((string) $value);
        if ($string === '') {
            return [];
        }

        if (str_contains($string, ',')) {
            return array_values(array_filter(array_map('trim', explode(',', $string))));
        }

        return [$string];
    }

    /**
     * Row validation elements: when Tests (parameters) is on the form, Analysis Type is derived on save — do not require it.
     *
     * @return Collection<int, SubmissionFormElement>
     */
    private function walkInRowElementsForValidation(SubmissionFormSection $section): Collection
    {
        $elements = $this->uniqueRowElementsForSection($section);
        $usesParameters = $elements->contains(
            fn (SubmissionFormElement $element): bool => in_array((string) ($element->name ?? ''), ['parameters', 'parameter'], true)
        );

        if (! $usesParameters) {
            return $elements;
        }

        return $elements
            ->filter(fn (SubmissionFormElement $element): bool => ! in_array(
                (string) ($element->name ?? ''),
                ['analysis_type_id', 'analysis_type', 'analysis_types'],
                true
            ))
            ->values();
    }

    private function validateWalkInSchemaRows(?SubmissionFormSection $section = null): void
    {
        $sections = $section !== null
            ? collect([$section])
            : $this->walkInSections->filter(fn ($walkInSection) => $this->walkInSectionUsesSampleCards($walkInSection));

        $rowElements = $sections
            ->flatMap(fn (SubmissionFormSection $walkInSection) => $this->walkInRowElementsForValidation($walkInSection));

        if ($rowElements->isEmpty()) {
            return;
        }

        $rules = [];
        $messages = [];
        $hasFilledRow = false;
        $rowCount = $this->schemaRowCount();

        for ($index = 0; $index < $rowCount; $index++) {
            if (! $this->schemaRowHasContent($index)) {
                continue;
            }

            $hasFilledRow = true;

            $parameterIds = $this->normalizeRowSelectValues($this->formData['parameters'][$index] ?? null);
            if ($parameterIds !== []) {
                $this->syncDerivedAnalysisTypesForRow($index, $parameterIds);
            }

            foreach ($rowElements as $element) {
                if (! $element->is_required) {
                    continue;
                }

                $name = (string) ($element->name ?? '');
                if ($name === '') {
                    continue;
                }

                $key = 'formData.'.$name.'.'.$index;
                if (in_array($name, ['parameters', 'parameter'], true)) {
                    $rules[$key] = 'required|array|min:1';
                    $messages[$key.'.required'] = 'Tests are required for row '.($index + 1).'.';
                    $messages[$key.'.min'] = 'Choose at least one test for row '.($index + 1).'.';

                    continue;
                }

                $rules[$key] = 'required';
                $messages[$key.'.required'] = ($element->label ?? $name).' is required for row '.($index + 1).'.';
            }

            if ($this->isFood && ! $this->rowHasFoodCategorySelection($index)) {
                $this->addError('formData.test_category.'.$index, 'Test category is required for row '.($index + 1).'.');
            }

            if ($this->usesWaterSampleCardLayout() && ! $this->rowHasMultiOptionSelection($index, 'test_requirements')) {
                $this->addError('formData.test_requirements.'.$index, 'Test requirement is required for row '.($index + 1).'.');
            }

            if ($this->usesWasteWaterSampleCardLayout() && ! $this->rowHasMultiOptionSelection($index, 'test_requirements')) {
                $this->addError('formData.test_requirements.'.$index, 'Test requirements are required for row '.($index + 1).'.');
            }
        }

        if (! $hasFilledRow) {
            throw ValidationException::withMessages([
                'formData.sample_description.0' => 'Add at least one sample row with details in Test & sample information.',
            ]);
        }

        if ($rules !== []) {
            $this->validate($rules, $messages);
        }

        $this->throwIfWalkInValidationErrorsPresent();
    }

    private function throwIfWalkInValidationErrorsPresent(): void
    {
        if ($this->getErrorBag()->isNotEmpty()) {
            throw ValidationException::withMessages($this->getErrorBag()->toArray());
        }
    }

    private function rowHasFoodCategorySelection(int $index): bool
    {
        if ($this->rowHasMultiOptionSelection($index, 'test_category')) {
            return true;
        }

        $resolver = app(\App\Services\SubmissionForm\TrfDocumentCodeForSampleType::class);

        foreach (['analysis_type_id', 'analysis_type', 'analysis_types'] as $key) {
            foreach ($this->rowFieldStringTokens($index, $key) as $token) {
                if ($resolver->isFoodSampleTypeLabel($token)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function rowFieldStringTokens(int $index, string $fieldName): array
    {
        $raw = $this->formData[$fieldName][$index] ?? null;

        if ($raw === null || $raw === '') {
            return [];
        }

        $candidates = is_array($raw) ? $raw : [$raw];
        $tokens = [];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) || is_bool($candidate)) {
                continue;
            }

            $token = trim((string) $candidate);
            if ($token === '' || in_array($token, $tokens, true)) {
                continue;
            }

            $tokens[] = $token;
        }

        return $tokens;
    }

    private function rowHasMultiOptionSelection(int $index, string $fieldName): bool
    {
        $value = $this->formData[$fieldName][$index] ?? null;

        if (is_array($value)) {
            foreach ($value as $selected) {
                if (is_bool($selected) && $selected) {
                    return true;
                }

                if (! is_bool($selected) && trim((string) $selected) !== '') {
                    return true;
                }
            }

            return false;
        }

        return trim((string) ($value ?? '')) !== '';
    }

    private function schemaRowHasContent(int $index): bool
    {
        foreach ($this->schemaRowFieldNames() as $name) {
            $value = $this->formData[$name][$index] ?? null;

            if (is_array($value)) {
                if (array_filter($value) !== []) {
                    return true;
                }

                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if ($name === 'sample_description') {
                $text = trim(strip_tags((string) $value));
                if ($text !== '') {
                    return true;
                }

                continue;
            }

            return true;
        }

        return false;
    }

    public function getIsPhysicalCheckInProperty(): bool
    {
        return $this->selectedFormInstanceIds !== [];
    }

    public function previewDraft(): void
    {
        if (!$this->selectedSampleTypeId) {
            $this->addError('selectedSampleTypeId', 'Select a sample type to preview the test request form.');
            return;
        }

        $submission = null;
        if (!empty($this->selectedFormInstanceIds)) {
            $submission = SubmissionFormInstance::with('crmCustomer')->find($this->selectedFormInstanceIds[0]);
        }

        session([
            'test_request_form_preview_draft' => [
                'form_data' => $this->formData,
                'field_values' => $this->formData,
                'sample_type_id' => $this->selectedSampleTypeId,
                'submission_form_id' => $this->submissionForm?->id,
                'submission_form_instance_id' => $submission?->id,
            ],
        ]);

        $this->dispatch('open-test-request-preview', url: route('test-request-form.preview-draft'));
    }

    private function prepareLabUseFields(): void
    {
        // lab_received_datetime / lab_received_by stay empty until Receive Samples
        // in Ready for Reception (TrfLabUseFieldsService::applyAfterPhysicalReceive).
        $this->formData['lab_received_datetime'] = '';
        $this->formData['lab_received_by'] = '';

        $this->applyCustomerRepresentativeFromSelectedContact();
    }

    private function applyCustomerRepresentativeFromSelectedContact(): void
    {
        $contactId = trim((string) ($this->formData['contact_person'] ?? ''));
        if ($contactId === '') {
            return;
        }

        $contact = CustomerContact::query()->find($contactId);
        if ($contact === null) {
            return;
        }

        if (array_key_exists('crm_contact_id', $this->formData) || $this->formDataHasElement('crm_contact_id')) {
            $this->formData['crm_contact_id'] = (string) $contact->id;
        } else {
            $this->formData['crm_contact_id'] = (string) $contact->id;
        }

        $this->applyCustomerRepresentativeFromContact($contact);
    }

    private function applyCustomerRepresentativeFromContact(CustomerContact $contact): void
    {
        $name = trim(implode(' ', array_filter([
            (string) ($contact->first_name ?? ''),
            (string) ($contact->middle_name ?? ''),
            (string) ($contact->last_name ?? ''),
        ])));
        $mobile = (string) ($contact->mobile ?? $contact->telephone ?? '');

        $nameKeys = ['customer_representative_name', 'customer_rep_name'];
        $contactKeys = ['customer_representative_contact', 'customer_rep_contact'];

        foreach ($nameKeys as $key) {
            if ($name !== '' && (array_key_exists($key, $this->formData) || $this->formDataHasElement($key))) {
                $this->formData[$key] = $name;
            }
        }

        foreach ($contactKeys as $key) {
            if ($mobile !== '' && (array_key_exists($key, $this->formData) || $this->formDataHasElement($key))) {
                $this->formData[$key] = $mobile;
            }
        }

        // Always keep keys present so processFormData / commercial sync can persist when schema has them.
        if ($name !== '') {
            $this->formData['customer_representative_name'] = $name;
            $this->formData['customer_rep_name'] = $name;
        }
        if ($mobile !== '') {
            $this->formData['customer_representative_contact'] = $mobile;
            $this->formData['customer_rep_contact'] = $mobile;
        }
    }

    private function formDataHasElement(string $name): bool
    {
        $form = $this->submissionForm;
        if ($form === null) {
            return false;
        }

        return $form->sections
            ->flatMap(fn ($section) => $section->elementHolders->flatMap->elements)
            ->contains(fn ($element) => (string) ($element->name ?? '') === $name);
    }

    private function handleCustomerFieldUpdated(?string $value): void
    {
        $this->resetValidation([
            'formData.customer_name',
            'formData.client_name',
            'formData.customer',
            'formData.client',
        ]);
        $this->prefillCustomerDetailsFromSelection($value);
    }

    private function prefillCustomerDetailsFromSelection(?string $customerName): void
    {
        if ($customerName === null || trim($customerName) === '') {
            $this->selectedCrmCustomerId = null;

            return;
        }

        $customer = $this->findCrmCustomerBySelectedValue($customerName);

        if ($customer) {
            $customer->loadMissing('contacts');
            $this->selectedCrmCustomerId = (string) $customer->id;
            $this->applyCustomerPrefillFromCrm($customer, onlyEmpty: false);

            return;
        }

        $this->selectedCrmCustomerId = null;
    }

    private function findCrmCustomerBySelectedValue(mixed $value): ?CRMCustomer
    {
        $normalized = trim((string) ($value ?? ''));
        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $normalized) === 1) {
            return CRMCustomer::query()->whereKey($normalized)->first();
        }

        return CRMCustomer::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($normalized)])
            ->first();
    }

    private function applyCustomerPrefillFromCrm(CRMCustomer $customer, bool $onlyEmpty = false): void
    {
        $this->selectedCrmCustomerId = (string) $customer->id;

        if (! $onlyEmpty) {
            $this->resetCustomerDependentCrmFields();
        }

        $prefillService = app(CustomerContactPrefillService::class);
        $prefill = $prefillService->buildWalkInCustomerPrefillMap($customer);

        foreach ($prefill as $key => $value) {
            if (! array_key_exists($key, $this->formData)) {
                continue;
            }

            if ($onlyEmpty && ! empty($this->formData[$key])) {
                continue;
            }

            $this->formData[$key] = $value;
        }

        $selectedContactId = trim((string) ($this->formData['contact_person'] ?? ''));
        if ($selectedContactId !== '') {
            $contact = CustomerContact::query()
                ->where('crm_customer_id', $customer->id)
                ->whereKey($selectedContactId)
                ->first();

            if ($contact !== null) {
                $this->applyContactCommunicationPrefill($contact, $onlyEmpty, withCustomerFallbacks: true);
                $this->applyCustomerRepresentativeFromContact($contact);

                return;
            }
        }

        if ($onlyEmpty) {
            return;
        }

        $this->autoSelectSoleCompanyUnit($customer);
        $this->autoSelectDefaultContactForCustomer($customer);
    }

    private function resetCustomerDependentCrmFields(): void
    {
        if (array_key_exists('company_unit_id', $this->formData)) {
            $this->formData['company_unit_id'] = '';
        }

        $this->clearContactPersonAndCommunicationFields();

        foreach (['sampling_point', 'sampling_location', 'location'] as $fieldName) {
            if (! array_key_exists($fieldName, $this->formData)) {
                continue;
            }

            if (! is_array($this->formData[$fieldName])) {
                $this->formData[$fieldName] = '';

                continue;
            }

            $this->formData[$fieldName] = array_map(static fn (): string => '', $this->formData[$fieldName]);
        }
    }

    private function autoSelectSoleCompanyUnit(CRMCustomer $customer): void
    {
        if (! array_key_exists('company_unit_id', $this->formData)) {
            return;
        }

        $unitIds = CRMCompanyUnit::query()
            ->where('crm_customer_id', $customer->id)
            ->where('active', 1)
            ->orderBy('name')
            ->pluck('id');

        if ($unitIds->count() !== 1) {
            return;
        }

        $this->formData['company_unit_id'] = (string) $unitIds->first();
    }

    private function autoSelectDefaultContactForCustomer(CRMCustomer $customer): void
    {
        $prefillService = app(CustomerContactPrefillService::class);
        $unitId = trim((string) ($this->formData['company_unit_id'] ?? ''));
        $contact = $prefillService->resolveDefaultContact($customer);

        if ($contact !== null && $unitId !== '' && ! $contact->isLinkedToCompanyUnit($unitId)) {
            $contact = CustomerContact::query()
                ->where('crm_customer_id', $customer->id)
                ->where('active', 1)
                ->orderByDesc('is_main_customer_contact')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->first(fn (CustomerContact $candidate): bool => $candidate->isLinkedToCompanyUnit($unitId));
        }

        if ($contact === null) {
            return;
        }

        if (array_key_exists('contact_person', $this->formData)) {
            $this->formData['contact_person'] = (string) $contact->id;
        }

        $this->applyContactCommunicationPrefill($contact, onlyEmpty: false, withCustomerFallbacks: true);
        $this->applyCustomerRepresentativeFromContact($contact);
    }

    private function applyContactCommunicationPrefill(
        CustomerContact $contact,
        bool $onlyEmpty = false,
        bool $withCustomerFallbacks = false
    ): void {
        $prefillService = app(CustomerContactPrefillService::class);

        if ($withCustomerFallbacks) {
            $customer = CRMCustomer::query()->find($contact->crm_customer_id)
                ?? CRMCustomer::query()->find($this->selectedCrmCustomerId);
            $communicationFields = $customer !== null
                ? $prefillService->buildContactCommunicationFields($contact, $customer)
                : $prefillService->buildSelectedContactCommunicationFields($contact);
            $communicationFields['crm_contact_id'] = (string) $contact->id;
        } else {
            $communicationFields = $prefillService->buildSelectedContactCommunicationFields($contact);
        }

        foreach ($communicationFields as $key => $fieldValue) {
            if (! array_key_exists($key, $this->formData)) {
                continue;
            }

            if ($onlyEmpty && ! empty($this->formData[$key])) {
                continue;
            }

            $this->formData[$key] = $fieldValue;
        }
    }

    private function clearContactCommunicationFields(): void
    {
        foreach ([
            'customer_phone',
            'mobile_number',
            'customer_email',
            'tel_fax_no',
            'phone',
            'telephone',
            'email',
            'email_address',
            'crm_contact_id',
        ] as $key) {
            if (array_key_exists($key, $this->formData)) {
                $this->formData[$key] = '';
            }
        }
    }

    private function clearContactPersonAndCommunicationFields(): void
    {
        if (array_key_exists('contact_person', $this->formData)) {
            $this->formData['contact_person'] = '';
        }

        $this->clearContactCommunicationFields();
    }

    public function render()
    {
        return view('livewire.sampleworkflow.receive-sample-request', [
            'submissionForm' => $this->submissionForm,
            'walkInSections' => $this->walkInSections,
            'formTypeCards' => ($this->pageMode || $this->isOfflineIntake()) ? $this->formTypeCards : collect(),
            'hasHiddenRftForms' => $this->pageMode && ! $this->plannerMode ? $this->hasHiddenRftForms : false,
            'rftInstances' => $this->pageMode && ! $this->wizardOnly && ! $this->plannerMode ? $this->rftInstances : collect(),
            'plannerSchedules' => $this->pageMode && ! $this->wizardOnly && $this->plannerMode ? $this->plannerSchedules : collect(),
            'plannerScheduleOptions' => $this->plannerMode && $this->wizardOnly ? $this->plannerScheduleOptions : collect(),
        ]);
    }

    private function refreshCheckInContexts(): void
    {
        if ($this->selectedFormInstanceIds === []) {
            $this->checkInContexts = [];
            $this->checkInTrfFields = [];

            return;
        }

        $this->checkInContexts = app(SampleReceivingCheckInService::class)
            ->buildCheckInContexts($this->selectedFormInstanceIds);

        $metadataService = app(ReceivingLabMetadataService::class);
        $normalizer = app(SubmissionFormValueNormalizer::class);
        $instances = SubmissionFormInstance::query()
            ->with(['values.element', 'submissionForm.sections.elementHolders.elements'])
            ->whereIn('id', $this->selectedFormInstanceIds)
            ->get()
            ->keyBy('id');

        foreach ($this->selectedFormInstanceIds as $instanceId) {
            if (isset($this->checkInTrfFields[$instanceId])) {
                continue;
            }

            $instance = $instances->get($instanceId);
            $formData = $instance !== null
                ? $normalizer->valuesMapFromInstance($instance)
                : [];

            $this->checkInTrfFields[$instanceId] = $metadataService->hydrateFromFormData($formData, $instance);
        }
    }

}
