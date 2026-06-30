<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmissionFormSubmissionService
{
    /**
     * @return list<string>
     */
    public static function collectionExtraFieldNames(): array
    {
        return array_column(config('test_request_form_fields.collection_extra_fields', []), 'name');
    }

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
    public function buildValidationRules(Collection $elements, Request $request): array
    {
        $rules = [];
        $formData = $request->all();

        foreach ($elements as $element) {
            $fieldName = $element->name;
            $elementRules = [];

            if ($this->isElementVisibleInRequest($element, $elements, $formData)) {
                if ($element->is_required) {
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
                    $rules[$fieldName.'.*'] = ['nullable', 'string'];
                } else {
                    $rules[$fieldName.'.*'] = $elementRules;
                }
            } else {
                $rules[$fieldName] = $elementRules;
            }
        }

        $rules = $this->applyTrfCollectionExtrasCompletenessRules($rules, $request);

        return $rules;
    }

    /**
     * @param  array<string, array<int, mixed>|string>  $rules
     * @return array<string, array<int, mixed>|string>
     */
    private function applyTrfCollectionExtrasCompletenessRules(array $rules, Request $request): array
    {
        $fieldsInSchema = array_values(array_filter(
            self::collectionExtraFieldNames(),
            static fn (string $field): bool => array_key_exists($field, $rules)
        ));

        if ($fieldsInSchema === []) {
            return $rules;
        }

        $hasAnyValue = false;
        foreach ($fieldsInSchema as $field) {
            if ($this->isFilledValue($request->input($field))) {
                $hasAnyValue = true;
                break;
            }
        }

        if (! $hasAnyValue) {
            return $rules;
        }

        foreach ($fieldsInSchema as $field) {
            $fieldRules = $rules[$field] ?? [];
            if (! is_array($fieldRules)) {
                continue;
            }

            $fieldRules = array_values(array_filter(
                $fieldRules,
                static fn (mixed $rule): bool => $rule !== 'nullable'
            ));

            if (! in_array('required', $fieldRules, true)) {
                array_unshift($fieldRules, 'required');
            }

            $rules[$field] = $fieldRules;
        }

        return $rules;
    }

    private function isFilledValue(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->isFilledValue($item)) {
                    return true;
                }
            }

            return false;
        }

        if ($value === null) {
            return false;
        }

        return trim((string) $value) !== '';
    }

    public function processFormData(
        SubmissionFormInstance $instance,
        Request $request,
        Collection $elements
    ): void {
        foreach ($elements as $element) {
            $fieldName = $element->name;
            // Check if this is a multiple select field (has [] in form name but not from rows)
            $isMultipleSelect = $this->isMultipleSelectField($element, $request);
            
            $inputValue = $request->input($fieldName);
            $fileValue = $request->file($fieldName);
            $isArray = is_array($inputValue) || is_array($fileValue);

            if ($isArray) {
                if ($isMultipleSelect) {
                    $this->processMultipleSelectField($instance, $element, $inputValue ?? []);
                } else {
                    // Handle array fields (from rows sections)
                    $this->processArrayField($instance, $element, $inputValue ?? [], $request, $fieldName);
                }
            } else {
                $this->processSingleField($instance, $element, $inputValue, $request);
            }
        }
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
        return DB::transaction(function () use ($submissionForm, $fieldValues, $crmCustomerId, $sampleTypeId, $sourceChannel, $samplingScheduleId): SubmissionFormInstance {
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
                'sampling_schedule_id' => $samplingScheduleId,
            ];

            if ($this->supportsSelectedSampleTypeColumn()) {
                $instanceData['selected_sample_type_id'] = $sampleTypeId !== null && $sampleTypeId !== ''
                    ? $sampleTypeId
                    : null;
            }

            $instance = SubmissionFormInstance::query()->create($instanceData);

            $elements = $this->elementsForForm($submissionForm);
            $request = new Request();
            $request->merge($fieldValues);
            $this->processFormData($instance, $request, $elements);

            $this->assignFormNumberWithRetry($instance, $submissionForm);
            $instance->refresh();

            $this->syncCommercialPipelineIfApplicable($instance->fresh(['values.element']), $submissionForm);

            return $instance->fresh(['submissionForm', 'values.element']);
        });
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

        $syncService = app(CommercialEnquirySyncService::class);
        if (! $syncService->isCommercialTestRequestForm($instance)) {
            return;
        }

        try {
            $syncService->syncFromSubmittedInstance($instance->fresh(['values.element', 'submissionForm', 'crmCustomer']));
        } catch (\Throwable $exception) {
            Log::warning('Commercial enquiry sync failed after submission form submit.', [
                'instance_id' => $instance->id,
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            app(TestRequestFormPdfService::class)->generateAndStore($instance->fresh(['values.element', 'submissionForm']));
        } catch (\Throwable $exception) {
            Log::warning('Test Request Form PDF generation failed after submission.', [
                'instance_id' => $instance->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /*
     * @deprecated-remove TRF_LAYER_MANIFEST.md Phase 1
     * Replaced by syncCommercialPipelineIfApplicable — retained for reference.
     *
    private function createTestRequestFormInstanceIfApplicable(
        SubmissionFormInstance $instance,
        SubmissionForm $submissionForm
    ): void {
        ...
    }
    */

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
        $elements = app(SubmissionFormSchemaHelper::class)->uniqueElements($submissionForm);

        return new Collection($elements->all());
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

            if (($saveValue !== null && $saveValue !== '') || $filePath !== null) {
                $this->saveFieldValue($instance, $element, $saveValue, $filePath, is_numeric($index) ? (int) $index : null);
            }
        }
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
        SubmissionFormInstanceValue::withoutAuditing(function () use ($instance, $element, $value, $filePath, $arrayIndex): void {
            SubmissionFormInstanceValue::updateOrCreate(
                [
                    'submission_form_instance_id' => $instance->id,
                    'submission_form_element_id' => $element->id,
                    'array_index' => $arrayIndex,
                ],
                [
                    'value' => $value,
                    'file_path' => $filePath,
                ]
            );
        });
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

            $matched = false;
            if ($op === '==' || $op === 'equals') {
                $matched = ((string) $actual === (string) $expected);
            } elseif ($op === '!=' || $op === 'not_equals') {
                $matched = ((string) $actual !== (string) $expected);
            } elseif ($op === 'not_empty') {
                $matched = ($actual !== '' && $actual !== null);
            } elseif ($op === 'empty') {
                $matched = ($actual === '' || $actual === null);
            } elseif ($op === 'contains') {
                $matched = (strpos((string) $actual, (string) $expected) !== false);
            } else {
                $matched = true;
            }

            if (!$matched) {
                return false;
            }
        }

        return true;
    }
}

