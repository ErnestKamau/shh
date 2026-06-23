<?php

namespace Tests\Feature\Billing;

use App\Livewire\Billing\PricelistShowManager;
use App\Models\Billing\Pricelist;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PricelistShowManagerValidTillTest extends TestCase
{
    use RefreshDatabase;

    public function test_fill_pricelist_form_formats_valid_till_as_date_string(): void
    {
        $currency = Currency::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'AED',
            'description' => 'UAE Dirham',
            'active' => true,
        ]);

        $validTill = Carbon::parse('2027-06-15');

        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-TEST-001',
            'description' => 'Test Pricelist',
            'currency_id' => $currency->id,
            'valid_till' => $validTill,
            'status' => 'no-changes',
            'active' => true,
            'document_no' => 'DOC-TEST',
            'revision_number' => '1',
        ]);

        Livewire::test(PricelistShowManager::class, ['pricelistId' => $pricelist->id])
            ->assertSet('pricelistForm.valid_till', '2027-06-15');
    }

    public function test_save_pricelist_details_preserves_valid_till_when_only_description_changes(): void
    {
        $currency = Currency::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'USD',
            'description' => 'US Dollar',
            'active' => true,
        ]);

        $pricelist = Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PL-TEST-002',
            'description' => 'Original Description',
            'currency_id' => $currency->id,
            'valid_till' => '2027-12-31',
            'status' => 'no-changes',
            'active' => true,
            'document_no' => 'DOC-TEST',
            'revision_number' => '1',
        ]);

        Livewire::test(PricelistShowManager::class, ['pricelistId' => $pricelist->id])
            ->set('pricelistForm.description', 'Updated Description')
            ->call('savePricelist')
            ->assertHasNoErrors();

        $pricelist->refresh();

        $this->assertSame('Updated Description', $pricelist->description);
        $this->assertSame('2027-12-31', $pricelist->valid_till?->format('Y-m-d'));
    }
}
