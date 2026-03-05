<?php

namespace App\Services;

use App\BatchAttachment;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        int $attachmentTypeId
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
                compact('submissionForm', 'instance', 'existingValues', 'logoSrc', 'processedSampleData'),
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

                $sentBy = $instance->submittedBy ? $instance->submittedBy->name : 'N/A';
                $sentDate = $instance->submitted_at ? $instance->submitted_at->format('d/m/Y') : 'N/A';
                $sentInfo = "{$sentBy} ({$sentDate})";

                $processedAnalyses[] = [
                    'analysis_type_name' => $analysisData['name'],
                    'sample_count' => $samples->count(),
                    'code_range' => $codeRange,
                    'reported_info' => $reportedInfo,
                    'sent_info' => $sentInfo,
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
                    $sentBy = $instance->submittedBy ? $instance->submittedBy->name : 'N/A';
                    $sentDate = $instance->submitted_at ? $instance->submitted_at->format('d/m/Y') : 'N/A';
                    $processedAnalyses[] = [
                        'analysis_type_name' => strip_tags((string) $testsRequired),
                        'sample_count' => $sampleCount,
                        'code_range' => $codeRange,
                        'reported_info' => 'Pending',
                        'sent_info' => $sentBy . ' (' . $sentDate . ')',
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
}
