<?php

namespace App\Http\Controllers;

use App\SampleHeader;
use App\Services\ProcedureWorksheetPdfService;
use App\Models\Procedures\ProcedureWorksheet;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class WorksheetsController extends Controller
{
    public function index(string $batchId): View
    {
        $batch = SampleHeader::findOrFail($batchId);

        return view('worksheets.index', [
            'batch' => $batch,
        ]);
    }

    /**
     * Temporary route for designing the procedure worksheet PDF template.
     */
    public function previewProcedureWorksheetPdf(
        Request $request,
        string $batchId,
        string $worksheetId,
        ProcedureWorksheetPdfService $pdfService
    ): Response {
        $batch = SampleHeader::findOrFail($batchId);
        $worksheet = ProcedureWorksheet::findOrFail($worksheetId);

        $sampleIds = [];
        if ($request->filled('samples')) {
            $raw = $request->input('samples', '');
            $parts = is_array($raw) ? $raw : explode(',', (string) $raw);
            $sampleIds = array_values(array_filter(array_map(
                fn ($id) => trim((string) $id),
                $parts
            )));
        }

        $analyteIds = [];
        if ($request->filled('analytes')) {
            $raw = $request->input('analytes', '');
            $parts = is_array($raw) ? $raw : explode(',', (string) $raw);
            $analyteIds = array_values(array_filter(array_map(
                fn ($id) => trim((string) $id),
                $parts
            )));
        }

        $viewData = $pdfService->prepareViewDataForPreview($batch, $worksheet, $sampleIds, $analyteIds);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('procedure-worksheets.print.worksheet', $viewData);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="procedure-worksheet-preview.pdf"',
        ]);
    }
}
