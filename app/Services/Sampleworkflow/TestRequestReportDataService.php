<?php

namespace App\Services\Sampleworkflow;

use App\BatchLabSectionApprover;
use App\CapturedResult;
use App\Models\SubmissionFormInstance;
use App\SampleAnalysisDates;
use App\SampleDetails;
use App\SampleHeader;
use App\SamplesCategory;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\User;
use Illuminate\Support\Facades\Storage;

class TestRequestReportDataService
{
    public function __construct(
        private readonly TestRequestFormReportDataBuilder $trfReportBuilder,
        private readonly TrfSampleFieldMapper $trfMapper,
    ) {}

    /**
     * @param  array{logoPublicUrlFallback?: bool}  $options
     * @return array<string, mixed>
     */
    public function build(SampleHeader $batch, string $reportNumber, array $options = []): array
    {
        $batch->loadMissing(['customer', 'sample_type', 'samples', 'receivingofficer']);

        $sfi = $this->resolveSubmissionFormInstance($batch);
        $trfPayload = $sfi !== null ? $this->trfReportBuilder->buildFromSubmissionFormInstance($sfi) : null;
        $formData = $sfi !== null
            ? app(\App\Services\SubmissionForm\SubmissionFormValueNormalizer::class)->valuesMapFromInstance($sfi)
            : [];
        $trfRows = $this->trfMapper->sampleRowsFromFormData($formData);
        $normalizedRows = is_array($trfPayload['sampleRows'] ?? null) ? $trfPayload['sampleRows'] : [];

        $samples = SamplesCategory::where('sample_header_id', $batch->id)->get();
        $firstDetail = SampleDetails::where('sample_header_id', $batch->id)->first();

        $analysisDate = SampleAnalysisDates::where('sample_header_id', $batch->id)
            ->orderBy('start_analysis_date', 'ASC')
            ->first();

        [$analysisStartDate, $analysisEndDate] = $this->resolveBatchAnalysisDateRange($batch->id);

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

        $trfCollectionExtras = is_array($collection['collection_extras'] ?? null)
            ? $collection['collection_extras']
            : [
                'date_received' => $this->formatReportDate($formData['date_received'] ?? null),
                'packaging' => $this->scalarValue($formData['packaging'] ?? null),
                'sample_weight' => $this->scalarValue($formData['sample_weight'] ?? null),
                'sample_information' => $this->scalarValue($formData['sample_information'] ?? null),
                'ship_name' => $this->scalarValue($formData['ship_name'] ?? null),
                'port_of_loading' => $this->scalarValue($formData['port_of_loading'] ?? null),
                'port_of_discharge' => $this->scalarValue($formData['port_of_discharge'] ?? null),
                'seal_number' => $this->scalarValue($formData['seal_number'] ?? null),
            ];

        $trfCollectionExtras = [
            'date_received' => $this->firstNonEmptyFromMixed(
                $trfCollectionExtras['date_received'] ?? null,
                $this->formatReportDate($formData['date_received'] ?? null),
            ) ?? '-',
            'packaging' => $this->firstNonEmptyFromMixed($trfCollectionExtras['packaging'] ?? null) ?? '-',
            'sample_weight' => $this->firstNonEmptyFromMixed($trfCollectionExtras['sample_weight'] ?? null) ?? '-',
            'sample_information' => $this->firstNonEmptyFromMixed($trfCollectionExtras['sample_information'] ?? null) ?? '-',
            'ship_name' => $this->firstNonEmptyFromMixed($trfCollectionExtras['ship_name'] ?? null) ?? '-',
            'port_of_loading' => $this->firstNonEmptyFromMixed($trfCollectionExtras['port_of_loading'] ?? null) ?? '-',
            'port_of_discharge' => $this->firstNonEmptyFromMixed($trfCollectionExtras['port_of_discharge'] ?? null) ?? '-',
            'seal_number' => $this->firstNonEmptyFromMixed($trfCollectionExtras['seal_number'] ?? null) ?? '-',
        ];

        $dateReceived = $this->firstNonEmptyFromMixed(
            $trfCollectionExtras['date_received'] ?? null,
            $batch->receipt_date ? date('d/m/Y', strtotime($batch->receipt_date)) : null,
        ) ?? '-';

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

        [$reportLogos, $reportLogo, $companyLogo] = $this->resolveLogos(
            $company,
            (bool) ($options['logoPublicUrlFallback'] ?? false),
        );

        [$approver, $approverUser, $approverRole, $approvalDate, $signatureSrc, $signatureWarning] = $this->resolveApproverSignature($batch);

        $measureUncertaintyByCapturedResultId = app(UncertaintyBudgetResolver::class)
            ->buildMuPercentIndexForCapturedResults(
                CapturedResult::query()
                    ->where('sample_header_id', $batch->id)
                    ->get()
            );

        return [
            'batch' => $batch,
            'samples' => $samples,
            'reportNumber' => $reportNumber,
            'approver' => $approver,
            'approverUser' => $approverUser,
            'approverRole' => $approverRole,
            'approvalDate' => $approvalDate,
            'analysisDate' => $analysisDate,
            'analysisStartDate' => $analysisStartDate,
            'analysisEndDate' => $analysisEndDate,
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
            'dateReceived' => $dateReceived,
            'trfCollectionExtras' => $trfCollectionExtras,
            'attention' => $attention,
            'samplePointByIndex' => $samplePointByIndex,
            'totalPages' => $totalPages,
            'signatureSrc' => $signatureSrc,
            'signatureWarning' => $signatureWarning,
            'measureUncertaintyByCapturedResultId' => $measureUncertaintyByCapturedResultId,
        ];
    }

    private function resolveSubmissionFormInstance(SampleHeader $batch): ?SubmissionFormInstance
    {
        if (! $batch->submission_form_instance_id) {
            return null;
        }

        return SubmissionFormInstance::query()
            ->with(['values.element', 'submissionForm', 'crmCustomer'])
            ->find($batch->submission_form_instance_id);
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
        $positionLabel = $approver ? $approver->getApproverPositionDetails() : '-';
        $approverRole = ($positionLabel !== '' && $positionLabel !== '-')
            ? $positionLabel
            : 'Laboratory Manager';
        $approvalDate = $approver && $approver->approval_date
            ? date('d/m/Y', strtotime((string) $approver->approval_date))
            : date('d/m/Y');

        $signatureSrc = $this->resolveUserSignatureDataUri($approverUser);

        // Keep primary approver identity; fall back only for the signature image.
        if ($signatureSrc === '') {
            foreach ([$batch->approve_user_id ?? null, $batch->verify_user_id ?? null] as $fallbackUserId) {
                if ($fallbackUserId === null || $fallbackUserId === '' || ($approverUser && (string) $fallbackUserId === (string) $approverUser->id)) {
                    continue;
                }

                $fallbackUser = User::find($fallbackUserId);
                $signatureSrc = $this->resolveUserSignatureDataUri($fallbackUser);
                if ($signatureSrc !== '') {
                    break;
                }
            }
        }

        $signatureWarning = ($approverUser !== null && $signatureSrc === '')
            ? 'Approver has no electronic signature on file.'
            : null;

        return [$approver, $approverUser, $approverRole, $approvalDate, $signatureSrc, $signatureWarning];
    }

    private function resolveUserSignatureDataUri(?User $user): string
    {
        if ($user === null) {
            return '';
        }

        $fromElectronicSig = $this->resolveSignatureDataUri($user->electronic_sig ?? null);
        if ($fromElectronicSig !== '') {
            return $fromElectronicSig;
        }

        if (method_exists($user, 'getSignaturePath')) {
            $path = $user->getSignaturePath();
            if (is_string($path) && $path !== '') {
                if (str_starts_with($path, 'data:')) {
                    return $path;
                }

                if (is_readable($path)) {
                    $contents = @file_get_contents($path);
                    if ($contents !== false && $contents !== '') {
                        $mime = @mime_content_type($path) ?: 'image/png';

                        return 'data:'.$mime.';base64,'.base64_encode($contents);
                    }
                }

                $fromPath = $this->resolveSignatureDataUri($path);
                if ($fromPath !== '') {
                    return $fromPath;
                }
            }
        }

        return '';
    }

    private function resolveSignatureDataUri(?string $electronicSig): string
    {
        if ($electronicSig === null || trim($electronicSig) === '') {
            return '';
        }

        if (function_exists('signatureToDataUri')) {
            $dataUri = signatureToDataUri($electronicSig);
            if ($dataUri !== '') {
                return $dataUri;
            }
        }

        $electronicSig = trim($electronicSig);

        if (str_starts_with($electronicSig, 'data:')) {
            return $electronicSig;
        }

        if (str_starts_with($electronicSig, 'http://') || str_starts_with($electronicSig, 'https://')) {
            return $electronicSig;
        }

        // Direct filesystem candidates for personnel-signature uploads/pads
        $decoded = urldecode($electronicSig);
        $relative = ltrim((string) preg_replace('#^.*/storage/#', '', $decoded), '/');
        if ($relative !== '') {
            foreach ([
                storage_path('app/public/'.$relative),
                storage_path('app/'.$relative),
                public_path('storage/'.$relative),
                public_path($relative),
            ] as $candidate) {
                if (! is_readable($candidate)) {
                    continue;
                }

                $contents = @file_get_contents($candidate);
                if ($contents === false || $contents === '') {
                    continue;
                }

                $mime = @mime_content_type($candidate) ?: 'image/png';

                return 'data:'.$mime.';base64,'.base64_encode($contents);
            }
        }

        if ($this->signatureFileIsReadable($electronicSig) && function_exists('imageTobase64')) {
            $encoded = imageTobase64($electronicSig);
            if (is_string($encoded) && str_starts_with($encoded, 'data:')) {
                return $encoded;
            }
        }

        return '';
    }

    private function signatureFileIsReadable(string $electronicSig): bool
    {
        if (function_exists('getCoaApproverSignature')) {
            $path = getCoaApproverSignature($electronicSig);
            if (is_string($path) && $path !== '' && ! str_starts_with($path, 'data:') && is_readable($path)) {
                return true;
            }
        }

        $decoded = urldecode($electronicSig);
        $relative = ltrim((string) preg_replace('#^.*/storage/#', '', $decoded), '/');

        foreach ([
            storage_path('app/public/' . $relative),
            storage_path('app/' . $relative),
            public_path('storage/' . $relative),
        ] as $candidate) {
            if (is_readable($candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve logos configured for Test Report in System Settings → Companies.
     *
     * @return array{0: array<string, array{src: string, show_on_every_page: bool}>, 1: string, 2: string}
     */
    private function resolveLogos(?object $company, bool $allowPublicUrlFallback = false): array
    {
        $reportLogos = [];
        $reportLogo = '';
        $companyLogo = '';

        if ($company === null) {
            return [$reportLogos, $reportLogo, $companyLogo];
        }

        if (! empty($company->logo)) {
            $companyLogo = $this->resolveLogoSrc((string) $company->logo, $allowPublicUrlFallback);
        }

        if (method_exists($company, 'reportLogos')) {
            $assignedLogos = $company->reportLogos()
                ->where(function ($query) {
                    $query->where('report_type', 'test_request_report')
                        ->orWhereNull('report_type')
                        ->orWhere('report_type', '');
                })
                ->orderByRaw("CASE WHEN report_type = 'test_request_report' THEN 0 ELSE 1 END")
                ->get();

            foreach ($assignedLogos as $logo) {
                if (empty($logo->logo_path)) {
                    continue;
                }

                $key = ($logo->position_vertical ?? 'top').'_'.($logo->position_horizontal ?? 'left');
                $isExplicit = ($logo->report_type ?? '') === 'test_request_report';
                if (! $isExplicit && isset($reportLogos[$key])) {
                    continue;
                }

                $src = $this->resolveLogoSrc((string) $logo->logo_path, $allowPublicUrlFallback);
                if ($src === '') {
                    continue;
                }

                $reportLogos[$key] = [
                    'src' => $src,
                    'show_on_every_page' => (bool) ($logo->show_on_every_page ?? true),
                ];

                if ($reportLogo === '' || $isExplicit) {
                    $reportLogo = $src;
                }
            }
        }

        if ($reportLogo === '' && ! empty($company->report_logo)) {
            $src = $this->resolveLogoSrc((string) $company->report_logo, $allowPublicUrlFallback);
            if ($src !== '') {
                $reportLogo = $src;
                $reportLogos['top_left'] = $reportLogos['top_left'] ?? [
                    'src' => $src,
                    'show_on_every_page' => true,
                ];
            }
        }

        if ($reportLogo === '') {
            foreach (array_filter([
                $company->logo ?? null,
                'images/logo-report.png',
                'images/company_logo.png',
            ]) as $candidate) {
                $src = $this->resolveLogoSrc((string) $candidate, $allowPublicUrlFallback);
                if ($src !== '') {
                    $reportLogo = $src;
                    $reportLogos['top_left'] = $reportLogos['top_left'] ?? [
                        'src' => $src,
                        'show_on_every_page' => true,
                    ];
                    if ($companyLogo === '') {
                        $companyLogo = $src;
                    }
                    break;
                }
            }
        }

        if ($companyLogo === '' && $reportLogo !== '') {
            $companyLogo = $reportLogo;
        }

        return [$reportLogos, $reportLogo, $companyLogo];
    }

    private function resolveLogoSrc(string $path, bool $allowPublicUrlFallback = false): string
    {
        $dataUri = $this->pathToDataUri($path);
        if ($dataUri !== '') {
            return $dataUri;
        }

        if (! $allowPublicUrlFallback) {
            return '';
        }

        return $this->pathToPublicUrl($path);
    }

    private function pathToPublicUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            return url('/'.ltrim($path, '/'));
        }

        if (str_starts_with($path, 'images/') || str_starts_with($path, '/images/')) {
            return url('/'.ltrim($path, '/'));
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');
        if ($relative !== '') {
            return url('/storage/'.$relative);
        }

        return '';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveBatchAnalysisDateRange(int|string $batchId): array
    {
        $records = SampleAnalysisDates::where('sample_header_id', $batchId)
            ->get(['start_analysis_date', 'analysis_dates']);

        $startDates = [];
        $endDates = [];

        foreach ($records as $record) {
            $recordStart = $this->normalizeDateValue($record->start_analysis_date ?? null);
            if ($recordStart !== null) {
                $startDates[] = $recordStart;
                $endDates[] = $recordStart;
            }

            $decoded = json_decode((string) $record->analysis_dates, true);
            if (! is_array($decoded)) {
                continue;
            }

            foreach ($decoded as $sectionRange) {
                if (is_array($sectionRange)) {
                    $startDate = $this->normalizeDateValue($sectionRange['start_date'] ?? $sectionRange['start'] ?? null);
                    $endDate = $this->normalizeDateValue($sectionRange['end_date'] ?? $sectionRange['end'] ?? null);

                    if ($startDate !== null) {
                        $startDates[] = $startDate;
                        $endDates[] = $startDate;
                    }
                    if ($endDate !== null) {
                        $endDates[] = $endDate;
                    }

                    continue;
                }

                $singleDate = $this->normalizeDateValue($sectionRange);
                if ($singleDate !== null) {
                    $startDates[] = $singleDate;
                    $endDates[] = $singleDate;
                }
            }
        }

        $startDate = ! empty($startDates) ? min($startDates) : null;
        $endDate = ! empty($endDates) ? max($endDates) : $startDate;

        return [
            $this->formatReportDate($startDate),
            $this->formatReportDate($endDate),
        ];
    }

    private function pathToDataUri(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        $absolute = $this->resolveReadableLogoPath($path);
        if ($absolute === null) {
            return '';
        }

        $contents = @file_get_contents($absolute);
        if ($contents === false || $contents === '') {
            return '';
        }

        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    private function resolveReadableLogoPath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        if (is_readable($path)) {
            return $path;
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        $candidates = [
            public_path($path),
            public_path(ltrim($path, '/')),
            public_path('storage/'.$relative),
            storage_path('app/public/'.$relative),
            storage_path('app/'.$relative),
            base_path('public/'.$relative),
        ];

        try {
            if ($relative !== '' && Storage::disk('public')->exists($relative)) {
                $candidates[] = Storage::disk('public')->path($relative);
            }
        } catch (\Throwable) {
            // continue with filesystem candidates
        }

        foreach (array_unique(array_filter($candidates)) as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
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

    private function normalizeDateValue(mixed $candidate): ?string
    {
        $value = $this->scalarValue($candidate);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
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
