<?php

namespace App\Services\Procedures;

use App\AnalysisMethod;
use App\AnalysisType;
use App\Lab;
use App\Models\CRM\CompanyProduct;
use App\Models\CRM\SamplePoint;
use App\Models\CustomerSubmissionFormColumn;
use App\Models\Equipments\Equipment;
use App\ReportingUnit;
use App\SampleHeader;
use App\SampleCondition;
use App\SampleType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProcedureConfigFieldSampleCatalog
{
    /**
     * Foreign-key columns on sample_details and how to resolve display values.
     *
     * @return array<string, array{label: string, display_columns: array<string, string>}>
     */
    public static function foreignKeyRelations(): array
    {
        $sampleHeaderDisplayColumns = self::tableColumnDisplayOptions('sample_headers', ['deleted_at']);

        return [
            'sample_header_id' => [
                'label' => 'Sample Header',
                'display_columns' => $sampleHeaderDisplayColumns,
                'resolver' => fn ($id) => SampleHeader::find($id),
            ],
            'company_product_id' => [
                'label' => 'Product',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => CompanyProduct::find($id),
            ],
            'sample_point_id' => [
                'label' => 'Sample source / point',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => SamplePoint::find($id),
            ],
            'lab_id' => [
                'label' => 'Lab',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => Lab::find($id),
            ],
            'sample_condition_id' => [
                'label' => 'Sample condition',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => SampleCondition::find($id),
            ],
            'reporting_unit_id' => [
                'label' => 'Reporting unit',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => ReportingUnit::find($id),
            ],
            'sample_type_id' => [
                'label' => 'Sample type',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => SampleType::find($id),
            ],
            'main_standard' => [
                'label' => 'Main standard',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => AnalysisMethod::find($id),
            ],
            'secondary_standard' => [
                'label' => 'Secondary standard',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => AnalysisMethod::find($id),
            ],
            'third_standard_id' => [
                'label' => 'Third standard',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => AnalysisMethod::find($id),
            ],
            'analysis_type_id' => [
                'label' => 'Analysis type',
                'display_columns' => [
                    'name' => 'Name',
                    'code' => 'Code',
                ],
                'resolver' => fn ($id) => AnalysisType::find($id),
            ],
            'equipment_id' => [
                'label' => 'Equipment',
                'display_columns' => [
                    'name' => 'Name',
                    'equipment_number' => 'Equipment number',
                ],
                'resolver' => fn ($id) => Equipment::find($id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sampleDetailColumnOptions(): array
    {
        $preferred = CustomerSubmissionFormColumn::sampleDetailColumns();

        if (Schema::hasTable('sample_details')) {
            foreach (Schema::getColumnListing('sample_details') as $column) {
                if (in_array($column, ['id', 'deleted_at'], true)) {
                    continue;
                }
                if (! isset($preferred[$column])) {
                    $preferred[$column] = Str::title(str_replace('_', ' ', $column));
                }
            }
        }

        uasort($preferred, fn (string $a, string $b) => strcasecmp($a, $b));

        return $preferred;
    }

    public static function isForeignKeyColumn(string $column): bool
    {
        return array_key_exists($column, self::foreignKeyRelations())
            || Str::endsWith($column, '_id');
    }

    /**
     * @return array<string, string>
     */
    public static function relationDisplayColumnsFor(string $column): array
    {
        $relations = self::foreignKeyRelations();
        if (isset($relations[$column])) {
            return $relations[$column]['display_columns'];
        }

        if (Str::endsWith($column, '_id')) {
            return [
                'id' => 'ID',
                'name' => 'Name',
                'code' => 'Code',
            ];
        }

        return [];
    }

    public static function foreignKeyLabel(string $column): string
    {
        return self::foreignKeyRelations()[$column]['label']
            ?? Str::title(str_replace('_', ' ', preg_replace('/_id$/', '', $column) ?: $column));
    }

    /**
     * @return callable|null
     */
    public static function relationResolver(string $column): ?callable
    {
        return self::foreignKeyRelations()[$column]['resolver'] ?? null;
    }

    /**
     * @param  array<int, string>  $excludedColumns
     * @return array<string, string>
     */
    protected static function tableColumnDisplayOptions(string $table, array $excludedColumns = []): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return collect(Schema::getColumnListing($table))
            ->reject(fn (string $column) => in_array($column, $excludedColumns, true))
            ->mapWithKeys(fn (string $column) => [$column => Str::title(str_replace('_', ' ', $column))])
            ->all();
    }
}
