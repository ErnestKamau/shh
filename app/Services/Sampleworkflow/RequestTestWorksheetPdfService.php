<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\Services\Billing\QuotationReportService;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

final class RequestTestWorksheetPdfService
{
    public const INTEGRITY_ATTACHMENT_TITLE = 'Sample Integrity & Acceptance Check';

    public const BATCH_ATTACHMENT_TITLE = 'Request Tests Worksheet';

    public function __construct(
        private readonly RequestTestExportDataService $dataService,
        private readonly SampleIntegrityCheckService $integrityCheckService,
        private readonly QuotationReportService $quotationReportService,
    ) {}

    /**
     * Generate the integrity worksheet PDF, keep it in system storage and
     * return the in-app URL used to view it.
     *
     * @param  list<array<string, mixed>>  $testRows
     * @param  array<string, string>  $labSectionNames
     * @param  array<string, string>  $analystNamesById
     */
    public function storeIntegrityPdf(
        SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
        array $testRows,
        array $labSectionNames = [],
        array $analystNamesById = [],
    ): string {
        $payload = $this->dataService->buildFromIntegrityRows(
            $instance,
            $enquiry,
            $testRows,
            $labSectionNames,
            $analystNamesById,
        );

        $path = $this->integrityStoragePath($instance);
        Storage::disk('public')->makeDirectory(dirname($path));
        $this->makePdf($payload)->save(Storage::disk('public')->path($path));
        app(SubmissionFormInstanceDocumentAttachmentService::class)->attachRequestTestWorksheet(
            $instance,
            $path,
            $this->integrityFilename($instance),
            auth()->id() !== null ? (string) auth()->id() : null,
        );

        return $this->integrityViewUrl($instance);
    }

    /**
     * Serve the stored integrity worksheet PDF inline, rebuilding it from saved
     * data when no copy exists yet.
     */
    public function viewStoredIntegrityPdf(SubmissionFormInstance $instance): Response
    {
        $path = $this->integrityStoragePath($instance);
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk->makeDirectory(dirname($path));
            $this->makePdf($this->integrityPayloadFromStoredData($instance))
                ->save($disk->path($path));
        }

        return response($disk->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->integrityFilename($instance).'"',
        ]);
    }

    public function integrityStoragePath(SubmissionFormInstance $instance): string
    {
        return 'request-test-worksheets/integrity-'.$instance->id.'.pdf';
    }

    public function integrityViewUrl(SubmissionFormInstance $instance): string
    {
        return route('submission-forms.instances.integrity-worksheet-pdf', ['instance' => $instance->id]);
    }

    public function integrityFilename(SubmissionFormInstance $instance): string
    {
        $reference = trim((string) ($instance->code ?? $instance->id));

        return 'integrity-check-'.$this->safeFilename($reference).'.pdf';
    }

    /**
     * @return array<string, mixed>
     */
    private function integrityPayloadFromStoredData(SubmissionFormInstance $instance): array
    {
        $enquiry = $instance->sampleSubmissionRequest
            ?? SampleSubmissionRequest::query()
                ->where('submission_form_instance_id', $instance->id)
                ->first();

        $testRows = $enquiry !== null
            ? $this->integrityCheckService->buildTestRows($enquiry, $instance)
            : [];

        $sectionIds = [];
        $analystIds = [];
        foreach ($testRows as $row) {
            foreach ((is_array($row['lab_section_ids'] ?? null) ? $row['lab_section_ids'] : []) as $sectionId) {
                $sectionIds[(string) $sectionId] = true;
            }
            foreach ((is_array($row['analysts_by_lab_section'] ?? null) ? $row['analysts_by_lab_section'] : []) as $assigned) {
                foreach ((is_array($assigned) ? $assigned : []) as $analystId) {
                    $analystIds[(string) $analystId] = true;
                }
            }
        }

        $analystNamesById = $analystIds === []
            ? []
            : User::query()
                ->whereIn('id', array_keys($analystIds))
                ->get(['id', 'name'])
                ->mapWithKeys(static fn (User $user): array => [(string) $user->id => (string) $user->name])
                ->all();

        $payload = $this->dataService->buildFromIntegrityRows(
            $instance,
            $enquiry,
            $testRows,
            $this->dataService->labSectionNamesForIds(array_keys($sectionIds)),
            $analystNamesById,
        );

        return $payload;
    }

    /**
     * Generate the Samples In Lab worksheet PDF, keep it in system storage,
     * register it on the batch Attachments tab, and return the in-app view URL.
     */
    public function storeBatchPdf(SampleHeader $batch): string
    {
        $this->persistBatchPdf($batch);

        return $this->batchViewUrl($batch);
    }

    /**
     * Serve the stored batch worksheet PDF inline.
     * When $regenerate is true (or no file exists), rebuild from current batch data first.
     */
    public function viewStoredBatchPdf(SampleHeader $batch, bool $regenerate = false): Response
    {
        $path = $this->batchStoragePath($batch);
        $disk = Storage::disk('public');

        if ($regenerate || ! $disk->exists($path)) {
            $this->persistBatchPdf($batch);
        }

        return response($disk->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->batchFilename($batch).'"',
        ]);
    }

    public function batchStoragePath(SampleHeader $batch): string
    {
        return 'request-test-worksheets/batch-'.$batch->id.'.pdf';
    }

    public function batchViewUrl(SampleHeader $batch): string
    {
        return route('batch.request-test-worksheet-pdf', ['batch' => $batch->id]);
    }

    public function batchFilename(SampleHeader $batch): string
    {
        $reference = trim((string) ($batch->batch_code ?? $batch->id));

        return 'request-tests-'.$this->safeFilename($reference).'.pdf';
    }

    private function persistBatchPdf(SampleHeader $batch): void
    {
        $path = $this->batchStoragePath($batch);
        Storage::disk('public')->makeDirectory(dirname($path));
        $this->makePdf($this->batchPayload($batch))->save(Storage::disk('public')->path($path));

        app(BatchWorkflowDocumentAttachmentService::class)->attachRequestTestWorksheet(
            $batch,
            $this->batchViewUrl($batch),
            auth()->id() !== null ? (string) auth()->id() : null,
        );
    }

    /**
     * Worksheet PDF always includes a blank Result column for handwritten entry.
     *
     * @return array<string, mixed>
     */
    private function batchPayload(SampleHeader $batch): array
    {
        $payload = $this->dataService->buildFromBatch($batch, includeResultColumn: true);
        foreach ($payload['flat_rows'] as $index => $row) {
            $payload['flat_rows'][$index]['result'] = '';
        }
        foreach ($payload['sections'] as $sectionIndex => $section) {
            foreach (($section['samples'] ?? []) as $sampleIndex => $sample) {
                foreach (($sample['tests'] ?? []) as $testIndex => $test) {
                    $payload['sections'][$sectionIndex]['samples'][$sampleIndex]['tests'][$testIndex]['result'] = '';
                }
            }
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function makePdf(array $payload): DomPdf
    {
        $fontDirectory = storage_path('fonts');
        if (! is_dir($fontDirectory)) {
            mkdir($fontDirectory, 0755, true);
        }

        $branding = $this->quotationReportService->resolveCompanyBranding(true);

        $pdf = Pdf::loadView('sampleworkflow.request-test-worksheet-pdf', [
            'payload' => $payload,
            'branding' => $branding,
            'logoSrc' => $branding['logoSrc'],
            'generatedAt' => now()->format('Y-m-d H:i'),
        ]);

        $dompdf = $pdf->getDomPDF();
        $dompdf->set_option('enable_php', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('defaultFont', 'DejaVu Sans');
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('defaultMediaType', 'print');
        $dompdf->set_option('isFontSubsettingEnabled', true);
        $pdf->setPaper('a4', 'portrait');
        app(\App\Services\Reports\ReportWatermarkService::class)->applyToPdf($pdf);

        return $pdf;
    }

    private function safeFilename(string $value): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?? 'export';
        $safe = trim($safe, '-');

        return $safe !== '' ? $safe : 'export';
    }

}
