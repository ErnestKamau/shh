<?php

namespace App\Services;

use App\AnalysisType;
use App\BatchAttachment;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SubmissionFormPdfService
{
    /**
     * Generate a PDF of the submission form instance and attach it to the batch as a batch attachment.
     * Uses the form's print template (e.g. submission-forms.print.default). On failure, logs and does not throw.
     */
    public function attachSubmissionFormPdfToBatch(
        SubmissionFormInstance $instance,
        SampleHeader $sampleHeader,
        string $attachmentTypeId
    ): void {
        try {
            $submissionForm = $instance->submissionForm;

            $instance->load('submittedBy', 'reviewedBy');

            $submissionForm->load([
                'sections.elementHolders.elements' => function ($query) {
                    $query->orderBy('sort_order');
                }
            ]);

            $templateName = $submissionForm->getPrintTemplateName();

            if (! view()->exists($templateName)) {
                $templateName = 'submission-forms.print.default';
            }

            $customTemplates = ['submission-forms.print.microbiology', 'submission-forms.print.serology'];
            if (in_array($templateName, $customTemplates, true)) {
                $existingValues = $instance->getSubmittedFormData();
                $existingValues['sample_header'] = $this->prepareSampleHeaderForPdf(
                    $existingValues['sample_header'] ?? [],
                    $instance,
                    $sampleHeader
                );
            } else {
                $existingValues = $instance->values()->with('element')->get()->map(function ($v) {
                    return (object) ['element_id' => $v->submission_form_element_id, 'value' => $v->value];
                });
            }

            // Embed logo as base64 so DomPDF can show it without file access or HTTP (no deadlock, no chroot issues)
            $logoSrc = $this->resolveLogoAsDataUri();

            // Tests Required table data (Reported By & Date, Sent By & Date) for microbiology/serology print
            $processedSampleData = in_array($templateName, $customTemplates, true)
                ? $this->buildProcessedSampleData($instance)
                : [];
            $testsRequiredTableGroups = in_array($templateName, $customTemplates, true)
                ? $this->buildTestsRequiredTableGroups($instance)
                : [];

            // Footer: QR code pointing to this PDF's URL (for custom templates only)
            $safeBatchCode = preg_replace('/[^a-zA-Z0-9_-]/', '_', $sampleHeader->batch_code);
            $filename = 'submission-form-' . $instance->id . '-batch-' . $safeBatchCode . '.pdf';
            $footerQrcode = '';
            if (in_array($templateName, $customTemplates, true)) {
                $pdfFileUrl = url('/storage/batch-attachments/' . $filename);
                $footerQrcode = base64_encode(
                    QrCode::format('svg')->size(96)->errorCorrection('H')->generate($pdfFileUrl)
                );
            }

            $viewData = array_merge(
                compact('submissionForm', 'instance', 'existingValues', 'logoSrc', 'processedSampleData', 'testsRequiredTableGroups'),
                ['footerQrcode' => $footerQrcode]
            );

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->loadView($templateName, $viewData);
            $pdfContent = $pdf->output();

            Storage::put('batch-attachments/' . $filename, $pdfContent);

            $attachmentUrl = '/storage/batch-attachments/' . urlencode($filename);

            $attachment = new BatchAttachment();
            $attachment->batch_id = $sampleHeader->id;
            $attachment->uploaded_by = auth()->id();
            $attachment->title = $submissionForm->name;
            $attachment->attachment_type = $attachmentTypeId;
            $attachment->attachment_url = $attachmentUrl;
            $attachment->is_internal = 0;
            $attachment->show_on_coa = 0;
            $attachment->save();

            Log::info('Submission form PDF attached to batch', [
                'batch_id' => $sampleHeader->id,
                'instance_id' => $instance->id,
                'template' => $templateName,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to attach submission form PDF to batch', [
                'instance_id' => $instance->id,
                'batch_id' => $sampleHeader->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Read active company logo from disk and return as a data URI for embedding in PDF (no HTTP, no chroot issues).
     */
    private function resolveLogoAsDataUri(): string
    {
        $fullPath = $this->resolveLogoPath();
        if ($fullPath === '' || ! is_readable($fullPath)) {
            return '';
        }
        $contents = @file_get_contents($fullPath);
        if ($contents === false) {
            return '';
        }
        $mime = $this->mimeTypeFromPath($fullPath);

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    /**
     * Local filesystem path for the active company logo.
     * Logos are stored in storage/app/companies/ and served at /storage/companies/; also try public disk.
     */
    private function resolveLogoPath(): string
    {
        $company = getActiveCompany();
        if (! $company || empty($company->logo)) {
            return '';
        }
        $path = $company->logo;
        // Normalize: if full URL, use path part only
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?? $path;
        }
        $path = ltrim($path, '/');
        $filename = basename($path);

        if ($filename === '' || $filename === $path) {
            return '';
        }

        // 1) Try public disk (storage/app/public/companies/...) for backwards compatibility
        $relative = preg_replace('#^storage/#', '', $path);
        if ($relative !== $path) {
            $fullPath = Storage::disk('public')->path($relative);
            if ($fullPath !== '' && file_exists($fullPath)) {
                return $fullPath;
            }
        }

        // 2) App convention: logos live in storage/app/companies/<filename>
        $fullPath = storage_path('app/companies/' . $filename);
        if (file_exists($fullPath)) {
            return $fullPath;
        }

        return '';
    }

    private function mimeTypeFromPath(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
    }

    /**
     * Build Tests Required table data (analysis rows with Reported By & Date, Sent By & Date) for print templates.
     * Public so FormInstanceController can pass it when rendering custom print template in browser.
     *
     * @return array<int, array{sample_type_name: string, analyses: array<int, array{analysis_type_name: string, sample_count: int, code_range: string, reported_info: string, sent_info: string}>}>
     */
    public function buildProcessedSampleData(SubmissionFormInstance $instance): array
    {
        $instance->load([
            'batches' => function ($q) {
                $q->with(['samples' => function ($q) {
                    $q->orderBy('sample_code', 'asc');
                }, 'sample_type']);
            },
        ]);
        $batches = $instance->batches;

        $groupedSamples = [];
        foreach ($batches as $batch) {
            $sampleTypeId = $batch->sample_type_id;
            $sampleTypeName = $batch->sample_type ? $batch->sample_type->name : 'Unknown Type';
            if (! isset($groupedSamples[$sampleTypeId])) {
                $groupedSamples[$sampleTypeId] = ['name' => $sampleTypeName, 'analyses' => []];
            }
            foreach ($batch->samples ?? [] as $sample) {
                $relations = \App\SampleAnalysisTypeRelation::where('sample_detail_id', $sample->id)->get();
                foreach ($relations as $relation) {
                    $analysisType = \App\AnalysisType::find($relation->analysis_type_id);
                    if ($analysisType) {
                        $aid = $relation->analysis_type_id;
                        if (! isset($groupedSamples[$sampleTypeId]['analyses'][$aid])) {
                            $groupedSamples[$sampleTypeId]['analyses'][$aid] = ['name' => $analysisType->name, 'samples' => []];
                        }
                        $groupedSamples[$sampleTypeId]['analyses'][$aid]['samples'][] = $sample;
                    }
                }
            }
        }

        $processedSampleData = [];
        foreach ($groupedSamples as $sampleTypeId => $sampleTypeData) {
            $processedAnalyses = [];
            foreach ($sampleTypeData['analyses'] as $analysisTypeId => $analysisData) {
                $samples = collect($analysisData['samples'])->unique('id');
                $sampleCodes = $samples->pluck('sample_code')->sort()->values();
                $minCode = $sampleCodes->first();
                $maxCode = $sampleCodes->last();
                $codeRange = $minCode === $maxCode ? (string) $minCode : "{$minCode} - {$maxCode}";

                $sampleIds = $samples->pluck('id')->toArray();
                $results = \App\CapturedResult::whereIn('sample_detail_id', $sampleIds)
                    ->where('analysis_type_id', $analysisTypeId)
                    ->whereNotNull('result')
                    ->with('operator')
                    ->get();
                $reporters = $results->pluck('operator.name')->unique()->filter()->implode(', ');
                $reportDate = $results->max('updated_at');
                $reportedInfo = $reporters ? $reporters . ($reportDate ? ' (' . $reportDate->format('d/m/Y') . ')' : '') : 'Pending';

                $processedAnalyses[] = [
                    'analysis_type_name' => $analysisData['name'],
                    'sample_count' => $samples->count(),
                    'code_range' => $codeRange,
                    'reported_info' => $reportedInfo,
                    'sent_info' => 'Pending',
                ];
            }

            if (empty($processedAnalyses)) {
                $batchForThisType = $batches->where('sample_type_id', $sampleTypeId)->first();
                if ($batchForThisType) {
                    $allSamples = $batchForThisType->samples ? $batchForThisType->samples->unique('id') : collect();
                    $sampleCount = $allSamples->count();
                    $codeRange = 'Pending Assignment';
                    if ($sampleCount > 0) {
                        $sc = $allSamples->pluck('sample_code')->sort()->values();
                        $codeRange = $sc->first() === $sc->last() ? (string) $sc->first() : $sc->first() . ' - ' . $sc->last();
                    } else {
                        $staging = \App\Models\SampleDetailStaging::where('sample_header_id', $batchForThisType->id)->first();
                        if ($staging && isset($staging->data_json['quantity'])) {
                            $sampleCount = (int) $staging->data_json['quantity'];
                        }
                    }
                    $testsRequired = $instance->resolveDisplayValueByName('tests_required')
                        ?: $instance->resolveDisplayValueByName('analysis_elements_select')
                        ?: $instance->resolveDisplayValueByName('analysis_type_id')
                        ?: 'General Analysis';
                    $processedAnalyses[] = [
                        'analysis_type_name' => strip_tags((string) $testsRequired),
                        'sample_count' => $sampleCount,
                        'code_range' => $codeRange,
                        'reported_info' => 'Pending',
                        'sent_info' => 'Pending',
                    ];
                }
            }

            $processedSampleData[] = [
                'sample_type_name' => $sampleTypeData['name'],
                'analyses' => $processedAnalyses,
            ];
        }

        return $processedSampleData;
    }

    /**
     * Rowspan-friendly groups for print: one row per test; shared columns (sample count, lab no, reported, sent).
     *
     * @return array<int, array{sample_type_name: string, rowspan: int, shared_sample_count: int, shared_code_range: string, shared_reported_info: string, shared_sent_info: string, tests: array<int, array{name: string}>}>
     */
    public function buildTestsRequiredTableGroups(SubmissionFormInstance $instance): array
    {
        $fromProcessed = $this->transformProcessedSampleDataToRowspanGroups(
            $this->buildProcessedSampleData($instance)
        );
        if ($fromProcessed !== []) {
            return $fromProcessed;
        }

        return $this->buildTestsRequiredGroupsFromSubmittedFormRows($instance);
    }

    /**
     * @param  array<int, array{sample_type_name: string, analyses: array<int, array<string, mixed>>}>  $processedSampleData
     * @return array<int, array{sample_type_name: string, rowspan: int, shared_sample_count: int, shared_code_range: string, shared_reported_info: string, shared_sent_info: string, tests: array<int, array{name: string}>}>
     */
    private function transformProcessedSampleDataToRowspanGroups(array $processedSampleData): array
    {
        $groups = [];
        foreach ($processedSampleData as $group) {
            $analyses = $group['analyses'] ?? [];
            if ($analyses === []) {
                continue;
            }
            $n = count($analyses);
            $counts = array_map(fn ($a) => (int) ($a['sample_count'] ?? 0), $analyses);
            $sampleCount = max($counts) > 0 ? max($counts) : $n;

            $ranges = collect($analyses)->pluck('code_range')->unique()->filter(fn ($v) => $v !== null && (string) $v !== '')->values();
            $codeRange = $ranges->count() === 1 ? (string) $ranges->first() : $ranges->implode('; ');
            if ($codeRange === '') {
                $codeRange = '—';
            }

            $reported = collect($analyses)->pluck('reported_info')->unique()->values();
            $reportedInfo = $reported->count() === 1 ? (string) $reported->first() : 'Pending';

            $sentInfo = 'Pending';

            $groups[] = [
                'sample_type_name' => (string) ($group['sample_type_name'] ?? ''),
                'rowspan' => $n,
                'shared_sample_count' => $sampleCount,
                'shared_code_range' => $codeRange,
                'shared_reported_info' => $reportedInfo,
                'shared_sent_info' => $sentInfo,
                'tests' => array_map(fn ($a) => ['name' => (string) ($a['analysis_type_name'] ?? '')], $analyses),
            ];
        }

        return $groups;
    }

    /**
     * @return array<int, array{sample_type_name: string, rowspan: int, shared_sample_count: int, shared_code_range: string, shared_reported_info: string, shared_sent_info: string, tests: array<int, array{name: string}>}>
     */
    private function buildTestsRequiredGroupsFromSubmittedFormRows(SubmissionFormInstance $instance): array
    {
        $data = $instance->getSubmittedFormData();
        $details = $data['sample_details'] ?? [];
        $byAnalysisId = [];

        foreach ($details as $row) {
            $raw = $row['analysis_type_id'] ?? $row['analysis_type_ids'] ?? null;
            if ($raw === null || $raw === '') {
                continue;
            }
            $ids = is_array($raw) ? $raw : explode(',', (string) $raw);
            foreach ($ids as $id) {
                $id = (int) trim((string) $id);
                if ($id <= 0) {
                    continue;
                }
                $at = AnalysisType::query()->find($id);
                if ($at) {
                    $byAnalysisId[$at->id] = ['name' => (string) $at->name];
                }
            }
        }

        if ($byAnalysisId === []) {
            return [];
        }

        $tests = collect($byAnalysisId)->sortBy(fn ($t) => $t['name'])->values()->all();
        $n = count($tests);
        $rowCount = max(count($details), 1);

        return [[
            'sample_type_name' => '',
            'rowspan' => $n,
            'shared_sample_count' => $rowCount,
            'shared_code_range' => 'Pending assignment',
            'shared_reported_info' => 'Pending',
            'shared_sent_info' => 'Pending',
            'tests' => $tests,
        ]];
    }

    /**
     * Map alternate signature field names and embed images as data URIs so Dompdf can render them.
     *
     * @param  array<string, mixed>  $header
     * @return array<string, mixed>
     */
    public function prepareSampleHeaderForPdf(array $header, ?SubmissionFormInstance $instance = null, ?SampleHeader $batchHeader = null): array
    {
        if ($instance !== null) {
            $header = $this->mergeInstanceContextIntoSampleHeader($header, $instance);
        }

        if ($batchHeader !== null) {
            $header = $this->mergeBatchReceivingContextIntoSampleHeader($header, $batchHeader);
        } elseif ($instance !== null) {
            $instance->loadMissing('batches');
            $firstBatch = $instance->batches->first();
            if ($firstBatch !== null) {
                $header = $this->mergeBatchReceivingContextIntoSampleHeader($header, $firstBatch);
            }
        }

        foreach ([
            'signature_submission' => ['submission_signature', 'client_submission_signature', 'submitter_signature', 'submit_signature'],
            'signature_reception' => ['reception_signature', 'received_signature', 'receiver_signature', 'lab_reception_signature'],
        ] as $canonical => $aliases) {
            if (empty($header[$canonical])) {
                foreach ($aliases as $alias) {
                    if (! empty($header[$alias])) {
                        $header[$canonical] = $header[$alias];
                        break;
                    }
                }
            }
        }

        if (empty($header['signature_sampling'])) {
            foreach (['sampling_signature', 'sampled_by_signature', 'sampler_signature', 'signature'] as $alias) {
                if (! empty($header[$alias])) {
                    $header['signature_sampling'] = $header[$alias];
                    break;
                }
            }
        }

        foreach ($header as $key => $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }
            $keyLower = Str::lower((string) $key);
            $looksLikeSignatureField = str_contains($keyLower, 'signature')
                || str_contains($value, '/personnel-signature/')
                || str_contains($value, '/submission')
                || str_contains($value, '/signatures/');
            if (! $looksLikeSignatureField) {
                continue;
            }
            if (str_starts_with($value, 'data:image')) {
                continue;
            }
            $dataUri = $this->resolveUrlOrPathToImageDataUri($value);
            if ($dataUri !== '') {
                $header[$key] = $dataUri;

                continue;
            }

            $compact = preg_replace('/\s+/', '', $value);
            if (strlen($compact) > 120 && preg_match('/^[A-Za-z0-9+\/]+=*$/', $compact)) {
                $header[$key] = 'data:image/png;base64,'.$compact;
            }
        }

        return $this->enrichSignaturesFromLinkedUsers($header);
    }

    /**
     * @param  array<string, mixed>  $header
     * @return array<string, mixed>
     */
    private function mergeInstanceContextIntoSampleHeader(array $header, SubmissionFormInstance $instance): array
    {
        if (empty($header['submitted_by']) && $instance->submitted_by) {
            $header['submitted_by'] = (int) $instance->submitted_by;
        }

        $instance->loadMissing('submittedBy');

        $submitBy = isset($header['submit_by']) ? trim((string) $header['submit_by']) : '';
        if ($submitBy === '' && $instance->submittedBy) {
            $header['submit_by'] = $instance->submittedBy->name;
        }

        return $header;
    }

    /**
     * @param  array<string, mixed>  $header
     * @return array<string, mixed>
     */
    private function mergeBatchReceivingContextIntoSampleHeader(array $header, SampleHeader $batch): array
    {
        $ro = $batch->receiving_officer ?? null;
        $roHeader = $header['receiving_officer'] ?? null;
        if (($roHeader === null || $roHeader === '') && $ro !== null && $ro !== '') {
            $header['receiving_officer'] = $ro;
        }

        $ron = $batch->receiving_officer_name ?? null;
        $ronHeader = isset($header['receiving_officer_name']) ? trim((string) $header['receiving_officer_name']) : '';
        if ($ronHeader === '' && $ron !== null && trim((string) $ron) !== '') {
            $header['receiving_officer_name'] = $ron;
        }

        return $header;
    }

    /**
     * When reception/submission/sampling signature images are missing but a numeric user id is present,
     * use the user's stored electronic signature for the PDF.
     *
     * @param  array<string, mixed>  $header
     * @return array<string, mixed>
     */
    private function enrichSignaturesFromLinkedUsers(array $header): array
    {
        if ($this->shouldTryElectronicSigForPdf($header['signature_reception'] ?? null)) {
            $userId = $this->resolveReceptionUserIdFromHeader($header);
            if ($userId !== null) {
                $dataUri = $this->userElectronicSignatureDataUri($userId);
                if ($dataUri !== '') {
                    $header['signature_reception'] = $dataUri;
                }
            }
        }

        if ($this->isPdfSignatureValueEmpty($header['signature_submission'] ?? null)) {
            $userId = $this->resolveNumericUserIdFromHeader($header, [
                'submitted_by',
                'submission_user_id',
                'submit_by',
            ]);
            if ($userId !== null) {
                $dataUri = $this->userElectronicSignatureDataUri($userId);
                if ($dataUri !== '') {
                    $header['signature_submission'] = $dataUri;
                }
            }
        }

        if ($this->isPdfSignatureValueEmpty($header['signature_sampling'] ?? null)) {
            $userId = $this->resolveNumericUserIdFromHeader($header, [
                'sampled_by',
                'sampling_officer',
                'sampling_officer_name',
            ]);
            if ($userId !== null) {
                $dataUri = $this->userElectronicSignatureDataUri($userId);
                if ($dataUri !== '') {
                    $header['signature_sampling'] = $dataUri;
                }
            }
        }

        return $header;
    }

    private function isPdfSignatureValueEmpty(mixed $value): bool
    {
        if (! is_string($value)) {
            return true;
        }

        return trim($value) === '';
    }

    /**
     * True when the PDF should fall back to the user's profile electronic signature (empty value,
     * or a path/URL that could not be embedded as a data URI for Dompdf).
     */
    private function shouldTryElectronicSigForPdf(mixed $value): bool
    {
        if (! is_string($value)) {
            return true;
        }
        $trimmed = trim($value);
        if ($trimmed === '') {
            return true;
        }
        if (str_starts_with($trimmed, 'data:image')) {
            return false;
        }

        return $this->resolveUrlOrPathToImageDataUri($value) === '';
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, string>  $keys
     */
    private function resolveNumericUserIdFromHeader(array $header, array $keys): ?int
    {
        foreach ($keys as $key) {
            $raw = $header[$key] ?? null;
            $id = $this->coerceHeaderValueToUserId($raw);
            if ($id !== null) {
                return $id;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $header
     */
    private function resolveReceptionUserIdFromHeader(array $header): ?int
    {
        $id = $this->resolveNumericUserIdFromHeader($header, [
            'receiving_officer',
            'receiving_officer_name',
            'receive_by',
            'received_by',
        ]);
        if ($id !== null) {
            return $id;
        }

        foreach (['receiving_officer_name', 'receive_by', 'received_by', 'receiving_officer'] as $key) {
            $raw = $header[$key] ?? null;
            if (! is_string($raw)) {
                continue;
            }
            $name = trim($raw);
            if ($name === '' || ctype_digit($name)) {
                continue;
            }
            $userId = \App\User::query()->where('name', $name)->orderBy('id')->value('id');
            if ($userId !== null) {
                return (int) $userId;
            }
        }

        return null;
    }

    private function coerceHeaderValueToUserId(mixed $raw): ?int
    {
        if ($raw === null || $raw === '' || $raw === false) {
            return null;
        }
        if (is_array($raw)) {
            foreach ($raw as $item) {
                $id = $this->coerceHeaderValueToUserId($item);
                if ($id !== null) {
                    return $id;
                }
            }

            return null;
        }
        if (is_int($raw) && $raw > 0) {
            return $raw;
        }
        if (is_float($raw) && $raw > 0 && floor($raw) === $raw) {
            return (int) $raw;
        }
        if (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed !== '' && ctype_digit($trimmed)) {
                $id = (int) $trimmed;

                return $id > 0 ? $id : null;
            }
            if ($trimmed !== '' && is_numeric($trimmed)) {
                $n = (float) $trimmed;
                if ($n > 0 && floor($n) === $n) {
                    return (int) $n;
                }
            }
        }
        if (is_numeric($raw) && ! is_string($raw)) {
            $n = (float) $raw;
            if ($n > 0 && floor($n) === $n) {
                return (int) $n;
            }
        }

        return null;
    }

    private function userElectronicSignatureDataUri(int $userId): string
    {
        $user = \App\User::find($userId);
        if ($user === null) {
            return '';
        }
        $path = $user->electronic_sig;
        if (! is_string($path) || trim($path) === '') {
            return '';
        }

        return $this->resolveUrlOrPathToImageDataUri($path);
    }

    private function resolveUrlOrPathToImageDataUri(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (str_starts_with($value, 'data:image')) {
            return $value;
        }

        $path = $value;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsed) ? $parsed : '';
        }
        $path = ltrim($path, '/');

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'storage/')) {
            $relative = substr($path, strlen('storage/'));
            $fullPath = Storage::disk('public')->path($relative);
            if ($fullPath !== '' && is_readable($fullPath)) {
                return $this->fileToImageDataUri($fullPath);
            }
        }

        $publicPath = public_path($path);
        if (is_readable($publicPath)) {
            return $this->fileToImageDataUri($publicPath);
        }

        $storagePublic = storage_path('app/public/'.$path);
        if (is_readable($storagePublic)) {
            return $this->fileToImageDataUri($storagePublic);
        }

        $publicDiskPath = Storage::disk('public')->path($path);
        if ($publicDiskPath !== '' && is_readable($publicDiskPath)) {
            return $this->fileToImageDataUri($publicDiskPath);
        }

        return '';
    }

    private function fileToImageDataUri(string $fullPath): string
    {
        $contents = @file_get_contents($fullPath);
        if ($contents === false) {
            return '';
        }
        $mime = $this->mimeTypeFromPath($fullPath);

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
