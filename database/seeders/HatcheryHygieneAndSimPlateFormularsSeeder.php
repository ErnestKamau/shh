<?php

namespace Database\Seeders;

use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaMandatoryField;
use App\Models\Formulars\FormulaStep;
use App\Models\Formulars\FormulaVersion;
use App\Models\Formulars\LookupTable;
use App\Models\Formulars\LookupTableEntry;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HatcheryHygieneAndSimPlateFormularsSeeder extends Seeder
{
    /**
     * Seed Hatchery Hygiene + SimPlate formulas and their lookup tables.
     *
     * Run: php artisan db:seed --class=Database\\Seeders\\HatcheryHygieneAndSimPlateFormularsSeeder
     */
    public function run(): void
    {
        $createdBy = User::query()->orderBy('created_at')->value('id');

        if (! $createdBy) {
            throw new RuntimeException(
                'HatcheryHygieneAndSimPlateFormularsSeeder requires at least one user (formula_versions.created_by).'
            );
        }

        DB::transaction(function () use ($createdBy): void {
            $hatcheryLookup = $this->seedHatcheryHygieneLookupTable();
            $simPlateLookup = $this->seedSimPlateLookupTable();
            $this->seedMpnLookupTable();

            $this->seedHatcheryHygieneFormula($hatcheryLookup, (string) $createdBy);
            $this->seedSimPlateFormula($simPlateLookup, (string) $createdBy);
        });

        $this->command?->info('Seeded lookup tables: Hatchery Hygiene CFU, MPN Table, Multi-Dose SimPlate HPC MPN Table TVC.');
        $this->command?->info('Seeded formulas: Hatchery Hygiene CFU Score, Multi-Dose SimPlate HPC MPN.');
    }

    private function seedHatcheryHygieneLookupTable(): LookupTable
    {
        $table = LookupTable::withTrashed()->updateOrCreate(
            ['name' => 'Hatchery Hygiene standards CFU Scores/ 16cm (Squared)'],
            [
                'description' => 'Range-based CFU score and interpretation for hatchery hygiene (16cm squared)',
                'lookup_type' => 'range_based',
                'key_columns' => ['low', 'high'],
                'value_column' => 'Result',
                'range_variable_name' => 'count',
                'value_interpretation_column' => 'interpretation',
                'key_label' => 'CFU count',
                'value_label' => 'Score',
                'is_active' => true,
                'is_standard' => true,
                'show_on_report' => false,
                'deleted_at' => null,
            ]
        );

        $this->replaceLookupEntries($table, function () use ($table): void {
            /** @var list<array{low: int, high: int|null, value: string, interpretation: string}> $entries */
            $entries = require database_path('seeders/data/formulars/hatchery_hygiene_cfu_entries.php');

            foreach ($entries as $entry) {
                LookupTableEntry::create([
                    'lookup_table_id' => $table->id,
                    'keys' => [
                        'low' => $entry['low'],
                        'high' => $entry['high'],
                        'value_interpretation' => $entry['interpretation'],
                    ],
                    'value' => $entry['value'],
                ]);
            }
        });

        return $table;
    }

    private function seedSimPlateLookupTable(): LookupTable
    {
        $table = LookupTable::withTrashed()->updateOrCreate(
            ['name' => 'Multi-Dose SimPlate for HPC MPN Table TVC'],
            [
                'description' => 'Maps number of fluoresced positive wells to MPN per ml (SimPlate HPC)',
                'lookup_type' => 'key_value_comparison',
                'key_columns' => ['positive_wells'],
                'value_column' => 'mpn_value_per_ml',
                'range_variable_name' => null,
                'value_interpretation_column' => null,
                'key_label' => 'Positive wells',
                'value_label' => 'MPN value per ml',
                'is_active' => true,
                'is_standard' => false,
                'show_on_report' => false,
                'deleted_at' => null,
            ]
        );

        $this->replaceLookupEntries($table, function () use ($table): void {
            /** @var list<array{positive_wells: string, value: string}> $entries */
            $entries = require database_path('seeders/data/formulars/simplate_hpc_mpn_entries.php');

            foreach ($entries as $entry) {
                LookupTableEntry::create([
                    'lookup_table_id' => $table->id,
                    'keys' => ['positive_wells' => $entry['positive_wells']],
                    'value' => $entry['value'],
                ]);
            }
        });

        return $table;
    }

    private function seedMpnLookupTable(): LookupTable
    {
        $table = LookupTable::withTrashed()->updateOrCreate(
            ['name' => 'MPN Table'],
            [
                'description' => 'Maps big well and small well positive counts to MPN value',
                'lookup_type' => 'key_value_comparison',
                'key_columns' => ['big_wells', 'small_wells'],
                'value_column' => 'mpn_value',
                'range_variable_name' => null,
                'value_interpretation_column' => null,
                'key_label' => 'Big & Small Well Values',
                'value_label' => 'MPN Value',
                'is_active' => true,
                'is_standard' => false,
                'show_on_report' => false,
                'deleted_at' => null,
            ]
        );

        $this->replaceLookupEntries($table, function () use ($table): void {
            /** @var list<array{big_wells: string, small_wells: string, value: string}> $entries */
            $entries = require database_path('seeders/data/formulars/mpn_table_entries.php');

            foreach ($entries as $entry) {
                LookupTableEntry::create([
                    'lookup_table_id' => $table->id,
                    'keys' => [
                        'big_wells' => $entry['big_wells'],
                        'small_wells' => $entry['small_wells'],
                    ],
                    'value' => $entry['value'],
                ]);
            }
        });

        return $table;
    }

    private function seedHatcheryHygieneFormula(LookupTable $lookupTable, string $createdBy): void
    {
        $formula = Formula::withTrashed()->updateOrCreate(
            ['name' => 'Hatchery Hygiene CFU Score'],
            [
                'description' => 'Score TCC count against Hatchery Hygiene CFU standards (16cm squared)',
                'is_active' => true,
                'deleted_at' => null,
            ]
        );

        $version = $this->ensureActiveVersion($formula, $createdBy);

        $this->replaceFormulaSteps($version, [
            [
                'step_number' => 1,
                'variable_name' => 'tcc_count',
                'step_type' => 'input',
                'expression' => null,
                'label' => 'TCC Count',
                'description' => 'TCC Count',
                'lookup_config' => [],
            ],
            [
                'step_number' => 2,
                'variable_name' => 'score',
                'step_type' => 'lookup',
                'expression' => null,
                'label' => 'score',
                'description' => 'Get the count score from the standard lookup table',
                'lookup_config' => [
                    'lookup_table_id' => $lookupTable->id,
                    'range_variable' => 'tcc_count',
                    'return_interpretation' => false,
                ],
            ],
        ]);

        $this->replaceMandatoryFields($version, [
            [
                'order' => 1,
                'label' => 'MacConkey Agar',
                'field_type' => 'input',
                'form_placement' => 'top',
                'help_text' => 'Provide MacConkey Agar batch no',
                'model_tied_to' => null,
                'is_required' => true,
                'field_value_name' => 'macconkey_agar_batch_no',
            ],
            [
                'order' => 2,
                'label' => 'Expiry Date',
                'field_type' => 'date',
                'form_placement' => 'top',
                'help_text' => null,
                'model_tied_to' => null,
                'is_required' => true,
                'field_value_name' => 'expiry_date',
            ],
            [
                'order' => 3,
                'label' => 'Incubator Used',
                'field_type' => 'dataset_related',
                'form_placement' => 'top',
                'help_text' => null,
                'model_tied_to' => 'equipments',
                'is_required' => true,
                'field_value_name' => 'incubator_id',
            ],
        ]);
    }

    private function seedSimPlateFormula(LookupTable $lookupTable, string $createdBy): void
    {
        $formula = Formula::withTrashed()->updateOrCreate(
            ['name' => 'Multi-Dose SimPlate HPC MPN'],
            [
                'description' => 'Calculate MPN per ml from number of fluoresced wells using SimPlate TVC table',
                'is_active' => true,
                'deleted_at' => null,
            ]
        );

        $version = $this->ensureActiveVersion($formula, $createdBy);

        $this->replaceFormulaSteps($version, [
            [
                'step_number' => 1,
                'variable_name' => 'positive_wells',
                'step_type' => 'input',
                'expression' => null,
                'label' => 'Number of Wells Flouresced',
                'description' => 'Give the count of number of wells flouresced',
                'lookup_config' => [],
            ],
            [
                'step_number' => 2,
                'variable_name' => 'mpn_per_ml',
                'step_type' => 'lookup',
                'expression' => null,
                'label' => 'MPN per ml',
                'description' => 'Calculate the MPN per ml',
                'lookup_config' => [
                    'lookup_table_id' => $lookupTable->id,
                    'key_expressions' => [
                        'positive_wells' => 'positive_wells',
                    ],
                    'key_values' => [
                        'positive_wells' => '',
                    ],
                ],
            ],
        ]);

        $this->replaceMandatoryFields($version, [
            [
                'order' => 1,
                'label' => 'Date & Time of Incubation',
                'field_type' => 'datetime',
                'form_placement' => 'top',
                'help_text' => null,
                'model_tied_to' => null,
                'is_required' => true,
                'field_value_name' => 'date_time_incubation',
            ],
            [
                'order' => 2,
                'label' => 'Date & Time Out of Incubation',
                'field_type' => 'datetime',
                'form_placement' => 'top',
                'help_text' => null,
                'model_tied_to' => null,
                'is_required' => true,
                'field_value_name' => 'date_time_out_incubation',
            ],
            [
                'order' => 3,
                'label' => 'Incubator Used',
                'field_type' => 'dataset_related',
                'form_placement' => 'top',
                'help_text' => null,
                'model_tied_to' => 'equipments',
                'is_required' => true,
                'field_value_name' => 'incubator_id',
            ],
        ]);
    }

    private function ensureActiveVersion(Formula $formula, string $createdBy): FormulaVersion
    {
        $version = FormulaVersion::withTrashed()
            ->where('formula_id', $formula->id)
            ->where('version_number', 1)
            ->first();

        if ($version) {
            $version->update([
                'is_active' => true,
                'mandatory_fields_placement' => 'top',
                'created_by' => $version->created_by ?: $createdBy,
                'deleted_at' => null,
            ]);

            FormulaVersion::query()
                ->where('formula_id', $formula->id)
                ->where('id', '!=', $version->id)
                ->update(['is_active' => false]);

            return $version->fresh();
        }

        FormulaVersion::query()
            ->where('formula_id', $formula->id)
            ->update(['is_active' => false]);

        return FormulaVersion::create([
            'formula_id' => $formula->id,
            'version_number' => 1,
            'is_active' => true,
            'mandatory_fields_placement' => 'top',
            'created_by' => $createdBy,
        ]);
    }

    /**
     * @param  callable(): void  $seed
     */
    private function replaceLookupEntries(LookupTable $table, callable $seed): void
    {
        LookupTableEntry::withTrashed()
            ->where('lookup_table_id', $table->id)
            ->forceDelete();

        $seed();
    }

    /**
     * @param  list<array<string, mixed>>  $steps
     */
    private function replaceFormulaSteps(FormulaVersion $version, array $steps): void
    {
        FormulaStep::withTrashed()
            ->where('formula_version_id', $version->id)
            ->forceDelete();

        foreach ($steps as $step) {
            FormulaStep::create([
                'formula_version_id' => $version->id,
                ...$step,
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     */
    private function replaceMandatoryFields(FormulaVersion $version, array $fields): void
    {
        FormulaMandatoryField::withTrashed()
            ->where('formula_version_id', $version->id)
            ->forceDelete();

        foreach ($fields as $field) {
            FormulaMandatoryField::create([
                'formula_version_id' => $version->id,
                ...$field,
            ]);
        }
    }
}
