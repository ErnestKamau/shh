<?php

namespace App\Services;

use App\BatchAttachment;
use App\CapturedResult;
use App\Models\Procedures\CapturedProcedureConfigValue;
use App\Models\Procedures\CapturedProcedureValue;
use App\Models\Procedures\ProcedureConfigField;
use App\Models\Procedures\ProcedureTestKitColumn;
use App\Models\Procedures\ProcedureTestKitRow;
use App\Models\Procedures\ProcedureTestKitValue;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\System\SystemConfiguration;
use App\SampleHeader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcedureWorksheetPdfService
{
    public function generateAndAttach(SampleHeader $batch, ProcedureWorksheet $worksheet): void
    {
        try {
            $worksheet->load([
                'steps' => fn ($q) => $q->orderBy('order')->orderBy('id'),
                'configFields' => fn ($q) => $q->orderBy('order'),
                'testKitColumns' => fn ($q) => $q->orderBy('order'),
            ]);
            $steps = $worksheet->steps;
            $configFields = $worksheet->configFields;
            $testKitColumns = $worksheet->testKitColumns;

            $batch->load('sample_type');

            $capturedResults = CapturedResult::where('sample_header_id', $batch->id)
                ->where('procedure_worksheet_id', $worksheet->id)
                ->with(['sample', 'analysis_type'])
                ->get();

            $crIds = $capturedResults->pluck('id');
            $stepValues = CapturedProcedureValue::whereIn('captured_result_id', $crIds)->get()->groupBy('captured_result_id');
            $configValues = CapturedProcedureConfigValue::whereIn('captured_result_id', $crIds)->get()->groupBy('captured_result_id');

            $sampleTypeName = $batch->sample_type ? $batch->sample_type->name : '—';
            $analysisTypeName = $capturedResults->isNotEmpty() && $capturedResults->first()->analysis_type
                ? $capturedResults->first()->analysis_type->name
                : $worksheet->name;

            $samplesForWorksheet = [];
            $seen = [];
            foreach ($capturedResults as $cr) {
                $sample = $cr->sample;
                if (!$sample || isset($seen[$sample->id])) {
                    continue;
                }
                $seen[$sample->id] = true;
                $samplesForWorksheet[] = [
                    'sample_code' => $sample->sample_code ?? '—',
                    'batch_code' => $batch->batch_code,
                    'sample_type_name' => $sampleTypeName,
                ];
            }

            $sampleRows = [];
            foreach ($capturedResults as $cr) {
                $sample = $cr->sample;
                $analysisType = $cr->analysis_type;
                $sampleRows[] = [
                    'sample_code' => $sample ? ($sample->sample_code ?? '—') : '—',
                    'batch_code' => $batch->batch_code,
                    'sample_type_name' => $sampleTypeName,
                    'analysis_type_name' => $analysisType ? $analysisType->name : $worksheet->name,
                    'step_values' => ($stepValues->get($cr->id) ?? collect())->pluck('value', 'procedure_worksheet_step_id')->toArray(),
                    'config_values' => ($configValues->get($cr->id) ?? collect())->pluck('value', 'procedure_config_field_id')->toArray(),
                ];
            }

            $testKitRows = [];
            $rows = ProcedureTestKitRow::where('procedure_worksheet_id', $worksheet->id)->orderBy('row_index')->get();
            $tkValues = ProcedureTestKitValue::whereIn('procedure_test_kit_row_id', $rows->pluck('id'))->get()->groupBy('procedure_test_kit_row_id');
            foreach ($rows as $row) {
                $vals = ($tkValues->get($row->id) ?? collect())->pluck('value', 'procedure_test_kit_column_id')->toArray();
                $testKitRows[] = ['values' => $vals];
            }

            $logoSrc = $this->resolveLogoAsDataUri();
            $printedAt = now()->format('d M Y H:i');
            $company = getActiveCompany();

            $viewData = compact(
                'batch', 'steps', 'configFields', 'testKitColumns',
                'sampleRows', 'testKitRows', 'logoSrc', 'printedAt',
                'samplesForWorksheet', 'sampleTypeName', 'analysisTypeName', 'company'
            );
            $viewData['procedure'] = $worksheet;
            $viewData['testKitRows'] = collect($testKitRows);
            $viewData['testKitColumns'] = $testKitColumns;

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->loadView('procedure-worksheets.print.worksheet', $viewData);
            $pdfContent = $pdf->output();

            $filename = 'procedure-worksheet-' . $worksheet->id . '-batch-' . $batch->id . '.pdf';
            Storage::disk('public')->put('batch-attachments/' . $filename, $pdfContent);
            $attachmentUrl = '/storage/batch-attachments/' . urlencode($filename);

            $attachmentTypeId = SystemConfiguration::where('key', 'attachment_type')
                ->where('value', 'Procedure Worksheet')
                ->value('id');
            if ($attachmentTypeId === null) {
                $attachmentTypeId = SystemConfiguration::where('key', 'attachment_type')->value('id');
            }

            if ($attachmentTypeId !== null) {
                $title = $worksheet->name . ' - ' . $batch->batch_code;
                $existing = BatchAttachment::where('batch_id', $batch->id)
                    ->where('title', $title)
                    ->first();

                if ($existing) {
                    $existing->attachment_url = $attachmentUrl;
                    $existing->updated_at = now();
                    $existing->save();
                } else {
                    $attachment = new BatchAttachment();
                    $attachment->batch_id = $batch->id;
                    $attachment->uploaded_by = auth()->id();
                    $attachment->title = $title;
                    $attachment->attachment_type = $attachmentTypeId;
                    $attachment->attachment_url = $attachmentUrl;
                    $attachment->is_internal = 0;
                    $attachment->show_on_coa = 0;
                    $attachment->save();
                }
            } else {
                Log::warning('Procedure worksheet PDF: no attachment_type in system_configurations; PDF file saved but not linked to batch.');
            }

            Log::info('Procedure worksheet PDF attached to batch', [
                'batch_id' => $batch->id,
                'worksheet_id' => $worksheet->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Procedure worksheet PDF generation failed', [
                'batch_id' => $batch->id,
                'worksheet_id' => $worksheet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function resolveLogoAsDataUri(): string
    {
        $fullPath = $this->resolveLogoPath();
        if ($fullPath === '' || !is_readable($fullPath)) {
            return '';
        }
        $contents = @file_get_contents($fullPath);
        if ($contents === false) {
            return '';
        }
        return 'data:' . $this->mimeTypeFromPath($fullPath) . ';base64,' . base64_encode($contents);
    }

    private function resolveLogoPath(): string
    {
        $company = getActiveCompany();
        if (!$company || empty($company->logo)) {
            return '';
        }
        $path = $company->logo;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?? $path;
        }
        $path = ltrim($path, '/');
        $filename = basename($path);
        if ($filename === '' || $filename === $path) {
            return '';
        }
        $relative = preg_replace('#^storage/#', '', $path);
        if ($relative !== $path) {
            $fullPath = Storage::disk('public')->path($relative);
            if ($fullPath !== '' && file_exists($fullPath)) {
                return $fullPath;
            }
        }
        $fullPath = storage_path('app/companies/' . $filename);
        return file_exists($fullPath) ? $fullPath : '';
    }

    private function mimeTypeFromPath(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
    }
}

