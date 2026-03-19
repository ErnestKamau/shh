<?php

namespace App\Services;

use App\BatchAttachment;
use App\BatchLabSectionApprover;
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
use App\Models\Procedures\ProcedureWorksheetStepAnalyst;
use App\Models\System\SystemConfiguration;
use App\SampleHeader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\User;
use Carbon\Carbon;

class ProcedureWorksheetPdfService
{
    /**
     * Prepare the full view data array used by the worksheet PDF Blade template.
     * This is extracted so we can both attach PDFs and stream previews without
     * duplicating the data-building logic.
     */
    public function prepareViewDataForPreview(
        SampleHeader $batch,
        ProcedureWorksheet $worksheet,
        array $sampleIds = [],
        array $analyteIds = []
    ): array
    {
        $worksheet->load([
            'steps' => fn ($q) => $q->orderBy('order')->orderBy('id'),
            'configFields' => fn ($q) => $q->orderBy('order'),
            'testKitColumns' => fn ($q) => $q->orderBy('order'),
        ]);
        $steps = $worksheet->steps;
        $configFields = $worksheet->configFields;
        $testKitColumns = $worksheet->testKitColumns;

        $batch->load('sample_type');

        $capturedQuery = CapturedResult::where('sample_header_id', $batch->id)
            ->where('procedure_worksheet_id', $worksheet->id);

        if (!empty($sampleIds)) {
            // Restrict to the same samples currently selected in the worksheet UI
            $capturedQuery->whereIn('sample_detail_id', $sampleIds);
        }

        if (!empty($analyteIds)) {
            // Restrict to the currently selected parameter/analyte tab
            $capturedQuery->whereIn('analyte_id', $analyteIds);
        }

        $capturedResults = $capturedQuery
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
            $valuesForCr = $stepValues->get($cr->id) ?? collect();

            // Build per-step detail including decoded measurand map and equipment/analyst IDs.
            $stepDetails = [];
            foreach ($valuesForCr as $val) {
                /** @var \App\Models\Procedures\CapturedProcedureValue $val */
                $rawValue = $val->value;
                $decodedMap = null;
                if (is_string($rawValue)) {
                    $trimmed = trim($rawValue);
                    if ($trimmed !== '' && $trimmed[0] === '{') {
                        $maybe = json_decode($trimmed, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) {
                            $decodedMap = $maybe;
                        }
                    }
                }

                $stepDetails[$val->procedure_worksheet_step_id] = [
                    'raw_value' => $rawValue,
                    'measurand_map' => $decodedMap,
                    'equipment_ids' => $val->equipment_ids ?? [],
                    'analyst_ids' => $val->analyst_ids ?? [],
                ];
            }

            $sampleRows[] = [
                'sample_code' => $sample ? ($sample->sample_code ?? '—') : '—',
                'batch_code' => $batch->batch_code,
                'sample_type_name' => $sampleTypeName,
                'analysis_type_name' => $analysisType ? $analysisType->name : $worksheet->name,
                'steps' => $stepDetails,
                'config_values' => ($configValues->get($cr->id) ?? collect())->pluck('value', 'procedure_config_field_id')->toArray(),
            ];
        }

        // Resolve per-step analysts via dedicated table (same logic as the Livewire manager).
        $stepAnalystMap = [];
        $analyteIds = $capturedResults->pluck('analyte_id')->filter()->unique()->values();
        if ($analyteIds->count() === 1) {
            $activeAnalyteId = (int) $analyteIds->first();
            $stepAnalystRows = ProcedureWorksheetStepAnalyst::where('batch_id', $batch->id)
                ->where('analyte_id', $activeAnalyteId)
                ->where('procedure_worksheet_id', $worksheet->id)
                ->get();

            // Collect all analyst IDs to minimise queries.
            $allAnalystIds = $stepAnalystRows->pluck('analyst_ids')->filter()->flatten()->unique()->values();
            $analystLookup = $allAnalystIds->isNotEmpty()
                ? User::whereIn('id', $allAnalystIds)->get()->keyBy('id')
                : collect();

            foreach ($stepAnalystRows as $row) {
                $ids = is_array($row->analyst_ids) ? $row->analyst_ids : [];
                $names = collect($ids)
                    ->map(function ($id) use ($analystLookup) {
                        $user = $analystLookup->get((int) $id);
                        return $user ? ($user->name ?? null) : null;
                    })
                    ->filter()
                    ->implode(', ');

                if ($names !== '') {
                    $stepAnalystMap[$row->procedure_worksheet_step_id] = $names;
                }
            }
        }

        $headerConfig = $this->buildHeaderConfigFromFirstResult(
            $configFields,
            $configValues,
            $crIds
        );

        // Override / backfill key header fields so they always have sensible defaults
        // even when no explicit CapturedProcedureConfigValue rows exist yet.

        // Laboratory number: show all sample codes (comma-separated)
        $labNumbers = collect($samplesForWorksheet)
            ->pluck('sample_code')
            ->filter()
            ->unique()
            ->implode(', ');
        if ($labNumbers !== '') {
            $headerConfig['lab_no'] = $labNumbers;
            $headerConfig['laboratory_number'] = $labNumbers;
        }

        // Sample type: fall back to the batch sample type name
        if (!array_key_exists('sample_type', $headerConfig) || $headerConfig['sample_type'] === '') {
            $headerConfig['sample_type'] = $sampleTypeName;
        }

        // Date received: fall back to the batch receipt_date
        if (
            (!array_key_exists('date_received', $headerConfig) || $headerConfig['date_received'] === '') &&
            (!array_key_exists('date_recieved', $headerConfig) || $headerConfig['date_recieved'] === '')
        ) {
            $headerConfig['date_received'] = $this->formatDateValue($batch->receipt_date ?? null);
        }

        // Tests: fall back to the resolved analysis type / worksheet name
        if (!array_key_exists('tests', $headerConfig) || $headerConfig['tests'] === '') {
            $headerConfig['tests'] = $analysisTypeName;
        }

        $analystValue = $headerConfig['analyst'] ?? '';
        $analystName = is_numeric($analystValue) ? (User::find($analystValue)?->name ?? $analystValue) : $analystValue;

        $worksheetHeader = [
            'laboratory_number' => $labNumbers !== '' ? $labNumbers : ($headerConfig['lab_no'] ?? $batch->batch_code),
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

        $capturedResultIds = $capturedResults->pluck('id')->values()->all();
        $testKitRows = [];

        // Candidate rows are either:
        // - instance rows for the selected captured results
        // - or legacy shared rows (captured_result_id IS NULL)
        $rowsQuery = ProcedureTestKitRow::where('procedure_worksheet_id', $worksheet->id);
        if (!empty($capturedResultIds)) {
            $rowsQuery->where(function ($q) use ($capturedResultIds) {
                $q->whereIn('captured_result_id', $capturedResultIds)
                    ->orWhereNull('captured_result_id');
            });
        } else {
            $rowsQuery->whereNull('captured_result_id');
        }

        $rows = $rowsQuery->get();
        $rowIds = $rows->pluck('id')->values()->all();

        // Render a single table: the row set is the union of row_index values.
        $rowIndexSet = $rows->pluck('row_index')->unique()->sort()->values()->all();

        $rowIndexByRowId = [];
        foreach ($rows as $row) {
            $rowIndexByRowId[(int) $row->id] = (int) $row->row_index;
        }

        $columnIds = $testKitColumns->pluck('id')->values()->all();

        $tkValuesQuery = ProcedureTestKitValue::whereIn('procedure_test_kit_row_id', $rowIds)
            ->whereIn('procedure_test_kit_column_id', $columnIds)
            ->where(function ($q) use ($capturedResultIds) {
                if (!empty($capturedResultIds)) {
                    $q->whereIn('captured_result_id', $capturedResultIds)
                        ->orWhereNull('captured_result_id');
                } else {
                    $q->whereNull('captured_result_id');
                }
            });

        $tkValues = $tkValuesQuery->get();

        // Build maps keyed by (row_index, column_id).
        $defaultsByRowIndexCell = [];
        $instanceByCapturedIdRowIndexCell = [];

        foreach ($tkValues as $val) {
            $rowId = (int) $val->procedure_test_kit_row_id;
            $rowIndex = $rowIndexByRowId[$rowId] ?? null;
            if ($rowIndex === null) continue;

            $colId = (int) $val->procedure_test_kit_column_id;

            if ($val->captured_result_id === null) {
                $defaultsByRowIndexCell[$rowIndex][$colId] = $val->value;
                continue;
            }

            $capturedId = (int) $val->captured_result_id;
            $instanceByCapturedIdRowIndexCell[$capturedId][$rowIndex][$colId] = $val->value;
        }

        foreach ($rowIndexSet as $rowIndex) {
            $vals = [];
            $rowHasAnyNonEmptyValue = false;

            foreach ($testKitColumns as $col) {
                $colId = (int) $col->id;
                $merged = [];

                foreach ($capturedResultIds as $capturedId) {
                    $capturedId = (int) $capturedId;

                    $v = $instanceByCapturedIdRowIndexCell[$capturedId][$rowIndex][$colId] ?? null;
                    if ($v !== null) {
                        $vStr = (string) $v;
                        if (trim($vStr) !== '') {
                            $merged[] = $vStr;
                            continue;
                        }
                    }

                    $dv = $defaultsByRowIndexCell[$rowIndex][$colId] ?? null;
                    if ($dv !== null) {
                        $dvStr = (string) $dv;
                        if (trim($dvStr) !== '') {
                            $merged[] = $dvStr;
                        }
                    }
                }

                // If there are no captured results selected, still show defaults.
                if (empty($capturedResultIds) && isset($defaultsByRowIndexCell[$rowIndex][$colId])) {
                    $dvStr = (string) ($defaultsByRowIndexCell[$rowIndex][$colId] ?? '');
                    if (trim($dvStr) !== '') {
                        $merged[] = $dvStr;
                    }
                }

                $merged = array_values(array_unique($merged));
                $cell = implode(', ', $merged);
                $vals[$colId] = $cell;

                if (trim($cell) !== '') {
                    $rowHasAnyNonEmptyValue = true;
                }
            }

            if ($rowHasAnyNonEmptyValue) {
                $testKitRows[] = ['values' => $vals];
            }
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
            'headerConfig',
            'stepAnalystMap'
        );
        $viewData['procedure'] = $worksheet;
        $viewData['testKitRows'] = collect($testKitRows);
        $viewData['testKitColumns'] = $testKitColumns;

        return $viewData;
    }

    public function generateAndAttach(
        SampleHeader $batch,
        ProcedureWorksheet $worksheet,
        ?BatchLabSectionApprover $checker = null,
        array $analyteIds = [],
        array $sampleIds = []
    ): void
    {
        try {
            // For attachments we still include all samples for this batch+worksheet;
            // sample / analyte filtering is applied per-parameter when provided.
            $viewData = $this->prepareViewDataForPreview($batch, $worksheet, $sampleIds, $analyteIds);

            if ($checker) {
                $viewData['checker_name'] = optional($checker->getApproverDetails())->name ?? '';
                // When the worksheet is generated (COA "Process Results" step), approval_date
                // may not be populated yet. Fallback to "now" (worksheet/PDF creation time).
                $viewData['checker_signed_at'] = $checker->approval_date
                    ? Carbon::parse($checker->approval_date)->format('d/m/Y')
                    : now()->format('d/m/Y');
                $viewData['checker_signature'] = optional($checker->getApproverDetails())->electronic_sig ?? null;
            } else {
                $viewData['checker_name'] = '';
                $viewData['checker_signed_at'] = '';
                $viewData['checker_signature'] = null;
            }

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->loadView('procedure-worksheets.print.worksheet', $viewData);
            $pdfContent = $pdf->output();

            // Ensure each (worksheet, analyte) combination gets its own file
            $analyteSuffix = '';
            if (! empty($analyteIds)) {
                $analyteSuffix = '-analyte-' . implode('_', array_map('intval', $analyteIds));
            }

            $filename = 'procedure-worksheet-' . $worksheet->id . '-batch-' . $batch->id . $analyteSuffix . '.pdf';
            Storage::disk('public')->put('batch-attachments/' . $filename, $pdfContent);
            $attachmentUrl = '/storage/batch-attachments/' . urlencode($filename);

            $attachmentTypeId = SystemConfiguration::where('key', 'attachment_type')
                ->where('value', 'Procedure Worksheet')
                ->value('id');
            if ($attachmentTypeId === null) {
                $attachmentTypeId = SystemConfiguration::where('key', 'attachment_type')->value('id');
            }

            if ($attachmentTypeId !== null) {
                $parameterName = $viewData['analysisTypeName'] ?? $worksheet->name;
                $title = 'Procedure Worksheet for ' . $parameterName;
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
                    $attachment->uploaded_by = Auth::id();
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

