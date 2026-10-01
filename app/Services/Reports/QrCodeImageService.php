<?php

namespace App\Services\Reports;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * SVG QR code images for printed documents and labels (medium error correction keeps short URLs sparse).
 */
class QrCodeImageService
{
    public function svgDataUri(string $content, int $size = 220): string
    {
        if ($content === '' || ! class_exists(QrCode::class)) {
            return '';
        }

        return 'data:image/svg+xml;base64,'.base64_encode(
            QrCode::format('svg')
                ->size($size)
                ->margin(0)
                ->errorCorrection('M')
                ->generate($content)
        );
    }

    /**
     * Local SVG file for an SVG data URI; DomPDF canvas drawing (page scripts) only accepts file paths.
     */
    public function svgFileFromDataUri(string $dataUri): ?string
    {
        $prefix = 'data:image/svg+xml;base64,';
        if (! str_starts_with($dataUri, $prefix)) {
            return null;
        }

        $svg = base64_decode(substr($dataUri, strlen($prefix)), true);
        if ($svg === false || $svg === '') {
            return null;
        }

        $directory = storage_path('app/tmp/qr-codes');
        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return null;
        }

        $path = $directory.'/'.sha1($svg).'.svg';
        if (! is_file($path) && file_put_contents($path, $svg) === false) {
            return null;
        }

        return $path;
    }
}
