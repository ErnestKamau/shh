<?php

namespace App\Services\Commercial;

use App\AnalysisElements;
use App\Models\Billing\PricelistCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\SampleSubmissionRequestRequestedAnalysis;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Support\Facades\DB;

final class CommercialEnquiryFromFormService
{
    /** @var list<string> */
    private const COLLECTION_DATA_KEYS = [
        'sampling_date',
        'sampling_time',
        'sampling_location',
        'sampling_apparatus',
        'method_of_sampling',
        'reason_of_collection',
        'transport_condition',
        'sample_sampling_point_description',
        'sampling_technique',
        'sampling_source',
        'sample_physical_state',
    ];

    public function __construct(
        private SubmissionRequestSampleLineService $sampleLineService,
    ) {}

    public function isLaboratoryServiceRequestForm(SubmissionFormInstance $instance): bool
    {
        return $this->isCommercialTestRequestForm($instance);
    }

    public function isCommercialTestRequestForm(SubmissionFormInstance $instance): bool
    {
        $instance->loadMissing('submissionForm');

        $code = strtoupper((string) ($instance->submissionForm->document_code ?? ''));
        $name = strtolower((string) ($instance->submissionForm->name ?? ''));

        if ($code === 'LSR-001' || str_contains($name, 'laboratory service request')) {
            return true;
        }

        if (str_starts_with($code, 'TRF-')) {
            return true;
        }

        if (str_contains($name, 'test request form')) {
            return true;
        }

        $form = $instance->submissionForm;

        return $form->form_type === 'template'
            && (bool) $form->is_customer_portal_form
            && str_starts_with($code, 'TRF-');
    }

    public function syncFromSubmittedInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        if (! $this->isCommercialTestRequestForm($instance)) {
            return null;
        }

        $instance->loadMissing(['crmCustomer', 'values.element', 'submissionForm']);

        return DB::transaction(function () use ($instance): SampleSubmissionRequest {
            $enquiry = $this->findOrCreateEnquiry($instance);
            $this->applyHeaderFields($enquiry, $instance);
            $this->syncSampleLines($enquiry, $instance);
            $this->syncRequestedAnalyses($enquiry, $instance);

            if (PricelistCustomer::query()->where('customer_id', $enquiry->crm_customer_id)->exists()) {
                $enquiry->pricing_source = 'contract';
            }

            $enquiry->status = SampleSubmissionRequest::STATUS_REQUESTED;
            $enquiry->source_channel = $this->resolveSourceChannel($instance);
            $enquiry->submission_form_instance_id = $instance->id;
            $enquiry->save();

            return $enquiry->fresh(['requestedAnalyses', 'customer']);
        });
    }

    private function findOrCreateEnquiry(SubmissionFormInstance $instance): SampleSubmissionRequest
    {
        $existing = SampleSubmissionRequest::query()
            ->where('submission_form_instance_id', $instance->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $portalRequestId = trim((string) ($instance->portal_request_id ?? ''));
        if ($portalRequestId !== '') {
            $linkedViaPortal = SampleSubmissionRequest::query()->find($portalRequestId);
            if ($linkedViaPortal !== null) {
                return $linkedViaPortal;
            }
        }

        return SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $instance->crm_customer_id,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => $this->resolveSourceChannel($instance),
            'submission_form_instance_id' => $instance->id,
        ]);
    }

    private function resolveSourceChannel(SubmissionFormInstance $instance): string
    {
        if ($instance->portal_account_id !== null && $instance->portal_account_id !== '') {
            return 'portal';
        }

        return 'walk_in';
    }

    private function applyHeaderFields(SampleSubmissionRequest $enquiry, SubmissionFormInstance $instance): void
    {
        $values = $this->indexedValues($instance);

        $enquiry->reporting_language = $values['reporting_language'] ?? $enquiry->reporting_language;
        $enquiry->request_date_of_service = $values['request_date_of_service'] ?? $enquiry->request_date_of_service;
        $enquiry->zone_id = $values['lab_zone_location'] ?? $values['zone_id'] ?? $enquiry->zone_id;
        $enquiry->request_for_sampling = filter_var($values['request_for_sampling'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $enquiry->mode_of_service_priority = $values['mode_of_service'] ?? $values['mode_of_service_priority'] ?? $enquiry->mode_of_service_priority;
        $enquiry->mode_of_payment = $values['mode_of_payment'] ?? $enquiry->mode_of_payment;
        $enquiry->purpose = $values['remarks'] ?? $values['purpose'] ?? $enquiry->purpose;
        $enquiry->crm_contact_id = $values['crm_contact_id'] ?? $enquiry->crm_contact_id;
        $enquiry->submitted_by_full_name = $values['customer_representative_name']
            ?? $values['submitted_by_full_name']
            ?? $values['submitting_personnel']
            ?? $values['sampled_by']
            ?? $enquiry->submitted_by_full_name;
        $enquiry->submitted_by_signature = $values['customer_representative_signature']
            ?? $values['submitted_by_signature']
            ?? $enquiry->submitted_by_signature;
        $enquiry->further_request = $values['further_request'] ?? $enquiry->further_request;
        $enquiry->statement_of_conformity = $values['statement_of_conformity'] ?? $enquiry->statement_of_conformity;
        $enquiry->submitted_by_title = $values['submitted_by_title'] ?? $enquiry->submitted_by_title;
        $enquiry->submitted_by_date = $values['submitted_by_date'] ?? $values['date_of_submission'] ?? $enquiry->submitted_by_date;
        $enquiry->unique_identification = $values['unique_identification'] ?? $enquiry->unique_identification;
        $enquiry->safety_precautions = $values['safety_precautions'] ?? $enquiry->safety_precautions;
        $enquiry->safety_precautions_mention = $values['safety_precautions_mention'] ?? $enquiry->safety_precautions_mention;

        $collectionData = $this->buildCollectionData($values);
        if ($collectionData !== []) {
            $enquiry->collection_data = $collectionData;
        }

        if ($this->collectionDataHasValues($collectionData)) {
            $enquiry->request_for_sampling = true;
        }
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function buildCollectionData(array $values): array
    {
        $data = [];

        foreach (self::COLLECTION_DATA_KEYS as $key) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            if ($key === 'sampling_apparatus') {
                $data[$key] = $this->normalizeSamplingApparatusValue($value);
                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    private function normalizeSamplingApparatusValue(mixed $value): mixed
    {
        $tokens = is_array($value)
            ? $value
            : array_filter(array_map('trim', explode(',', (string) $value)));

        if ($tokens === []) {
            return $value;
        }

        $resolved = array_map(fn ($token) => $this->formatApparatusLabel((string) $token), $tokens);

        return is_array($value) ? $resolved : implode(', ', $resolved);
    }

    private function formatApparatusLabel(string $token): string
    {
        if ($token === 'thermometer_ams_c_ins_116') {
            return 'Thermometer ID AMS/C/INS/116';
        }

        return ucwords(str_replace('_', ' ', $token));
    }

    /**
     * @param  array<string, mixed>  $collectionData
     */
    private function collectionDataHasValues(array $collectionData): bool
    {
        return $collectionData !== [];
    }

    private function syncSampleLines(SampleSubmissionRequest $enquiry, SubmissionFormInstance $instance): void
    {
        $lines = $this->sampleLineService->linesForInstance($instance);
        $payload = [];
        $totalQty = 0;

        foreach ($lines as $line) {
            $qty = max(1, (int) ($line['number_of_samples'] ?? 1));
            $totalQty += $qty;

            $row = [
                'sort_order' => $line['row_index'] ?? count($payload),
                'sample_description' => $line['sample_description'] ?? null,
                'parameter_category' => $line['parameter_category'] ?? null,
                'sample_id' => $line['customer_sample_id'] ?? null,
                'sample_type_id' => $line['sample_type_id'] ?? null,
                'sample_type_name' => $line['sample_type_name'] ?? null,
                'analysis_type_id' => $line['analysis_type_id'] ?? null,
                'analysis_type_name' => $line['analysis_type_name'] ?? null,
                'analysis_element_id' => $line['analysis_element_id'] ?? null,
                'parameter_label' => $line['parameter_label'] ?? null,
                'number_of_samples' => $qty,
                'sample_condition' => $line['sample_condition'] ?? null,
                'state_of_sample' => $line['state_of_sample'] ?? null,
                'sampling_point' => $line['sampling_point'] ?? null,
                'location' => $line['location'] ?? null,
                'production_date' => $line['production_date'] ?? null,
                'expiration_date' => $line['expiration_date'] ?? null,
                'batch_number' => $line['batch_number'] ?? null,
                'picture_of_samples' => $line['picture_of_samples'] ?? null,
            ];

            if (! empty($line['attributes']) && is_array($line['attributes'])) {
                $row['attributes'] = $line['attributes'];
            }

            $payload[] = $row;
        }

        $enquiry->sample_lines = $payload;

        if ($payload !== []) {
            $first = $payload[0];
            $enquiry->sample_type_id = $first['sample_type_id'] ?? $enquiry->sample_type_id;
            $enquiry->matrix_id = $first['analysis_type_id'] ?? $enquiry->matrix_id;
            $enquiry->sample_id = $first['sample_id'] ?? $enquiry->sample_id;
            $enquiry->number_of_samples = $totalQty > 0 ? $totalQty : count($payload);
        }
    }

    private function syncRequestedAnalyses(SampleSubmissionRequest $enquiry, SubmissionFormInstance $instance): void
    {
        $lines = $this->sampleLineService->linesForInstance($instance);
        $parameterIds = [];

        $enquiry->requestedAnalyses()->delete();

        foreach ($lines as $line) {
            $elementId = (string) ($line['analysis_element_id'] ?? '');
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
            $sampleTypeId = (string) ($line['sample_type_id'] ?? '');

            if ($elementId === '' && $analysisTypeId === '') {
                continue;
            }

            if ($elementId !== '') {
                $parameterIds[] = $elementId;
            }

            $label = (string) ($line['parameter_label'] ?? 'Parameter');
            if ($elementId !== '') {
                $element = AnalysisElements::query()->with('analyte')->find($elementId);
                if ($element !== null) {
                    $label = (string) ($element->analyte->name ?? $label);
                }
            }

            SampleSubmissionRequestRequestedAnalysis::query()->create([
                'sample_submission_request_id' => $enquiry->id,
                'sample_type_id' => $sampleTypeId !== '' ? $sampleTypeId : null,
                'analysis_type_id' => $analysisTypeId !== '' ? $analysisTypeId : null,
                'analysis_element_id' => $elementId !== '' ? $elementId : null,
                'analysis_key' => $elementId !== '' ? $elementId : $analysisTypeId,
                'analysis_label' => $label,
                'number_of_samples' => max(1, (int) ($line['number_of_samples'] ?? 1)),
            ]);
        }

        $enquiry->parameter_ids = array_values(array_unique($parameterIds));
    }

    /**
     * @return array<string, mixed>
     */
    private function indexedValues(SubmissionFormInstance $instance): array
    {
        $values = [];

        foreach ($instance->values as $row) {
            $name = (string) ($row->element->name ?? '');
            if ($name === '') {
                continue;
            }

            if ($row->array_index === null) {
                $values[$name] = $row->value;
            }
        }

        return $values;
    }
}
