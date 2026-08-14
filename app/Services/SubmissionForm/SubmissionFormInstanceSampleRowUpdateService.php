<?php

namespace App\Services\SubmissionForm;

use App\Models\CRM\SamplePoint;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Services\Commercial\CommercialEnquirySyncService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SubmissionFormInstanceSampleRowUpdateService
{
    public function __construct(
        private readonly CommercialEnquirySyncService $enquirySyncService,
    ) {}

    /**
     * @return Collection<int, SubmissionFormElement>
     */
    public function rowElements(SubmissionForm $form): Collection
    {
        $form->loadMissing(['sections.elementHolders.elements']);

        $elements = collect();

        foreach ($form->sections as $section) {
            if ((string) ($section->section_type ?? '') !== 'rows_section') {
                continue;
            }

            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    $elements->push($element);
                }
            }
        }

        return $elements->sortBy('sort_order')->values();
    }

    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     label: string,
     *     element_type: string,
     *     required: bool,
     *     options: array<int|string, mixed>
     * }>
     */
    public function rowFieldDefinitions(SubmissionForm $form): array
    {
        $elements = $this->rowElements($form);

        return $elements
            ->filter(function (SubmissionFormElement $element) use ($elements): bool {
                if ((bool) ($element->is_hidden ?? false)) {
                    return false;
                }

                if (SubmissionFormSchemaHelper::shouldExcludeFromSampleRowEditor($element)) {
                    return false;
                }

                $holderElements = $elements->filter(
                    fn (SubmissionFormElement $candidate): bool => $candidate->submission_form_element_holder_id
                        === $element->submission_form_element_holder_id,
                );

                return ! SubmissionFormSchemaHelper::shouldHideSupersededRowField($element, $holderElements);
            })
            ->map(function (SubmissionFormElement $element): array {
                return [
                    'id' => (string) $element->id,
                    'name' => (string) $element->name,
                    'label' => (string) ($element->label ?? $element->name),
                    'element_type' => (string) $element->element_type,
                    'required' => (bool) $element->is_required,
                    'options' => $element->getFormattedOptions(),
                ];
            })
            ->values()
            ->all();
    }

    public function collectionSamplingLocationLabel(SubmissionFormInstance $instance): string
    {
        $instance->loadMissing(['values.element']);

        foreach ($instance->values as $value) {
            if ($value->array_index !== null) {
                continue;
            }

            $name = trim((string) ($value->element?->name ?? ''));
            if ($name !== 'sampling_location') {
                continue;
            }

            $raw = trim((string) ($value->value ?? ''));
            if ($raw === '') {
                return '';
            }

            return $this->resolveSamplePointLabel($raw);
        }

        return '';
    }

    /**
     * @return array<string, mixed>
     */
    public function rowValues(SubmissionFormInstance $instance, int $rowIndex): array
    {
        $instance->loadMissing(['values.element', 'submissionForm']);

        $values = [];

        foreach ($instance->values as $value) {
            if ($value->array_index === null || (int) $value->array_index !== $rowIndex) {
                continue;
            }

            $name = trim((string) ($value->element?->name ?? ''));
            if ($name === '') {
                continue;
            }

            $values[$name] = $this->decodeStoredValue(
                (string) ($value->value ?? ''),
                (string) ($value->element?->element_type ?? 'text'),
                $name,
            );
        }

        return $this->enrichRowValuesFromFormData($instance, $rowIndex, $values);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function enrichRowValuesFromFormData(
        SubmissionFormInstance $instance,
        int $rowIndex,
        array $values,
    ): array {
        $formData = app(SubmissionFormValueNormalizer::class)->valuesMapFromInstance($instance);
        $sampleRow = is_array($formData['sample_rows'][$rowIndex] ?? null)
            ? $formData['sample_rows'][$rowIndex]
            : [];

        foreach ($sampleRow as $name => $raw) {
            $name = trim((string) $name);
            if ($name === '' || $this->rowValueIsFilled($values[$name] ?? null)) {
                continue;
            }

            $values[$name] = $this->decodeStoredValue(
                is_array($raw) ? implode(',', array_map('strval', $raw)) : (string) $raw,
                $this->guessElementTypeForRowField($name),
                $name,
            );
        }

        $lines = app(SubmissionRequestSampleLineService::class)->linesForInstance($instance);
        $line = collect($lines)->first(
            fn (array $candidate): bool => (int) ($candidate['row_index'] ?? -1) === $rowIndex,
        );

        if (! is_array($line)) {
            return $values;
        }

        $lineFallbacks = [
            'sample_type_id' => $line['sample_type_id'] ?? ($line['attributes']['sample_type_ids'] ?? null),
            'analysis_type_id' => $line['analysis_type_id'] ?? ($line['attributes']['analysis_type_ids'] ?? null),
            'parameters' => $line['attributes']['analysis_element_ids'] ?? $line['analysis_element_id'] ?? null,
            'sample_description' => $line['sample_description'] ?? null,
            'sampling_point_manual' => $line['attributes']['sampling_point_manual'] ?? null,
            'field_sample_temp' => $line['field_sample_temp'] ?? null,
            'test_requirements' => $line['attributes']['test_requirements']
                ?? $line['test_requirements']
                ?? $line['parameter_category']
                ?? $line['test_category']
                ?? null,
        ];

        foreach ($lineFallbacks as $name => $raw) {
            if ($this->rowValueIsFilled($values[$name] ?? null)) {
                continue;
            }

            if ($raw === null || $raw === '' || $raw === []) {
                continue;
            }

            $elementType = $this->guessElementTypeForRowField($name);

            if ($elementType === 'checkbox' || in_array($name, ['test_requirements', 'test_category'], true)) {
                $values[$name] = SubmissionFormSchemaHelper::checkboxGroupValueMap($raw);

                continue;
            }

            $values[$name] = $this->decodeStoredValue(
                is_array($raw) ? implode(',', array_map('strval', $raw)) : (string) $raw,
                $elementType,
                $name,
            );
        }

        if (is_array($line['attributes']['sample_type_ids'] ?? null) && ! $this->rowValueIsFilled($values['sample_type_id'] ?? null)) {
            $values['sample_type_id'] = array_values(array_map('strval', $line['attributes']['sample_type_ids']));
        }

        if (is_array($line['attributes']['analysis_type_ids'] ?? null) && ! $this->rowValueIsFilled($values['analysis_type_id'] ?? null)) {
            $values['analysis_type_id'] = array_values(array_map('strval', $line['attributes']['analysis_type_ids']));
        }

        return $values;
    }

    private function rowValueIsFilled(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return trim((string) $value) !== '';
    }

    private function guessElementTypeForRowField(string $name): string
    {
        return match ($name) {
            'sample_type_id', 'sample_type' => 'sample_type_select',
            'analysis_type_id', 'analysis_type', 'analysis_types' => 'analysis_type_select',
            'parameters', 'parameter' => 'analysis_elements_select',
            'test_requirements', 'test_category' => 'checkbox',
            default => 'text',
        };
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public function updateRow(
        SubmissionFormInstance $instance,
        int $rowIndex,
        array $fields,
    ): SubmissionFormInstance {
        return DB::transaction(function () use ($instance, $rowIndex, $fields): SubmissionFormInstance {
            $instance->loadMissing(['submissionForm']);
            $form = $instance->submissionForm;

            if ($form === null) {
                return $instance;
            }

            $elementByName = $this->rowElements($form)->keyBy(
                fn (SubmissionFormElement $element): string => (string) $element->name,
            );

            foreach ($fields as $name => $value) {
                $name = trim((string) $name);
                if ($name === '' || ! $elementByName->has($name)) {
                    continue;
                }

                /** @var SubmissionFormElement $element */
                $element = $elementByName->get($name);
                $stored = $this->encodeValueForStorage($value, (string) $element->element_type);

                SubmissionFormInstanceValue::withoutAuditing(function () use ($instance, $element, $stored, $rowIndex): void {
                    SubmissionFormInstanceValue::query()->updateOrCreate(
                        [
                            'submission_form_instance_id' => $instance->id,
                            'submission_form_element_id' => $element->id,
                            'array_index' => $rowIndex,
                        ],
                        [
                            'value' => $stored,
                        ],
                    );
                });
            }

            $fresh = $instance->fresh(['values.element', 'submissionForm', 'batches', 'analysisAcceptanceForms']) ?? $instance;

            $enquiry = $this->enquirySyncService->resyncSampleDataFromInstance($fresh);
            if ($enquiry !== null) {
                $this->patchEnquirySampleConfiguration($enquiry, $rowIndex, $fields);
            }

            return $fresh;
        });
    }

    private function decodeStoredValue(string $raw, string $elementType, string $fieldName = ''): mixed
    {
        if ($raw === '') {
            if ($elementType === 'checkbox' || in_array($fieldName, ['test_requirements', 'test_category'], true)) {
                return [];
            }

            if (in_array($fieldName, SubmissionFormSchemaHelper::sampleRowMultiSelectFieldNames(), true)
                || $elementType === 'analysis_elements_select') {
                return [];
            }

            return '';
        }

        if ($elementType === 'checkbox' || in_array($fieldName, ['test_requirements', 'test_category'], true)) {
            return SubmissionFormSchemaHelper::checkboxGroupValueMap($raw);
        }

        if (in_array($elementType, ['analysis_elements_select'], true) && str_contains($raw, ',')) {
            return array_values(array_filter(array_map('trim', explode(',', $raw))));
        }

        if (in_array($fieldName, SubmissionFormSchemaHelper::sampleRowMultiSelectFieldNames(), true)) {
            if (str_contains($raw, ',')) {
                return array_values(array_filter(array_map('trim', explode(',', $raw))));
            }

            return $raw === '' ? [] : [$raw];
        }

        return $raw;
    }

    private function encodeValueForStorage(mixed $value, string $elementType): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            if ($this->isAssociativeSelectionMap($value)) {
                $selected = [];
                foreach ($value as $key => $flag) {
                    if ((bool) $flag) {
                        $selected[] = (string) $key;
                    }
                }

                return $selected === [] ? null : implode(',', $selected);
            }

            $flat = array_values(array_filter(array_map(
                static fn ($item): string => trim((string) $item),
                $value,
            ), static fn (string $token): bool => $token !== ''));

            return $flat === [] ? null : implode(',', $flat);
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    /**
     * @param  array<int|string, mixed>  $value
     */
    private function isAssociativeSelectionMap(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function patchEnquirySampleConfiguration(
        SampleSubmissionRequest $enquiry,
        int $rowIndex,
        array $fields,
    ): void {
        $configs = is_array($enquiry->enquiry_sample_configuration)
            ? array_values($enquiry->enquiry_sample_configuration)
            : [];

        if ($configs === [] || ! isset($configs[$rowIndex]) || ! is_array($configs[$rowIndex])) {
            return;
        }

        $config = $configs[$rowIndex];

        $map = [
            'sample_description' => 'sample_description',
            'sample_quantity' => 'sample_quantity',
            'sample_quantity_unit' => 'sample_quantity_unit',
            'sampling_point' => 'sampling_point',
            'sampling_point_manual' => 'sampling_point',
            'location' => 'location',
            'production_date' => 'production_date',
            'expiration_date' => 'expiration_date',
            'batch_number' => 'batch_number',
            'test_category' => 'test_category',
            'test_requirements' => 'test_requirements',
            'field_sample_temp' => 'field_sample_temp',
            'sample_type_id' => 'sample_type_id',
            'analysis_type_id' => 'analysis_type_id',
        ];

        foreach ($map as $fieldName => $configKey) {
            if (! array_key_exists($fieldName, $fields)) {
                continue;
            }

            $config[$configKey] = $fields[$fieldName];
        }

        if (array_key_exists('parameters', $fields) && is_array($fields['parameters'])) {
            $config['parameter_keys'] = array_values($fields['parameters']);
        }

        $configs[$rowIndex] = $config;
        $enquiry->enquiry_sample_configuration = $configs;
        $enquiry->save();
    }

    private function resolveSamplePointLabel(string $value): string
    {
        if (! \Illuminate\Support\Str::isUuid($value)) {
            return $value;
        }

        $name = SamplePoint::query()->whereKey($value)->value('name');

        return $name !== null && $name !== '' ? (string) $name : $value;
    }
}
