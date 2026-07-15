<?php

namespace App\Services\Planner;

use App\AnalysisElements;
use App\Analyte;
use App\AnalysisType;
use App\Models\SamplingSchedule;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Maps System Planner sampling-schedule data onto TRF form fields so the lab
 * request view (and downstream sample workflow) shows collection/qty/tests.
 */
final class SamplingScheduleTrfSync
{
    /**
     * Scalar / collection fields always refreshed from the schedule on save.
     *
     * @var list<string>
     */
    private const FORCE_SCALAR_FIELDS = [
        'sampling_date',
        'sampling_time',
        'date_received',
        'sample_quantity',
        'number_of_samples',
        'qty',
    ];

    /**
     * Row fields always refreshed from the schedule on save.
     *
     * @var list<string>
     */
    private const FORCE_ROW_FIELDS = [
        'analysis_type_id',
        'parameters',
        'sample_quantity',
        'number_of_samples',
        'qty',
        'sampling_point',
        'location',
    ];

    /**
     * Build TRF field values from a schedule entry for the given sample type.
     *
     * @return array<string, mixed>
     */
    public function fieldValuesFromSchedule(SamplingSchedule $schedule, string $sampleTypeId): array
    {
        $sampleTypeId = trim($sampleTypeId);
        $matchingEntry = $this->matchingScheduleSampleEntry($schedule, $sampleTypeId);
        $values = [];

        if ($schedule->sampling_datetime) {
            $values['sampling_date'] = $schedule->sampling_datetime->format('Y-m-d');
            $values['sampling_time'] = $schedule->sampling_datetime->format('H:i');
            $values['date_received'] = $schedule->sampling_datetime->format('Y-m-d');
        }

        $location = trim((string) ($schedule->location ?? ''));
        if ($location !== '') {
            $values['sampling_location'] = $location;
            $values['sampling_point'] = $location;
            $values['location'] = $location;
        }

        $qty = max(1, (int) ($schedule->number_of_samples ?? 1));
        $values['sample_quantity'] = $qty;
        $values['number_of_samples'] = $qty;
        $values['qty'] = $qty;

        $analysisTypeId = trim((string) ($matchingEntry['analysis_type_id'] ?? $schedule->analysis_type_id ?? ''));
        if ($analysisTypeId !== '') {
            $values['analysis_type_id'] = $analysisTypeId;
        }

        $parameterIds = is_array($matchingEntry['parameters'] ?? null)
            ? $matchingEntry['parameters']
            : (is_array($schedule->parameters ?? null) ? $schedule->parameters : []);
        $parameterNames = $this->resolveParameterNames($parameterIds);
        if ($parameterNames !== []) {
            $values['parameters'] = $parameterNames;
        }

        $title = trim((string) ($schedule->title ?? ''));
        $description = trim((string) ($schedule->description ?? ''));
        if ($title !== '') {
            $values['sample_description'] = $title;
        } elseif ($description !== '') {
            $values['sample_description'] = $description;
        }

        if ($description !== '' && $description !== $title) {
            $values['sample_information'] = $description;
            $values['remarks'] = $description;
        }

        $personnelName = $this->resolvePersonnelName($schedule);
        if ($personnelName !== '') {
            $values['sampled_by'] = $personnelName;
        }

        return $values;
    }

    /**
     * Merge schedule-derived values into Livewire capture formData.
     *
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function mergeIntoFormData(
        array $formData,
        SamplingSchedule $schedule,
        string $sampleTypeId,
        SubmissionForm $submissionForm,
    ): array {
        $submissionForm->loadMissing(['sections.elementHolders.elements']);
        $scheduleValues = $this->fieldValuesFromSchedule($schedule, $sampleTypeId);

        foreach ($scheduleValues as $name => $value) {
            $meta = $this->elementMeta($submissionForm, (string) $name);
            if ($meta === null) {
                continue;
            }

            $formData = $this->assignIntoFormData($formData, (string) $name, $value, $meta['section_type'], force: true);
        }

        return $formData;
    }

    /**
     * Fill empty stored values on a scheduled TRF instance from its linked schedule.
     * Never overwrites non-empty captured values.
     */
    public function fillEmptyInstanceValues(SubmissionFormInstance $instance): int
    {
        $scheduleId = trim((string) ($instance->sampling_schedule_id ?? ''));
        if ($scheduleId === '') {
            return 0;
        }

        $schedule = SamplingSchedule::query()->find($scheduleId);
        if ($schedule === null) {
            return 0;
        }

        $sampleTypeId = trim((string) ($instance->selected_sample_type_id ?? ''));
        if ($sampleTypeId === '') {
            $sampleTypeId = trim((string) ($schedule->sample_type_id ?? ''));
        }
        if ($sampleTypeId === '' && is_array($schedule->sample_details) && $schedule->sample_details !== []) {
            $sampleTypeId = trim((string) ($schedule->sample_details[0]['sample_type_id'] ?? ''));
        }

        $instance->loadMissing([
            'values.element',
            'submissionForm.sections.elementHolders.elements',
        ]);

        $form = $instance->submissionForm;
        if ($form === null) {
            return 0;
        }

        $scheduleValues = $this->fieldValuesFromSchedule($schedule, $sampleTypeId);
        if ($scheduleValues === []) {
            return 0;
        }

        $filled = 0;

        foreach ($scheduleValues as $name => $value) {
            $meta = $this->elementMeta($form, (string) $name);
            if ($meta === null) {
                continue;
            }

            $element = $meta['element'];
            $isRow = $meta['section_type'] === 'rows_section';
            $arrayIndex = $isRow ? 0 : null;

            $existing = $instance->values
                ->first(function (SubmissionFormInstanceValue $row) use ($element, $arrayIndex): bool {
                    if ((string) $row->submission_form_element_id !== (string) $element->id) {
                        return false;
                    }

                    if ($arrayIndex === null) {
                        return $row->array_index === null;
                    }

                    return (int) ($row->array_index ?? 0) === $arrayIndex;
                });

            $current = $existing?->value;
            if (! $this->isEmptyValue($current)) {
                continue;
            }

            $stored = $this->serializeValueForStorage($value);
            if ($stored === null) {
                continue;
            }

            try {
                SubmissionFormInstanceValue::withoutAuditing(function () use ($instance, $element, $stored, $arrayIndex): void {
                    SubmissionFormInstanceValue::updateOrCreate(
                        [
                            'submission_form_instance_id' => $instance->id,
                            'submission_form_element_id' => $element->id,
                            'array_index' => $arrayIndex,
                        ],
                        [
                            'value' => $stored,
                        ]
                    );

                    // Drop stale blank scalars created by older schedule saves that
                    // wrote row fields with array_index = null.
                    if ($arrayIndex === 0) {
                        SubmissionFormInstanceValue::query()
                            ->where('submission_form_instance_id', $instance->id)
                            ->where('submission_form_element_id', $element->id)
                            ->whereNull('array_index')
                            ->where(function ($query): void {
                                $query->whereNull('value')->orWhere('value', '');
                            })
                            ->delete();
                    }
                });
                $filled++;
            } catch (Throwable $e) {
                Log::warning('SamplingScheduleTrfSync: failed to fill empty TRF value', [
                    'instance_id' => $instance->id,
                    'element' => $name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($filled > 0) {
            $instance->unsetRelation('values');
            $instance->load('values.element');
        }

        return $filled;
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    private function assignIntoFormData(
        array $formData,
        string $name,
        mixed $value,
        string $sectionType,
        bool $force = false,
    ): array {
        $isRowField = $sectionType === 'rows_section';

        if ($isRowField) {
            if (! isset($formData[$name]) || ! is_array($formData[$name])) {
                $formData[$name] = [];
            }

            $current = $formData[$name][0] ?? null;
            if ($force || $this->isEmptyValue($current) || in_array($name, self::FORCE_ROW_FIELDS, true)) {
                $formData[$name][0] = $value;
            }

            return $formData;
        }

        $current = $formData[$name] ?? null;
        if ($force || $this->isEmptyValue($current) || in_array($name, self::FORCE_SCALAR_FIELDS, true)) {
            $formData[$name] = $value;
        }

        return $formData;
    }

    /**
     * @return array{element: SubmissionFormElement, section_type: string}|null
     */
    private function elementMeta(SubmissionForm $submissionForm, string $name): ?array
    {
        foreach ($submissionForm->sections as $section) {
            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    if ((string) ($element->name ?? '') === $name) {
                        return [
                            'element' => $element,
                            'section_type' => (string) ($section->section_type ?? ''),
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return array{sample_type_id?: string, analysis_type_id?: string|null, parameters?: list<string>}|null
     */
    private function matchingScheduleSampleEntry(SamplingSchedule $schedule, string $sampleTypeId): ?array
    {
        if (! empty($schedule->sample_details) && is_array($schedule->sample_details)) {
            foreach ($schedule->sample_details as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                if ($sampleTypeId !== '' && ($entry['sample_type_id'] ?? '') === $sampleTypeId) {
                    return $entry;
                }
            }

            $first = $schedule->sample_details[0] ?? null;
            if (is_array($first) && (! empty($first['analysis_type_id']) || ! empty($first['parameters']))) {
                return $first;
            }
        }

        if ($sampleTypeId === '' || (string) ($schedule->sample_type_id ?? '') === $sampleTypeId
            || (string) ($schedule->sample_type_id ?? '') !== '') {
            return [
                'sample_type_id' => (string) ($schedule->sample_type_id ?? $sampleTypeId),
                'analysis_type_id' => $schedule->analysis_type_id ? (string) $schedule->analysis_type_id : null,
                'parameters' => is_array($schedule->parameters ?? null) ? $schedule->parameters : [],
            ];
        }

        return null;
    }

    /**
     * @param  list<string>|array<int, mixed>  $parameterIds
     * @return list<string>
     */
    private function resolveParameterNames(array $parameterIds): array
    {
        $parameterIds = array_values(array_filter(array_map('strval', $parameterIds)));
        if ($parameterIds === []) {
            return [];
        }

        $byAnalyte = Analyte::query()
            ->whereIn('id', $parameterIds)
            ->pluck('name', 'id');

        $names = [];
        $missing = [];
        foreach ($parameterIds as $id) {
            $name = trim((string) ($byAnalyte[$id] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            } else {
                $missing[] = $id;
            }
        }

        if ($missing !== []) {
            $fromElements = AnalysisElements::query()
                ->with('analyte')
                ->whereIn('id', $missing)
                ->get();

            foreach ($fromElements as $element) {
                $name = trim((string) ($element->analyte?->name ?? $element->method ?? ''));
                if ($name !== '') {
                    $names[] = $name;
                    $idx = array_search((string) $element->id, $missing, true);
                    if ($idx !== false) {
                        unset($missing[$idx]);
                    }
                }
            }
        }

        // Keep unresolved IDs so they remain visible rather than silently dropped.
        foreach ($missing as $id) {
            $analysisTypeName = AnalysisType::query()->where('id', $id)->value('name');
            $names[] = $analysisTypeName ? (string) $analysisTypeName : (string) $id;
        }

        return array_values(array_unique(array_filter($names, fn (string $name): bool => $name !== '')));
    }

    private function resolvePersonnelName(SamplingSchedule $schedule): string
    {
        $personnelId = trim((string) ($schedule->personnel_id ?? ''));
        if ($personnelId === '') {
            return '';
        }

        $user = User::query()->find($personnelId);

        return trim((string) ($user?->name ?? ''));
    }

    private function isEmptyValue(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [] || $value === false;
    }

    private function serializeValueForStorage(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $flat = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    foreach ($item as $nested) {
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

            return $flat === [] ? null : implode(', ', $flat);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
