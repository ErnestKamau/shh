<?php

namespace Database\Seeders\Concerns;

use App\Company;

/**
 * Food pricelist seeding is owned by Phase 14 (three-pricelist model):
 * master per-parameter, customer per-parameter, and one package pricelist.
 *
 * Kept for backward-compatible Phase 13 wiring.
 */
trait SeedsAmSpecFoodPricelist
{
    use ClearsAmSpecFoodPricelistData;

    /**
     * @return array{pricelist: int, items: int, assignment: int}
     */
    protected function seedAmSpecFoodPricelist(Company $company): array
    {
        $this->clearAmSpecFoodPricelistData();

        $this->command?->info(
            'Skipping Phase 13 food pricelist. Phase 14 seeds master, customer (per-parameter), and one package pricelist.'
        );

        return [
            'pricelist' => 0,
            'items' => 0,
            'assignment' => 0,
        ];

        $customer = CRMCustomer::query()
            ->where('company_id', $company->id)
            ->where('name', self::ADNOC_CUSTOMER_NAME)
            ->first();

        if ($customer === null) {
            $this->command?->error('ADNOC customer ('.self::ADNOC_CUSTOMER_NAME.') not found. Run Phase 3 first.');

            return $stats;
        }

        $currency = Currency::query()
            ->where('code', 'AED')
            ->first()
            ?? Currency::query()->orderBy('code')->first();

        if ($currency === null) {
            $this->command?->error('No currency found. Seed currencies before running Phase 13.');

            return $stats;
        }

        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => self::FOOD_PRICELIST_CODE,
            'description' => 'ADNOC Food Testing Pricelist',
            'currency_id' => $currency->id,
            'is_master' => false,
            'active' => true,
            'document_no' => 'DOC-FOOD-ADNOC',
            'revision_number' => '1',
            'status' => 'no-changes',
            'valid_till' => now()->addYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $stats['pricelist']++;

        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $stats['assignment']++;

        $sampleTypeIds = SampleType::query()
            ->where('company_id', $company->id)
            ->whereIn('code', self::FOOD_SAMPLE_TYPE_CODES)
            ->pluck('id');

        if ($sampleTypeIds->isEmpty()) {
            $this->command?->warn('No FOOD sample type found. Run Phase 8 first.');

            return $stats;
        }

        $elements = AnalysisElements::query()
            ->with(['analysis_type.sample_type', 'analyte'])
            ->whereHas('analysis_type', function ($query) use ($company, $sampleTypeIds): void {
                $query->where('company_id', $company->id)
                    ->whereIn('sample_type_id', $sampleTypeIds);
            })
            ->where('active', true)
            ->orderBy('analysis_type_id')
            ->orderBy('id')
            ->get();

        $level = 1;
        $defaultPrices = [
            'MOISTURE AND VOLATILE MATTER' => ['cost' => 30, 'sell' => 55],
        ];

        foreach ($elements as $element) {
            $analysisType = $element->analysis_type;
            $sampleType = $analysisType?->sample_type;

            if ($analysisType === null || $sampleType === null) {
                continue;
            }

            $analyteCode = $element->analyte?->code ?? '';
            $prices = $defaultPrices[$analyteCode] ?? ['cost' => 25, 'sell' => 50];

            PricelistItem::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $pricelist->id,
                'sample_type_id' => $sampleType->id,
                'analysis_id' => $analysisType->id,
                'analysis_element_id' => $element->id,
                'cost_price' => $prices['cost'],
                'selling_price' => $prices['sell'],
                'changed_price' => $prices['sell'],
                'vat' => false,
                'internal_use' => false,
                'external_view' => true,
                'active' => true,
                'level' => $level,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $level++;
            $stats['items']++;
        }

        $this->command?->info(sprintf(
            'Seeded food pricelist %s for %s with %d item(s), valid till %s.',
            $pricelist->code,
            $customer->name,
            $stats['items'],
            $pricelist->valid_till?->format('Y-m-d') ?? 'n/a',
        ));

        return $stats;
    }
}
