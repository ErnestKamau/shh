<?php

namespace App\Imports;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\Analyte;
use App\AnalysisType;
use App\ReportingUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportAnalysisElements implements ToCollection, WithHeadingRow
{
    use Importable;

    private AnalysisType $analysisType;

    public int $importedRows = 0;

    public int $skippedRows = 0;

    /** @var list<string> */
    public array $errors = [];

    public function __construct(AnalysisType $analysisType)
    {
        $this->analysisType = $analysisType;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // heading row is 1
            $rowData = $this->normalizeRow($row instanceof Collection ? $row->toArray() : (array) $row);

            if ($this->isEmptyRow($rowData)) {
                continue;
            }

            $parameter = trim((string) $this->value($rowData, ['parameter', 'analyte', 'name']));
            $methodName = trim((string) $this->value($rowData, ['method']));

            if ($parameter === '' || $methodName === '') {
                $this->skippedRows++;
                $this->errors[] = "Row {$rowNumber}: parameter and method are required.";

                continue;
            }

            try {
                DB::transaction(function () use ($rowData, $parameter, $methodName): void {
                    $method = $this->resolveAnalysisMethod($methodName);

                    $ltMethodName = trim((string) $this->value($rowData, ['ltmethod', 'ltm_method']));
                    $ltMethod = $ltMethodName !== ''
                        ? $this->resolveAnalysisMethod($ltMethodName, true)
                        : null;

                    $reportingUnit = $this->resolveReportingUnit(
                        trim((string) $this->value($rowData, ['reporting_unit', 'unit']))
                    );

                    $nonAccredited = $this->parseAccreditedColumn(
                        $this->value($rowData, ['accredited', 'is_accredited'], null)
                    );

                    $analyte = Analyte::query()
                        ->where(function ($query) use ($parameter): void {
                            $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($parameter)])
                                ->orWhereRaw('LOWER(TRIM(code)) = ?', [strtolower($parameter)]);
                        })
                        ->first();

                    if (! $analyte) {
                        $analyte = Analyte::create([
                            'code' => $parameter,
                            'name' => $parameter,
                            'decimal_places' => 2,
                            'company_id' => getUserCompany(),
                            'method' => $method->id,
                            'reporting_unit' => $reportingUnit?->name,
                            'non_detectable' => 0,
                            'non_accredited' => $nonAccredited,
                            'show_on_report' => 1,
                            'active' => 1,
                        ]);
                    }

                    $payload = [
                        'method' => $method->id,
                        'reporting_unit' => $reportingUnit?->name,
                        'analyte_id' => $analyte->id,
                        'non_accredited' => $nonAccredited,
                        'lab_section_id' => $this->resolveLabSectionId(),
                        'analysis_type_id' => $this->analysisType->id,
                        'ltm_method_id' => $ltMethod?->id,
                        'reporting_time' => $this->value($rowData, ['tat', 'reporting_time']),
                        'active' => 1,
                        'show_on_report' => 1,
                    ];

                    $existing = AnalysisElements::query()
                        ->where('analysis_type_id', $this->analysisType->id)
                        ->where('analyte_id', $analyte->id)
                        ->first();

                    if ($existing) {
                        $existing->update($payload);
                    } else {
                        $nextLevel = ((int) AnalysisElements::query()
                            ->where('analysis_type_id', $this->analysisType->id)
                            ->max('level')) + 1;

                        AnalysisElements::create(array_merge($payload, [
                            'level' => max($nextLevel, 1),
                        ]));
                    }

                    $this->importedRows++;
                });
            } catch (\Throwable $e) {
                $this->skippedRows++;
                $this->errors[] = "Row {$rowNumber}: {$e->getMessage()}";
                Log::error('Analysis parameter import row failed', [
                    'analysis_type_id' => $this->analysisType->id,
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function resolveLabSectionId(): ?string
    {
        $labSectionId = $this->analysisType->lab_section_id;

        return $labSectionId !== null && $labSectionId !== ''
            ? (string) $labSectionId
            : null;
    }

    /**
     * Template column "accredited": 1/yes/true => accredited (stores non_accredited=0).
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

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    protected function value(array $row, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            $normalized = $this->normalizeHeaderName($key);
            if (array_key_exists($normalized, $row) && $row[$normalized] !== null && trim((string) $row[$normalized]) !== '') {
                return is_string($row[$normalized]) ? trim($row[$normalized]) : $row[$normalized];
            }
            if (array_key_exists($key, $row) && $row[$key] !== null && trim((string) $row[$key]) !== '') {
                return is_string($row[$key]) ? trim($row[$key]) : $row[$key];
            }
        }

        return $default;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[$this->normalizeHeaderName((string) $key)] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    protected function normalizeHeaderName(string $header): string
    {
        $header = str_replace("\xA0", ' ', $header);
        $header = strtolower(trim($header));
        $header = str_replace('*', '', $header);
        $header = preg_replace('/\s+/', '_', $header) ?? $header;
        $header = preg_replace('/[^a-z0-9_]/', '', $header) ?? $header;

        return $header;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
