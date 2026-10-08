<?php

namespace App\Services\Sampleworkflow;

use App\Models\TestReportDocument;
use App\SampleHeader;
use App\Services\Reports\QrCodeImageService;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Dompdf\Canvas;
use Dompdf\Dompdf;
use Illuminate\Support\Str;

/**
 * Per-sample Test Report documents: public QR tokens, QR images and stored PDF snapshots.
 */
class SampleTestReportDocumentService
{
    public const FOOTER_QR_PAGES_GLOBAL = 'trrFooterQrStarts';

    private const TOKEN_LENGTH = 12;

    /** Footer QR slot geometry; must match .pdf-footer-qr-slot and the PDF page margins in test_request_report.blade.php. */
    private const FOOTER_QR_SIZE_MM = 18;

    private const FOOTER_QR_RIGHT_MM = 12;

    private const FOOTER_QR_BOTTOM_MM = 4;

    public function __construct(
        private readonly TestRequestReportDataService $reportDataService,
        private readonly QrCodeImageService $qrCodeImages,
    ) {}

    /**
     * Create (or reuse) one official document per sample so each token exists before the PDF is rendered.
     * Re-issuing the same revision + language keeps the token, so QR codes already printed stay valid.
     *
     * @param  iterable<object>  $samples  Report samples; `id` is the sample_details id.
     * @return array<string, TestReportDocument> Keyed by sample_detail_id.
     */
    public function reserveForSamples(
        SampleHeader $batch,
        int $revisionNo,
        string $language,
        string $batchReportNumber,
        iterable $samples,
    ): array {
        $documents = [];

        foreach ($samples as $sample) {
            $sampleId = trim((string) ($sample->id ?? ''));
            if ($sampleId === '' || isset($documents[$sampleId])) {
                continue;
            }

            $document = TestReportDocument::query()->firstOrNew([
                'batch_id' => (string) $batch->id,
                'sample_detail_id' => $sampleId,
                'revision_no' => max(1, $revisionNo),
                'language' => $language,
            ]);

            if (! $document->exists) {
                $document->token = $this->uniqueToken();
            }

            $document->report_number = $this->reportDataService->perSampleReportNumber(
                (string) ($sample->sample_code ?? ''),
                $batchReportNumber,
            );
            $document->is_official = true;
            $document->save();

            $documents[$sampleId] = $document;
        }

        return $documents;
    }

    /**
     * @param  array<string, TestReportDocument>  $documents
     * @return array<string, string> QR image data URIs keyed by sample_detail_id.
     */
    public function qrCodesFor(array $documents): array
    {
        $qrCodes = [];

        foreach ($documents as $sampleId => $document) {
            $qrCode = $this->qrCodeDataUri($document->publicUrl());
            if ($qrCode !== '') {
                $qrCodes[(string) $sampleId] = $qrCode;
            }
        }

        return $qrCodes;
    }

    public function qrCodeDataUri(string $url): string
    {
        return $this->qrCodeImages->svgDataUri($url);
    }

    /**
     * Save a rendered per-sample PDF as a new snapshot and point the document at it.
     */
    public function storePdf(
        TestReportDocument $document,
        SampleHeader $batch,
        DomPdfDocument $pdf,
        ?string $userId = null,
    ): TestReportDocument {
        $batch->loadMissing('customer');

        $customerFolder = preg_replace('/[^A-Za-z0-9\-\_]/', '_', (string) ($batch->customer->name ?? 'customer'));
        $customerFolder = trim((string) $customerFolder, '_') ?: 'customer';

        $safeNumber = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) ($document->report_number ?: 'report')) ?: 'report';
        $filename = 'TR_'.$safeNumber.'-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(3)).'.pdf';

        $relativePath = 'reports/'.$customerFolder.'/samples/'.$filename;
        $absoluteDir = storage_path('app/public/reports/'.$customerFolder.'/samples');

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $pdf->save($absoluteDir.'/'.$filename);

        $document->file_path = $relativePath;
        $document->generated_by = $userId;
        $document->generated_at = now();
        $document->save();

        return $document;
    }

    /**
     * Draw each sample's QR into the footer slot of every page that sample covers.
     * Must run after rendering, or the fixed footer paints over the QR on the last page.
     * Sample blocks record their first page in this global while rendering (test_request_report.blade.php).
     */
    public function drawFooterQrCodes(Dompdf $dompdf): void
    {
        $qrFilesByStartPage = $GLOBALS[self::FOOTER_QR_PAGES_GLOBAL] ?? [];
        unset($GLOBALS[self::FOOTER_QR_PAGES_GLOBAL]);

        if (! is_array($qrFilesByStartPage) || $qrFilesByStartPage === []) {
            return;
        }

        ksort($qrFilesByStartPage);

        $canvas = $dompdf->getCanvas();
        $mmToPt = 72 / 25.4;
        $qrSize = self::FOOTER_QR_SIZE_MM * $mmToPt;
        $qrX = $canvas->get_width() - (self::FOOTER_QR_RIGHT_MM * $mmToPt) - $qrSize;
        $qrY = $canvas->get_height() - (self::FOOTER_QR_BOTTOM_MM * $mmToPt) - $qrSize;

        $canvas->page_script(function (int $pageNumber, int $pageCount, Canvas $canvas) use ($qrFilesByStartPage, $qrX, $qrY, $qrSize): void {
            $qrFile = null;
            foreach ($qrFilesByStartPage as $startPage => $file) {
                if ((int) $startPage > $pageNumber) {
                    break;
                }
                $qrFile = $file;
            }

            if (is_string($qrFile) && is_file($qrFile)) {
                $canvas->image($qrFile, $qrX, $qrY, $qrSize, $qrSize);
            }
        });
    }

    /**
     * Latest issued revision of a sample's report with a stored PDF, English first.
     */
    public function latestOfficialDocumentFor(string $sampleDetailId): ?TestReportDocument
    {
        $documents = TestReportDocument::query()
            ->official()
            ->where('sample_detail_id', $sampleDetailId)
            ->whereNotNull('file_path')
            ->orderByDesc('revision_no')
            ->orderByDesc('generated_at')
            ->get()
            ->filter(static fn (TestReportDocument $document): bool => $document->hasStoredFile());

        if ($documents->isEmpty()) {
            return null;
        }

        $latestRevision = (int) $documents->first()->revision_no;
        $latest = $documents->filter(static fn (TestReportDocument $document): bool => (int) $document->revision_no === $latestRevision);

        return $latest->firstWhere('language', 'en') ?? $latest->first();
    }

    private function uniqueToken(): string
    {
        do {
            $token = Str::random(self::TOKEN_LENGTH);
        } while (TestReportDocument::query()->where('token', $token)->exists());

        return $token;
    }
}
