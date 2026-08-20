<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmissionFormSubmissionService
{
    private ?bool $hasSelectedSampleTypeColumn = null;

    public function mergeSubmissionFieldsIntoRequest(Request $request): void
    {
        $fields = $request->input('fields');

        if (! is_array($fields)) {
            return;
        }

        $request->merge($this->normalizeSubmissionFields($fields));
    }

    /**
     * Normalize portal submission payloads into a request-friendly field map.
     *
     * Supported formats:
     * - fields: { name: value }
     * - fields: [ { name: string, value: mixed }, ... ]
     * - fields: { name: { value: mixed } }
     *
     * @param  array<int|string, mixed>  $fields
     * @return array<string, mixed>
     */
    private function normalizeSubmissionFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $key => $value) {
            if (is_int($key) && is_array($value) && isset($value['name'])) {
                $normalized[(string) $value['name']] = $value['value'] ?? null;
                continue;
            }

            if (is_array($value) && count($value) === 1 && array_key_exists('value', $value)) {
                $normalized[$key] = $value['value'];
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function buildValidationRules(Collection $elements, Request $request, bool $requireRequired = true): array
    {
        $rules = [];
        $formData = $request->all();

        foreach ($elements as $element) {
            $fieldName = $element->name;
            $elementRules = [];

            if (SubmissionFormSchemaHelper::isBuilderHiddenElement($element)) {
                $rules[$fieldName] = ['nullable'];
                continue;
            }

            if ($this->isElementVisibleInRequest($element, $elements, $formData)) {
                if ($requireRequired && $element->is_required) {
                    $elementRules[] = 'required';
                } else {
                    $elementRules[] = 'nullable';
                }
            } else {
                $elementRules[] = 'nullable';
            }

            switch ($element->element_type) {
                case 'email':
                    $elementRules[] = 'email';
                    break;
                case 'number':
                    $elementRules[] = 'numeric';
                    break;
                case 'date':
                case 'datetime':
                    $elementRules[] = 'date';
                    break;
                case 'file':
                    $elementRules[] = 'file';
                    break;
                case 'camera_photo':
                case 'image_upload':
                    $elementRules[] = 'image';
                    $elementRules[] = 'mimes:jpg,jpeg,png,webp';
                    break;
                case 'sample_point_select':
                case 'analysis_elements_select':
                    if ($this->isMultipleSelectField($element, $request)) {
                        $elementRules[] = 'array';
                    }
                    break;
                case 'user_select':
                    $elementRules[] = 'integer';
                    $elementRules[] = 'exists:users,id';
                    break;
                case 'zone_select':
                    $elementRules[] = 'string';
                    $elementRules[] = 'exists:zones,id';
                    break;
            }

            if ($element->validation_rules) {
                $elementRules = array_merge($elementRules, $element->validation_rules);
            }

            if ($request->has($fieldName) && is_array($request->input($fieldName))) {
                if ($this->isMultipleSelectField($element, $request)) {
                    $rules[$fieldName] = $elementRules;
                    $rules[$fieldName.'.*'] = ['array'];
                } else {
                    $rules[$fieldName.'.*'] = $elementRules;
                }
            } else {
                $rules[$fieldName] = $elementRules;
            }
        }

        return $rules;
    }

    public function processFormData(
        SubmissionFormInstance $instance,
        Request $request,
        Collection $elements
    ): void {
        foreach ($elements as $element) {
            $fieldName = $element->name;
            $inputValue = $request->input($fieldName);
            $fileValue = $request->file($fieldName);

            if (in_array((string) $fieldName, ['test_requirements', 'test_category', 'sample_condition'], true) && is_array($inputValue)) {
                $this->processArrayField(
                    $instance,
                    $element,
                    $this->flattenPerRowMultiSelectValues($inputValue),
                    $request,
                    $fieldName,
                );

                continue;
            }

            // Per-row multi-select: parameters[0] = [a,b], parameters[1] = [c]
            // must stay row-indexed — never expand each token into its own array_index.
            $isPerRowMultiSelect = is_array($inputValue) && $this->isPerRowMultiSelectValues($inputValue);
            $isMultipleSelect = $this->isMultipleSelectField($element, $request)
                && ! $isPerRowMultiSelect
                && ! $this->isIndexedRowFieldValues(is_array($inputValue) ? $inputValue : []);

            $isArray = is_array($inputValue) || is_array($fileValue);

            if ($isArray) {
                if ($isPerRowMultiSelect || ($isArray && ! $isMultipleSelect && is_array($inputValue) && $this->isIndexedRowFieldValues($inputValue))) {
                    // Row-indexed values (possibly nested multi-selects per row).
                    $this->processArrayField(
                        $instance,
                        $element,
                        $this->flattenPerRowMultiSelectValues($inputValue ?? []),
                        $request,
                        $fieldName,
                    );
                } elseif ($isMultipleSelect) {
                    $this->processMultipleSelectField($instance, $element, $inputValue ?? []);
                } else {
                    $this->processArrayField($instance, $element, $inputValue ?? [], $request, $fieldName);
                }
            } else {
                if (in_array((string) $fieldName, ['test_requirements', 'test_category', 'sample_condition'], true)
                    && is_string($inputValue)) {
                    if ($fieldName === 'sample_condition') {
                        $tokens = \App\Services\Sampleworkflow\TestRequestFormReportDataBuilder::sampleConditionTokens($inputValue);
                    } else {
                        $tokens = SubmissionFormSchemaHelper::testCategoryTokens($inputValue);
                    }

                    if ($tokens !== []) {
                        $this->saveFieldValue($instance, $element, implode(',', $tokens), null, 0);

                        continue;
                    }
                }

                $this->processSingleField($instance, $element, $inputValue, $request);
            }
        }

        $this->persistCanonicalQtyFields($instance, $request, $elements);
        $this->persistIndexedCheckboxRowFields($instance, $request, $elements);
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function isPerRowMultiSelectValues(array $values): bool
    {
        if ($values === []) {
            return false;
        }

        foreach ($values as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int|string, mixed>  $values
     * @return array<int, mixed>
     */
    private function flattenPerRowMultiSelectValues(array $values): array
    {
        $out = [];
        foreach ($values as $index => $value) {
            if (is_array($value)) {
                $selectedKeys = SubmissionFormSchemaHelper::selectedCheckboxKeys($value);

                if ($selectedKeys !== null) {
                    $out[(int) $index] = implode(',', $selectedKeys);

                    continue;
                }

                $categoryTokens = SubmissionFormSchemaHelper::testCategoryTokens($value);
                if ($categoryTokens !== []) {
                    $out[(int) $index] = implode(',', $categoryTokens);

                    continue;
                }

                $tokens = [];
                foreach ($value as $item) {
                    if ($item === null || $item === '') {
                        continue;
                    }
                    $tokens[] = (string) $item;
                }
                $out[(int) $index] = implode(',', $tokens);
            } else {
                $out[(int) $index] = $value;
            }
        }
        ksort($out);

        return $out;
    }

    /**
     * Persist walk-in Qty/Unit even when the form only has legacy number_of_samples.
     *
     * @param  Collection<int, SubmissionFormElement>  $elements
     */
    private function persistCanonicalQtyFields(
        SubmissionFormInstance $instance,
        Request $request,
        Collection $elements,
    ): void {
        $qtyValues = $request->input('sample_quantity');
        $unitValues = $request->input('sample_quantity_unit');

        if (! is_array($qtyValues) && ! is_array($unitValues)) {
            return;
        }

        $byName = $elements->keyBy(fn (SubmissionFormElement $el): string => (string) $el->name);
        $qtyElement = $byName->get('sample_quantity') ?? $byName->get('number_of_samples');
        $unitElement = $byName->get('sample_quantity_unit');

        if ($qtyElement instanceof SubmissionFormElement && is_array($qtyValues)) {
            $this->processArrayField($instance, $qtyElement, $qtyValues);
        }

        if ($unitElement instanceof SubmissionFormElement && is_array($unitValues)) {
            $this->processArrayField($instance, $unitElement, $unitValues);
        }
    }

    /**
     * Checkbox groups on sample cards (e.g. water test_requirements) must stay
     * row-indexed. The generic array walker can skip them when Livewire posts
     * nested option maps that flatten to an empty string.
     *
     * @param  Collection<int, SubmissionFormElement>  $elements
     */
    private function persistIndexedCheckboxRowFields(
        SubmissionFormInstance $instance,
        Request $request,
        Collection $elements,
    ): void {
        $byName = $elements->keyBy(fn (SubmissionFormElement $el): string => (string) $el->name);
        $sampleRows = $request->input('sample_rows');
        $sampleRows = is_array($sampleRows) ? array_values($sampleRows) : [];

        foreach (['test_requirements', 'test_category', 'sample_condition'] as $fieldName) {
            $element = $byName->get($fieldName);
            if (! $element instanceof SubmissionFormElement) {
                continue;
            }

            $indexed = $request->input($fieldName);
            if (! is_array($indexed)) {
                $indexed = [];
            }

            foreach ($sampleRows as $rowIndex => $row) {
                if (! is_array($row) || ! array_key_exists($fieldName, $row)) {
                    continue;
                }

                if ($this->checkboxRowHasSelection($indexed[$rowIndex] ?? null, $fieldName)) {
                    continue;
                }

                $indexed[$rowIndex] = $row[$fieldName];
            }

            $flattened = $this->flattenPerRowMultiSelectValues($indexed);

            foreach ($flattened as $rowIndex => $value) {
                $tokens = $fieldName === 'sample_condition'
                    ? \App\Services\Sampleworkflow\TestRequestFormReportDataBuilder::sampleConditionTokens($value)
                    : SubmissionFormSchemaHelper::testCategoryTokens($value);
                if ($tokens === []) {
                    continue;
                }

                $this->saveFieldValue(
                    $instance,
                    $element,
                    implode(',', $tokens),
                    null,
                    (int) $rowIndex,
                );
            }
        }
    }

    private function checkboxRowHasSelection(mixed $value, string $fieldName = ''): bool
    {
        if ($fieldName === 'sample_condition') {
            return \App\Services\Sampleworkflow\TestRequestFormReportDataBuilder::sampleConditionTokens($value) !== [];
        }

        return SubmissionFormSchemaHelper::testCategoryTokens($value) !== [];
    }

    public function submitPortalInstance(
        SubmissionFormInstance $instance,
        SubmissionForm $submissionForm
    ): SubmissionFormInstance {
        if ($instance->status !== 'draft') {
            throw ValidationException::withMessages([
                'action' => ['Only draft instances can be submitted.'],
            ]);
        }

        if (empty($instance->form_number)) {
            $this->assignFormNumberWithRetry($instance, $submissionForm);
            $instance->refresh();
        }

        $instance->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->syncCommercialPipelineIfApplicable($instance, $submissionForm);

        $labIntakeCaseServiceClass = 'App\\Services\\LabIntakeCaseService';

        if (class_exists($labIntakeCaseServiceClass)) {
            try {
                app($labIntakeCaseServiceClass)->syncFromSubmission($instance->fresh(), null);
            } catch (\Throwable $th) {
                Log::warning('Lab intake case sync failed after portal form submit.', [
                    'instance_id' => $instance->id,
                    'message' => $th->getMessage(),
                ]);
            }
        }

        return $instance->fresh(['submissionForm', 'values.element']);
    }

    /**
     * Walk-in capture from Samples Receiving modal.
     *
     * @param  array<string, mixed>  $fieldValues
     */
    public function submitWalkInInstance(
        SubmissionForm $submissionForm,
        array $fieldValues,
        ?string $crmCustomerId = null,
        ?string $sampleTypeId = null,
        string $sourceChannel = CommercialEnquirySyncService::SOURCE_WALK_IN,
        ?string $samplingScheduleId = null,
    ): SubmissionFormInstance {
        $instance = DB::transaction(function () use ($submissionForm, $fieldValues, $crmCustomerId, $sampleTypeId, $sourceChannel, $samplingScheduleId): SubmissionFormInstance {
            if ($sourceChannel === CommercialEnquirySyncService::SOURCE_SCHEDULED) {
                $samplingScheduleId = trim((string) ($samplingScheduleId ?? ''));
                if ($samplingScheduleId === '') {
                    throw new \InvalidArgumentException(
                        'Scheduled sampling forms must be linked to a sampling schedule.'
                    );
                }
            }

            $userId = Auth::id();

            $instanceData = [
                'submission_form_id' => $submissionForm->id,
                'title' => 'Test Request Form - '.now()->format('Y-m-d H:i'),
                'submitted_by' => $userId,
                'status' => 'submitted',
                'submitted_at' => now(),
                'priority' => 'normal',
                'crm_customer_id' => $crmCustomerId,
                'source_channel' => $sourceChannel,
                'sampling_schedule_id' => $samplingScheduleId !== null && $samplingScheduleId !== ''
                    ? $samplingScheduleId
                    : null,
            ];

            if ($this->supportsSelectedSampleTypeColumn()) {
                $instanceData['selected_sample_type_id'] = $sampleTypeId !== null && $sampleTypeId !== ''
                    ? $sampleTypeId
                    : null;
            }

            $instance = SubmissionFormInstance::query()->create($instanceData);

            $this->unhideRowElementsPresentInPayload($submissionForm, $fieldValues);
            $elements = $this->elementsForWalkInSubmission($submissionForm, $fieldValues);
            $request = new Request();
            $request->merge($fieldValues);
            $this->processFormData($instance, $request, $elements);

            $this->assignFormNumberWithRetry($instance, $submissionForm);
            $instance->refresh();

            $this->syncCommercialEnquiryAfterSubmit(
                $instance->fresh(['values.element', 'submissionForm', 'crmCustomer'])
            );

            return $instance->fresh(['submissionForm', 'values.element']);
        });

        $this->runPostSubmitDocumentSideEffects($instance);
        $this->runPostSubmitLabIntakeSync($instance);

        return $instance->fresh(['submissionForm', 'values.element']) ?? $instance;
    }

    private function supportsSelectedSampleTypeColumn(): bool
    {
        if ($this->hasSelectedSampleTypeColumn === null) {
            $this->hasSelectedSampleTypeColumn = Schema::hasColumn('submission_form_instances', 'selected_sample_type_id');
        }

        return $this->hasSelectedSampleTypeColumn;
    }

    private function syncCommercialPipelineIfApplicable(
        SubmissionFormInstance $instance,
        SubmissionForm $submissionForm,
    ): void {
        unset($submissionForm);

        $this->syncCommercialEnquiryAfterSubmit(
            $instance->fresh(['values.element', 'submissionForm', 'crmCustomer'])
        );
        $this->runPostSubmitDocumentSideEffects($instance);
    }

    private function syncCommercialEnquiryAfterSubmit(SubmissionFormInstance $instance): void
    {
        $syncService = app(CommercialEnquirySyncService::class);
        if (! $syncService->isCommercialTestRequestForm($instance)) {
            return;
        }

        $sourceChannel = strtolower(trim((string) ($instance->source_channel ?? '')));
        $required = CommercialEnquirySyncService::isOfflineChannel($sourceChannel)
            || $sourceChannel === CommercialEnquirySyncService::SOURCE_SCHEDULED;

        if ($required) {
            $syncService->syncFromSubmittedInstance($instance);

            return;
        }

        try {
            DB::transaction(function () use ($syncService, $instance): void {
                $syncService->syncFromSubmittedInstance($instance);
            });
        } catch (\Throwable $exception) {
            Log::warning('Commercial enquiry sync failed after submission form submit.', [
                'instance_id' => $instance->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function runPostSubmitDocumentSideEffects(SubmissionFormInstance $instance): void
    {
        try {
            app(TestRequestFormPdfService::class)->generateAndStore(
                $instance->fresh(['values.element', 'submissionForm'])
            );
        } catch (\Throwable $exception) {
            Log::warning('Test Request Form PDF generation failed after submission.', [
                'instance_id' => $instance->id,
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            app(SubmissionFormInstanceDocumentAttachmentService::class)->attachTestRequestForm(
                $instance->fresh(['values.element', 'submissionForm']),
                auth()->id(),
                regenerate: false,
            );
        } catch (\Throwable $exception) {
            Log::warning('Test Request Form attachment sync failed after submission.', [
                'instance_id' => $instance->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function runPostSubmitLabIntakeSync(SubmissionFormInstance $instance): void
    {
        $labIntakeCaseServiceClass = 'App\\Services\\LabIntakeCaseService';
        if (! class_exists($labIntakeCaseServiceClass)) {
            return;
        }

        try {
            app($labIntakeCaseServiceClass)->syncFromSubmission($instance->fresh(), null);
        } catch (\Throwable $th) {
            Log::warning('Lab intake case sync failed after walk-in form submit.', [
                'instance_id' => $instance->id,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function assignFormNumberWithRetry(
        SubmissionFormInstance $instance,
        SubmissionForm $submissionForm
    ): void {
        $maxRetries = 5;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            $retryCount++;
            $savepoint = 'assign_form_number_' . $retryCount . '_' . uniqid();

            try {
                // Manually create a PostgreSQL savepoint to isolate this attempt
                DB::statement("SAVEPOINT {$savepoint}");

                $formNumber = \App\Services\FormNumberGenerator::generate($submissionForm);

                SubmissionFormInstance::withoutAuditing(function () use ($instance, $formNumber): void {
                    $instance->update([
                        'form_number' => $formNumber['format'],
                        'sequence_number' => $formNumber['sequence_no'],
                    ]);
                });

                // Success - release the savepoint
                DB::statement("RELEASE SAVEPOINT {$savepoint}");

                return;
            } catch (\Illuminate\Database\QueryException $e) {
                // If a constraint violation (or any error) occurs, rollback to the savepoint
                // This clears the PostgreSQL aborted state (25P02), allowing the transaction to continue
                DB::statement("ROLLBACK TO SAVEPOINT {$savepoint}");

                // In Laravel 10+, this might be a UniqueConstraintViolationException which extends QueryException.
                $isDuplicate = in_array((string) $e->getCode(), ['23000', '23505'], true);

                if ($isDuplicate) {
                    if ($retryCount >= $maxRetries) {
                        throw new \RuntimeException('Failed to assign form number after maximum retries', 0, $e);
                    }

                    usleep(100000); // 100ms
                    continue;
                }

                throw $e;
            }
        }
    }

    /**
     * @return Collection<int, SubmissionFormElement>
     */
    public function elementsForForm(SubmissionForm $submissionForm): Collection
    {
        return SubmissionFormElement::query()
            ->with('holder.section')
            ->whereHas('holder.section', function ($query) use ($submissionForm): void {
                $query->where('submission_form_id', $submissionForm->id)
                    ->where(function ($sectionQuery): void {
                        $sectionQuery->where('is_hidden', false)->orWhereNull('is_hidden');
                    });
            })
            ->where(function ($elementQuery): void {
                $elementQuery->where('is_hidden', false)->orWhereNull('is_hidden');
            })
            ->get();
    }

    /**
     * Walk-in payloads may include builder-hidden row fields (e.g. water test_requirements).
     *
     * @param  array<string, mixed>  $fieldValues
     * @return Collection<int, SubmissionFormElement>
     */
    private function elementsForWalkInSubmission(SubmissionForm $submissionForm, array $fieldValues): Collection
    {
        $elements = $this->elementsForForm($submissionForm);
        $knownNames = $elements
            ->map(static fn (SubmissionFormElement $element): string => (string) ($element->name ?? ''))
            ->filter()
            ->all();

        $missingNames = [];
        foreach ($fieldValues as $name => $value) {
            if (! is_string($name) || $name === '' || in_array($name, $knownNames, true)) {
                continue;
            }

            if (! is_array($value) || ! $this->isIndexedRowPayload($value)) {
                continue;
            }

            $missingNames[] = $name;
        }

        if ($missingNames === []) {
            return $elements;
        }

        $extra = SubmissionFormElement::query()
            ->with('holder.section')
            ->whereHas('holder.section', function ($query) use ($submissionForm): void {
                $query->where('submission_form_id', $submissionForm->id);
            })
            ->whereIn('name', $missingNames)
            ->get();

        return $elements->merge($extra)->unique('id')->values();
    }

    /**
     * @param  array<string, mixed>  $fieldValues
     */
    private function unhideRowElementsPresentInPayload(SubmissionForm $submissionForm, array $fieldValues): void
    {
        $names = [];
        foreach ($fieldValues as $name => $value) {
            if (! is_string($name) || $name === '' || ! is_array($value) || ! $this->rowPayloadHasContent($value)) {
                continue;
            }

            $names[] = $name;
        }

        if ($names === []) {
            return;
        }

        SubmissionFormElement::query()
            ->whereHas('holder.section', function ($query) use ($submissionForm): void {
                $query->where('submission_form_id', $submissionForm->id);
            })
            ->whereIn('name', $names)
            ->where(function ($query): void {
                $query->where('is_hidden', true)->orWhere('is_hidden', 1);
            })
            ->update(['is_hidden' => false]);
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function isIndexedRowPayload(array $values): bool
    {
        if ($values === []) {
            return false;
        }

        return array_keys($values) === array_keys(array_values($values));
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function rowPayloadHasContent(array $values): bool
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                foreach ($value as $selected) {
                    if (filter_var($selected, FILTER_VALIDATE_BOOLEAN)) {
                        return true;
                    }

                    if (! is_bool($selected) && trim((string) $selected) !== '') {
                        return true;
                    }
                }

                continue;
            }

            if (trim((string) ($value ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function processSingleField(
        SubmissionFormInstance $instance,
        SubmissionFormElement $element,
        mixed $value,
        Request $request
    ): void {
        if (($value === null || $value === '') && $element->element_type === 'user_select' && Auth::check()) {
            $value = Auth::id();
        }

        if (in_array($element->element_type, ['file', 'camera_photo', 'image_upload'], true) && $request->hasFile($element->name)) {
            $file = $request->file($element->name);
            $filename = time().'_'.Str::slug($element->name).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('submission-forms/'.$instance->id, $filename, 'public');

            $this->saveFieldValue($instance, $element, null, $path);
        } else {
            $this->saveFieldValue($instance, $element, $value);
        }
    }

    private function processArrayField(
        SubmissionFormInstance $instance,
        SubmissionFormElement $element,
        array $values,
        Request $request = null,
        string $fieldName = null
    ): void {
        $inputs = $values;

        // Some checkbox/select controls post associative maps like
        // ['option_a' => true, 'option_b' => false]. Convert those into
        // a sequential list of selected option keys so array_index remains numeric.
        if ($this->isAssociativeSelectionMap($inputs)) {
            $inputs = array_values(array_map(
                static fn ($key): string => (string) $key,
                array_keys(array_filter($inputs, static fn ($selected): bool => (bool) $selected))
            ));
        }

        $files = [];

        if ($request && $fieldName) {
            $files = $request->file($fieldName) ?? [];
        }

        $indices = array_unique(array_merge(array_keys($inputs), array_keys($files)));
        sort($indices);

        // Fetch existing values for this element to preserve already-uploaded files
        $existingValues = $instance->values()
            ->where('submission_form_element_id', $element->id)
            ->get()
            ->keyBy('array_index');

        SubmissionFormInstanceValue::withoutAuditing(function () use ($instance, $element): void {
            $instance->values()->where('submission_form_element_id', $element->id)->delete();
        });

        foreach ($indices as $index) {
            if (! is_numeric($index)) {
                continue;
            }

            $arrayIndex = (int) $index;
            $inputValue = $inputs[$index] ?? null;
            $fileValue = $files[$index] ?? null;

            $filePath = null;
            $saveValue = $inputValue;

            if (in_array($element->element_type, ['file', 'camera_photo', 'image_upload'], true)) {
                if ($fileValue instanceof \Illuminate\Http\UploadedFile) {
                    // Save new file upload
                    $filename = time() . '_' . $index . '_' . Str::slug($element->name) . '.' . $fileValue->getClientOriginalExtension();
                    $filePath = $fileValue->storeAs('submission-forms/' . $instance->id, $filename, 'public');
                    $saveValue = null;
                } else {
                    // Keep existing file if it was already uploaded and no new file was uploaded
                    $existing = $existingValues->get($index);
                    if ($existing && $existing->file_path) {
                        $filePath = $existing->file_path;
                        $saveValue = null;
                    }
                }
            }

            if (($saveValue === null || $saveValue === '') && $element->element_type === 'user_select' && Auth::check()) {
                $saveValue = Auth::id();
            }

            if (! $this->shouldPersistFieldValue($saveValue, $filePath)) {
                continue;
            }

            $this->saveFieldValue($instance, $element, $saveValue, $filePath, $arrayIndex);
        }
    }

    private function shouldPersistFieldValue(mixed $saveValue, ?string $filePath): bool
    {
        if ($filePath !== null) {
            return true;
        }

        if ($saveValue === null || $saveValue === '') {
            return false;
        }

        if (is_array($saveValue) && $saveValue === []) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function isAssociativeSelectionMap(array $values): bool
    {
        if ($values === []) {
            return false;
        }

        $hasStringKey = false;
        foreach ($values as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $hasStringKey = true;

            if (is_array($value)) {
                return false;
            }
        }

        return $hasStringKey;
    }

    private function isMultipleSelectField(SubmissionFormElement $element, Request $request): bool
    {
        $multipleSelectTypes = ['sample_point_select', 'analysis_type_select', 'analysis_elements_select'];

        if (in_array($element->element_type, $multipleSelectTypes, true)) {
            return true;
        }

        if (is_array($element->options) && ($element->options['multiple'] ?? false) === true) {
            return true;
        }

        return false;
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function isIndexedRowFieldValues(array $values): bool
    {
        if ($values === []) {
            return false;
        }

        // Nested multi-select per sample card: [0 => ['a','b'], 1 => ['c']]
        foreach ($values as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return array_keys($values) === array_keys(array_values($values));
    }

    private function processMultipleSelectField(
        SubmissionFormInstance $instance,
        SubmissionFormElement $element,
        array $values
    ): void
    {
        $convertToArray = [];

        foreach ($values as $value) {
            $convertToArray[] = is_array($value) ? implode(',', $value) : (string) $value;
        }

        $this->processArrayField($instance, $element, $convertToArray);
    }

    private function saveFieldValue(
        SubmissionFormInstance $instance,
        SubmissionFormElement $element,
        mixed $value,
        ?string $filePath = null,
        ?int $arrayIndex = null
    ): void {
        $storedValue = $this->serializeValueForStorage($value);

        SubmissionFormInstanceValue::withoutAuditing(function () use ($instance, $element, $storedValue, $filePath, $arrayIndex): void {
            SubmissionFormInstanceValue::updateOrCreate(
                [
                    'submission_form_instance_id' => $instance->id,
                    'submission_form_element_id' => $element->id,
                    'array_index' => $arrayIndex,
                ],
                [
                    'value' => $storedValue,
                    'file_path' => $filePath,
                ]
            );
        });
    }

    private function serializeValueForStorage(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            if ($this->isAssociativeSelectionMap($value)) {
                $selected = [];
                foreach ($value as $key => $selectedFlag) {
                    if ((bool) $selectedFlag) {
                        $selected[] = (string) $key;
                    }
                }

                return $selected === [] ? null : implode(',', $selected);
            }

            $flat = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    foreach ($item as $nestedKey => $nested) {
                        if (is_string($nestedKey) && is_bool($nested)) {
                            if ($nested) {
                                $flat[] = (string) $nestedKey;
                            }

                            continue;
                        }

                        if ($nested !== null && $nested !== '') {
                            $flat[] = (string) $nested;
                        }
                    }

                    continue;
                }

                if ($item !== null && $item !== '') {
                    $flat[] = (string) $item;
                }
            }

            $flat = array_values(array_filter(array_map('trim', $flat), fn (string $token): bool => $token !== ''));

            return $flat === [] ? null : implode(',', $flat);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function isElementVisibleInRequest(
        SubmissionFormElement $element,
        Collection $allElements,
        array $formData
    ): bool {
        $logic = $element->conditional_logic;
        if (empty($logic)) {
            return true;
        }

        // Normalize logic into a list of conditions
        $conditions = [];
        if (isset($logic['operator']) || isset($logic['field_id']) || isset($logic['depends_on']) || isset($logic['field'])) {
            $conditions[] = $logic;
        } elseif (is_array($logic)) {
            if (is_numeric(key($logic))) {
                $conditions = $logic;
            } else {
                $conditions[] = $logic;
            }
        }

        if (empty($conditions)) {
            return true;
        }

        $elementsById = $allElements->keyBy(fn ($e) => (string) $e->id);

        foreach ($conditions as $condition) {
            if (!is_array($condition)) {
                continue;
            }

            $parentName = $condition['depends_on'] ?? $condition['field'] ?? null;
            if (empty($parentName) && !empty($condition['field_id'])) {
                $parentEl = $elementsById->get((string) $condition['field_id']);
                if ($parentEl) {
                    $parentName = $parentEl->name;
                }
            }

            if (empty($parentName)) {
                continue;
            }

            $actual = $formData[$parentName] ?? '';
            $op = $condition['operator'] ?? 'equals';
            $expected = $condition['value'] ?? '';

            if (! $this->conditionMatches($actual, $op, $expected)) {
                return false;
            }
        }

        return true;
    }

    private function conditionMatches(mixed $actual, string $operator, mixed $expected): bool
    {
        $actualValues = is_array($actual) ? Arr::flatten($actual) : [$actual];
        $actualValues = array_map(
            static fn (mixed $value): string => (string) ($value ?? ''),
            $actualValues
        );
        $expectedValue = (string) ($expected ?? '');

        return match ($operator) {
            '==', 'equals' => in_array($expectedValue, $actualValues, true),
            '!=', 'not_equals' => ! in_array($expectedValue, $actualValues, true),
            'not_empty' => collect($actualValues)->contains(
                static fn (string $value): bool => $value !== ''
            ),
            'empty' => collect($actualValues)->every(
                static fn (string $value): bool => $value === ''
            ),
            'contains' => collect($actualValues)->contains(
                static fn (string $value): bool => str_contains($value, $expectedValue)
            ),
            default => true,
        };
    }
}

