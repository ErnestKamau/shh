<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class PortalEnquiryFormInstanceSyncService
{
    private const TARGET_RECORD_TYPE = SampleSubmissionRequest::class;

    private const LSR_DOCUMENT_CODE = 'LSR-001';

    public function syncFromEnquiry(SampleSubmissionRequest $enquiry): ?SubmissionFormInstance
    {
        $form = $this->resolveLaboratoryServiceRequestForm();
        if ($form === null) {
            return null;
        }

        return DB::transaction(function () use ($enquiry, $form): ?SubmissionFormInstance {
            $enquiry->refresh();

            $instance = $this->resolveOrCreateInstance($enquiry, $form);
            $elementMap = $this->buildElementMap($form);

            $instance->values()->delete();

            $this->syncHeaderValues($instance, $elementMap, $enquiry);
            $this->syncSampleLineValues($instance, $elementMap, $enquiry);
            $this->updateInstanceMetadata($instance, $enquiry);

            if ($enquiry->submission_form_instance_id !== $instance->id) {
                $enquiry->submission_form_instance_id = $instance->id;
                $enquiry->save();
            }

            return $instance->fresh(['values']);
        });
    }

    private function resolveLaboratoryServiceRequestForm(): ?SubmissionForm
    {
        return SubmissionForm::query()
            ->where('document_code', self::LSR_DOCUMENT_CODE)
            ->where('is_active', true)
            ->first();
    }

    private function resolveOrCreateInstance(SampleSubmissionRequest $enquiry, SubmissionForm $form): SubmissionFormInstance
    {
        if ($enquiry->submission_form_instance_id) {
            $existing = SubmissionFormInstance::query()->find($enquiry->submission_form_instance_id);
            if ($existing !== null) {
                return $existing;
            }
        }

        $linked = SubmissionFormInstance::query()
            ->where('portal_request_id', (string) $enquiry->id)
            ->first();

        if ($linked !== null) {
            return $linked;
        }

        return SubmissionFormInstance::query()->create([
            'submission_form_id' => $form->id,
            'crm_customer_id' => $enquiry->crm_customer_id,
            'portal_request_id' => (string) $enquiry->id,
            'target_record_type' => self::TARGET_RECORD_TYPE,
            'status' => 'submitted',
            'submitted_at' => now(),
            'title' => $this->instanceTitle($enquiry),
            'priority' => $this->instancePriority($enquiry),
        ]);
    }

    /**
     * @return array<string, array{id: string, is_row: bool}>
     */
    private function buildElementMap(SubmissionForm $form): array
    {
        $form->loadMissing(['sections.elementHolders.elements']);

        $map = [];

        foreach ($form->sections as $section) {
            $isRow = (string) ($section->section_type ?? '') === 'rows_section';

            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    $name = (string) ($element->name ?? '');
                    if ($name === '') {
                        continue;
                    }

                    $map[$name] = [
                        'id' => (string) $element->id,
                        'is_row' => $isRow,
                    ];
                }
            }
        }

        return $map;
    }

    /**
     * @param  array<string, array{id: string, is_row: bool}>  $elementMap
     */
    private function syncHeaderValues(
        SubmissionFormInstance $instance,
        array $elementMap,
        SampleSubmissionRequest $enquiry,
    ): void {
        $this->storeValue($instance, $elementMap, 'crm_customer_id', $enquiry->crm_customer_id);
        $this->storeValue($instance, $elementMap, 'reporting_language', $enquiry->reporting_language);
        $this->storeValue($instance, $elementMap, 'request_date_of_service', $this->formatDate($enquiry->request_date_of_service));
        $this->storeValue($instance, $elementMap, 'lab_zone_location', $enquiry->zone_id);
        $this->storeValue($instance, $elementMap, 'request_for_sampling', $enquiry->request_for_sampling ? '1' : '0');
        $this->storeValue($instance, $elementMap, 'mode_of_service', $enquiry->mode_of_service_priority);
        $this->storeValue($instance, $elementMap, 'mode_of_payment', $enquiry->mode_of_payment);
        $this->storeValue($instance, $elementMap, 'statement_of_conformity', $enquiry->statement_of_conformity);
        $this->storeValue($instance, $elementMap, 'purpose', $enquiry->purpose);
        $this->storeValue($instance, $elementMap, 'submitted_by_full_name', $enquiry->submitted_by_full_name);
        $this->storeValue($instance, $elementMap, 'submitted_by_title', $enquiry->submitted_by_title);
        $this->storeValue($instance, $elementMap, 'submitted_by_signature', $enquiry->submitted_by_signature);
        $this->storeValue($instance, $elementMap, 'submitted_by_date', $this->formatDate($enquiry->submitted_by_date));

        if (! $enquiry->request_for_sampling) {
            return;
        }

        $collection = is_array($enquiry->collection_data) ? $enquiry->collection_data : [];

        foreach ([
            'sampling_date',
            'sampling_time',
            'sampling_location',
            'sampling_apparatus',
            'method_of_sampling',
            'reason_of_collection',
            'transport_condition',
        ] as $field) {
            $value = $collection[$field] ?? null;
            if ($field === 'sampling_date') {
                $value = $this->formatDate($value);
            }
            $this->storeValue($instance, $elementMap, $field, $value);
        }
    }

    /**
     * @param  array<string, array{id: string, is_row: bool}>  $elementMap
     */
    private function syncSampleLineValues(
        SubmissionFormInstance $instance,
        array $elementMap,
        SampleSubmissionRequest $enquiry,
    ): void {
        $lines = $this->normalizeSampleLines($enquiry->sample_lines ?? null);

        if ($lines === []) {
            $lines = [$this->flatLineFromEnquiry($enquiry)];
        }

        foreach ($lines as $line) {
            $rowIndex = (int) ($line['row_index'] ?? 0);
            $parameterIds = $line['analysis_element_ids'] ?? [];

            $this->storeValue($instance, $elementMap, 'sample_description', $line['sample_description'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sample_id', $line['customer_sample_id'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sample_type_id', $line['sample_type_id'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'analysis_type_id', $line['analysis_type_id'] ?? null, $rowIndex);
            $this->storeValue(
                $instance,
                $elementMap,
                'parameters',
                $parameterIds !== [] ? implode(',', $parameterIds) : null,
                $rowIndex,
            );
            $this->storeValue($instance, $elementMap, 'parameter_category', $line['parameter_category'] ?? null, $rowIndex);
            $this->storeValue($instance, $elementMap, 'sampling_point', $line['sampling_point'] ?? null, $rowIndex);
            $this->storeValue(
                $instance,
                $elementMap,
                'number_of_samples',
                (string) max(1, (int) ($line['number_of_samples'] ?? 1)),
                $rowIndex,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeSampleLines(mixed $rawLines): array
    {
        if (! is_array($rawLines) || $rawLines === []) {
            return [];
        }

        $lines = [];

        foreach (array_values($rawLines) as $index => $line) {
            if (! is_array($line)) {
                continue;
            }

            $parameterIds = $line['analysis_element_ids'] ?? $line['parameter_ids'] ?? [];
            if (! is_array($parameterIds)) {
                $parameterIds = [];
            }

            $parameterIds = array_values(array_filter(array_map(
                static fn (mixed $id): string => trim((string) $id),
                $parameterIds,
            )));

            $normalized = [
                'row_index' => (int) ($line['row_index'] ?? $index),
                'customer_sample_id' => trim((string) ($line['customer_sample_id'] ?? $line['sample_id'] ?? '')),
                'sample_description' => trim((string) ($line['sample_description'] ?? '')),
                'parameter_category' => trim((string) ($line['parameter_category'] ?? '')),
                'sample_type_id' => trim((string) ($line['sample_type_id'] ?? '')),
                'analysis_type_id' => trim((string) ($line['analysis_type_id'] ?? $line['matrix_id'] ?? '')),
                'analysis_element_ids' => $parameterIds,
                'number_of_samples' => max(1, (int) ($line['number_of_samples'] ?? 1)),
                'sampling_point' => trim((string) ($line['sampling_point'] ?? '')),
            ];

            if ($normalized['customer_sample_id'] === '' && $normalized['sample_type_id'] === '' && $parameterIds === []) {
                continue;
            }

            $lines[] = $normalized;
        }

        usort($lines, static fn (array $a, array $b): int => $a['row_index'] <=> $b['row_index']);

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    private function flatLineFromEnquiry(SampleSubmissionRequest $enquiry): array
    {
        $parameterIds = is_array($enquiry->parameter_ids) ? $enquiry->parameter_ids : [];

        if ($parameterIds === []) {
            $enquiry->loadMissing('requestedAnalyses');
            $parameterIds = $enquiry->requestedAnalyses
                ->pluck('analysis_element_id')
                ->filter()
                ->map(static fn (mixed $id): string => trim((string) $id))
                ->unique()
                ->values()
                ->all();
        }

        return [
            'row_index' => 0,
            'customer_sample_id' => $enquiry->sample_id,
            'sample_description' => $enquiry->sample_description,
            'sample_type_id' => $enquiry->sample_type_id,
            'analysis_type_id' => $enquiry->matrix_id,
            'analysis_element_ids' => array_values($parameterIds),
            'number_of_samples' => max(1, (int) ($enquiry->number_of_samples ?? 1)),
        ];
    }

    private function updateInstanceMetadata(SubmissionFormInstance $instance, SampleSubmissionRequest $enquiry): void
    {
        $instance->crm_customer_id = $enquiry->crm_customer_id;
        $instance->zone_id = $enquiry->zone_id;
        $instance->portal_request_id = (string) $enquiry->id;
        $instance->target_record_type = self::TARGET_RECORD_TYPE;
        $instance->status = 'submitted';
        $instance->title = $this->instanceTitle($enquiry);
        $instance->priority = $this->instancePriority($enquiry);

        if ($instance->submitted_at === null) {
            $instance->submitted_at = now();
        }

        $instance->save();
    }

    /**
     * @param  array<string, array{id: string, is_row: bool}>  $elementMap
     */
    private function storeValue(
        SubmissionFormInstance $instance,
        array $elementMap,
        string $name,
        mixed $value,
        ?int $arrayIndex = null,
    ): void {
        if ($value === null || $value === '') {
            return;
        }

        if (! isset($elementMap[$name])) {
            return;
        }

        $meta = $elementMap[$name];

        SubmissionFormInstanceValue::query()->create([
            'submission_form_instance_id' => $instance->id,
            'submission_form_element_id' => $meta['id'],
            'array_index' => $meta['is_row'] ? ($arrayIndex ?? 0) : null,
            'value' => $this->stringifyValue($value),
        ]);
    }

    private function stringifyValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return trim((string) $value);
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return trim((string) $value) !== '' ? trim((string) $value) : null;
        }
    }

    private function instanceTitle(SampleSubmissionRequest $enquiry): string
    {
        $label = trim((string) ($enquiry->unique_identification ?? $enquiry->reference_number ?? ''));

        if ($label === '') {
            $label = $enquiry->formatted_number;
        }

        return 'Laboratory Service Request — '.$label;
    }

    private function instancePriority(SampleSubmissionRequest $enquiry): string
    {
        return (string) ($enquiry->mode_of_service_priority ?? '') === 'express' ? 'high' : 'normal';
    }
}
