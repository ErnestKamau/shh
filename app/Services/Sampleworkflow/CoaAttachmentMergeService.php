<?php

namespace App\Services\Sampleworkflow;

use App\Models\TestRequestReportLanguageFile;
use App\SampleHeader;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\TcpdfFpdi;

class CoaAttachmentMergeService
{
    public function resolveTestRequestReportPath(SampleHeader $batch): ?string
    {
        foreach ($this->candidateReportPaths($batch) as $path) {
            if ($path !== '' && is_file($path) && $this->countPdfPages($path) !== null) {
                return $path;
            }
        }

        Log::warning('Test Report PDF is not available for attachment merge', [
            'batch_id' => $batch->id,
            'batch_report_url' => $batch->batch_report_url,
        ]);

        return null;
    }

    /**
     * @deprecated Use resolveTestRequestReportPath()
     */
    public function resolveTestRequestFormPath(SampleHeader $batch): ?string
    {
        return $this->resolveTestRequestReportPath($batch);
    }

    /**
     * @return list<string>
     */
    private function candidateReportPaths(SampleHeader $batch): array
    {
        $candidates = [];

        foreach ($this->reportRelativeUrls($batch) as $relativeUrl) {
            $relativeUrl = '/'.ltrim((string) $relativeUrl, '/');
            $candidates[] = storage_path('app'.$relativeUrl);
            $candidates[] = storage_path('app/public'.$relativeUrl);
            $candidates[] = public_path('storage'.$relativeUrl);
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * @return list<string>
     */
    private function reportRelativeUrls(SampleHeader $batch): array
    {
        $urls = [];

        if (! empty($batch->batch_report_url)) {
            $urls[] = (string) $batch->batch_report_url;
        }

        $languageFiles = TestRequestReportLanguageFile::query()
            ->where('batch_id', $batch->id)
            ->orderByDesc('revision_no')
            ->orderByRaw("CASE language WHEN 'en' THEN 0 ELSE 1 END")
            ->get(['report_url']);

        foreach ($languageFiles as $languageFile) {
            if (! empty($languageFile->report_url)) {
                $urls[] = (string) $languageFile->report_url;
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param  array<int, string>  $orderedPaths
     */
    public function mergeFilesToPdfContent(array $orderedPaths): string
    {
        $pdf = new TcpdfFpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        $validFiles = [];
        $totalPageCount = 0;
        $temporaryPaths = [];

        try {
            foreach ($orderedPaths as $path) {
                if (! is_string($path) || $path === '' || ! is_file($path)) {
                    throw new \RuntimeException('A selected PDF file could not be found on the server.');
                }

                $preparedPath = $this->preparePdfPathForMerge($path, $temporaryPaths);
                if ($preparedPath === null) {
                    throw new \RuntimeException('Could not read PDF for merge: '.basename($path));
                }

                $pageCount = $this->countPdfPages($preparedPath);
                if ($pageCount === null || $pageCount < 1) {
                    throw new \RuntimeException('Could not read PDF pages for merge: '.basename($path));
                }

                $totalPageCount += $pageCount;
                $validFiles[] = ['path' => $preparedPath, 'count' => $pageCount];
            }

            if ($validFiles === []) {
                throw new \RuntimeException('No valid PDF files found to merge.');
            }

            $currentPageGlobal = 1;

            foreach ($validFiles as $fileInfo) {
                $pdf->setSourceFile($fileInfo['path']);

                for ($pageNo = 1; $pageNo <= $fileInfo['count']; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);

                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetDrawColor(255, 255, 255);

                    $coverageWidth = 90;
                    $coverageHeight = 22;

                    $pdf->Rect($size['width'] - 95, $size['height'] - 28, $coverageWidth, $coverageHeight, 'F');
                    $pdf->Rect($size['width'] - 85, $size['height'] - 23, 80, 20, 'F');
                    $pdf->Rect($size['width'] - 75, $size['height'] - 18, 70, 18, 'F');
                    $pdf->Rect($size['width'] - 95, 0, $coverageWidth, $coverageHeight, 'F');
                    $pdf->Rect($size['width'] - 85, 0, 80, 28, 'F');
                    $pdf->Rect($size['width'] - 75, 0, 70, 22, 'F');

                    $bottomCenterX = ($size['width'] / 2) - ($coverageWidth / 2);
                    $pdf->Rect($bottomCenterX, $size['height'] - 28, $coverageWidth, $coverageHeight, 'F');
                    $pdf->Rect(($size['width'] / 2) - 45, $size['height'] - 23, 90, 20, 'F');

                    $topCenterX = ($size['width'] / 2) - ($coverageWidth / 2);
                    $pdf->Rect($topCenterX, 0, $coverageWidth, $coverageHeight, 'F');

                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->Text(($size['width'] / 2) - 15, $size['height'] - 10, "Page {$currentPageGlobal} of {$totalPageCount}");

                    $currentPageGlobal++;
                }
            }

            return $pdf->Output('', 'S');
        } finally {
            $this->cleanupTemporaryPaths($temporaryPaths);
        }
    }

    public function resolvePublicAttachmentPath(string $attachmentUrl): ?string
    {
        $relativePath = urldecode($attachmentUrl);
        $filePath = public_path($relativePath);

        if (! file_exists($filePath)) {
            $cleanPath = ltrim($relativePath, '/');
            if (str_starts_with($cleanPath, 'storage/')) {
                $storageInternalPath = substr($cleanPath, 8);
                $fallbackPath = storage_path('app/public/'.$storageInternalPath);
                if (file_exists($fallbackPath)) {
                    $filePath = $fallbackPath;
                } else {
                    $legacyPath = storage_path('app/'.$storageInternalPath);
                    if (file_exists($legacyPath)) {
                        $filePath = $legacyPath;
                    }
                }
            }
        }

        return file_exists($filePath) ? $filePath : null;
    }

    /**
     * @param  list<string>  $temporaryPaths
     */
    private function preparePdfPathForMerge(string $path, array &$temporaryPaths = []): ?string
    {
        if ($this->countPdfPages($path) !== null) {
            return $path;
        }

        $normalizedPath = $this->normalizePdfForFpdi($path);
        if ($normalizedPath === null) {
            return null;
        }

        $temporaryPaths[] = $normalizedPath;

        if ($this->countPdfPages($normalizedPath) !== null) {
            return $normalizedPath;
        }

        return null;
    }

    private function countPdfPages(string $path): ?int
    {
        try {
            $tempPdf = new TcpdfFpdi();
            $pageCount = $tempPdf->setSourceFile($path);

            return $pageCount > 0 ? $pageCount : null;
        } catch (\Throwable $exception) {
            Log::debug('FPDI could not read PDF', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function normalizePdfForFpdi(string $sourcePath): ?string
    {
        $outputPath = tempnam(sys_get_temp_dir(), 'trr_pdf_');
        if ($outputPath === false) {
            return null;
        }

        $outputPath .= '.pdf';

        $qpdf = $this->findExecutable('qpdf');
        if ($qpdf !== null) {
            $command = sprintf(
                '%s --linearize %s %s 2>/dev/null',
                escapeshellcmd($qpdf),
                escapeshellarg($sourcePath),
                escapeshellarg($outputPath)
            );

            if ($this->runCommand($command) && is_file($outputPath) && filesize($outputPath) > 0) {
                return $outputPath;
            }

            @unlink($outputPath);
        }

        $ghostscript = $this->findExecutable('gs');
        if ($ghostscript !== null) {
            $command = sprintf(
                '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>/dev/null',
                escapeshellcmd($ghostscript),
                escapeshellarg($outputPath),
                escapeshellarg($sourcePath)
            );

            if ($this->runCommand($command) && is_file($outputPath) && filesize($outputPath) > 0) {
                return $outputPath;
            }

            @unlink($outputPath);
        }

        return null;
    }

    private function findExecutable(string $binary): ?string
    {
        $command = sprintf('command -v %s 2>/dev/null', escapeshellarg($binary));
        $path = trim((string) shell_exec($command));

        return $path !== '' ? $path : null;
    }

    private function runCommand(string $command): bool
    {
        $exitCode = 1;
        exec($command, $output, $exitCode);

        return $exitCode === 0;
    }

    /**
     * @param  list<string>  $temporaryPaths
     */
    private function cleanupTemporaryPaths(array $temporaryPaths): void
    {
        foreach ($temporaryPaths as $temporaryPath) {
            if (is_string($temporaryPath) && is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }
}
