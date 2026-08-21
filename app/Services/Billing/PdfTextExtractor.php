<?php

namespace App\Services\Billing;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Extract plain text from PDF uploads via poppler-utils `pdftotext`.
 * Install on the host: apt install poppler-utils (provides /usr/bin/pdftotext).
 * When unavailable, callers should fall back to Excel import.
 */
class PdfTextExtractor
{
    private const BINARY = 'pdftotext';

    public function isAvailable(): bool
    {
        $path = $this->resolveBinaryPath();

        return $path !== null;
    }

    public function resolveBinaryPath(): ?string
    {
        foreach (['/usr/bin/pdftotext', '/usr/local/bin/pdftotext'] as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        $result = Process::run(['which', self::BINARY]);
        if (! $result->successful()) {
            return null;
        }

        $path = trim($result->output());

        return $path !== '' && is_executable($path) ? $path : null;
    }

    /**
     * @return array{available: bool, binary: ?string, hint: string}
     */
    public function capability(): array
    {
        $binary = $this->resolveBinaryPath();

        return [
            'available' => $binary !== null,
            'binary' => $binary,
            'hint' => $binary !== null
                ? 'PDF import is available via pdftotext.'
                : 'PDF import requires poppler-utils (pdftotext). Install it or use Excel (.xlsx) instead.',
        ];
    }

    /**
     * Layout-preserving text extract suitable for table-like quotation / pricelist PDFs.
     */
    public function extractLayoutText(UploadedFile|string $file): string
    {
        $binary = $this->resolveBinaryPath();
        if ($binary === null) {
            throw new RuntimeException(
                'PDF import requires the pdftotext binary (poppler-utils). Use Excel (.xlsx) import instead.'
            );
        }

        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        if (! is_string($path) || $path === '' || ! is_readable($path)) {
            throw new RuntimeException('Uploaded PDF could not be read.');
        }

        $result = Process::timeout(120)->run([
            $binary,
            '-layout',
            '-enc',
            'UTF-8',
            $path,
            '-',
        ]);

        if (! $result->successful()) {
            $error = trim($result->errorOutput());
            throw new RuntimeException(
                'Unable to extract text from PDF'.($error !== '' ? ': '.$error : '.')
                .' Use Excel (.xlsx) import instead.'
            );
        }

        return (string) $result->output();
    }
}
