<?php

namespace App\Services\Commercial;

use App\AnalysisElements;
use App\Models\SampleSubmissionRequest;
use App\Models\SampleSubmissionRequestRequestedAnalysis;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;

final class CommercialEnquirySampleLineSync
{
    public function __construct(
        private SubmissionRequestSampleLineService $sampleLineService,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function syncSampleLines(SampleSubmissionRequest $enquiry, array $lines): void
    {
        $payload = [];
        $totalQty = 0;

        foreach ($lines as $line) {
            $qty = 1;
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
     * @return list<array<string, mixed>>
     */
    public function linesForEnquirySync(SampleSubmissionRequest $enquiry, ?\App\Models\TestRequestFormInstance $trfi, ?\App\Models\SubmissionFormInstance $instance): array
    {
        if ($trfi !== null) {
            return $this->sampleLineService->linesForTrfi($trfi);
        }

        if ($instance !== null) {
            return $this->sampleLineService->linesForInstance($instance);
        }

        return [];
    }
}
