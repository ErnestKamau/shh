<?php

namespace App\Services\Worksheets\AmSpec;

use App\CapturedResult;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\Procedures\CapturedProcedureConfigValue;
use App\Models\Procedures\CapturedProcedureValue;
use App\Models\Procedures\ProcedureWorksheet;
use App\SampleHeader;
use Illuminate\Support\Facades\Storage;

class Lws056SalmonellaPdfService
{
    public const DOC_NO = 'AMS/QMS/LWS/056';

    public const PROCEDURE_NAME = 'LWS-056 Salmonella Confirmation';

    public const HOLDER_NAME = 'LWS-056 Salmonella Pipeline';

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(SampleHeader $batch, ?string $sampleDetailId = null): array
    {
        $holder = GroupedWorksheetHolder::query()
            ->where(function ($q) {
                $q->where('document_control_no', self::DOC_NO)
                    ->orWhere('name', self::HOLDER_NAME);
            })
            ->first();

        $procedure = ProcedureWorksheet::query()
            ->where(function ($q) {
                $q->where('document_control_no', self::DOC_NO)
                    ->orWhere('name', self::PROCEDURE_NAME);
            })
            ->with(['steps' => fn ($q) => $q->orderBy('order'), 'configFields' => fn ($q) => $q->orderBy('order')])
            ->first();

        $capturedQuery = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('has_no_result_capture', false);

        if ($holder) {
            $capturedQuery->where(function ($q) use ($holder) {
                $q->where('grouped_worksheet_holder_id', $holder->id)
                    ->orWhere('has_grouped_worksheet', true);
            });
        }

        if ($sampleDetailId) {
            $capturedQuery->where('sample_detail_id', $sampleDetailId);
        }

        $captured = $capturedQuery
            ->with(['sample', 'my_analyte'])
            ->orderBy('sample_detail_code')
            ->get();

        if ($captured->isEmpty()) {
            $captured = CapturedResult::query()
                ->where('sample_header_id', $batch->id)
                ->when($sampleDetailId, fn ($q) => $q->where('sample_detail_id', $sampleDetailId))
                ->with(['sample', 'my_analyte'])
                ->orderBy('sample_detail_code')
                ->get();
        }

        /** Prefer Salmonella-related rows when present. */
        $salmonellaRows = $captured->filter(function (CapturedResult $row) {
            $name = (string) ($row->my_analyte?->name ?? $row->analyte_code ?? '');

            return stripos($name, 'Salmonella') !== false;
        });

        $rows = $salmonellaRows->isNotEmpty() ? $salmonellaRows : $captured;
        $primary = $rows->first();
        $sample = $primary?->sample;

        $configValues = $this->configValuesForCaptured($procedure, $primary);
        $stepValues = $this->stepValuesForCaptured($procedure, $primary);

        return [
            'docNo' => self::DOC_NO,
            'revisionNo' => $procedure?->revision ?: ($holder?->revision ?: '11.2025.R0'),
            'revisionDate' => optional($procedure?->issue_date)->format('d F, Y')
                ?: optional($holder?->issue_date)->format('d F, Y')
                ?: '03 November, 2025',
            'title' => 'MICROBIOLOGICAL ANALYSIS – DETECTION OF SALMONELLA SPP',
            'batch' => $batch,
            'sample' => $sample,
            'captured' => $primary,
            'jobNumber' => $this->firstFilled(
                $configValues['Job Number'] ?? null,
                $batch->batch_code
            ),
            'sampleName' => $this->firstFilled(
                $configValues['Sample Name'] ?? null,
                $sample?->sample_name,
                $sample?->sample_code,
                $primary?->sample_detail_code
            ),
            'otherDetails' => $this->firstFilled($configValues['Other Details'] ?? null),
            'foodProduct' => $this->firstFilled(
                $configValues['Food Product'] ?? null,
                $sample?->product_name
            ),
            'testMethod' => 'CMMEF 5th Edition, Chapter 36',
            'analysisStartDate' => $this->firstFilled($configValues['Analysis Start Date'] ?? null),
            'completionDate' => $this->firstFilled($configValues['Completion Date'] ?? null),
            'analystName' => $this->firstFilled($configValues['Analyst Name'] ?? null),
            'incubationStartTime' => $this->firstFilled($configValues['Incubation Start Time'] ?? null),
            'observationDateTime' => $this->firstFilled($configValues['Observation Date & Time'] ?? null),
            'incubatorId' => $this->firstFilled(
                $configValues['Incubator ID'] ?? null,
                'AMS/M/INS/-------'
            ),
            'steps' => $stepValues,
            'finalResult' => $this->firstFilled(
                $stepValues['Selective isolation Result (per 25 g)'] ?? null,
                $primary?->result
            ),
            'companyFooter' => 'AMSPEC MIDDLE EAST INSPECTION & TESTING SERVICES LLC, DUBAI, UAE',
            'positiveReference' => 'Salmonella typhimurium ATCC 14028',
            'negativeReference' => '',
        ];
    }

    public function streamPdf(SampleHeader $batch, ?string $sampleDetailId = null)
    {
        $viewData = $this->buildViewData($batch, $sampleDetailId);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('worksheets.print.amspec.lws-056-salmonella', $viewData);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'LWS-056-Salmonella-'.$batch->batch_code.'.pdf';

        return $pdf->stream($filename);
    }

    public function generateAndStore(SampleHeader $batch, ?string $sampleDetailId = null): string
    {
        $viewData = $this->buildViewData($batch, $sampleDetailId);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('worksheets.print.amspec.lws-056-salmonella', $viewData);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'LWS-056-Salmonella-'.$batch->batch_code.'-'.now()->format('YmdHis').'.pdf';
        $path = 'batch-attachments/'.$filename;
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * @return array<string, string>
     */
    protected function configValuesForCaptured(?ProcedureWorksheet $procedure, ?CapturedResult $captured): array
    {
        if (! $procedure || ! $captured) {
            return [];
        }

        $fields = $procedure->configFields;
        if ($fields->isEmpty()) {
            return [];
        }

        $values = CapturedProcedureConfigValue::query()
            ->where('captured_result_id', $captured->id)
            ->whereIn('procedure_config_field_id', $fields->pluck('id'))
            ->get()
            ->keyBy('procedure_config_field_id');

        $out = [];
        foreach ($fields as $field) {
            $out[$field->label] = (string) ($values->get($field->id)?->value ?? '');
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    protected function stepValuesForCaptured(?ProcedureWorksheet $procedure, ?CapturedResult $captured): array
    {
        if (! $procedure || ! $captured) {
            return [];
        }

        $steps = $procedure->steps;
        if ($steps->isEmpty()) {
            return [];
        }

        $values = CapturedProcedureValue::query()
            ->where('captured_result_id', $captured->id)
            ->whereIn('procedure_worksheet_step_id', $steps->pluck('id'))
            ->get()
            ->keyBy('procedure_worksheet_step_id');

        $out = [];
        foreach ($steps as $step) {
            $out[$step->step] = (string) ($values->get($step->id)?->value ?? '');
        }

        return $out;
    }

    public function matchesHolder(?GroupedWorksheetHolder $holder): bool
    {
        if (! $holder) {
            return false;
        }

        return $holder->document_control_no === self::DOC_NO
            || $holder->name === self::HOLDER_NAME;
    }

    protected function firstFilled(mixed ...$values): string
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $string = trim((string) $value);
            if ($string !== '') {
                return $string;
            }
        }

        return '';
    }
}
