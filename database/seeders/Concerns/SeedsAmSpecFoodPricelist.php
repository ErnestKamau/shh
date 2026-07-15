<?php

namespace Database\Seeders\Concerns;

use App\AnalysisElements;
use App\Company;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\Billing\PricelistItemElement;
use App\Models\CRM\CRMCustomer;
use App\Models\Currency;
use App\SampleType;
use Illuminate\Support\Str;

trait SeedsAmSpecFoodPricelist
{
    use ClearsAmSpecFoodPricelistData;

    public const ADNOC_CUSTOMER_CODE = 'INT-ENRG-001';

    public const ADNOC_CUSTOMER_NAME = 'ADNOC Group';

    /** @var list<string> */
    private const FOOD_SAMPLE_TYPE_CODES = ['FOOD'];

    /**
     * @return array{pricelist: int, items: int, assignment: int}
     */
    protected function seedAmSpecFoodPricelist(Company $company): array
    {
        $this->clearAmSpecFoodPricelistData();

        $stats = [
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
            'valid_till' => now()->addYear()->toDateString(),
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
            ->get()
            ->groupBy('analysis_type_id');

        $level = 1;

        foreach ($elements as $analysisElements) {
            $first = $analysisElements->first();
            $analysisType = $first?->analysis_type;
            $sampleType = $analysisType?->sample_type;

            if ($analysisType === null || $sampleType === null) {
                continue;
            }

            $parameterCount = $analysisElements->count();
            $cost = 25 * $parameterCount;
            $sell = 50 * $parameterCount;

            $item = PricelistItem::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $pricelist->id,
                'sample_type_id' => $sampleType->id,
                'analysis_id' => $analysisType->id,
                'analysis_element_id' => null,
                'cost_price' => $cost,
                'selling_price' => $sell,
                'changed_price' => $sell,
                'vat' => false,
                'internal_use' => false,
                'external_view' => true,
                'active' => true,
                'is_package' => true,
                'level' => $level,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($analysisElements as $element) {
                PricelistItemElement::query()->create([
                    'id' => (string) Str::uuid(),
                    'pricelist_item_id' => $item->id,
                    'analysis_element_id' => $element->id,
                ]);
            }

            $level++;
            $stats['items']++;
        }

        $this->command?->info(sprintf(
            'Seeded food pricelist %s for %s with %d package item(s) (one per analysis type), valid till %s.',
            $pricelist->code,
            $customer->name,
            $stats['items'],
            $pricelist->valid_till?->format('Y-m-d') ?? 'n/a',
        ));

        return $stats;
    }
}
