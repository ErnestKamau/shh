<?php

namespace App\Services\Commercial;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\SampleSubmissionRequest;
use App\Models\SampleSubmissionRequestRequestedAnalysis;
use App\Models\SubmissionFormInstance;
use App\Services\Lab\AnalysisReferenceLabelResolver;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Support\Str;

final class CommercialEnquirySampleLineSync
{
    public function __construct(
        private SubmissionRequestSampleLineService $sampleLineService,
        private AnalysisReferenceLabelResolver $referenceLabelResolver,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function syncSampleLines(SampleSubmissionRequest $enquiry, array $lines): void
    {
        $payload = [];
        $totalQty = 0;

        foreach ($lines as $line) {
            $qty = $this->resolveLineNumberOfSamples($line);
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
                'sample_quantity' => $line['sample_quantity'] ?? null,
                'sample_quantity_unit' => $line['sample_quantity_unit'] ?? null,
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

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function syncRequestedAnalyses(SampleSubmissionRequest $enquiry, array $lines): void
    {
        $parameterIds = [];

        $enquiry->requestedAnalyses()->delete();

        foreach ($lines as $line) {
            $elementIds = $this->resolveLineElementIds($line);

            foreach ($elementIds as $elementId) {
                $parameterIds[] = $elementId;
                $this->createRequestedAnalysisForElement($enquiry, $line, $elementId);
            }

            if ($elementIds !== []) {
                continue;
            }

            $elementId = (string) ($line['analysis_element_id'] ?? '');
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');

            if ($elementId === '' && $analysisTypeId === '') {
                $parameterLabel = trim((string) ($line['parameter_label'] ?? ''));
                if ($parameterLabel === '') {
                    continue;
                }

                $this->createRequestedAnalysisForLabel($enquiry, $line, $parameterLabel);

                continue;
            }

            if ($elementId !== '') {
                $parameterIds[] = $elementId;
            }

            $this->createRequestedAnalysisForElement($enquiry, $line, $elementId !== '' ? $elementId : null, $analysisTypeId);
        }

        $enquiry->parameter_ids = array_values(array_unique($parameterIds));
    }

    /**
     * Persist lab Process Enquiry sample-config edits back onto the enquiry's
     * canonical portal/LIMS parameter stores (sample_lines, requested_analyses, parameter_ids).
     *
     * @param  list<array<string, mixed>>  $sampleConfigs
     */
    public function syncFromSampleConfigs(SampleSubmissionRequest $enquiry, array $sampleConfigs): void
    {
        $lines = [];

        foreach (array_values($sampleConfigs) as $index => $config) {
            $parameterKeys = collect(is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [])
                ->map(fn (mixed $key): string => trim((string) $key))
                ->filter(fn (string $key): bool => $key !== '')
                ->unique()
                ->values()
                ->all();

            $customerSampleId = trim((string) ($config['customer_sample_id'] ?? ''));

            $lines[] = [
                'row_index' => array_key_exists('row_index', $config) && $config['row_index'] !== null
                    ? (int) $config['row_index']
                    : $index,
                'sample_type_id' => $config['sample_type_id'] ?? null,
                'analysis_type_id' => $config['analysis_type_id'] ?? null,
                'analysis_element_id' => $parameterKeys[0] ?? null,
                'parameter_label' => 'Parameter',
                'number_of_samples' => 1,
                'customer_sample_id' => $customerSampleId !== '' ? $customerSampleId : null,
                'sample_condition_id' => $config['sample_condition_id'] ?? null,
                'attributes' => $parameterKeys !== []
                    ? ['analysis_element_ids' => $parameterKeys]
                    : [],
            ];
        }

        $this->syncSampleLines($enquiry, $lines);
        $this->syncRequestedAnalyses($enquiry, $lines);
        $enquiry->save();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function linesForEnquirySync(
        SampleSubmissionRequest $enquiry,
        ?SubmissionFormInstance $instance,
    ): array {
        unset($enquiry);

        if ($instance !== null) {
            return $this->sampleLineService->linesForInstance($instance);
        }

        return [];
    }

    /**
     * One TRF sample card / line = one physical sample.
     * `sample_quantity` + unit are mass/volume, never a sample count.
     *
     * @param  array<string, mixed>  $line
     */
    private function resolveLineNumberOfSamples(array $line): int
    {
        unset($line);

        return 1;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    private function resolveLineElementIds(array $line): array
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

        $parameterLabel = trim((string) ($line['parameter_label'] ?? ''));
        if ($parameterLabel === '') {
            return [];
        }

        return array_values(array_filter(
            $this->referenceLabelResolver->extractTokens($parameterLabel),
            fn (string $token): bool => Str::isUuid($token),
        ));
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function createRequestedAnalysisForElement(
        SampleSubmissionRequest $enquiry,
        array $line,
        ?string $elementId,
        string $analysisTypeId = '',
    ): void {
        $elementId = trim((string) ($elementId ?? ''));
        $analysisTypeId = $analysisTypeId !== '' ? $analysisTypeId : (string) ($line['analysis_type_id'] ?? '');

        $label = (string) ($line['parameter_label'] ?? 'Parameter');
        if ($elementId !== '') {
            $element = AnalysisElements::query()->with('analyte')->find($elementId);
            if ($element !== null) {
                $label = (string) ($element->analyte->name ?? $label);
                // A card can carry several analysis types; the parameter's own type is the truth.
                $ownAnalysisTypeId = trim((string) ($element->analysis_type_id ?? ''));
                if ($ownAnalysisTypeId !== '') {
                    $analysisTypeId = $ownAnalysisTypeId;
                }
            } elseif ($this->looksLikeUuidList($label)) {
                $label = 'Parameter';
            }
        }

        $sampleTypeId = $this->resolveSampleTypeIdForAnalysisType($line, $analysisTypeId);

        $label = $this->referenceLabelResolver->resolveToken($label);

        SampleSubmissionRequestRequestedAnalysis::query()->create([
            'sample_submission_request_id' => $enquiry->id,
            'sample_type_id' => $sampleTypeId !== '' ? $sampleTypeId : null,
            'analysis_type_id' => $analysisTypeId !== '' ? $analysisTypeId : null,
            'analysis_element_id' => $elementId !== '' ? $elementId : null,
            'analysis_key' => $elementId !== '' ? $elementId : $analysisTypeId,
            'analysis_label' => $label !== '' ? $label : 'Parameter',
            'number_of_samples' => max(1, (int) ($line['number_of_samples'] ?? 1)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function createRequestedAnalysisForLabel(
        SampleSubmissionRequest $enquiry,
        array $line,
        string $parameterLabel,
    ): void {
        foreach ($this->referenceLabelResolver->extractTokens($parameterLabel) as $token) {
            if (Str::isUuid($token)) {
                $this->createRequestedAnalysisForElement($enquiry, $line, $token);

                continue;
            }

            $resolved = $this->referenceLabelResolver->resolveToken($token);
            if ($resolved === '' || $resolved === $token) {
                continue;
            }

            SampleSubmissionRequestRequestedAnalysis::query()->create([
                'sample_submission_request_id' => $enquiry->id,
                'sample_type_id' => ($line['sample_type_id'] ?? null) ?: null,
                'analysis_type_id' => ($line['analysis_type_id'] ?? null) ?: null,
                'analysis_element_id' => null,
                'analysis_key' => $token,
                'analysis_label' => $resolved,
                'number_of_samples' => max(1, (int) ($line['number_of_samples'] ?? 1)),
            ]);
        }
    }

    /**
     * Pick the sample type the given analysis type belongs to when the card holds several.
     *
     * @param  array<string, mixed>  $line
     */
    private function resolveSampleTypeIdForAnalysisType(array $line, string $analysisTypeId): string
    {
        $primary = trim((string) ($line['sample_type_id'] ?? ''));
        if ($analysisTypeId === '' || ! Str::isUuid($analysisTypeId)) {
            return $primary;
        }

        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        $candidates = [];
        foreach (is_array($attributes['sample_type_ids'] ?? null) ? $attributes['sample_type_ids'] : [] as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '') {
                $candidates[] = $candidate;
            }
        }

        $owner = trim((string) (AnalysisType::query()->whereKey($analysisTypeId)->value('sample_type_id') ?? ''));
        if ($owner === '') {
            return $primary;
        }

        if ($primary === '' || in_array($owner, $candidates, true)) {
            return $owner;
        }

        return $primary;
    }

    private function looksLikeUuidList(string $label): bool
    {
        $tokens = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $label) ?: [])));
        if ($tokens === []) {
            return false;
        }

        foreach ($tokens as $token) {
            if (! Str::isUuid($token)) {
                return false;
            }
        }

        return true;
    }
}
