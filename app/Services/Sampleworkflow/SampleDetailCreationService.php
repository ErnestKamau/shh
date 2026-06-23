<?php

namespace App\Services\Sampleworkflow;

use App\SampleDetails;
use App\SampleHeader;

class SampleDetailCreationService
{
    public function __construct(
        private readonly JobSampleNumberingService $numberingService,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(
        SampleHeader $header,
        string $categoryPrefix,
        array $attributes = [],
        ?string $customerSampleId = null,
        int $reportRevision = 1,
    ): SampleDetails {
        $jobNumber = (string) $header->batch_code;

        if ($this->numberingService->isJobNumberFormat($jobNumber)) {
            $sampleCode = $this->numberingService->nextSampleCode($jobNumber, $categoryPrefix);
            $sampleNo = $this->numberingService->sampleNumberFromCode($sampleCode);
            $reportNumber = $this->numberingService->reportNumber($jobNumber, $reportRevision);
        } else {
            $sampleCode = $attributes['sample_code'] ?? $jobNumber;
            $sampleNo = $attributes['sample_no'] ?? '01';
            $reportNumber = $attributes['report_number'] ?? $jobNumber;
        }

        $detailData = array_merge($attributes, [
            'sample_header_id' => $header->id,
            'sample_code' => $sampleCode,
            'sample_no' => $sampleNo,
            'report_number' => $reportNumber,
        ]);

        if ($customerSampleId !== null && trim($customerSampleId) !== '') {
            $detailData['customer_sample_id'] = trim($customerSampleId);
        }

        return SampleDetails::query()->create($detailData);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function createFromTrfRow(
        SampleHeader $header,
        array $row,
        array $attributes = [],
        int $reportRevision = 1,
    ): SampleDetails {
        $prefix = $this->numberingService->resolveCategoryPrefixFromRow($row);
        $customerSampleId = $row['sample_no'] ?? $row['customer_sample_id'] ?? null;

        return $this->create(
            $header,
            $prefix,
            $attributes,
            is_string($customerSampleId) ? $customerSampleId : null,
            $reportRevision,
        );
    }
}
