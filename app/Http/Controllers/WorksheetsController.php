<?php

namespace App\Http\Controllers;

use App\SampleHeader;
use App\Models\Procedures\ProcedureWorksheet;
use App\Services\ProcedureWorksheetPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class WorksheetsController extends Controller
{
    public function index(int $batchId): View
    {
        $batch = SampleHeader::findOrFail($batchId);
        
        return view('worksheets.index', [
            'batch' => $batch,
        ]);
    }

    /**
     * Temporary route for designing the procedure worksheet PDF template.
     * Streams the generated PDF directly to the browser without saving/attaching.
     */
    public function previewProcedureWorksheetPdf(
        Request $request,
        int $batchId,
        int $worksheetId,
        ProcedureWorksheetPdfService $pdfService
    ): Response
    {
        $batch = SampleHeader::findOrFail($batchId);
        $worksheet = ProcedureWorksheet::findOrFail($worksheetId);

        // Optional: restrict to a specific set of sample IDs (sample_details.id)
        // passed from the UI so the PDF reflects the same subset as the worksheet tab.
        $sampleIds = [];
        if ($request->filled('samples')) {
            $raw = $request->input('samples', '');
            $parts = is_array($raw) ? $raw : explode(',', (string) $raw);
            $sampleIds = array_values(array_filter(array_map('intval', $parts)));
        }

        // Optional: restrict to a specific parameter/analyte subset.
        // The worksheet UI is per analyte (activeTabs). Filtering here ensures the PDF
        // uses the same captured values as the selected parameter tab.
        $analyteIds = [];
        if ($request->filled('analytes')) {
            $raw = $request->input('analytes', '');
            $parts = is_array($raw) ? $raw : explode(',', (string) $raw);
            $analyteIds = array_values(array_filter(array_map('intval', $parts)));
        }

        // Use the shared data-preparation helper from the PDF service
        // so the preview matches the real generated attachment exactly.
        $viewData = $pdfService->prepareViewDataForPreview($batch, $worksheet, $sampleIds, $analyteIds);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('procedure-worksheets.print.worksheet', $viewData);

        $pdfContent = $pdf->output();

        // Stream inline in the browser instead of prompting a download,
        // so you can see live design changes while editing the template.
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="procedure-worksheet-preview.pdf"',
        ]);
    }
}
