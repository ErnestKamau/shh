<?php

namespace App\Services\Commercial;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\SampleType;
use App\Services\Lab\AnalysisReferenceLabelResolver;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Support\Str;

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

    public function headerSampleTypeLabel(SampleSubmissionRequest $enquiry): string
    {
        $enquiry->loadMissing([
            'submissionFormInstance.submissionForm.sampleTypes',
        ]);

        if ($enquiry->submissionFormInstance !== null) {
            $sampleTypeId = $enquiry->submissionFormInstance->resolveSelectedSampleTypeId();
            if ($sampleTypeId !== null) {
                $name = SampleType::query()->find($sampleTypeId)?->name;
                if (is_string($name) && $name !== '') {
                    return $name;
                }
            }

            foreach ($enquiry->submissionFormInstance->submissionForm?->sampleTypes ?? [] as $sampleType) {
                $name = trim((string) ($sampleType->name ?? ''));
                if ($name !== '') {
                    return $name;
                }
            }
        }

        $lines = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
        if ($lines !== []) {
            $label = $this->resolveTrfSampleTypeLabel($lines[0]);

            return $label !== '—' ? $label : '—';
        }

        if ($enquiry->sample_type_id) {
            $name = SampleType::query()->find($enquiry->sample_type_id)?->name;
            if (is_string($name) && $name !== '') {
                return $name;
            }
        }

        return '—';
    }

    /**
     * @return list<array{
     *     sample_description: string,
     *     sample_description_html: string,
     *     qty: string,
     *     analysis_types: string,
     *     tests_requested: string,
     *     tests_requested_count: int
     * }>
     */
    public function sampleRows(SampleSubmissionRequest $enquiry): array
    {
        $enquiry->loadMissing([
            'submissionFormInstance.submissionForm.sampleTypes',
            'requestedAnalyses',
        ]);

        $lines = [];

        if ($enquiry->submissionFormInstance !== null) {
            $lines = $this->sampleLineService->linesForInstance($enquiry->submissionFormInstance);
        }

        if ($lines === []) {
            $lines = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
        }

        $rows = [];

        foreach ($this->groupLinesByTrfRow($lines) as $group) {
            $primary = $group[0];
            $description = $primary['sample_description'] ?? null;
            $testsRequested = $this->resolveTestsRequestedParameters($group);
            if ($testsRequested['label'] === '—') {
                $testsRequested = $this->resolveTestsRequestedFromEnquiryAnalyses($enquiry);
            }

            $rows[] = [
                'sample_description' => $this->formatRichTextPlain($description),
                'sample_description_html' => $this->formatRichTextHtml($description),
                'qty' => $this->formatLineQuantity($primary),
                'analysis_types' => $this->resolveAnalysisTypesLabel($group),
                'tests_requested' => $testsRequested['label'],
                'tests_requested_count' => $testsRequested['count'],
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{label: string}>
     */
    public function requestedTests(SampleSubmissionRequest $enquiry): array
    {
        $enquiry->loadMissing('requestedAnalyses');

        $tests = [];

        foreach ($enquiry->requestedAnalyses as $analysis) {
            $elementId = trim((string) ($analysis->analysis_element_id ?? ''));
            if ($elementId === '') {
                $elementId = trim((string) ($analysis->analysis_key ?? ''));
            }

            if ($elementId !== '' && Str::isUuid($elementId)) {
                $code = $this->resolveElementParameterCode($elementId);
                if ($code !== '' && ! $this->isTestCategoryLabel($code)) {
                    $tests[] = ['label' => $code];

                    continue;
                }
            }

            $label = trim((string) ($analysis->analysis_label ?? ''));
            if ($label === '') {
                continue;
            }

            foreach ($this->referenceLabelResolver->extractTokens($label) as $token) {
                $code = Str::isUuid($token)
                    ? $this->resolveElementParameterCode($token)
                    : $this->resolveParameterCodeFromToken(
                        $token,
                        (string) ($analysis->analysis_type_id ?? ''),
                    );

                if ($code !== '' && ! $this->isTestCategoryLabel($code)) {
                    $tests[] = ['label' => $code];
                }
            }
        }

        $seen = [];
        $unique = [];
        foreach ($tests as $test) {
            if (isset($seen[$test['label']])) {
                continue;
            }

            $seen[$test['label']] = true;
            $unique[] = $test;
        }

        return $unique;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<list<array<string, mixed>>>
     */
    private function groupLinesByTrfRow(array $lines): array
    {
        if ($lines === []) {
            return [];
        }

        $groups = [];

        foreach ($lines as $line) {
            $rowIndex = (int) ($line['row_index'] ?? $line['sort_order'] ?? count($groups));
            $groups[$rowIndex][] = $line;
        }

        ksort($groups);

        return array_values($groups);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveTrfSampleTypeLabel(array $line): string
    {
        $sampleTypeId = $line['sample_type_id'] ?? null;
        if (is_string($sampleTypeId) && $sampleTypeId !== '' && Str::isUuid($sampleTypeId)) {
            $resolved = SampleType::query()->find($sampleTypeId)?->name;
            if (is_string($resolved) && $resolved !== '') {
                return $resolved;
            }
        }

        $name = trim((string) ($line['sample_type_name'] ?? ''));
        if ($name !== '') {
            if (str_contains($name, ' — ')) {
                return trim((string) Str::before($name, ' — '));
            }

            return $name;
        }

        $analysisTypeId = $line['analysis_type_id'] ?? null;
        if (is_string($analysisTypeId) && $analysisTypeId !== '' && Str::isUuid($analysisTypeId)) {
            $analysisType = AnalysisType::query()->find($analysisTypeId);
            if ($analysisType?->sample_type_id) {
                $resolved = SampleType::query()->find($analysisType->sample_type_id)?->name;
                if (is_string($resolved) && $resolved !== '') {
                    return $resolved;
                }
            }
        }

        return '—';
    }

    /**
     * @param  list<array<string, mixed>>  $group
     */
    private function resolveAnalysisTypesLabel(array $group): string
    {
        $labels = [];

        foreach ($group as $line) {
            $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $foodSampleType = trim((string) ($attributes['food_sample_type'] ?? ''));
            if ($foodSampleType !== '') {
                $labels[] = $foodSampleType;
            }

            $analysisName = trim((string) ($line['analysis_type_name'] ?? ''));
            if ($analysisName !== '' && $analysisName !== $foodSampleType) {
                $labels[] = $analysisName;
            }

            $analysisTypeId = $line['analysis_type_id'] ?? null;
            if (is_string($analysisTypeId) && $analysisTypeId !== '' && Str::isUuid($analysisTypeId)) {
                $resolved = AnalysisType::query()->find($analysisTypeId)?->name;
                if (is_string($resolved) && $resolved !== '' && ! in_array($resolved, $labels, true)) {
                    $labels[] = $resolved;
                }
            }
        }

        $labels = array_values(array_unique(array_filter($labels)));

        return $labels !== [] ? implode(', ', $labels) : '—';
    }

    /**
     * @param  list<array<string, mixed>>  $group
     * @return array{label: string, count: int}
     */
    private function resolveTestsRequestedParameters(array $group): array
    {
        $codes = [];

        foreach ($group as $line) {
            $scopedAnalysisTypeId = $this->scopedAnalysisTypeIdForLine($line);

            foreach ($this->extractElementIdsFromLine($line) as $elementId) {
                $code = $this->resolveElementParameterCode($elementId);
                if ($code !== '' && ! $this->isTestCategoryLabel($code)) {
                    $codes[] = $code;
                }
            }

            foreach ($this->extractParameterTokensFromLine($line, $this->extractElementIdsFromLine($line) === []) as $token) {
                $code = Str::isUuid($token)
                    ? $this->resolveElementParameterCode($token)
                    : $this->resolveParameterCodeFromToken($token, $scopedAnalysisTypeId);

                if ($code !== '' && ! $this->isTestCategoryLabel($code)) {
                    $codes[] = $code;
                }
            }
        }

        $codes = array_values(array_unique(array_filter($codes)));

        return [
            'label' => $codes !== [] ? implode(', ', $codes) : '—',
            'count' => count($codes),
        ];
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    private function extractParameterTokensFromLine(array $line, bool $includeParameterLabel = true): array
    {
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        $sources = [
            $line['parameters'] ?? null,
            $attributes['parameters'] ?? null,
        ];

        if ($includeParameterLabel) {
            $sources[] = $line['parameter_label'] ?? null;
        }

        $tokens = [];
        foreach ($sources as $raw) {
            if ($raw === null || $raw === '') {
                continue;
            }

            $tokens = array_merge($tokens, $this->referenceLabelResolver->extractTokens($raw));
        }

        return array_values(array_unique(array_filter(array_map('trim', $tokens))));
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function scopedAnalysisTypeIdForLine(array $line): string
    {
        $analysisTypeId = trim((string) ($line['analysis_type_id'] ?? ''));
        if ($analysisTypeId !== '' && Str::isUuid($analysisTypeId)) {
            return $analysisTypeId;
        }

        $foodLabel = trim((string) ($line['attributes']['food_sample_type'] ?? ''));
        $sampleTypeId = trim((string) ($line['sample_type_id'] ?? ''));
        if ($foodLabel === '' || $sampleTypeId === '' || ! Str::isUuid($sampleTypeId)) {
            return '';
        }

        $resolved = AnalysisType::query()
            ->where('sample_type_id', $sampleTypeId)
            ->where('name', $foodLabel)
            ->value('id');

        return $resolved ? (string) $resolved : '';
    }

    private function resolveElementParameterCode(string $elementId): string
    {
        $element = AnalysisElements::query()->with('analyte')->find($elementId);
        if ($element === null) {
            return '';
        }

        $analyteCode = trim((string) ($element->analyte?->code ?? ''));
        if ($analyteCode !== '') {
            return $analyteCode;
        }

        $method = trim((string) ($element->method ?? ''));

        return $method;
    }

    private function resolveParameterCodeFromToken(string $token, string $scopedAnalysisTypeId = ''): string
    {
        $token = trim($token);
        if ($token === '') {
            return '';
        }

        if (Str::isUuid($token)) {
            return $this->resolveElementParameterCode($token);
        }

        $element = $this->findAnalysisElementForParameterToken($token, $scopedAnalysisTypeId)
            ?? ($scopedAnalysisTypeId !== '' ? $this->findAnalysisElementForParameterToken($token, '') : null);

        if ($element !== null) {
            return $this->resolveElementParameterCode((string) $element->id);
        }

        return '';
    }

    private function findAnalysisElementForParameterToken(string $token, string $scopedAnalysisTypeId): ?AnalysisElements
    {
        $normalized = mb_strtolower(trim($token));

        $query = AnalysisElements::query()->with('analyte')
            ->whereHas('analyte', function ($analyteQuery) use ($normalized): void {
                $analyteQuery
                    ->whereRaw('LOWER(name) = ?', [$normalized])
                    ->orWhereRaw('LOWER(code) = ?', [$normalized]);
            });

        if ($scopedAnalysisTypeId !== '') {
            $query->where('analysis_type_id', $scopedAnalysisTypeId);
        }

        return $query->first();
    }

    /**
     * @return array{label: string, count: int}
     */
    private function resolveTestsRequestedFromEnquiryAnalyses(SampleSubmissionRequest $enquiry): array
    {
        $codes = [];

        foreach ($enquiry->requestedAnalyses as $analysis) {
            $elementId = trim((string) ($analysis->analysis_element_id ?? ''));
            if ($elementId === '') {
                $elementId = trim((string) ($analysis->analysis_key ?? ''));
            }

            if ($elementId !== '' && Str::isUuid($elementId)) {
                $code = $this->resolveElementParameterCode($elementId);
                if ($code !== '' && ! $this->isTestCategoryLabel($code)) {
                    $codes[] = $code;
                }

                continue;
            }

            $label = trim((string) ($analysis->analysis_label ?? ''));
            foreach ($this->referenceLabelResolver->extractTokens($label) as $token) {
                $code = $this->resolveParameterCodeFromToken(
                    $token,
                    (string) ($analysis->analysis_type_id ?? ''),
                );
                if ($code !== '' && ! $this->isTestCategoryLabel($code)) {
                    $codes[] = $code;
                }
            }
        }

        $codes = array_values(array_unique(array_filter($codes)));

        return [
            'label' => $codes !== [] ? implode(', ', $codes) : '—',
            'count' => count($codes),
        ];
    }

    private function isTestCategoryLabel(string $label): bool
    {
        return in_array(strtolower(trim($label)), [
            'microbiology',
            'chemistry',
            'chemical',
            'chemical analysis',
            'legionella',
        ], true);
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    private function extractElementIdsFromLine(array $line): array
    {
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        $fromAttributes = $attributes['analysis_element_ids'] ?? [];

        if (is_array($fromAttributes) && $fromAttributes !== []) {
            return array_values(array_filter(array_map('strval', $fromAttributes)));
        }

        $elementId = trim((string) ($line['analysis_element_id'] ?? ''));
        if ($elementId !== '') {
            return [$elementId];
        }

        $parameter = trim((string) ($line['parameter_label'] ?? ''));
        if ($parameter === '') {
            return [];
        }

        return array_values(array_filter(
            $this->referenceLabelResolver->extractTokens($parameter),
            fn (string $token): bool => Str::isUuid($token),
        ));
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
