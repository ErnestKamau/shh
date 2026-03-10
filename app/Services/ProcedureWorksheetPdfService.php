<?php

namespace App\Services;

use App\BatchAttachment;
use App\CapturedResult;
use App\SampleDetails;
use App\SampleType;
use App\AnalysisMethod;
use App\ReportFormat;
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
use Illuminate\Support\Str;
use App\User;

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

            // Build a simple header mapping from configurable fields (using first captured result),
            // so the PDF header can render dynamic fields like Analyst, Method, Lab No, etc.
            $headerConfig = $this->buildHeaderConfigFromFirstResult(
                $configFields,
                $configValues,
                $crIds
            );

            $analystValue = $headerConfig['analyst'] ?? '';
            $analystName = is_numeric($analystValue) ? (User::find($analystValue)?->name ?? $analystValue) : $analystValue;

            // High-level worksheet header values used by the Blade template.
            $worksheetHeader = [
                'laboratory_number' => $headerConfig['lab_no'] ?? $batch->batch_code,
                'analyst' => $analystName,
                'date_received' => $this->formatDateValue($batch->receipt_date ?? null),
                'tests' => $analysisTypeName,
                'room_temperature' => $headerConfig['room_temperature'] ?? ($headerConfig['temperature_under_25'] ?? ''),
                'sample_type' => $sampleTypeName,
                'dilution_used' => $headerConfig['dilution_used'] ?? '',
                'date_tested' => $headerConfig['date_tested'] ?? '',
                'method_used' => $headerConfig['method'] ?? '',
                'start_time' => $headerConfig['start_time'] ?? '',
            ];

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
                'batch',
                'steps',
                'configFields',
                'testKitColumns',
                'sampleRows',
                'testKitRows',
                'logoSrc',
                'printedAt',
                'samplesForWorksheet',
                'sampleTypeName',
                'analysisTypeName',
                'company',
                'worksheetHeader',
                'headerConfig'
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

    /**
     * Build a simple key/value map of configurable field values taken from the
     * first captured result for this worksheet. Keys are normalized from
     * field_value_name (or label) so the Blade template can reference them.
     *
     * @param \Illuminate\Support\Collection<int,\App\Models\Procedures\ProcedureConfigField> $configFields
     * @param \Illuminate\Support\Collection<int,\Illuminate\Support\Collection> $groupedConfigValues
     * @param \Illuminate\Support\Collection<int,int> $capturedResultIds
     * @return array<string,string>
     */
    private function buildHeaderConfigFromFirstResult($configFields, $groupedConfigValues, $capturedResultIds): array
    {
        if ($capturedResultIds->isEmpty()) {
            return [];
        }

        $firstId = (int) $capturedResultIds->first();
        $valuesForFirst = ($groupedConfigValues->get($firstId) ?? collect())
            ->pluck('value', 'procedure_config_field_id');

        $header = [];

        /** @var \App\Models\Procedures\ProcedureConfigField $field */
        foreach ($configFields as $field) {
            $raw = $valuesForFirst->get($field->id);
            if ($raw === null || $raw === '') {
                continue;
            }

            $key = $field->field_value_name ?: Str::slug($field->label, '_');

            // For dataset-backed fields (any field tied to a dataset model),
            // resolve IDs into human readable labels.
            if ($field->model_tied_to && $field->model_tied_to !== '') {
                $isMulti = $field->field_type === 'dataset_multiselect';
                $header[$key] = $this->resolveDatasetLabels($field->model_tied_to, (string) $raw, $isMulti);
            } else {
                $header[$key] = (string) $raw;
            }
        }

        return $header;
    }

    /**
     * Resolve dataset config values (stored as IDs) into display labels.
     */
    private function resolveDatasetLabels(string $modelTiedTo, string $rawValue, bool $isMulti): string
    {
        $ids = $isMulti ? array_filter(explode(',', $rawValue)) : [$rawValue];
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            return '';
        }

        return match ($modelTiedTo) {
            'users' => User::whereIn('id', $ids)->pluck('name')->implode(', '),
            'sample_details' => SampleDetails::whereIn('id', $ids)->pluck('sample_code')->implode(', '),
            'sample_types' => SampleType::whereIn('id', $ids)->pluck('name')->implode(', '),
            'methods' => AnalysisMethod::whereIn('id', $ids)->pluck('name')->implode(', '),
            'captured_results' => CapturedResult::whereIn('id', $ids)
                ->with(['sample', 'analysis_type'])
                ->get()
                ->map(function (CapturedResult $cr): string {
                    $sampleCode = $cr->sample ? ($cr->sample->sample_code ?? '—') : '—';
                    $analysisName = $cr->analysis_type ? $cr->analysis_type->name : '';
                    return trim($sampleCode . ' ' . $analysisName);
                })
                ->implode(', '),
            'report_formats' => ReportFormat::whereIn('id', $ids)->pluck('report_name')->implode(', '),
            default => implode(', ', array_map('strval', $ids)),
        };
    }

    /**
     * Format a date-ish value for display, accepting strings or DateTime.
     */
    private function formatDateValue($value): string
    {
        if (! $value) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        $timestamp = strtotime((string) $value);
        if ($timestamp === false) {
            return (string) $value;
        }

        return date('d/m/Y', $timestamp);
    }
}

