<?php

namespace App\Http\Controllers;

use App\SampleHeader;
use App\Services\ProcedureWorksheetPdfService;
use App\Services\Worksheets\AmSpec\Lws056SalmonellaPdfService;
use App\Services\Worksheets\WorksheetPrintService;
use App\Models\Procedures\ProcedureWorksheet;
use Illuminate\Http\RedirectResponse;
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

    public function printWorksheet(
        Request $request,
        string $batchId,
        WorksheetPrintService $printService,
    ): View|RedirectResponse|Response {
        $batch = SampleHeader::findOrFail($batchId);
        $tab = (string) $request->query('tab', '');

        $params = array_filter([
            'formula_id' => $request->query('formula_id'),
            'stage_header_id' => $request->query('stage_header_id'),
            'track_id' => $request->query('track_id'),
            'log_entry_worksheet_id' => $request->query('log_entry_worksheet_id'),
            'pipeline_id' => $request->query('pipeline_id'),
            'procedure_worksheet_id' => $request->query('procedure_worksheet_id'),
            'run_id' => $request->query('run_id'),
            'analysis_type_id' => $request->query('analysis_type_id'),
            'sample_detail_id' => $request->query('sample_detail_id'),
        ], fn ($value) => $value !== null && $value !== '');

        $viewData = $printService->build($tab, $batch, $params);

        if (! empty($viewData['redirectToAmSpecLws056Pdf'])) {
            $url = route('batch-worksheets.amspec-lws056', ['batch' => $batch->id]);
            if (! empty($params['sample_detail_id'])) {
                $url .= '?'.http_build_query(['sample' => $params['sample_detail_id']]);
            }

            return redirect($url);
        }

        if (! empty($viewData['redirectToPdf']) && ! empty($params['procedure_worksheet_id'])) {
            $query = http_build_query(array_filter([
                'samples' => $request->query('samples'),
                'analytes' => $request->query('analytes'),
            ]));

            $url = route('batch-worksheets.procedure-preview', [
                'batch' => $batch->id,
                'worksheet' => $params['procedure_worksheet_id'],
            ]);

            return redirect($query !== '' ? $url.'?'.$query : $url);
        }

        return view($viewData['view'], $viewData);
    }

    public function printAmSpecLws056Salmonella(
        Request $request,
        string $batchId,
        Lws056SalmonellaPdfService $pdfService,
    ): Response {
        $batch = SampleHeader::findOrFail($batchId);
        $sampleDetailId = $request->query('sample');

        return $pdfService->streamPdf(
            $batch,
            filled($sampleDetailId) ? (string) $sampleDetailId : null
        );
    }
}
