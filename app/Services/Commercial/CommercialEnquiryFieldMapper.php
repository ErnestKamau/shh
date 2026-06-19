<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;

final class CommercialEnquiryFieldMapper
{
    /** @var list<string> */
    public const COLLECTION_DATA_KEYS = [
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

    /**
     * @param  array<string, mixed>  $formData
     */
    public function applyHeaderFieldsFromFormData(SampleSubmissionRequest $enquiry, array $formData): void
    {
        $enquiry->reporting_language = $this->scalar($formData, 'reporting_language') ?? $enquiry->reporting_language;
        $enquiry->request_date_of_service = $this->scalar($formData, 'request_date_of_service') ?? $enquiry->request_date_of_service;
        $enquiry->zone_id = $this->scalar($formData, 'lab_zone_location', 'zone_id') ?? $enquiry->zone_id;
        $enquiry->request_for_sampling = filter_var(
            $this->scalar($formData, 'request_for_sampling') ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
        $enquiry->mode_of_service_priority = $this->scalar($formData, 'mode_of_service', 'mode_of_service_priority')
            ?? $enquiry->mode_of_service_priority;
        $enquiry->mode_of_payment = $this->scalar($formData, 'mode_of_payment') ?? $enquiry->mode_of_payment;
        $enquiry->purpose = $this->scalar($formData, 'remarks', 'purpose') ?? $enquiry->purpose;
        $enquiry->crm_contact_id = $this->scalar($formData, 'crm_contact_id') ?? $enquiry->crm_contact_id;
        $enquiry->submitted_by_full_name = $this->scalar(
            $formData,
            'customer_representative_name',
            'customer_rep_name',
            'submitted_by_full_name',
            'submitting_personnel',
            'sampled_by',
            'contact_person',
        ) ?? $enquiry->submitted_by_full_name;
        $enquiry->submitted_by_signature = $this->scalar(
            $formData,
            'customer_representative_signature',
            'submitted_by_signature',
        ) ?? $enquiry->submitted_by_signature;
        $enquiry->further_request = $this->scalar($formData, 'further_request') ?? $enquiry->further_request;
        $enquiry->statement_of_conformity = $this->scalar($formData, 'statement_of_conformity')
            ?? $enquiry->statement_of_conformity;
        $enquiry->submitted_by_title = $this->scalar($formData, 'submitted_by_title') ?? $enquiry->submitted_by_title;
        $enquiry->submitted_by_date = $this->scalar($formData, 'submitted_by_date', 'date_of_submission')
            ?? $enquiry->submitted_by_date;
        $enquiry->unique_identification = $this->scalar($formData, 'unique_identification')
            ?? $enquiry->unique_identification;
        $enquiry->safety_precautions = $this->scalar($formData, 'safety_precautions') ?? $enquiry->safety_precautions;
        $enquiry->safety_precautions_mention = $this->scalar($formData, 'safety_precautions_mention')
            ?? $enquiry->safety_precautions_mention;

        $collectionData = $this->buildCollectionData($formData);
        if ($collectionData !== []) {
            $enquiry->collection_data = $collectionData;
        }

        if ($collectionData !== []) {
            $enquiry->request_for_sampling = true;
        }
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function applyHeaderFieldsFromIndexedValues(SampleSubmissionRequest $enquiry, array $values): void
    {
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

        if ($collectionData !== []) {
            $enquiry->request_for_sampling = true;
        }
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    public function buildCollectionData(array $source): array
    {
        $data = [];

        foreach (self::COLLECTION_DATA_KEYS as $key) {
            if (! array_key_exists($key, $source)) {
                continue;
            }

            $value = $source[$key];
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
     * @param  array<string, mixed>  $formData
     * @param  string  ...$keys
     */
    private function scalar(array $formData, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $formData)) {
                continue;
            }

            $value = $formData[$key];
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $isSequential = array_keys($value) === range(0, count($value) - 1);
                $value = $isSequential ? implode(', ', $value) : implode(', ', array_keys(array_filter($value)));
            }

            return $value;
        }

        return null;
    }
}
