<?php

namespace Tests\Unit\Services;

use App\AnalysisType;
use App\Models\CRM\CRMCustomer;
use App\Models\CustomerAnalysisTypeStandard;
use App\SampleType;
use App\Services\Sampleworkflow\CustomerAnalysisTypeStandardService;
use App\Standards;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerAnalysisTypeStandardServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CustomerAnalysisTypeStandardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CustomerAnalysisTypeStandardService::class);

        if (! Schema::hasColumn('analysis_types', 'default_standard_id')
            || ! Schema::hasTable('customer_analysis_type_standards')
            || ! Schema::hasColumn('customer_analysis_type_standards', 'standard_id')) {
            $this->markTestSkipped('Default standard migrations have not been applied.');
        }
    }

    public function test_resolve_prefill_uses_analysis_type_default_when_no_preference(): void
    {
        [$customer, $analysisType, $defaultStandard] = $this->seedCustomerAnalysisTypeAndStandards();

        $resolved = $this->service->resolvePrefillStandardId(
            (string) $customer->id,
            (string) $analysisType->id,
        );

        $this->assertSame((string) $defaultStandard->id, $resolved);
    }

    public function test_resolve_prefill_prefers_customer_preference_over_type_default(): void
    {
        [$customer, $analysisType, $defaultStandard, $overrideStandard] = $this->seedCustomerAnalysisTypeAndStandards(withOverride: true);

        CustomerAnalysisTypeStandard::query()->create([
            'crm_customer_id' => $customer->id,
            'analysis_type_id' => $analysisType->id,
            'standard_id' => $overrideStandard->id,
        ]);

        $resolved = $this->service->resolvePrefillStandardId(
            (string) $customer->id,
            (string) $analysisType->id,
        );

        $this->assertSame((string) $overrideStandard->id, $resolved);
        $this->assertNotSame((string) $defaultStandard->id, $resolved);
    }

    public function test_apply_prefill_fills_empty_main_standard_only(): void
    {
        [$customer, $analysisType, $defaultStandard, $overrideStandard] = $this->seedCustomerAnalysisTypeAndStandards(withOverride: true);

        $configs = $this->service->applyPrefillToConfigs([
            [
                'analysis_type_id' => (string) $analysisType->id,
                'main_standard_id' => null,
            ],
            [
                'analysis_type_id' => (string) $analysisType->id,
                'main_standard_id' => (string) $overrideStandard->id,
            ],
        ], (string) $customer->id);

        $this->assertSame((string) $defaultStandard->id, $configs[0]['main_standard_id']);
        $this->assertSame((string) $overrideStandard->id, $configs[1]['main_standard_id']);
    }

    public function test_sync_preferences_saves_when_standard_differs_from_type_default(): void
    {
        [$customer, $analysisType, $defaultStandard, $overrideStandard] = $this->seedCustomerAnalysisTypeAndStandards(withOverride: true);

        $this->service->syncPreferencesFromConfigs((string) $customer->id, [
            [
                'analysis_type_id' => (string) $analysisType->id,
                'main_standard_id' => (string) $overrideStandard->id,
            ],
        ]);

        $preference = CustomerAnalysisTypeStandard::query()
            ->where('crm_customer_id', $customer->id)
            ->where('analysis_type_id', $analysisType->id)
            ->first();

        $this->assertNotNull($preference);
        $this->assertSame((string) $overrideStandard->id, (string) $preference->standard_id);
        $this->assertSame((string) $defaultStandard->id, (string) $analysisType->fresh()->default_standard_id);
    }

    public function test_sync_preferences_clears_when_standard_matches_type_default(): void
    {
        [$customer, $analysisType, $defaultStandard, $overrideStandard] = $this->seedCustomerAnalysisTypeAndStandards(withOverride: true);

        CustomerAnalysisTypeStandard::query()->create([
            'crm_customer_id' => $customer->id,
            'analysis_type_id' => $analysisType->id,
            'standard_id' => $overrideStandard->id,
        ]);

        $this->service->syncPreferencesFromConfigs((string) $customer->id, [
            [
                'analysis_type_id' => (string) $analysisType->id,
                'main_standard_id' => (string) $defaultStandard->id,
            ],
        ]);

        $this->assertFalse(
            CustomerAnalysisTypeStandard::query()
                ->where('crm_customer_id', $customer->id)
                ->where('analysis_type_id', $analysisType->id)
                ->exists()
        );
    }

    public function test_sync_preferences_last_chosen_non_default_wins_for_same_analysis_type(): void
    {
        [$customer, $analysisType, $defaultStandard, $overrideStandard] = $this->seedCustomerAnalysisTypeAndStandards(withOverride: true);
        $secondOverride = $this->createStandard('Second Override '.Str::random(4));

        $this->service->syncPreferencesFromConfigs((string) $customer->id, [
            [
                'analysis_type_id' => (string) $analysisType->id,
                'main_standard_id' => (string) $overrideStandard->id,
            ],
            [
                'analysis_type_id' => (string) $analysisType->id,
                'main_standard_id' => (string) $secondOverride->id,
            ],
        ]);

        $preference = CustomerAnalysisTypeStandard::query()
            ->where('crm_customer_id', $customer->id)
            ->where('analysis_type_id', $analysisType->id)
            ->first();

        $this->assertNotNull($preference);
        $this->assertSame((string) $secondOverride->id, (string) $preference->standard_id);
        $this->assertNotSame((string) $defaultStandard->id, (string) $preference->standard_id);
    }

    /**
     * @return array{0: CRMCustomer, 1: AnalysisType, 2: Standards, 3?: Standards}
     */
    private function seedCustomerAnalysisTypeAndStandards(bool $withOverride = false): array
    {
        $suffix = Str::upper(Str::random(5));

        $customer = CRMCustomer::query()->create([
            'name' => 'Std Pref Customer '.$suffix,
            'code' => 'SPC'.$suffix,
            'active' => 1,
        ]);

        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Food Pref '.$suffix,
            'code' => 'FP-'.$suffix,
            'active' => 1,
        ]);

        $defaultStandard = $this->createStandard('Default Std '.$suffix);
        $overrideStandard = $withOverride ? $this->createStandard('Override Std '.$suffix) : null;

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Analysis Pref '.$suffix,
            'code' => 'AP-'.$suffix,
            'sample_type_id' => $sampleType->id,
            'active' => 1,
            'default_standard_id' => $defaultStandard->id,
            'company_id' => (string) Str::uuid(),
        ]);

        if ($withOverride) {
            return [$customer, $analysisType, $defaultStandard, $overrideStandard];
        }

        return [$customer, $analysisType, $defaultStandard];
    }

    private function createStandard(string $name): Standards
    {
        return Standards::query()->create([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'code' => 'STD-'.Str::upper(Str::random(6)),
            'status' => true,
            'main_standard' => true,
        ]);
    }
}
