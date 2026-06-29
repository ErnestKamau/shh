<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use Illuminate\Support\Str;

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
            'customer_rep_signature',
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
        $enquiry->reporting_language = $this->pickScalar($values, 'reporting_language') ?? $enquiry->reporting_language;
        $enquiry->request_date_of_service = $this->pickScalar($values, 'request_date_of_service') ?? $enquiry->request_date_of_service;
        $enquiry->zone_id = $this->pickUuid($values, 'lab_zone_location', 'zone_id') ?? $enquiry->zone_id;
        $enquiry->request_for_sampling = filter_var($values['request_for_sampling'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $enquiry->mode_of_service_priority = $this->pickScalar($values, 'mode_of_service', 'mode_of_service_priority')
            ?? $enquiry->mode_of_service_priority;
        $enquiry->mode_of_payment = $this->pickScalar($values, 'mode_of_payment') ?? $enquiry->mode_of_payment;
        $enquiry->purpose = $this->pickScalar($values, 'remarks', 'purpose') ?? $enquiry->purpose;
        $enquiry->crm_contact_id = $this->pickUuid($values, 'crm_contact_id') ?? $enquiry->crm_contact_id;
        $enquiry->submitted_by_full_name = $this->pickScalar(
            $values,
            'customer_representative_name',
            'submitted_by_full_name',
            'submitting_personnel',
            'sampled_by',
        ) ?? $enquiry->submitted_by_full_name;
        $enquiry->submitted_by_signature = $this->pickScalar(
            $values,
            'customer_representative_signature',
            'customer_rep_signature',
            'submitted_by_signature',
        ) ?? $enquiry->submitted_by_signature;
        $enquiry->further_request = $this->pickScalar($values, 'further_request') ?? $enquiry->further_request;
        $enquiry->statement_of_conformity = $this->pickScalar($values, 'statement_of_conformity')
            ?? $enquiry->statement_of_conformity;
        $enquiry->submitted_by_title = $this->pickScalar($values, 'submitted_by_title') ?? $enquiry->submitted_by_title;
        $enquiry->submitted_by_date = $this->pickScalar($values, 'submitted_by_date', 'date_of_submission')
            ?? $enquiry->submitted_by_date;
        $enquiry->unique_identification = $this->pickScalar($values, 'unique_identification')
            ?? $enquiry->unique_identification;
        $enquiry->safety_precautions = $this->pickScalar($values, 'safety_precautions') ?? $enquiry->safety_precautions;
        $enquiry->safety_precautions_mention = $this->pickScalar($values, 'safety_precautions_mention')
            ?? $enquiry->safety_precautions_mention;

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
     * @param  array<string, mixed>  $values
     * @param  string  ...$keys
     */
    private function pickScalar(array $values, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $normalized = $this->nullableScalar($values[$key]);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  string  ...$keys
     */
    private function pickUuid(array $values, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $normalized = $this->nullableUuid($values[$key]);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    private function nullableScalar(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $isSequential = array_keys($value) === range(0, count($value) - 1);
            $value = $isSequential ? implode(', ', $value) : implode(', ', array_keys(array_filter($value)));
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function nullableUuid(mixed $value): ?string
    {
        $string = $this->nullableScalar($value);
        if ($string === null) {
            return null;
        }

        return Str::isUuid($string) ? $string : null;
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
