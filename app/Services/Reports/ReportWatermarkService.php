<?php

namespace App\Services\Reports;

use App\Company;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Dompdf\Dompdf;

class ReportWatermarkService
{
    /**
     * Resolve the company watermark image for HTML preview / PDF templates.
     *
     * Prefers the dedicated watermark upload, then falls back to company logo.
     *
     * @return array{src: string, absolutePath: string|null}
     */
    public function resolve(?Company $company = null, bool $forPdf = false): array
    {
        $company ??= getActiveCompany();

        if (! $company) {
            return ['src' => '', 'absolutePath' => null];
        }

        foreach ($this->candidates($company) as $path) {
            $absolute = $this->absolutePath((string) $path);
            if ($absolute === null) {
                continue;
            }

            if ($forPdf) {
                return [
                    'src' => $this->toDataUri($absolute),
                    'absolutePath' => $absolute,
                ];
            }

            $url = $this->toPublicUrl((string) $path);

            return [
                'src' => $url !== '' ? $url : $this->toDataUri($absolute),
                'absolutePath' => $absolute,
            ];
        }

        return ['src' => '', 'absolutePath' => null];
    }

    public function src(?Company $company = null, bool $forPdf = false): string
    {
        return $this->resolve($company, $forPdf)['src'];
    }

    public function absolutePathFor(?Company $company = null): ?string
    {
        return $this->resolve($company, true)['absolutePath'];
    }

    /**
     * Draw a faint centered watermark on every DomPDF page (draft and final).
     */
    public function applyToDompdf(Dompdf $dompdf, ?Company $company = null): void
    {
        $absolute = $this->absolutePathFor($company);
        if ($absolute === null || ! is_readable($absolute)) {
            return;
        }

        $imagePath = $absolute;

        $canvas = $dompdf->getCanvas();
        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($imagePath): void {
            $pageWidth = $canvas->get_width();
            $pageHeight = $canvas->get_height();

            $targetWidth = $pageWidth * 0.48;
            $size = @getimagesize($imagePath);
            $ratio = ($size && ($size[0] ?? 0) > 0)
                ? (($size[1] ?? 1) / $size[0])
                : 0.45;
            $targetHeight = $targetWidth * $ratio;

            $x = ($pageWidth - $targetWidth) / 2;
            $y = ($pageHeight - $targetHeight) / 2;

            $canvas->set_opacity(0.08);
            $canvas->image($imagePath, $x, $y, $targetWidth, $targetHeight);
            $canvas->set_opacity(1.0);
        });
    }

    /**
     * Apply watermark to a barryvdh DomPDF wrapper after loadView/loadHTML.
     *
     * DomPDF 3 requires pages to exist before page_script runs, so we render first.
     */
    public function applyToPdf(DomPdfWrapper $pdf, ?Company $company = null): DomPdfWrapper
    {
        $pdf->render();
        $this->applyToDompdf($pdf->getDomPDF(), $company);

        return $pdf;
    }

    /**
     * @return list<string>
     */
    private function candidates(Company $company): array
    {
        return array_values(array_filter([
            $company->watermark,
            $company->getReportLogoPath('watermark'),
            $company->logo,
            $company->report_logo,
        ], fn ($path) => is_string($path) && trim($path) !== ''));
    }

    private function absolutePath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (is_readable($path)) {
            return $path;
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        foreach ([
            public_path($path),
            public_path(ltrim($path, '/')),
            public_path('storage/'.$relative),
            storage_path('app/public/'.$relative),
        ] as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function toPublicUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    private function toDataUri(string $absolutePath): string
    {
        $mime = mime_content_type($absolutePath) ?: 'image/png';
        $contents = file_get_contents($absolutePath);
        if ($contents === false) {
            return '';
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
