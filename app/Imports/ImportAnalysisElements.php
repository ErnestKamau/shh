<?php

namespace App\Imports;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\Models\Equipments\Equipment;
use App\ReportingUnit;
use App\SampleAnalysisStage;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
                DB::transaction(function () use ($rowData, $parameter, $methodName, $rowNumber): void {
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

                    $labSectionLabel = trim((string) $this->value($rowData, [
                        'lab_section', 'lab_section_name', 'lab_section_code', 'section',
                    ], ''));
                    $operatorLabel = trim((string) $this->value($rowData, [
                        'operator', 'operator_name', 'analyst',
                    ], ''));
                    $equipmentLabel = trim((string) $this->value($rowData, [
                        'equipment', 'equipment_name', 'equipment_code', 'equipment_number', 'instrument',
                    ], ''));
                    $tat = $this->value($rowData, ['tat', 'reporting_time', 'turnaround_time', 'turn_around_time']);

                    $labSectionId = $this->resolveLabSectionId($labSectionLabel !== '' ? $labSectionLabel : null);
                    $operatorId = $this->resolveOperatorId($operatorLabel !== '' ? $operatorLabel : null, $rowNumber);
                    $equipmentId = $this->resolveEquipmentId($equipmentLabel !== '' ? $equipmentLabel : null);

                    $payload = [
                        'method' => $method->id,
                        'reporting_unit' => $reportingUnit?->name,
                        'analyte_id' => $analyte->id,
                        'non_accredited' => $nonAccredited,
                        'analysis_type_id' => $this->analysisType->id,
                        'ltm_method_id' => $ltMethod?->id,
                        'active' => 1,
                        'show_on_report' => 1,
                    ];

                    if ($labSectionId !== null) {
                        $payload['lab_section_id'] = $labSectionId;
                    }

                    if ($operatorId !== null) {
                        $payload['operator_id'] = $operatorId;
                    }

                    if ($equipmentId !== null) {
                        $payload['equipment_id'] = $equipmentId;
                    }

                    if ($tat !== null && trim((string) $tat) !== '') {
                        $payload['reporting_time'] = (string) $tat;
                    }

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

    protected function resolveLabSectionId(?string $label = null): ?string
    {
        $companyId = getUserCompany();

        if ($label !== null && trim($label) !== '') {
            $label = trim($label);
            $labSection = SampleAnalysisStage::query()
                ->where('company_id', $companyId)
                ->where('is_sample_stage', 0)
                ->where(function ($query) use ($label): void {
                    $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($label)])
                        ->orWhereRaw('LOWER(TRIM(code)) = ?', [strtolower($label)]);
                })
                ->first();

            if ($labSection) {
                return (string) $labSection->id;
            }

            $labId = $this->analysisType->lab_id;

            try {
                $labSection = SampleAnalysisStage::create([
                    'code' => Str::upper(Str::limit(preg_replace('/\s+/', ' ', $label) ?? $label, 50, '')),
                    'name' => $label,
                    'company_id' => $companyId,
                    'lab_id' => $labId,
                    'is_sample_stage' => 0,
                    'active' => 1,
                ]);

                return (string) $labSection->id;
            } catch (\Throwable $e) {
                Log::warning('Could not create lab section during parameter import: '.$e->getMessage());
            }
        }

        $fallback = $this->analysisType->lab_section_id;

        return $fallback !== null && $fallback !== ''
            ? (string) $fallback
            : null;
    }

    protected function resolveOperatorId(?string $label, int $rowNumber): ?string
    {
        if ($label === null || trim($label) === '') {
            return null;
        }

        $label = trim($label);
        $companyId = getUserCompany();

        $operator = User::query()
            ->where('active', 1)
            ->where('is_client', 0)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where(function ($query) use ($label): void {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($label)])
                    ->orWhereRaw('LOWER(TRIM(email)) = ?', [strtolower($label)])
                    ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))) = ?", [strtolower($label)]);
            })
            ->first();

        if ($operator) {
            return (string) $operator->id;
        }

        $this->errors[] = "Row {$rowNumber}: operator '{$label}' was not found — parameter imported without operator.";

        return null;
    }

    protected function resolveEquipmentId(?string $label): ?string
    {
        if ($label === null || trim($label) === '') {
            return null;
        }

        $label = trim($label);
        $companyId = getUserCompany();

        $equipment = Equipment::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where(function ($query) use ($label): void {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($label)])
                    ->orWhereRaw('LOWER(TRIM(equipment_number)) = ?', [strtolower($label)]);
            })
            ->first();

        if ($equipment) {
            return (string) $equipment->id;
        }

        $equipmentNumber = 'IMP-'.strtoupper(substr(md5($label.microtime(true)), 0, 8));

        $equipment = Equipment::create([
            'name' => $label,
            'equipment_number' => $equipmentNumber,
            'description' => $label,
            'make' => 'Unknown',
            'model' => 'Unknown',
            'manufacturer' => 'Unknown',
            'date_purchased' => now()->toDateString(),
            'maintainance_days' => 365,
            'calibration_days' => 365,
            'status' => 'In Use',
            'condition' => 'Good',
            'active' => 1,
            'is_disposal' => false,
            'picture' => '/images/default-equipment.png',
            'company_id' => $companyId,
        ]);

        return (string) $equipment->id;
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
