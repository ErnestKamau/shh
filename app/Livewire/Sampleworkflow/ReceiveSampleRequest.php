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
use App\Services\Planner\SamplingScheduleCollectionProgress;
use App\Services\Planner\SamplingScheduleTrfSync;
use App\Services\Sampleworkflow\ReceivingLabMetadataService;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use App\Services\Sampleworkflow\SampleReceivingCheckInService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Services\SubmissionForm\SubmissionFormSubmissionService;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ReceiveSampleRequest extends Component
{
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

    public array $formData = [];

    public $sampleTypes = [];

    /** @var array<int, string> */
    public array $lastGeneratedSfiIds = [];

    public bool $showWalkInAddContactModal = false;

    public bool $showWalkInAddPointModal = false;

    public string $walkInNewContactName = '';

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

    public ?string $initialScheduleId = null;

    public ?string $selectedScheduleId = null;

    /** Resolved CRM customer for walk-in contact/point actions (avoids fragile name-only matching). */
    public ?string $selectedCrmCustomerId = null;

    /** Open Drafts / Today tab on the RFT list page (or pending/filled for planner). */
    public string $rftInstancesTab = 'today';

    public string $rftInstancesSearch = '';

    public bool $showPhysicalConfirmModal = false;

    public function mount(
        array $selectedFormInstanceIds = [],
        array $selectedFormSummaries = [],
        bool $pageMode = false,
        bool $wizardOnly = false,
        bool $plannerMode = false,
        ?string $initialSampleTypeId = null,
        ?string $initialScheduleId = null,
    ): void {
        $this->pageMode = $pageMode;
        $this->wizardOnly = $wizardOnly;
        $this->plannerMode = $plannerMode;
        $this->initialSampleTypeId = $initialSampleTypeId;
        $this->initialScheduleId = $initialScheduleId;
        $this->selectedFormInstanceIds = array_values(array_filter($selectedFormInstanceIds));
        $this->selectedFormSummaries = $selectedFormSummaries;

        if ($this->plannerMode) {
            $this->rftInstancesTab = 'pending';
        }

        $this->sampleTypes = \App\SampleType::orderBy('name')->get();

        if ($this->initialSampleTypeId) {
            $this->selectedSampleTypeId = (string) $this->initialSampleTypeId;
            $this->lastSelectedSampleTypeId = (string) $this->initialSampleTypeId;
            $this->initializeFormDataForSampleType((string) $this->initialSampleTypeId);
        }

        if ($this->plannerMode && $this->initialScheduleId) {
            $this->applyScheduleSelection((string) $this->initialScheduleId, forceSampleType: false);
        } elseif ($this->plannerMode && $this->wizardOnly && $this->selectedSampleTypeId && ! $this->selectedScheduleId) {
            $firstPending = $this->plannerScheduleOptions->first();
            if ($firstPending !== null) {
                $this->applyScheduleSelection((string) $firstPending->id, forceSampleType: false);
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

        $firstInstance = SubmissionFormInstance::with(['crmCustomer', 'submissionForm.sampleTypes', 'batches.sampleType', 'values.element'])->find($this->selectedFormInstanceIds[0]);
        
        if (!$firstInstance) {
            return;
        }

        // Try to determine sample type from submission form
        $sampleType = null;
        if ($firstInstance->submissionForm && $firstInstance->submissionForm->sampleTypes->first()) {
            $sampleType = $firstInstance->submissionForm->sampleTypes->first();
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

    private function initializeFormDataFromSubmissionForm(SubmissionForm $submissionForm): void
    {
        $this->formData = [];

        foreach (app(SubmissionFormSchemaHelper::class)->uniqueSections($submissionForm) as $section) {
            $elements = $section->elementHolders->flatMap->elements->sortBy('sort_order');

            if (($section->section_type ?? '') === 'rows_section') {
                foreach ($elements as $element) {
                    $this->formData[$element->name] = [$this->defaultValueForElement($element)];
                }

                continue;
            }

            foreach ($elements as $element) {
                $this->formData[$element->name] = $this->defaultValueForElement($element);
            }
        }
    }

    public function getWalkInSectionsProperty(): Collection
    {
        $form = $this->submissionForm;

        if ($form === null) {
            return collect();
        }

        return app(SubmissionFormSchemaHelper::class)->uniqueSections($form)
            ->reject(fn ($section) => ($section->title ?? '') === 'TRF storage');
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
                'is_rows' => ($section->section_type ?? '') === 'rows_section',
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
     * Sample-type cards for the tablet Request For Testing page (UI only).
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getFormTypeCardsProperty(): Collection
    {
        return collect($this->sampleTypes)
            ->map(function ($sampleType): ?array {
                $form = $this->resolveSubmissionFormForSampleType((string) $sampleType->id);
                if ($form === null) {
                    return null;
                }

                $defaultDescription = $this->plannerMode
                    ? 'Fill a sampling form for '.$sampleType->name.'.'
                    : 'Capture a test request for '.$sampleType->name.'.';

                return [
                    'submission_form_id' => (string) $form->id,
                    'sample_type_id' => (string) $sampleType->id,
                    'name' => (string) ($form->name ?: $sampleType->name),
                    'sample_type_name' => (string) $sampleType->name,
                    'document_code' => $form->document_code,
                    'sections_count' => $this->wizardStepCountForForm($form),
                    'description' => filled($form->description)
                        ? (string) $form->description
                        : $defaultDescription,
                    'icon' => $this->plannerMode ? 'mdi-clipboard-edit-outline' : 'mdi-flask-outline',
                    'view_url' => route('submission-forms.show', ['submissionForm' => $form, 'from' => 'rft']),
                    'edit_url' => route('submission-forms.builder', ['submissionForm' => $form, 'from' => 'rft']),
                    'details_url' => route('submission-forms.edit', ['submissionForm' => $form, 'from' => 'rft']),
                ];
            })
            ->filter()
            ->values();
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

        $formIds = collect($this->sampleTypes)
            ->map(fn ($sampleType) => $this->resolveSubmissionFormForSampleType((string) $sampleType->id)?->id)
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
            $query->where(function ($q) use ($search): void {
                $q->where('form_number', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%');
            });
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
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhereHas('client', function ($customerQuery) use ($search): void {
                        $customerQuery->where('name', 'like', '%'.$search.'%');
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
                $params = ['sampleType' => $sampleTypeId];

                $pendingSchedule = $this->findPendingScheduleForSampleType((string) $sampleTypeId);

                if ($pendingSchedule !== null) {
                    $params['schedule'] = $pendingSchedule->id;
                }

                $this->redirect(
                    route('system-planner.fill-sampling-forms.fill', $params),
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
    }

    public function startFillForSchedule(string $scheduleId): void
    {
        if (! $this->plannerMode) {
            return;
        }

        $schedule = SamplingSchedule::query()->visibleTo()->findOrFail($scheduleId);
        $sampleTypeId = $this->resolveSampleTypeIdFromSchedule($schedule);
        if ($sampleTypeId === null) {
            $this->dispatch('notify', type: 'error', message: 'This schedule has no sample type. Edit the schedule first.');

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
        $this->lastSelectedSampleTypeId = null;
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
        $this->formData['parameters'][$rowIndex] = $this->parametersForRow($rowIndex)->pluck('name')->values()->all();
    }

    public function clearWalkInParameters(int $rowIndex): void
    {
        $this->formData['parameters'][$rowIndex] = [];
    }

    /**
     * @param  list<string|int|float>  $parameters
     */
    public function setWalkInParameters(int $rowIndex, array $parameters): void
    {
        $this->formData['parameters'][$rowIndex] = array_values(array_map(
            static fn ($value): string => (string) $value,
            $parameters
        ));
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

        if (in_array($name, ['method_of_sampling', 'test_category', 'test_requirements'], true)) {
            return [];
        }

        if ($element->element_type === 'checkbox') {
            $options = $element->options ?? [];

            return is_array($options) && $options !== [] ? [] : false;
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
        $hasSampleQuantity = $elements->contains(
            fn (SubmissionFormElement $el): bool => ($el->name ?? '') === 'sample_quantity',
        );
        $seen = [];

        return $elements
            ->sortBy('sort_order')
            ->filter(function (SubmissionFormElement $element) use (&$seen, $hasSampleQuantity): bool {
                $name = trim((string) ($element->name ?? ''));
                if ($name === '' || isset($seen[$name])) {
                    return false;
                }

                if ($this->isHiddenWalkInRowElement($element)) {
                    return false;
                }

                if ($name === 'number_of_samples' && $hasSampleQuantity) {
                    return false;
                }

                if ($name === 'sample_quantity_unit' && $hasSampleQuantity) {
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

        foreach ($elements as $element) {
            $name = (string) ($element->name ?? '');

            if ($name === 'sample_quantity' || ($name === 'number_of_samples' && ! $hasSampleQuantity)) {
                $columns[] = [
                    'type' => 'qty_unit',
                    'label' => 'Qty / Unit',
                    'class' => 'walk-in-trf-col-qty',
                    'element' => $element,
                ];

                continue;
            }

            if (in_array($name, ['sample_quantity_unit', 'number_of_samples'], true)) {
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

        return $columns;
    }

    public function walkInRowColumnClass(string $fieldName): string
    {
        return match ($fieldName) {
            'sample_description' => 'walk-in-trf-col-desc',
            'sampling_point', 'location' => 'walk-in-trf-col-location',
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
     * Row 3: Sampling point | Test category | Sample/Analysis type
     * Then: Parameters (full), Sample description (full)
     *
     * @param  list<array{type: string, label: string, class: string, element: SubmissionFormElement, field?: array<string, mixed>}>  $tableColumns
     * @param  list<string>  $hiddenFields
     * @return array{
     *     grid_rows: list<list<array<string, mixed>|null>>,
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
                $take($findByNames(['sampling_point', 'location', 'sampling_location'])),
                $take($findByNames(['test_category', 'test_requirements'])),
                $take($findByNames(['analysis_type_id', 'analysis_type', 'analysis_types', 'sample_type_id', 'sample_type'])),
            ],
        ];

        $parametersColumn = $take($findByNames(['parameters', 'parameter']));
        $descriptionColumn = $take($findByNames(['sample_description']));

        $extraColumns = [];
        foreach ($tableColumns as $column) {
            $name = $columnName($column);
            if (isset($usedNames[$name]) || in_array($name, $hiddenFields, true)) {
                continue;
            }
            $extraColumns[] = $column;
        }

        return [
            'grid_rows' => $gridRows,
            'parameters_column' => $parametersColumn,
            'description_column' => $descriptionColumn,
            'extra_columns' => $extraColumns,
        ];
    }

    /**
     * @return array{analysis_label: string, param_count: int, param_preview: string}
     */
    public function walkInSampleRowSummary(int $rowIndex): array
    {
        $analysisLabel = '';
        foreach (['analysis_type', 'analysis_types'] as $key) {
            $candidate = $this->formData[$key][$rowIndex] ?? '';
            if (is_string($candidate) && $candidate !== '') {
                $analysisLabel = $candidate;
                break;
            }
        }

        if ($analysisLabel === '' && ! empty($this->formData['analysis_type_id'][$rowIndex] ?? null)) {
            $analysisType = $this->analysisTypes->firstWhere('id', $this->formData['analysis_type_id'][$rowIndex]);
            $analysisLabel = (string) ($analysisType->name ?? 'Analysis selected');
        }

        $paramRaw = $this->formData['parameters'][$rowIndex] ?? [];
        $paramList = is_array($paramRaw)
            ? array_values(array_map('strval', $paramRaw))
            : ($paramRaw ? [(string) $paramRaw] : []);

        return [
            'analysis_label' => $analysisLabel,
            'param_count' => count($paramList),
            'param_preview' => implode(', ', array_slice($paramList, 0, 2)),
        ];
    }

    /**
     * @return array{selected: list<string>, options: list<string>}
     */
    public function walkInParameterPickerState(int $rowIndex): array
    {
        $raw = $this->formData['parameters'][$rowIndex] ?? [];
        $selected = is_array($raw)
            ? array_values(array_map('strval', $raw))
            : ($raw !== '' && $raw !== null ? [(string) $raw] : []);

        $options = $this->parametersForRow($rowIndex)
            ->pluck('name')
            ->map(fn ($name) => (string) $name)
            ->values()
            ->all();

        return [
            'selected' => $selected,
            'options' => $options,
        ];
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
        $name = strtolower(trim((string) ($element->name ?? '')));
        $label = strtolower(trim((string) ($element->label ?? '')));

        if (in_array($name, ['sampling_point_other', 'sampling_point_others', 'other_sampling_point'], true)) {
            return true;
        }

        return str_contains($label, 'sampling point (other)');
    }

    public function getSubmissionFormProperty(): ?SubmissionForm
    {
        if (! $this->selectedSampleTypeId) {
            return null;
        }

        return $this->resolveSubmissionFormForSampleType((string) $this->selectedSampleTypeId);
    }

    public function addSchemaRow(string $sectionId): void
    {
        $form = $this->submissionForm;
        if ($form === null) {
            return;
        }

        $section = $form->sections->firstWhere('id', $sectionId);
        if ($section === null || ($section->section_type ?? '') !== 'rows_section') {
            return;
        }

        foreach ($section->elementHolders->flatMap->elements as $element) {
            $existing = $this->formData[$element->name] ?? [];
            if (! is_array($existing)) {
                $existing = [];
            }
            $existing[] = $this->defaultValueForElement($element);
            $this->formData[$element->name] = $existing;
        }

        $this->dispatch('trf-reinit-parameter-selects');
    }

    public function getSelectedSampleTypeProperty()
    {
        if (!$this->selectedSampleTypeId) {
            return null;
        }
        return \App\SampleType::find($this->selectedSampleTypeId);
    }

    public function getIsFoodProperty()
    {
        $st = $this->selectedSampleType;
        if (!$st) {
            return false;
        }

        $resolver = app(\App\Services\SubmissionForm\TrfDocumentCodeForSampleType::class);

        return $resolver->isFood($st) || $resolver->isFoodAndFeed($st);
    }

    public function getIsWaterProperty()
    {
        $st = $this->selectedSampleType;
        if (!$st) {
            return false;
        }
        $isWasteWater = stripos($st->name, 'Waste Water') !== false || stripos($st->code, 'WWTR') !== false;
        return !$isWasteWater && (stripos($st->name, 'Water') !== false || stripos($st->code, 'WTR') !== false);
    }

    public function getIsWasteWaterProperty()
    {
        $st = $this->selectedSampleType;
        if (!$st) {
            return false;
        }
        return stripos($st->name, 'Waste Water') !== false || stripos($st->code, 'WWTR') !== false;
    }

    public function removeSchemaRow(string $sectionId, int $rowIndex): void
    {
        $form = $this->submissionForm;
        if ($form === null) {
            return;
        }

        $section = $form->sections->firstWhere('id', $sectionId);
        if ($section === null || ($section->section_type ?? '') !== 'rows_section') {
            return;
        }

        $this->dispatch('trf-destroy-editors');

        foreach ($section->elementHolders->flatMap->elements as $element) {
            $name = (string) ($element->name ?? '');
            if ($name === '' || ! isset($this->formData[$name]) || ! is_array($this->formData[$name])) {
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

        if ($normalizedValue === $this->lastSelectedSampleTypeId) {
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
            return;
        }

        $contact = CustomerContact::query()->find($value);
        if ($contact === null) {
            return;
        }

        if (array_key_exists('customer_email', $this->formData)) {
            $this->formData['customer_email'] = (string) ($contact->email ?? $this->formData['customer_email'] ?? '');
        }

        if (array_key_exists('email', $this->formData) && empty($this->formData['email'])) {
            $this->formData['email'] = (string) ($contact->email ?? '');
        }

        $this->applyCustomerRepresentativeFromContact($contact);
    }

    public function openWalkInAddContactModal(): void
    {
        if ($this->resolveSelectedCustomerId() === null) {
            $this->addError('formData.customer_name', 'Select a customer before adding a contact.');

            return;
        }

        $this->resetWalkInContactModal();
        $this->showWalkInAddContactModal = true;
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
        $this->walkInNewPointUnitId = (string) (CRMCompanyUnit::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->value('id') ?? '');
        $this->showWalkInAddPointModal = true;
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
            'walkInNewContactEmail' => 'nullable|email|max:255',
            'walkInNewContactPhone' => 'nullable|string|max:50',
        ]);

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
        $contact->save();

        if (array_key_exists('contact_person', $this->formData)) {
            $this->formData['contact_person'] = (string) $contact->id;
        }

        if (array_key_exists('customer_email', $this->formData) && $contact->email) {
            $this->formData['customer_email'] = (string) $contact->email;
        }

        $this->showWalkInAddContactModal = false;
        $this->resetWalkInContactModal();
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

    public function closeWalkInAddContactModal(): void
    {
        $this->showWalkInAddContactModal = false;
        $this->resetWalkInContactModal();
    }

    public function closeWalkInAddPointModal(): void
    {
        $this->showWalkInAddPointModal = false;
        $this->resetWalkInPointModal();
    }

    private function resetWalkInContactModal(): void
    {
        $this->walkInNewContactName = '';
        $this->walkInNewContactEmail = '';
        $this->walkInNewContactPhone = '';
        $this->resetValidation([
            'walkInNewContactName',
            'walkInNewContactEmail',
            'walkInNewContactPhone',
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

        if ($rowIndex !== null) {
            if (! isset($this->formData[$field]) || ! is_array($this->formData[$field])) {
                $this->formData[$field] = [];
            }

            $this->formData[$field][$rowIndex] = $pointId;

            return;
        }

        if (array_key_exists($field, $this->formData)) {
            $this->formData[$field] = $pointId;

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

        return CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function getCustomerSamplePointsProperty(): Collection
    {
        $customerId = $this->resolveSelectedCustomerId();
        if ($customerId === null) {
            return collect();
        }

        return SamplePoint::query()
            ->where('crm_customer_id', $customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
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
        if (preg_match('/^formData\.analysis_type_id\.(\d+)$/', $propertyName, $matches)) {
            $rowIndex = (int) $matches[1];
            if (isset($this->formData['parameters'][$rowIndex])) {
                $this->formData['parameters'][$rowIndex] = [];
            }

            $this->dispatch('walk-in-params-row-reset', rowIndex: $rowIndex, options: $this->parametersForRow($rowIndex)->pluck('name')->values()->all(), selected: []);

            return;
        }

        $fieldKey = str_replace('formData.', '', $propertyName);

        if (in_array($fieldKey, ['analysis_type', 'analysis_types', 'analysis_type_id'], true)) {
            foreach (['parameter', 'parameters'] as $paramKey) {
                if (array_key_exists($paramKey, $this->formData)) {
                    $this->formData[$paramKey] = is_array($this->formData[$paramKey]) ? [] : '';
                }
            }
        }
    }

    public function getCustomersProperty()
    {
        return \App\Models\CRM\CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    public function getAnalysisTypesProperty()
    {
        if (!$this->selectedSampleTypeId) {
            return collect();
        }
        return \App\AnalysisType::where('sample_type_id', $this->selectedSampleTypeId)->orderBy('name')->get();
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
        if (! $this->selectedSampleTypeId) {
            return collect();
        }

        $atName = null;
        $atId = null;

        foreach (['analysis_type', 'analysis_types'] as $key) {
            if ($rowIndex !== null) {
                if (! empty($this->formData[$key][$rowIndex] ?? null)) {
                    $atName = $this->formData[$key][$rowIndex];
                    break;
                }
            } elseif (! empty($this->formData[$key])) {
                $atName = $this->formData[$key];
                break;
            }
        }

        if ($rowIndex !== null) {
            $atId = $this->formData['analysis_type_id'][$rowIndex] ?? null;
        } elseif (! empty($this->formData['analysis_type_id'])) {
            $atId = is_array($this->formData['analysis_type_id'])
                ? null
                : $this->formData['analysis_type_id'];
        }

        if ($atId) {
            $at = \App\AnalysisType::where('sample_type_id', $this->selectedSampleTypeId)
                ->where('id', $atId)
                ->first();
        } elseif ($atName) {
            $at = \App\AnalysisType::where('sample_type_id', $this->selectedSampleTypeId)
                ->where('name', $atName)
                ->first();
        } else {
            return collect();
        }

        if (! $at) {
            return collect();
        }

        return \App\Analyte::whereHas('analysis_elements', function ($q) use ($at): void {
            $q->where('analysis_type_id', $at->id)->where('active', 1);
        })->orderBy('name')->get();
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
            $this->lastSelectedSampleTypeId = null;
            $this->formData = [];
            $this->selectedCrmCustomerId = null;
            $this->walkInActiveStepIndex = 0;
            $this->resetValidation();
        }

        $this->selectedFormInstanceIds = $normalizedInstanceIds;
        $this->selectedFormSummaries = $summaries;
        $this->refreshCheckInContexts();

        if ($this->isPhysicalCheckIn) {
            $this->showPhysicalConfirmModal = true;

            return;
        }

        $this->showPhysicalConfirmModal = false;
        $this->dispatch('show-receive-sample-modal', physicalCheckIn: false);
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
    }

    protected function getListeners(): array
    {
        return [
            'receive-modal-open' => 'handleReceiveModalOpen',
            'hide-receive-sample-modal' => 'onHideReceiveSampleModal',
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
            $this->dispatch('notify', type: 'error', message: $message);

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

            return;
        }

        $message = $processed === 1
            ? '1 request checked in and moved to In Review.'
            : "{$processed} requests checked in and moved to In Review.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        session()->flash('success', $message);
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
            $this->dispatch('notify', type: 'error', message: $message);

            return;
        }

        $this->prepareLabUseFields();

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to receive samples.');

            return;
        }

        $crmCustomerId = $this->resolveSelectedCustomerId();

        $schedule = null;
        if ($this->plannerMode && $this->selectedScheduleId) {
            $schedule = SamplingSchedule::query()->visibleTo()->find($this->selectedScheduleId);
            if ($schedule === null) {
                $message = 'The selected sampling schedule could not be found.';
                $this->addError('selectedScheduleId', $message);
                $this->dispatch('notify', type: 'error', message: $message);

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
            DB::beginTransaction();

            $instance = app(SubmissionFormSubmissionService::class)->submitWalkInInstance(
                $submissionForm,
                $payload,
                $crmCustomerId !== null ? (string) $crmCustomerId : null,
                (string) $this->selectedSampleTypeId,
                $this->plannerMode
                    ? CommercialEnquirySyncService::SOURCE_SCHEDULED
                    : CommercialEnquirySyncService::SOURCE_WALK_IN,
                $schedule?->id !== null ? (string) $schedule->id : null,
            );

            if ($schedule !== null) {
                $schedule->refresh();
                $schedule->load('submissionFormInstances.values.element');
                $progress = app(SamplingScheduleCollectionProgress::class)->refresh($schedule);
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);
            $message = $this->plannerMode
                ? 'Could not submit sampling form. '.$exception->getMessage()
                : 'Could not submit walk-in request. '.$exception->getMessage();
            $this->addError('selection', $message);
            $this->dispatch('notify', type: 'error', message: $message);

            return;
        }

        $this->lastGeneratedSfiIds = [$instance->id];

        $successMessage = $this->plannerMode
            ? 'Sampling form submitted and linked to the schedule successfully.'
            : 'Walk-in test request submitted successfully.';
        if ($this->plannerMode && isset($progress)) {
            $successMessage = $progress['is_complete']
                ? 'Sampling form submitted. All '.$progress['scheduled'].' scheduled sample(s) are now collected.'
                : 'Sampling form submitted. Collection progress: '.$progress['label'].' (partial until complete).';
        }

        session()->flash(
            'success',
            $successMessage,
        );
        $this->dispatch('receive-completed', sfiIds: $this->lastGeneratedSfiIds);

        if ($this->pageMode) {
            if ($this->plannerMode) {
                $this->redirect(route('system-planner.fill-sampling-forms'), navigate: false);

                return;
            }

            $this->redirect(route('sample-workflow', ['status' => 'Samples Receiving']).'?tab=submitted', navigate: false);

            return;
        }

        $this->dispatch('hide-receive-sample-modal');
    }

    private function runWalkInCaptureValidations(): void
    {
        $this->validate([
            'selectedSampleTypeId' => 'required|exists:sample_types,id',
        ], [
            'selectedSampleTypeId.required' => 'Please select a Sample Type.',
        ]);

        $submissionForm = $this->submissionForm;
        if ($submissionForm === null) {
            $message = 'No active Test Request Form template found for the selected sample type.';
            $this->addError('selectedSampleTypeId', $message);
            $this->dispatch('notify', type: 'error', message: $message);

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

        if (($section->section_type ?? '') === 'rows_section') {
            $this->validateWalkInSchemaRows($section);

            return;
        }

        // Collection / Misc / Sign may be empty; progress still advances.
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

        if ($customerName === '') {
            $this->addError('formData.customer_name', 'Customer name is required in Customer details.');
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
            $this->dispatch('notify', type: 'error', message: $messages[0]);

            return;
        }

        $this->dispatch(
            'notify',
            type: 'error',
            message: 'Could not submit walk-in request. Please complete the required fields below.',
        );

        foreach (array_slice($messages, 0, 4) as $message) {
            $this->dispatch('notify', type: 'warning', message: $message);
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
        $normalizer = app(SubmissionFormValueNormalizer::class);

        return array_merge(
            $this->formData,
            $normalizer->toRequestPayload($this->formData),
        );
    }

    /**
     * @return list<string>
     */
    private function schemaRowFieldNames(): array
    {
        return $this->walkInSections
            ->filter(fn ($section) => ($section->section_type ?? '') === 'rows_section')
            ->flatMap(fn ($section) => $this->uniqueRowElementsForSection($section))
            ->map(fn ($element) => (string) ($element->name ?? ''))
            ->filter()
            ->values()
            ->all();
    }

    private function schemaRowCount(): int
    {
        $count = 0;
        foreach ($this->schemaRowFieldNames() as $name) {
            if (isset($this->formData[$name]) && is_array($this->formData[$name])) {
                $count = max($count, count($this->formData[$name]));
            }
        }

        return max(1, $count);
    }

    private function validateWalkInSchemaRows(?SubmissionFormSection $section = null): void
    {
        $sections = $section !== null
            ? collect([$section])
            : $this->walkInSections->filter(fn ($walkInSection) => ($walkInSection->section_type ?? '') === 'rows_section');

        $rowElements = $sections
            ->flatMap(fn (SubmissionFormSection $walkInSection) => $this->uniqueRowElementsForSection($walkInSection));

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

            foreach ($rowElements as $element) {
                if (! $element->is_required) {
                    continue;
                }

                $name = (string) ($element->name ?? '');
                if ($name === '') {
                    continue;
                }

                $key = 'formData.'.$name.'.'.$index;
                $rules[$key] = 'required';
                $messages[$key.'.required'] = ($element->label ?? $name).' is required for row '.($index + 1).'.';
            }

            if ($this->isFood && ! $this->rowHasFoodCategorySelection($index)) {
                $this->addError('formData.test_category.'.$index, 'Test category is required for row '.($index + 1).'.');
            }

            if ($this->isWater && ! $this->rowHasMultiOptionSelection($index, 'test_requirements')) {
                $this->addError('formData.test_requirements.'.$index, 'Test requirement is required for row '.($index + 1).'.');
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

        $analysisTypeId = trim((string) ($this->formData['analysis_type_id'][$index] ?? ''));
        if ($analysisTypeId !== '' && app(\App\Services\SubmissionForm\TrfDocumentCodeForSampleType::class)->isFoodSampleTypeLabel($analysisTypeId)) {
            return true;
        }

        foreach (['analysis_type', 'analysis_types'] as $key) {
            $analysisTypeName = trim((string) ($this->formData[$key][$index] ?? ''));
            if ($analysisTypeName !== '' && app(\App\Services\SubmissionForm\TrfDocumentCodeForSampleType::class)->isFoodSampleTypeLabel($analysisTypeName)) {
                return true;
            }
        }

        return false;
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
        $user = Auth::user();

        if (empty($this->formData['lab_received_datetime'])) {
            $this->formData['lab_received_datetime'] = now()->format('Y-m-d\TH:i');
        }

        if (empty($this->formData['lab_received_by']) && $user instanceof User) {
            $this->formData['lab_received_by'] = $user->name;
        }

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
        $customer->loadMissing('contacts');
        $contact = $customer->contacts->first();

        $contactName = '';
        $contactId = '';
        if ($contact) {
            $contactName = trim(implode(' ', array_filter([
                (string) ($contact->first_name ?? ''),
                (string) ($contact->middle_name ?? ''),
                (string) ($contact->last_name ?? ''),
            ])));
            $contactId = (string) $contact->id;
        }

        $canonicalName = trim((string) ($customer->name ?? ''));
        $address = (string) ($customer->physical_address ?? $customer->postal_address ?? '');
        $telFax = (string) ($customer->telephone1 ?? $customer->telephone2 ?? '');
        $mobile = (string) ($customer->telephone2 ?? $customer->telephone1 ?? '');
        $email = (string) ($customer->email ?? '');

        $prefill = [
            'customer_name' => $canonicalName,
            'customer_address' => $address,
            'customer_phone' => $telFax,
            'mobile_number' => $mobile,
            'contact_person' => $contactId !== '' ? $contactId : $contactName,
            'customer_email' => $email,
            'sampling_location' => '',
            'client_name' => $canonicalName,
            'customer' => $canonicalName,
            'client' => $canonicalName,
            'address' => $address,
            'physical_address' => (string) ($customer->physical_address ?? ''),
            'postal_address' => (string) ($customer->postal_address ?? ''),
            'phone' => $telFax,
            'telephone' => $telFax,
            'phone_number' => $telFax,
            'telephone_number' => $telFax,
            'tel_fax_no' => $telFax,
            'email' => $email,
            'email_address' => $email,
            'contact' => $contactName,
            'contact_name' => $contactName,
        ];

        foreach ($prefill as $key => $value) {
            if (! array_key_exists($key, $this->formData)) {
                continue;
            }

            if ($onlyEmpty && ! empty($this->formData[$key])) {
                continue;
            }

            if ($value !== '') {
                $this->formData[$key] = $value;
            }
        }

        if ($contact !== null) {
            $this->applyCustomerRepresentativeFromContact($contact);
        }
    }

    public function render()
    {
        return view('livewire.sampleworkflow.receive-sample-request', [
            'submissionForm' => $this->submissionForm,
            'walkInSections' => $this->walkInSections,
            'formTypeCards' => $this->pageMode ? $this->formTypeCards : collect(),
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
