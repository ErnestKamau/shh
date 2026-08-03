<?php

namespace App\Services\Reports;

use App\Company;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;

class ReportWatermarkService
{
    /**
     * Watermark opacity used on reports (matches AmSpec Quotation Format reference).
     */
    public const OPACITY = 0.07;

    /**
     * Resolve the company watermark image for HTML preview / PDF templates.
     *
     * Prefers the dedicated watermark upload, then falls back to company logo.
     * Landscape artwork is transposed into a cached portrait variant so it can
     * cover a full A4 portrait page, as in the AmSpec Quotation Format reference.
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

            $oriented = $this->portraitVariant($absolute);

            if ($forPdf) {
                return [
                    'src' => $this->toDataUri($oriented),
                    'absolutePath' => $oriented,
                ];
            }

            $url = $oriented === $absolute
                ? $this->toPublicUrl((string) $path)
                : $this->variantPublicUrl($oriented);

            return [
                'src' => $url !== '' ? $url : $this->toDataUri($oriented),
                'absolutePath' => $oriented,
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

        $size = @getimagesize($imagePath);
        $imageWidth = (int) ($size[0] ?? 0);
        $imageHeight = (int) ($size[1] ?? 0);
        if ($imageWidth < 1 || $imageHeight < 1) {
            return;
        }

        $canvas = $dompdf->getCanvas();
        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($imagePath, $imageWidth, $imageHeight): void {
            $pageWidth = $canvas->get_width();
            $pageHeight = $canvas->get_height();

            // Cover the full page (centered, bleeding off the shorter axis) —
            // matches the AmSpec Quotation Format reference.
            $scale = max($pageWidth / $imageWidth, $pageHeight / $imageHeight);
            $targetWidth = $imageWidth * $scale;
            $targetHeight = $imageHeight * $scale;
            $x = ($pageWidth - $targetWidth) / 2;
            $y = ($pageHeight - $targetHeight) / 2;

            $canvas->set_opacity(self::OPACITY);
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

    /**
     * Rotate landscape artwork 90° clockwise into a cached portrait PNG so the
     * watermark covers an A4 portrait page the same way the AmSpec Quotation
     * Format reference does. Portrait/square images pass through untouched.
     */
    private function portraitVariant(string $absolute): string
    {
        $size = @getimagesize($absolute);
        $width = (int) ($size[0] ?? 0);
        $height = (int) ($size[1] ?? 0);
        if ($width < 1 || $height < 1 || $width <= $height || ! function_exists('imagerotate')) {
            return $absolute;
        }

        $disk = Storage::disk('public');
        $relative = self::variantCacheDirectory().'/'.md5('rotate-cw|'.$absolute.'|'.(string) @filemtime($absolute)).'.png';
        $cached = $disk->path($relative);

        if (! is_readable($cached)) {
            $contents = @file_get_contents($absolute);
            $source = $contents !== false ? @imagecreatefromstring($contents) : false;
            if ($source === false) {
                return $absolute;
            }

            imagepalettetotruecolor($source);
            $transparent = imagecolorallocatealpha($source, 0, 0, 0, 127);
            $rotated = imagerotate($source, -90, $transparent);
            if ($rotated === false) {
                return $absolute;
            }

            imagealphablending($rotated, false);
            imagesavealpha($rotated, true);

            $disk->makeDirectory(self::variantCacheDirectory());
            imagepng($rotated, $cached);
        }

        return is_readable($cached) ? $cached : $absolute;
    }

    private static function variantCacheDirectory(): string
    {
        return 'companies/watermarks/page-variants';
    }

    private function variantPublicUrl(string $absoluteVariantPath): string
    {
        $disk = Storage::disk('public');
        $root = rtrim($disk->path(''), '/');

        if (! str_starts_with($absoluteVariantPath, $root.'/')) {
            return '';
        }

        return $disk->url(ltrim(substr($absoluteVariantPath, strlen($root)), '/'));
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
