<?php

namespace Tests\Feature\Seeders;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisType;
use App\Company;
use App\Country;
use App\Lab;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\CRM\CRMCustomer;
use App\Models\Currency;
use App\SampleType;
use App\StandardAnalytes;
use App\StandardValue;
use App\Standards;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Concerns\ClearsAmSpecFoodPricelistData;
use Database\Seeders\Concerns\ClearsAmSpecFoodStandardsData;
use Database\Seeders\Concerns\ClearsAmSpecStandardLookupData;
use Database\Seeders\Phase13FoodStandardsAndPricelistSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase13FoodStandardsAndPricelistSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'pgsql']);
    }

    public function test_seeder_creates_food_standard_limits_and_adnoc_pricelist(): void
    {
        $company = $this->seedPrerequisites();

        $this->seed(
            Phase13FoodStandardsAndPricelistSeeder::class,
        );

        $standard = Standards::query()
            ->where('code', ClearsAmSpecFoodStandardsData::FOOD_STANDARD_CODE)
            ->first();

        $this->assertNotNull($standard);
        $this->assertTrue((bool) $standard->main_standard);

        $this->assertGreaterThanOrEqual(8, StandardAnalytes::query()
            ->where('standard_id', $standard->id)
            ->count());

        $moistureLimit = StandardAnalytes::query()
            ->where('standard_id', $standard->id)
            ->whereHas('analyte', fn ($q) => $q->where('code', 'MOISTURE AND VOLATILE MATTER'))
            ->first();

        $this->assertNotNull($moistureLimit);
        $this->assertSame('is_range', $moistureLimit->standard_value_type);
        $this->assertSame('34', $moistureLimit->low);
        $this->assertSame('45', $moistureLimit->high);

        $energyLimit = StandardAnalytes::query()
            ->where('standard_id', $standard->id)
            ->whereHas('analyte', fn ($q) => $q->where('code', 'ENERGY'))
            ->first();

        $this->assertNotNull($energyLimit);
        $this->assertSame('min', $energyLimit->value_type);
        $this->assertSame('100', $energyLimit->standard_is_value);

        $pricelist = Pricelist::query()
            ->where('code', ClearsAmSpecFoodPricelistData::FOOD_PRICELIST_CODE)
            ->first();

        $this->assertNotNull($pricelist);
        $this->assertNotNull($pricelist->valid_till);

        $customer = CRMCustomer::query()
            ->where('code', 'INT-ENRG-001')
            ->first();

        $this->assertTrue(
            PricelistCustomer::query()
                ->where('pricelist_id', $pricelist->id)
                ->where('customer_id', $customer->id)
                ->exists()
        );

        $this->assertSame(
            1,
            PricelistItem::query()->where('pricelist_id', $pricelist->id)->count()
        );

        $packageItem = PricelistItem::query()
            ->with('packageElements')
            ->where('pricelist_id', $pricelist->id)
            ->first();

        $this->assertNotNull($packageItem);
        $this->assertTrue((bool) $packageItem->is_package);
        $this->assertNull($packageItem->analysis_element_id);
        $this->assertCount(5, $packageItem->packageElements);

        $analysisIds = PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->pluck('analysis_id');

        $this->assertSame($analysisIds->count(), $analysisIds->unique()->count());

        $this->assertSame(
            3,
            StandardValue::query()->count()
        );

        foreach (ClearsAmSpecStandardLookupData::ALLOWED_STANDARD_VALUE_CODES as $code) {
            $this->assertTrue(
                StandardValue::query()->where('code', $code)->exists(),
                "Missing standard value: {$code}"
            );
        }
    }

    private function seedPrerequisites(): Company
    {
        $country = Country::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'United Arab Emirates',
            'iso_code_2' => 'AE',
            'active' => true,
        ]);

        $company = Company::query()->create(array_merge(
            ['id' => AmSpecSeedData::DUBAI_COMPANY_ID],
            AmSpecSeedData::dubaiCompanyAttributes($country->id),
        ));

        $lab = Lab::query()->create([
            'id' => (string) Str::uuid(),
            'company_id' => $company->id,
            'code' => 'LAB-AGF',
            'name' => 'Agri & Food Lab',
            'active' => true,
        ]);

        Currency::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'AED',
            'description' => 'UAE Dirham',
            'active' => true,
        ]);

        CRMCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'company_id' => $company->id,
            'name' => 'ADNOC Group',
            'code' => 'INT-ENRG-001',
            'active' => 1,
        ]);

        $foodFeed = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'company_id' => $company->id,
            'code' => 'FOOD FEED',
            'name' => 'Food & Feed',
            'active' => true,
        ]);

        $food = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'company_id' => $company->id,
            'code' => 'FOOD',
            'name' => 'Food',
            'active' => true,
        ]);

        $this->createFoodAnalysisChain($company, $lab, $foodFeed, [
            'MESOPHILIC AEROBIC PLATE COUNT',
            'ENTEROBACTERIACEAE',
            'E COLI',
            'COLIFORMS',
            'YEAST AND MOLDS',
        ]);

        $this->createFoodAnalysisChain($company, $lab, $food, [
            'MOISTURE AND VOLATILE MATTER',
            'TOTAL ASH',
            'TOTAL FAT',
            'CARBOHYDRATES',
            'ENERGY',
        ]);

        return $company;
    }

    /**
     * @param  list<string>  $analyteCodes
     */
    private function createFoodAnalysisChain(Company $company, Lab $lab, SampleType $sampleType, array $analyteCodes): void
    {
        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'company_id' => $company->id,
            'sample_type_id' => $sampleType->id,
            'code' => $sampleType->code.'-AT',
            'name' => $sampleType->name.' Analysis',
            'lab_id' => $lab->id,
            'active' => true,
        ]);

        foreach ($analyteCodes as $analyteCode) {
            $analyte = Analyte::query()->create([
                'id' => (string) Str::uuid(),
                'company_id' => $company->id,
                'code' => $analyteCode,
                'name' => str_replace('_', ' ', $analyteCode),
                'active' => true,
            ]);

            AnalysisElements::query()->create([
                'id' => (string) Str::uuid(),
                'analysis_type_id' => $analysisType->id,
                'analyte_id' => $analyte->id,
                'active' => true,
            ]);
        }
    }
}
