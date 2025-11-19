<?php

namespace App\Services\Renderers;

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
            return $pdf->output();
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate PDF: ' . $e->getMessage());
        }
    }
}




