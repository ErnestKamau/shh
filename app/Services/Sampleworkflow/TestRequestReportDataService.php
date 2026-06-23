<?php

namespace App\Services\Sampleworkflow;

use App\BatchLabSectionApprover;
use App\Models\TestRequestFormInstance;
use App\SampleAnalysisDates;
use App\SampleDetails;
use App\SampleHeader;
use App\SamplesCategory;
use App\User;

class TestRequestReportDataService
{
    public function __construct(
        private readonly TestRequestFormReportDataBuilder $trfReportBuilder,
        private readonly TrfSampleFieldMapper $trfMapper,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(SampleHeader $batch, string $reportNumber): array
    {
        $batch->loadMissing(['customer', 'sample_type', 'samples', 'receivingofficer']);

        $trfi = $this->resolveTestRequestFormInstance($batch);
        $trfPayload = $trfi !== null ? $this->trfReportBuilder->build($trfi) : null;
        $formData = $trfi !== null && is_array($trfi->form_data) ? $trfi->form_data : [];
        $trfRows = $this->trfMapper->sampleRowsFromFormData($formData);
        $normalizedRows = is_array($trfPayload['sampleRows'] ?? null) ? $trfPayload['sampleRows'] : [];

        $samples = SamplesCategory::where('sample_header_id', $batch->id)->get();
        $firstDetail = SampleDetails::where('sample_header_id', $batch->id)->first();

        $analysisDate = SampleAnalysisDates::where('sample_header_id', $batch->id)
            ->orderBy('start_analysis_date', 'ASC')
            ->first();

        $firstNormalizedRow = $normalizedRows[0] ?? [];
        $firstRawRow = $trfRows[0] ?? [];

        $sampleDescription = $this->firstNonEmptyFromMixed(
            strip_tags($this->scalarValue($batch->description ?? '')),
            $firstNormalizedRow['sample_description'] ?? null,
            $firstRawRow['sample_description'] ?? null,
        ) ?? '-';

        $collection = is_array($trfPayload) && is_array($trfPayload['collection'] ?? null)
            ? $trfPayload['collection']
            : [];

        $containerType = $this->firstNonEmptyFromMixed(
            $this->selectedCheckboxLabels($collection['sampling_apparatus'] ?? null),
            $formData['sampling_apparatus'] ?? null,
            $firstRawRow['container_type'] ?? null,
            $formData['container_type'] ?? null,
        ) ?? '-';

        $sampleWeight = $this->firstNonEmptyFromMixed(
            $firstDetail?->quantity ?? null,
            $this->trfMapper->formatRowQuantity($firstRawRow),
            $firstNormalizedRow['qty'] ?? null,
            $formData['weight'] ?? null,
        ) ?? '-';

        $sampleTemperature = $this->firstNonEmptyFromMixed(
            $batch->condition_quality_sample ?? null,
            $firstRawRow['sample_temp'] ?? null,
            $firstNormalizedRow['sample_temp'] ?? null,
            $formData['sample_temperature'] ?? null,
        ) ?? '-';

        $samplePreservation = $this->firstNonEmptyFromMixed(
            $this->selectedCheckboxLabels($collection['transport_condition'] ?? null),
            $formData['transport_condition'] ?? null,
            $firstRawRow['preservation'] ?? null,
            $formData['sample_preservation'] ?? null,
            $firstRawRow['storage_condition'] ?? null,
        ) ?? '-';

        $mfgDate = $this->formatReportDate(
            $firstDetail?->mfg_date,
            $firstNormalizedRow['production_date'] ?? null,
            $firstRawRow['production_date'] ?? null,
        );

        $expiryDate = $this->formatReportDate(
            $firstDetail?->expiry_date,
            $firstNormalizedRow['expiration_date'] ?? null,
            $firstRawRow['expiration_date'] ?? $firstRawRow['expiry_date'] ?? null,
        );

        $batchLotNo = $this->firstNonEmptyFromMixed(
            $firstDetail?->batch_lot_no ?? null,
            $firstNormalizedRow['batch_number'] ?? null,
            $firstRawRow['batch_number'] ?? null,
        ) ?? '-';

        $attention = $batch->getContactPersonDetail();
        if ($attention === '-' || trim($attention) === '') {
            $attention = $this->firstNonEmptyFromMixed(
                $formData['contact_person'] ?? null,
                is_array($trfPayload) ? ($trfPayload['signatures']['customer_rep_name'] ?? null) : null,
                $formData['customer_representative_name'] ?? null,
            ) ?? '-';
        }

        $samplePointByIndex = [];
        foreach ($samples->values() as $index => $sample) {
            $samplePointByIndex[$index] = $this->firstNonEmptyFromMixed(
                $sample->sample_point_name ?? null,
                $normalizedRows[$index]['sampling_point'] ?? $normalizedRows[$index]['location'] ?? null,
                $trfRows[$index]['sampling_point'] ?? $trfRows[$index]['location'] ?? null,
            ) ?? '-';
        }

        $totalPages = max(1, $samples->count() + 1);
        $company = getActiveCompany();
        $customer = $batch->customer;

        [$reportLogos, $reportLogo, $companyLogo] = $this->resolveLogos($company);

        [$approver, $approverUser, $approverRole, $approvalDate, $signatureSrc, $signatureWarning] = $this->resolveApproverSignature($batch);

        return [
            'batch' => $batch,
            'samples' => $samples,
            'reportNumber' => $reportNumber,
            'approver' => $approver,
            'approverUser' => $approverUser,
            'approverRole' => $approverRole,
            'approvalDate' => $approvalDate,
            'analysisDate' => $analysisDate,
            'company' => $company,
            'customer' => $customer,
            'reportLogo' => $reportLogo,
            'reportLogos' => $reportLogos,
            'companyLogo' => $companyLogo,
            'mfgDate' => $mfgDate,
            'expiryDate' => $expiryDate,
            'batchLotNo' => $batchLotNo,
            'sampleWeight' => $sampleWeight,
            'containerType' => $containerType,
            'sampleTemperature' => $sampleTemperature,
            'samplePreservation' => $samplePreservation,
            'sampleDescription' => $sampleDescription,
            'attention' => $attention,
            'samplePointByIndex' => $samplePointByIndex,
            'totalPages' => $totalPages,
            'signatureSrc' => $signatureSrc,
            'signatureWarning' => $signatureWarning,
        ];
    }

    private function resolveTestRequestFormInstance(SampleHeader $batch): ?TestRequestFormInstance
    {
        if (! $batch->submission_form_instance_id) {
            return null;
        }

        return TestRequestFormInstance::query()
            ->where('submission_form_instance_id', $batch->submission_form_instance_id)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * @return array{0: ?BatchLabSectionApprover, 1: ?User, 2: string, 3: string, 4: string, 5: ?string}
     */
    private function resolveApproverSignature(SampleHeader $batch): array
    {
        $approver = BatchLabSectionApprover::query()
            ->where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Approval')
            ->where('show_report', 1)
            ->where('status', 1)
            ->orderByDesc('approval_date')
            ->first();

        if ($approver === null) {
            $approver = BatchLabSectionApprover::query()
                ->where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Approval')
                ->where('status', 1)
                ->orderByDesc('approval_date')
                ->first();
        }

        if ($approver === null) {
            $approver = BatchLabSectionApprover::query()
                ->where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Verification')
                ->where('status', 1)
                ->orderByDesc('approval_date')
                ->first();
        }

        $approverUser = $approver ? User::find($approver->user_id) : null;
        $approverRole = $approverUser ? ($approverUser->designation ?? 'Laboratory Manager') : 'Laboratory Manager';
        $approvalDate = $approver && $approver->approval_date
            ? date('d/m/Y', strtotime((string) $approver->approval_date))
            : date('d/m/Y');

        $signatureSrc = $this->resolveSignatureDataUri($approverUser?->electronic_sig);
        $signatureWarning = ($approverUser !== null && ($signatureSrc === '' || $signatureSrc === null))
            ? 'Approver has no electronic signature on file.'
            : null;

        return [$approver, $approverUser, $approverRole, $approvalDate, $signatureSrc, $signatureWarning];
    }

    private function resolveSignatureDataUri(?string $electronicSig): string
    {
        if ($electronicSig === null || trim($electronicSig) === '') {
            return '';
        }

        if (str_starts_with($electronicSig, 'data:')) {
            return $electronicSig;
        }

        if (function_exists('getCoaApproverSignature')) {
            $storagePath = getCoaApproverSignature($electronicSig);
            $dataUri = $this->pathToDataUri($storagePath);
            if ($dataUri !== '') {
                return $dataUri;
            }
        }

        return $this->pathToDataUri($electronicSig);
    }

    /**
     * @return array{0: array<string, array{src: string, show_on_every_page: bool}>, 1: string, 2: string}
     */
    private function resolveLogos(?object $company): array
    {
        $reportLogos = [];
        $reportLogo = '';
        $companyLogo = '';

        if ($company === null) {
            return [$reportLogos, $reportLogo, $companyLogo];
        }

        if (! empty($company->logo)) {
            $companyLogo = $this->pathToDataUri((string) $company->logo);
        }

        if (method_exists($company, 'reportLogos')) {
            $assignedLogos = $company->reportLogos()
                ->where('report_type', 'test_request_report')
                ->get();

            foreach ($assignedLogos as $logo) {
                if (empty($logo->logo_path)) {
                    continue;
                }

                $dataUri = $this->pathToDataUri((string) $logo->logo_path);
                if ($dataUri === '') {
                    continue;
                }

                $key = ($logo->position_vertical ?? 'top').'_'.($logo->position_horizontal ?? 'left');
                $reportLogos[$key] = [
                    'src' => $dataUri,
                    'show_on_every_page' => (bool) ($logo->show_on_every_page ?? true),
                ];

                if ($reportLogo === '') {
                    $reportLogo = $dataUri;
                }
            }
        }

        if ($reportLogo === '') {
            foreach (array_filter([
                $company->logo ?? null,
                'images/logo-report.png',
                'images/company_logo.png',
            ]) as $candidate) {
                $dataUri = $this->pathToDataUri((string) $candidate);
                if ($dataUri !== '') {
                    $reportLogo = $dataUri;
                    $reportLogos['top_left'] = ['src' => $dataUri, 'show_on_every_page' => true];
                    if ($companyLogo === '') {
                        $companyLogo = $dataUri;
                    }
                    break;
                }
            }
        }

        return [$reportLogos, $reportLogo, $companyLogo];
    }

    private function pathToDataUri(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        $candidates = [];
        if (! str_starts_with($path, 'http')) {
            if (is_readable($path)) {
                $candidates[] = $path;
            }
            $candidates[] = public_path($path);
            $candidates[] = public_path('storage/'.ltrim($path, '/'));
            $candidates[] = storage_path('app/public/'.ltrim(str_replace('/storage/', '', $path), '/'));
        }

        foreach (array_unique($candidates) as $candidate) {
            if (! is_readable($candidate)) {
                continue;
            }

            $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
            $mime = in_array($ext, ['png'], true)
                ? 'image/png'
                : (in_array($ext, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/png');

            return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($candidate));
        }

        return '';
    }

    private function formatReportDate(mixed ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            $value = $this->scalarValue($candidate);
            if ($value === '') {
                continue;
            }

            try {
                return date('d/m/Y', strtotime($value));
            } catch (\Throwable) {
                continue;
            }
        }

        return '-';
    }

    /**
     * @param  mixed  $checkboxGroup  Normalized checkbox map or raw TRF value
     */
    private function selectedCheckboxLabels(mixed $checkboxGroup): string
    {
        if (! is_array($checkboxGroup)) {
            return $this->scalarValue($checkboxGroup);
        }

        if (array_keys($checkboxGroup) !== range(0, count($checkboxGroup) - 1)) {
            $selected = [];
            foreach ($checkboxGroup as $label => $checked) {
                if ($checked) {
                    $selected[] = (string) $label;
                }
            }

            return implode(', ', $selected);
        }

        return $this->scalarValue($checkboxGroup);
    }

    private function scalarValue(mixed $value): string
    {
        if ($value === null || $value === false) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : '';
        }

        if (is_array($value)) {
            $selected = TestRequestFormReportDataBuilder::normalizeToSelectedList($value);

            return implode(', ', $selected);
        }

        return trim((string) $value);
    }

    private function firstNonEmptyFromMixed(mixed ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $value = $this->scalarValue($candidate);
            if ($value !== '' && $value !== '-') {
                return $value;
            }
        }

        return null;
    }
}
