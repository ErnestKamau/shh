<?php

namespace App\Services\Commercial;

use App\AnalysisType;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\SampleType;
use App\Services\Lab\AnalysisReferenceLabelResolver;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;

final class EnquiryReviewDisplayService
{
    /** @var array<string, string> */
    private const COLLECTION_LABELS = [
        'sampling_date' => 'Sampling date',
        'sampling_time' => 'Sampling time',
        'sampling_location' => 'Sampling location',
        'sampling_apparatus' => 'Sampling apparatus',
        'thermometer_id' => 'Thermometer ID',
        'method_of_sampling' => 'Method of sampling',
        'reason_of_collection' => 'Reason of collection',
        'transport_condition' => 'Transport condition',
        'sample_sampling_point_description' => 'Sample & sampling point description',
        'sampling_technique' => 'Sampling technique',
        'sampling_source' => 'Sampling source',
        'sample_physical_state' => 'Sample physical state',
    ];

    /** @var list<array{value: string, label: string}> */
    private const STATEMENT_OF_CONFORMITY_OPTIONS = [
        ['value' => 'yes', 'label' => 'YES'],
        ['value' => 'no', 'label' => 'No'],
        ['value' => 'as_per_contract', 'label' => 'As per Contract'],
        ['value' => 'as_per_email', 'label' => 'As per Email'],
    ];

    public function __construct(
        private SubmissionRequestSampleLineService $sampleLineService,
        private AnalysisReferenceLabelResolver $referenceLabelResolver,
    ) {}

    public function customerName(SampleSubmissionRequest $enquiry): string
    {
        $name = trim((string) ($enquiry->customer?->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $name = trim((string) ($enquiry->submissionFormInstance?->crmCustomer?->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $resolver = app(CommercialEnquiryCustomerResolver::class);

        return $resolver->customerNameFromEnquiry($enquiry);
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function collectionDataRows(SampleSubmissionRequest $enquiry): array
    {
        $data = is_array($enquiry->collection_data) ? $enquiry->collection_data : [];
        $rows = [];

        foreach ($data as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $rows[] = [
                'label' => self::COLLECTION_LABELS[$key] ?? $this->humanizeKey((string) $key),
                'value' => $this->formatCollectionValue((string) $key, $value),
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{value: string, label: string, checked: bool}>
     */
    public function statementOfConformityOptions(SampleSubmissionRequest $enquiry): array
    {
        $selected = strtolower(trim((string) ($enquiry->statement_of_conformity ?? '')));

        return array_map(
            fn (array $option): array => [
                'value' => $option['value'],
                'label' => $option['label'],
                'checked' => $selected !== '' && $selected === $option['value'],
            ],
            self::STATEMENT_OF_CONFORMITY_OPTIONS,
        );
    }

    /**
     * @return list<array{
     *     sample_description: string,
     *     sample_description_html: string,
     *     qty: string,
     *     sample_type: string,
     *     sample_condition: string,
     *     tests: string
     * }>
     */
    public function sampleRows(SampleSubmissionRequest $enquiry): array
    {
        $enquiry->loadMissing([
            'submissionFormInstance.submissionForm.sampleTypes',
        ]);

        $lines = [];

        if ($enquiry->submissionFormInstance !== null) {
            $lines = $this->sampleLineService->linesForInstance($enquiry->submissionFormInstance);
        }

        if ($lines === []) {
            $lines = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
        }

        $rows = [];

        foreach ($lines as $line) {
            $description = $line['sample_description'] ?? null;

            $rows[] = [
                'sample_description' => $this->formatRichTextPlain($description),
                'sample_description_html' => $this->formatRichTextHtml($description),
                'qty' => $this->formatLineQuantity($line),
                'sample_type' => $this->resolveSampleTypeLabel($line),
                'sample_condition' => $this->formatLabel($line['sample_condition'] ?? null) ?: '—',
                'tests' => $this->resolveTestsLabel($line),
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{label: string}>
     */
    public function requestedTests(SampleSubmissionRequest $enquiry): array
    {
        $tests = [];

        foreach ($enquiry->requestedAnalyses as $analysis) {
            $label = trim((string) ($analysis->analysis_label ?? ''));
            if ($label === '') {
                continue;
            }

            $resolved = $this->referenceLabelResolver->resolveMixed($label);
            $tests[] = ['label' => $resolved !== '' ? $resolved : $label];
        }

        return $tests;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveSampleTypeLabel(array $line): string
    {
        $name = trim((string) ($line['sample_type_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        $foodSampleType = trim((string) ($attributes['food_sample_type'] ?? ''));
        if ($foodSampleType !== '') {
            return $foodSampleType;
        }

        $analysisName = trim((string) ($line['analysis_type_name'] ?? ''));
        if ($analysisName !== '') {
            return $analysisName;
        }

        $sampleTypeId = $line['sample_type_id'] ?? null;
        if (is_string($sampleTypeId) && $sampleTypeId !== '') {
            $resolved = SampleType::query()->find($sampleTypeId)?->name;
            if (is_string($resolved) && $resolved !== '') {
                return $resolved;
            }
        }

        $analysisTypeId = $line['analysis_type_id'] ?? null;
        if (is_string($analysisTypeId) && $analysisTypeId !== '') {
            $analysisType = AnalysisType::query()->find($analysisTypeId);
            if ($analysisType?->sample_type_id) {
                $resolved = SampleType::query()->find($analysisType->sample_type_id)?->name;
                if (is_string($resolved) && $resolved !== '') {
                    return $resolved;
                }
            }

            if (is_string($analysisType?->name) && $analysisType->name !== '') {
                return $analysisType->name;
            }
        }

        return '—';
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveTestsLabel(array $line): string
    {
        $parameter = trim((string) ($line['parameter_label'] ?? ''));
        if ($parameter !== '') {
            $resolved = $this->referenceLabelResolver->resolveMixed($parameter);
            if ($resolved !== '') {
                return $resolved;
            }
        }

        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        $testsRequested = trim((string) ($attributes['tests_requested'] ?? $attributes['parameters'] ?? ''));
        if ($testsRequested !== '') {
            $resolved = $this->referenceLabelResolver->resolveMixed($testsRequested);
            if ($resolved !== '') {
                return $resolved;
            }
        }

        $analysisName = trim((string) ($line['analysis_type_name'] ?? ''));
        if ($analysisName !== '') {
            return $analysisName;
        }

        $analysisTypeId = $line['analysis_type_id'] ?? null;
        if (is_string($analysisTypeId) && $analysisTypeId !== '') {
            $resolved = $this->referenceLabelResolver->resolveToken($analysisTypeId);
            if ($resolved !== '') {
                return $resolved;
            }
        }

        return '—';
    }

    /**
     * @param  mixed  $value
     */
    private function formatCollectionValue(string $key, mixed $value): string
    {
        if ($key === 'sampling_apparatus') {
            return $this->formatList($value);
        }

        if ($key === 'transport_condition' || $key === 'method_of_sampling' || $key === 'reason_of_collection') {
            return $this->formatList($value);
        }

        if (is_array($value)) {
            return $this->formatList($value);
        }

        return $this->formatLabel($value);
    }

    /**
     * @param  mixed  $value
     */
    private function formatList(mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map(fn ($item) => $this->formatLabel($item), $value));
        }

        if ($value === null || $value === '') {
            return '';
        }

        $parts = array_filter(array_map('trim', explode(',', (string) $value)));

        return implode(', ', array_map(fn ($item) => $this->formatLabel($item), $parts));
    }

    private function formatLabel(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return ucwords(str_replace('_', ' ', (string) $value));
    }

    private function humanizeKey(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }

    private function customerNameFromFormValues(?SubmissionFormInstance $instance): string
    {
        if ($instance === null) {
            return '';
        }

        $instance->loadMissing('values.element');

        foreach ($instance->values as $row) {
            if ($row->array_index !== null) {
                continue;
            }

            if ((string) ($row->element->name ?? '') !== 'customer_name') {
                continue;
            }

            $name = trim((string) ($row->value ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }

    private function displayCell(mixed $value): string
    {
        $string = trim((string) ($value ?? ''));

        if ($string === '' || strcasecmp($string, 'N/A') === 0) {
            return '—';
        }

        return $string;
    }

    private function formatRichTextPlain(mixed $value): string
    {
        $string = $this->normalizeRichTextInput($value);

        if ($string === '' || strcasecmp($string, 'N/A') === 0) {
            return '—';
        }

        if ($this->containsHtml($string)) {
            $plain = html_entity_decode(strip_tags($string), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $plain = trim(preg_replace('/\s+/u', ' ', $plain) ?? '');

            return $plain !== '' ? $plain : '—';
        }

        return $string;
    }

    private function formatRichTextHtml(mixed $value): string
    {
        $string = $this->normalizeRichTextInput($value);

        if ($string === '' || strcasecmp($string, 'N/A') === 0) {
            return '—';
        }

        if ($this->containsHtml($string)) {
            $clean = strip_tags($string, '<p><br><strong><b><em><i><u><ul><ol><li><span><div>');
            $clean = trim($clean);

            return $clean !== '' ? $clean : '—';
        }

        return nl2br(e($string), false);
    }

    private function normalizeRichTextInput(mixed $value): string
    {
        $string = trim((string) ($value ?? ''));

        if ($string === '') {
            return '';
        }

        $decoded = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($decoded);
    }

    private function containsHtml(string $value): bool
    {
        return $value !== strip_tags($value);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function formatLineQuantity(array $line): string
    {
        $quantity = trim((string) ($line['sample_quantity'] ?? ''));
        $unit = trim((string) ($line['sample_quantity_unit'] ?? ''));

        if ($quantity !== '' && $unit !== '') {
            return $quantity.' '.$unit;
        }

        if ($quantity !== '') {
            return $quantity;
        }

        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        $legacy = trim((string) ($attributes['legacy_qty'] ?? $line['qty'] ?? ''));

        if ($legacy !== '') {
            return $legacy;
        }

        return '—';
    }
}
