<?php

namespace App\Services\Renderers;

use App\Services\Reports\ReportWatermarkService;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfRenderer
{
    /**
     * Generate PDF from HTML.
     */
    public function generatePdf(string $html): string
    {
        try {
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('a4', 'portrait');
            app(ReportWatermarkService::class)->applyToPdf($pdf);

            return $pdf->output();
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate PDF: ' . $e->getMessage());
        }
    }
}
