<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmissionFormSubmissionService
{
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

        return $rules;
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

        // Create TestRequestFormInstance if this is a test request form submission
        $this->createTestRequestFormInstanceIfApplicable($instance, $submissionForm);

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

    private function createTestRequestFormInstanceIfApplicable(
        SubmissionFormInstance $instance,
        SubmissionForm $submissionForm
    ): void {
        // Check if this submission form is a test request form type
        // by checking if it has test request form related fields
        $elements = $this->elementsForForm($submissionForm);
        $hasTestRequestFields = $elements->contains(function ($element) {
            return in_array($element->name, ['sample_rows', 'customer_name', 'sampling_date'], true);
        });

        if (!$hasTestRequestFields) {
            return;
        }

        // Extract form data from submission form instance values
        $formData = [];
        foreach ($instance->values as $value) {
            $fieldName = $value->element?->name ?? $value->element_name ?? null;
            if ($fieldName) {
                $formData[$fieldName] = $value->value;
            }
        }

        // Check if this has sample_rows which indicates it's a test request form
        if (!isset($formData['sample_rows'])) {
            return;
        }

        // Find the appropriate TestRequestForm based on sample type
        $sampleTypeId = $submissionForm->sampleTypes->first()?->id;
        if (!$sampleTypeId) {
            return;
        }

        $testRequestForm = \App\Models\TestRequestForm::where('sample_type_id', $sampleTypeId)
            ->where('is_active', true)
            ->first();

        if (!$testRequestForm) {
            return;
        }

        // Create TestRequestFormInstance
        \App\Models\TestRequestFormInstance::updateOrCreate(
            ['submission_form_instance_id' => $instance->id],
            [
                'test_request_form_id' => $testRequestForm->id,
                'form_data' => $formData,
                'status' => 'submitted',
                'created_by' => Auth::id(),
            ]
        );
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
            ->whereHas('holder.section', function ($query) use ($submissionForm): void {
                $query->where('submission_form_id', $submissionForm->id);
            })
            ->get();
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
                $this->saveFieldValue($instance, $element, $saveValue, $filePath, $index);
            }
        }
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

