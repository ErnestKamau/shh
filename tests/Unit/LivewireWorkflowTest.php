<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Livewire\SampleTypeManager;
use App\Livewire\StandardManager;
use App\SampleType;
use App\Standards;
use App\StandardValue;
use App\SampleTypeCategory;
use App\Lab;
use App\Analyte;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use App\User;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LivewireWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestData();
    }

    private function createTestData()
    {
        $this->category = SampleTypeCategory::create([
            'sample_type_category' => 'Test Category',
            'active' => 1,
        ]);

        $this->lab = Lab::create([
            'name' => 'Test Lab',
            'active' => 1,
        ]);

        $this->analyte = Analyte::create([
            'name' => 'Test Analyte',
            'code' => 'TEST001',
            'active' => 1,
        ]);

        $this->method = AnalysisMethod::create([
            'name' => 'Test Method',
            'active' => 1,
        ]);

        $this->equipment = Equipment::create([
            'name' => 'Test Equipment',
            'active' => 1,
        ]);

        $this->operator = User::create([
            'name' => 'Test Operator',
            'email' => 'operator@test.com',
            'password' => bcrypt('password'),
            'active' => 1,
        ]);
    }

    /** @test */
    public function can_create_sample_type_via_livewire()
    {
        Livewire::test(SampleTypeManager::class)
            ->call('showCreateSampleTypeModal')
            ->set('sampleTypeForm.name', 'Test Sample Type')
            ->set('sampleTypeForm.code', 'SAMPLE001')
            ->set('sampleTypeForm.description', 'Test Description')
            ->set('sampleTypeForm.category_id', $this->category->id)
            ->set('sampleTypeForm.active', true)
            ->call('saveSampleType')
            ->assertHasNoErrors()
            ->assertSee('Sample type created successfully!');

        $this->assertDatabaseHas('sample_types', [
            'name' => 'Test Sample Type',
            'code' => 'SAMPLE001',
        ]);
    }

    /** @test */
    public function can_create_standard_via_livewire()
    {
        Livewire::test(StandardManager::class)
            ->call('showCreateStandardModal')
            ->set('standardForm.name', 'Test Standard')
            ->set('standardForm.code', 'STD001')
            ->set('standardForm.main_standard', false)
            ->set('standardForm.is_qc_standard', false)
            ->set('standardForm.status', true)
            ->call('saveStandard')
            ->assertHasNoErrors()
            ->assertSee('Standard created successfully!');

        $this->assertDatabaseHas('standards', [
            'name' => 'Test Standard',
            'code' => 'STD001',
        ]);
    }

    /** @test */
    public function end_to_end_workflow_test()
    {
        // 1. Create Sample Type
        Livewire::test(SampleTypeManager::class)
            ->call('showCreateSampleTypeModal')
            ->set('sampleTypeForm.name', 'Water Sample')
            ->set('sampleTypeForm.code', 'WATER001')
            ->set('sampleTypeForm.description', 'Water quality testing')
            ->set('sampleTypeForm.category_id', $this->category->id)
            ->set('sampleTypeForm.active', true)
            ->call('saveSampleType');

        $sampleType = SampleType::where('code', 'WATER001')->first();

        // 2. Create Standard
        Livewire::test(StandardManager::class)
            ->call('showCreateStandardModal')
            ->set('standardForm.name', 'Water Quality Standard')
            ->set('standardForm.code', 'WQS001')
            ->set('standardForm.main_standard', true)
            ->set('standardForm.is_qc_standard', false)
            ->set('standardForm.status', true)
            ->call('saveStandard');

        $standard = Standards::where('code', 'WQS001')->first();

        // Verify all relationships exist
        $this->assertDatabaseHas('sample_types', ['code' => 'WATER001']);
        $this->assertDatabaseHas('standards', ['code' => 'WQS001']);
    }
}
