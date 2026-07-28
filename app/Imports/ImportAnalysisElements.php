<?php

namespace App\Imports;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\Analyte;
use App\Models\BulkImportBatch;
use App\ReportingUnit;
use App\Imports\BaseImporter;

class ImportAnalysisElements extends BaseImporter
{
    private $analysisType;

    public function __construct($analysisType, $batch = null)
    {
        $this->analysisType = $analysisType;

        if ($batch === null) {
            $companyId = '00000000-0000-0000-0000-000000000000';
            try {
                if (function_exists('getUserCompany')) {
                    $companyId = getUserCompany() ?: $companyId;
                }
            } catch (\Throwable $t) {
            }

            $companyId = auth()->user()?->company_id ?: $companyId;

            $userId = '00000000-0000-0000-0000-000000000000';
            try {
                if (auth()->check()) {
                    $userId = auth()->id() ?: $userId;
                }
            } catch (\Throwable $t) {
            }

            $batch = BulkImportBatch::create([
                'module' => 'lab',
                'form_type' => 'analysis_elements',
                'status' => 'processing',
                'company_id' => $companyId,
                'user_id' => $userId,
                'imported_rows' => 0,
                'total_rows' => 0,
                'error_rows' => 0,
                'started_at' => now(),
            ]);
        }

        parent::__construct($batch);
    }

    protected function validateRow(array $row): array
    {
        $errors = [];
        if (empty($this->fuzzyGet($row, ['parameter', 'analyte', 'name']))) {
            $errors[] = 'Parameter name is required';
        }
        if (empty($this->fuzzyGet($row, ['method']))) {
            $errors[] = 'Method is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $methodName = trim((string) $this->fuzzyGet($row, ['method']));
        $method = $this->resolveAnalysisMethod($methodName);

        $ltMethodName = trim((string) $this->fuzzyGet($row, ['ltmethod', 'ltm_method']));
        $ltMethod = null;
        if ($ltMethodName !== '') {
            $ltMethod = $this->resolveAnalysisMethod($ltMethodName, true);
        }

        $reportingUnitName = trim((string) $this->fuzzyGet($row, ['reporting_unit', 'unit']));
        $reportingUnit = $this->resolveReportingUnit($reportingUnitName);

        $parameter = trim((string) $this->fuzzyGet($row, ['parameter', 'analyte', 'name']));
        $analyte = Analyte::query()
            ->where(function ($query) use ($parameter): void {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($parameter)])
                    ->orWhereRaw('LOWER(TRIM(code)) = ?', [strtolower($parameter)]);
            })
            ->first();

        $nonAccredited = $this->parseAccreditedColumn(
            $this->fuzzyGet($row, ['accredited', 'is_accredited'], null)
        );

        if (! $analyte) {
            $analyte = Analyte::create([
                'code' => $parameter,
                'name' => $parameter,
                'decimal_places' => 2,
                'company_id' => getUserCompany(),
                'method' => $method->id,
                'reporting_unit' => $reportingUnit?->name,
                'non_accredited' => $nonAccredited,
                'show_on_report' => (
                    $this->fuzzyGet($row, ['show_on_report', 'show_on_reports']) !== null &&
                    in_array(strtolower(trim((string) $this->fuzzyGet($row, ['show_on_report', 'show_on_reports']))), ['0', 'no', 'false', 'off'], true)
                ) ? 0 : 1,
                'active' => 1,
            ]);
        }

        $labSectionId = $this->analysisType->lab_section_id
            ?: $this->analysisType->lab_id;

        return [
            'method' => $method->id,
            'reporting_unit' => $reportingUnit?->name,
            'analyte_id' => $analyte->id,
            'company_id' => getUserCompany(),
            'non_accredited' => $nonAccredited,
            'lab_section_id' => $labSectionId,
            'analysis_type_id' => $this->analysisType->id,
            'ltm_method_id' => $ltMethod?->id,
            'reporting_time' => $this->fuzzyGet($row, ['tat', 'reporting_time']),
            'active' => 1,
            'show_on_report' => 1,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        $existing = AnalysisElements::query()
            ->where('analysis_type_id', $transformedData['analysis_type_id'])
            ->where('analyte_id', $transformedData['analyte_id'])
            ->first();

        if ($existing) {
            $existing->update($transformedData);
            $this->recordUpsert((string) $transformedData['analyte_id'], 'updated');

            return true;
        }

        $nextLevel = ((int) AnalysisElements::query()
            ->where('analysis_type_id', $transformedData['analysis_type_id'])
            ->max('level')) + 1;

        AnalysisElements::create(array_merge($transformedData, [
            'level' => $nextLevel > 0 ? $nextLevel : 1,
        ]));

        $this->recordUpsert((string) $transformedData['analyte_id'], 'inserted');

        return true;
    }

    /**
     * Template column "accredited": 1/yes/true => accredited (stores non_accredited=0).
     * 0/no/false => not accredited (stores non_accredited=1).
     */
    protected function parseAccreditedColumn(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'yes', 'y', 'true', 'on', 'accredited'], true)) {
            return 0;
        }

        if (in_array($normalized, ['0', 'no', 'n', 'false', 'off'], true)) {
            return 1;
        }

        return 0;
    }

    protected function resolveAnalysisMethod(string $name, bool $isLtm = false): AnalysisMethod
    {
        $name = trim($name);
        $query = AnalysisMethod::query()
            ->where(function ($q) use ($name): void {
                $q->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                    ->orWhereRaw('LOWER(TRIM(code)) = ?', [strtolower($name)]);
            });

        if ($isLtm) {
            $query->where('is_ltm', 1);
        }

        $method = $query->first();
        if ($method) {
            return $method;
        }

        return AnalysisMethod::create([
            'name' => $name,
            'code' => $name,
            'description' => $name,
            'company_id' => getUserCompany(),
            'active' => 1,
            'is_ltm' => $isLtm ? 1 : 0,
        ]);
    }

    protected function resolveReportingUnit(string $name): ?ReportingUnit
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $unit = ReportingUnit::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
            ->first();

        if ($unit) {
            return $unit;
        }

        return ReportingUnit::create([
            'name' => $name,
            'active' => 1,
        ]);
    }
}
