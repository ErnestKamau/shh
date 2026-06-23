<?php

namespace Database\Seeders\Concerns;

use App\Analyte;
use App\Company;
use App\StandardAnalytes;
use App\StandardValue;
use App\Standards;

trait SeedsAmSpecFoodStandards
{
    use ClearsAmSpecFoodStandardsData;
    use ClearsAmSpecStandardLookupData;

    /**
     * @return array{standard: int, standard_values: int, standard_analytes: int, skipped: int}
     */
    protected function seedAmSpecFoodStandards(Company $company): array
    {
        $this->clearAmSpecStandardLookupData();

        $stats = [
            'standard' => 0,
            'standard_values' => 0,
            'standard_analytes' => 0,
            'skipped' => 0,
        ];

        $isValue = $this->ensureStandardValue('IsValue', 'IsValue', $stats);
        $this->ensureStandardValue('Not Specified', 'Not Specified', $stats);
        $this->ensureStandardValue('Not Detectable', 'Not Detectable', $stats);

        $standard = Standards::query()->updateOrCreate(
            ['code' => self::FOOD_STANDARD_CODE],
            [
                'name' => 'AmSpec Food Product Regulatory Limits',
                'status' => true,
                'main_standard' => true,
                'is_qc_standard' => false,
            ]
        );

        if ($standard->wasRecentlyCreated) {
            $stats['standard']++;
        }

        foreach ($this->foodStandardAnalyteLimits() as $limit) {
            $analyte = Analyte::query()
                ->where('company_id', $company->id)
                ->where('code', $limit['analyte_code'])
                ->first();

            if ($analyte === null) {
                $stats['skipped']++;
                $this->command?->warn("Skipping food standard limit — analyte not found: {$limit['analyte_code']}");

                continue;
            }

            $payload = $this->buildStandardAnalytePayload($limit, $isValue);

            StandardAnalytes::query()->updateOrCreate(
                [
                    'standard_id' => $standard->id,
                    'analyte_id' => $analyte->id,
                ],
                $payload
            );

            $stats['standard_analytes']++;
            $this->command?->info("Seeded food standard limit: {$analyte->name} ({$limit['expected_value']})");
        }

        return $stats;
    }

    /**
     * @param  array{standard_values: int}  $stats
     */
    private function ensureStandardValue(string $code, string $name, array &$stats): StandardValue
    {
        $value = StandardValue::query()->updateOrCreate(
            ['code' => $code],
            ['name' => $name, 'status' => true]
        );

        if ($value->wasRecentlyCreated) {
            $stats['standard_values']++;
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $limit
     * @return array<string, mixed>
     */
    private function buildStandardAnalytePayload(array $limit, StandardValue $isValue): array
    {
        if ($limit['value_type'] === 'range') {
            return [
                'standard_value_type' => 'is_range',
                'low' => (string) $limit['low'],
                'high' => (string) $limit['high'],
                'value_type' => 'range',
                'expected_value' => $limit['expected_value'],
                'is_active' => true,
            ];
        }

        return [
            'standard_value_type' => 'is_standard_value',
            'standard_value_id' => $isValue->id,
            'standard_is_value' => (string) $limit['standard_is_value'],
            'value_type' => $limit['value_type'],
            'matrix_operator' => $limit['value_type'],
            'matrix_value' => (string) $limit['standard_is_value'],
            'expected_value' => $limit['expected_value'],
            'is_active' => true,
        ];
    }

    /**
     * @return list<array{analyte_code: string, value_type: string, low?: string|int|float, high?: string|int|float, standard_is_value?: string|int|float, expected_value: string}>
     */
    private function foodStandardAnalyteLimits(): array
    {
        return [
            [
                'analyte_code' => 'MOISTURE AND VOLATILE MATTER',
                'value_type' => 'range',
                'low' => '34',
                'high' => '45',
                'expected_value' => '34 - 45',
            ],
            [
                'analyte_code' => 'TOTAL ASH',
                'value_type' => 'max',
                'standard_is_value' => '5',
                'expected_value' => 'max 5',
            ],
            [
                'analyte_code' => 'TOTAL FAT',
                'value_type' => 'range',
                'low' => '10',
                'high' => '30',
                'expected_value' => '10 - 30',
            ],
            [
                'analyte_code' => 'CARBOHYDRATES',
                'value_type' => 'min',
                'standard_is_value' => '20',
                'expected_value' => 'min 20',
            ],
            [
                'analyte_code' => 'ENERGY',
                'value_type' => 'min',
                'standard_is_value' => '100',
                'expected_value' => 'min 100',
            ],
            [
                'analyte_code' => 'MESOPHILIC AEROBIC PLATE COUNT',
                'value_type' => 'max',
                'standard_is_value' => '100',
                'expected_value' => 'max 100',
            ],
            [
                'analyte_code' => 'ENTEROBACTERIACEAE',
                'value_type' => 'max',
                'standard_is_value' => '10',
                'expected_value' => 'max 10',
            ],
            [
                'analyte_code' => 'E COLI',
                'value_type' => 'max',
                'standard_is_value' => '0',
                'expected_value' => 'max 0',
            ],
            [
                'analyte_code' => 'COLIFORMS',
                'value_type' => 'max',
                'standard_is_value' => '100',
                'expected_value' => 'max 100',
            ],
            [
                'analyte_code' => 'YEAST AND MOLDS',
                'value_type' => 'max',
                'standard_is_value' => '50',
                'expected_value' => 'max 50',
            ],
        ];
    }
}
