<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\CRM\CRMCustomer;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormSection;
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

    // New properties for dynamic TestRequestForms
    public ?string $selectedSampleTypeId = null;

    public array $formData = [];

    public $sampleTypes = [];

    /** @var array<int, string> */
    public array $lastGeneratedSfiIds = [];

    public function mount(array $selectedFormInstanceIds = [], array $selectedFormSummaries = []): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($selectedFormInstanceIds));
        $this->selectedFormSummaries = $selectedFormSummaries;

        $this->sampleTypes = \App\SampleType::orderBy('name')->get();

        // Auto-load form data if viewing an existing request
        if (!empty($this->selectedFormInstanceIds)) {
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
            $this->initializeFormDataForSampleType($sampleType->id);
            $this->loadFormDataFromInstance($firstInstance);
        }
    }

    private function initializeFormDataForSampleType(string $sampleTypeId): void
    {
        $submissionForm = $this->resolveSubmissionFormForSampleType($sampleTypeId);
        if ($submissionForm === null) {
            $this->formData = [];

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

    private function defaultValueForElement(SubmissionFormElement $element): mixed
    {
        if ($element->element_type === 'checkbox') {
            $options = $element->options ?? [];

            if (($element->name ?? '') === 'test_requirements') {
                return '';
            }

            return is_array($options) && $options !== [] ? [] : false;
        }

        if ($element->element_type === 'analysis_elements_select' || ($element->name ?? '') === 'parameters') {
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

        foreach ($this->uniqueRowElementsForSection($section) as $element) {
            $name = (string) ($element->name ?? '');

            if ($name === 'sample_quantity') {
                $columns[] = [
                    'type' => 'qty_unit',
                    'label' => 'Qty / Unit',
                    'class' => 'walk-in-trf-col-qty',
                    'element' => $element,
                ];

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
        return stripos($st->name, 'Food') !== false || stripos($st->code, 'FOOD') !== false;
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
        $this->formData = [];
        if ($value) {
            $this->initializeFormDataForSampleType((string) $value);

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

    public function updatedFormDataCustomerName(?string $value): void
    {
        $this->prefillCustomerDetailsFromSelection($value);
    }

    public function updatedFormDataClientName(?string $value): void
    {
        $this->prefillCustomerDetailsFromSelection($value);
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
     * Receives data from the parent WorkflowBoard and shows the Bootstrap modal
     * once this component's state is fully updated in the same response cycle.
     *
     * @param  array<int, string>  $instanceIds
     * @param  array<int, array{id: string, label: string, customer: string}>  $summaries
     */
    public function handleReceiveModalOpen(array $instanceIds, array $summaries): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($instanceIds));
        $this->selectedFormSummaries = $summaries;
        $this->remarks = '';
        $this->checkInTrfFields = [];
        $this->selectedSampleTypeId = null;
        $this->formData = [];
        $this->resetValidation();
        $this->refreshCheckInContexts();

        // Dispatched after state is set — JS listener shows the modal.
        $this->dispatch(
            'show-receive-sample-modal',
            physicalCheckIn: $this->isPhysicalCheckIn,
        );
    }

    protected function getListeners(): array
    {
        return [
            'receive-modal-open' => 'handleReceiveModalOpen',
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
            $this->addError('selection', 'You must be signed in to receive samples.');

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
            ? '1 request checked in at reception.'
            : "{$processed} requests checked in at reception.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        session()->flash('success', $message);
        $this->dispatch('receive-completed');
        $this->dispatch('hide-receive-sample-modal');
    }

    private function confirmWalkInCapture(): void
    {
        $this->validate([
            'selectedSampleTypeId' => 'required|exists:sample_types,id',
        ], [
            'selectedSampleTypeId.required' => 'Please select a Sample Type.',
        ]);

        $submissionForm = $this->submissionForm;
        if ($submissionForm === null) {
            $this->addError('selectedSampleTypeId', 'No active Test Request Form template found for the selected sample type.');

            return;
        }

        $rules = [];
        $messages = [];

        $hiddenWalkInFields = ['job_number', 'crm_contact_id'];
        $seenElements = [];
        foreach ($this->walkInSections as $section) {
            if (($section->section_type ?? '') === 'rows_section') {
                continue;
            }

            foreach ($section->elementHolders->flatMap->elements as $element) {
                $elementName = (string) ($element->name ?? '');
                if ($elementName === '' || isset($seenElements[$elementName])) {
                    continue;
                }

                if (in_array($elementName, $hiddenWalkInFields, true)) {
                    continue;
                }

                $seenElements[$elementName] = true;

                if (! $element->is_required) {
                    continue;
                }

                $key = 'formData.'.$elementName;
                $rules[$key] = 'required';
                $messages[$key.'.required'] = ($element->label ?? $elementName).' is required.';
            }
        }

        if ($rules !== []) {
            $this->validate($rules, $messages);
        }

        $this->validateWalkInSchemaRows();
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->prepareLabUseFields();

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to receive samples.');

            return;
        }

        $crmCustomerId = null;
        foreach (['customer_name', 'client_name', 'customer', 'client'] as $key) {
            if (! empty($this->formData[$key])) {
                $custName = $this->formData[$key];
                $crmCustomerId = CRMCustomer::query()
                    ->whereRaw('name ILIKE ?', [trim((string) $custName)])
                    ->value('id');
                break;
            }
        }

        $payload = $this->walkInSubmissionPayload();

        try {
            $instance = app(SubmissionFormSubmissionService::class)->submitWalkInInstance(
                $submissionForm,
                $payload,
                $crmCustomerId !== null ? (string) $crmCustomerId : null,
                (string) $this->selectedSampleTypeId,
            );
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('selection', 'Could not submit walk-in request. '.$exception->getMessage());

            return;
        }

        $this->lastGeneratedSfiIds = [$instance->id];

        session()->flash('success', 'Walk-in test request submitted successfully.');
        $this->dispatch('receive-completed', sfiIds: $this->lastGeneratedSfiIds);
        $this->dispatch('hide-receive-sample-modal');
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

    private function validateWalkInSchemaRows(): void
    {
        $rowElements = $this->walkInSections
            ->filter(fn ($section) => ($section->section_type ?? '') === 'rows_section')
            ->flatMap(fn ($section) => $this->uniqueRowElementsForSection($section));

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

            if ($this->isFood) {
                $rules['formData.test_category.'.$index] = 'required';
                $messages['formData.test_category.'.$index.'.required'] = 'Test category is required for row '.($index + 1).'.';
            }

            if ($this->isWater) {
                $rules['formData.test_requirements.'.$index] = 'required';
                $messages['formData.test_requirements.'.$index.'.required'] = 'Test requirement is required for row '.($index + 1).'.';
            }
        }

        if (! $hasFilledRow) {
            $this->addError('formData.sample_description.0', 'Add at least one sample row in Test & sample information.');

            return;
        }

        if ($rules !== []) {
            $this->validate($rules, $messages);
        }
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
    }

    private function prefillCustomerDetailsFromSelection(?string $customerName): void
    {
        if ($customerName === null || trim($customerName) === '') {
            return;
        }

        $customer = CRMCustomer::query()
            ->with('contacts')
            ->where('name', $customerName)
            ->first();

        if ($customer) {
            $this->applyCustomerPrefillFromCrm($customer, onlyEmpty: false);
        }
    }

    private function applyCustomerPrefillFromCrm(CRMCustomer $customer, bool $onlyEmpty = false): void
    {
        $customer->loadMissing('contacts');
        $contact = $customer->contacts->first();

        $contactName = '';
        if ($contact) {
            $contactName = trim(implode(' ', array_filter([
                (string) ($contact->first_name ?? ''),
                (string) ($contact->middle_name ?? ''),
                (string) ($contact->last_name ?? ''),
            ])));
        }

        $address = (string) ($customer->physical_address ?? $customer->postal_address ?? '');
        $telFax = (string) ($customer->telephone1 ?? $customer->telephone2 ?? '');
        $mobile = (string) ($contact?->mobile ?? $contact?->telephone ?? $customer->telephone2 ?? $customer->telephone1 ?? '');

        $prefill = [
            'customer_name' => (string) ($customer->name ?? ''),
            'customer_address' => $address,
            'customer_phone' => $telFax,
            'mobile_number' => $mobile,
            'contact_person' => $contactName,
            'client_name' => (string) ($customer->name ?? ''),
            'customer' => (string) ($customer->name ?? ''),
            'client' => (string) ($customer->name ?? ''),
            'address' => $address,
            'physical_address' => (string) ($customer->physical_address ?? ''),
            'postal_address' => (string) ($customer->postal_address ?? ''),
            'phone' => $telFax,
            'telephone' => $telFax,
            'phone_number' => $telFax,
            'telephone_number' => $telFax,
            'tel_fax_no' => $telFax,
            'email' => (string) ($customer->email ?? ''),
            'email_address' => (string) ($customer->email ?? ''),
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
    }

    public function render()
    {
        return view('livewire.sampleworkflow.receive-sample-request', [
            'submissionForm' => $this->submissionForm,
            'walkInSections' => $this->walkInSections,
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

            $this->checkInTrfFields[$instanceId] = $metadataService->hydrateFromFormData($formData);
        }
    }

}
