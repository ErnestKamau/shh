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
use App\Models\CRM\CustomerContact;
use App\Models\Currency;
use App\SampleType;
use App\User;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Concerns\ClearsSeedCommercialDemoData;
use Database\Seeders\Phase14CommercialDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase14CommercialPricelistSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'pgsql']);
    }

    public function test_seeder_creates_master_customer_and_one_package_pricelist_without_duplicate_parameters(): void
    {
        $this->seedPrerequisites();

        $this->seed(Phase14CommercialDemoSeeder::class);

        $master = Pricelist::query()
            ->where('code', ClearsSeedCommercialDemoData::SEED_PRICELIST_CODE_MASTER)
            ->first();
        $customer = Pricelist::query()
            ->where('code', ClearsSeedCommercialDemoData::SEED_PRICELIST_CODE_CUSTOMER)
            ->first();
        $package = Pricelist::query()
            ->where('code', ClearsSeedCommercialDemoData::SEED_PRICELIST_CODE_PACKAGE)
            ->first();

        $this->assertNotNull($master);
        $this->assertTrue((bool) $master->is_master);
        $this->assertNotNull($customer);
        $this->assertFalse((bool) $customer->is_master);
        $this->assertNotNull($package);
        $this->assertFalse((bool) $package->is_master);

        $this->assertSame(
            3,
            Pricelist::query()
                ->where('code', 'like', ClearsSeedCommercialDemoData::SEED_PRICELIST_CODE_PREFIX.'%')
                ->count()
        );

        $masterItems = PricelistItem::query()->where('pricelist_id', $master->id)->get();
        $customerItems = PricelistItem::query()->where('pricelist_id', $customer->id)->get();
        $packageItems = PricelistItem::query()->where('pricelist_id', $package->id)->get();

        $this->assertTrue($masterItems->every(fn (PricelistItem $item): bool => ! $item->is_package));
        $this->assertTrue($customerItems->every(fn (PricelistItem $item): bool => ! $item->is_package));
        $this->assertTrue($packageItems->every(fn (PricelistItem $item): bool => (bool) $item->is_package));

        $this->assertSame(
            $masterItems->count(),
            $masterItems->pluck('analysis_element_id')->unique()->count()
        );
        $this->assertSame(
            $customerItems->count(),
            $customerItems->pluck('analysis_element_id')->unique()->count()
        );
        $this->assertSame(
            $packageItems->count(),
            $packageItems->pluck('analysis_id')->unique()->count()
        );

        // FOOD analysis type has 5 parameters; master/customer get one row each.
        $this->assertSame(5, $masterItems->count());
        $this->assertSame(5, $customerItems->count());
        $this->assertSame(1, $packageItems->count());
        $this->assertCount(5, $packageItems->first()->load('packageElements')->packageElements);

        $adnoc = CRMCustomer::query()->where('code', 'INT-ENRG-001')->first();
        $this->assertTrue(
            PricelistCustomer::query()
                ->where('pricelist_id', $package->id)
                ->where('customer_id', $adnoc->id)
                ->exists()
        );
    }

    private function seedPrerequisites(): void
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

        User::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Seed Analyst',
            'email' => 'seed-analyst@example.com',
            'password' => bcrypt('password'),
            'active' => 1,
            'is_client' => 0,
        ]);

        foreach ([
            ['code' => 'INT-ENRG-001', 'name' => 'ADNOC Group'],
            ['code' => 'EXT-ENRG-001', 'name' => 'ENOC'],
            ['code' => 'EXT-ENRG-002', 'name' => 'Shell Trading'],
        ] as $customerData) {
            $customer = CRMCustomer::query()->create([
                'id' => (string) Str::uuid(),
                'company_id' => $company->id,
                'name' => $customerData['name'],
                'code' => $customerData['code'],
                'active' => 1,
            ]);

            CustomerContact::query()->create([
                'id' => (string) Str::uuid(),
                'company_id' => $company->id,
                'crm_customer_id' => $customer->id,
                'first_name' => $customerData['name'],
                'last_name' => 'Contact',
                'email' => strtolower($customerData['code']).'@example.com',
                'telephone' => '0000000000',
                'receive_price_list' => true,
                'receive_invoice' => true,
                'receive_report' => true,
                'active' => true,
            ]);
        }

        $food = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'company_id' => $company->id,
            'code' => 'FOOD',
            'name' => 'Food',
            'active' => true,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'company_id' => $company->id,
            'sample_type_id' => $food->id,
            'code' => 'FOOD-AT',
            'name' => 'Food Analysis',
            'lab_id' => $lab->id,
            'active' => true,
        ]);

        foreach (['MOISTURE', 'ASH', 'FAT', 'CARBS', 'ENERGY'] as $analyteCode) {
            $analyte = Analyte::query()->create([
                'id' => (string) Str::uuid(),
                'company_id' => $company->id,
                'code' => $analyteCode,
                'name' => $analyteCode,
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
