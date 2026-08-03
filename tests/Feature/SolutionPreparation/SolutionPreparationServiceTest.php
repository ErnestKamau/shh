<?php

namespace Tests\Feature\SolutionPreparation;

use App\LabCategoryItems;
use App\LabInventoryCategory;
use App\LabStockMovement;
use App\LabSubCategory;
use App\Models\PreparationStep;
use App\Models\SolutionPreparation;
use App\Models\SolutionPreparationStepTemplate;
use App\ReportingUnit;
use App\Services\Preparation\PreparationRunService;
use App\Services\Preparation\PreparationTemplateService;
use App\Services\StockMovementService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SolutionPreparationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected LabSubCategory $solution;

    protected LabCategoryItems $ingredient;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $category = LabInventoryCategory::create([
            'id' => (string) Str::uuid(),
            'name' => 'Buffers',
            'active' => 1,
        ]);

        $uom = ReportingUnit::create([
            'id' => (string) Str::uuid(),
            'name' => 'mL',
            'active' => 1,
        ]);

        $this->solution = LabSubCategory::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Buffer',
            'category_id' => $category->id,
            'reporting_unit' => $uom->id,
            'active' => 1,
            'stock' => 0,
        ]);

        $this->ingredient = LabCategoryItems::create([
            'id' => (string) Str::uuid(),
            'category_id' => $category->id,
            'sub_category_id' => $this->solution->id,
            'reagent_id' => (string) Str::uuid(),
            'inventory_sub_category_id' => (string) Str::uuid(),
            'unit_measure_id' => $uom->id,
            'amount_used' => 10,
        ]);
    }

    public function test_materializes_template_steps_on_preparation_create(): void
    {
        SolutionPreparationStepTemplate::create([
            'id' => (string) Str::uuid(),
            'lab_sub_category_id' => $this->solution->id,
            'step_number' => 1,
            'step_name' => 'Weigh reagent',
            'step_type' => SolutionPreparationStepTemplate::STEP_TYPE_REGULAR,
            'ingredient_id' => $this->ingredient->id,
        ]);

        $runService = app(PreparationRunService::class);
        $preparation = $runService->createPreparationRecord([
            'solution_id' => $this->solution->id,
            'quantity_prepared' => 100,
            'uom_id' => $this->solution->reporting_unit,
        ]);

        $this->assertCount(1, $preparation->steps);
        $this->assertEquals('Weigh reagent', $preparation->steps->first()->step_name);
        $this->assertEquals('preparing', $preparation->status);
    }

    public function test_regular_step_completion_moves_to_awaiting_approval(): void
    {
        SolutionPreparationStepTemplate::create([
            'id' => (string) Str::uuid(),
            'lab_sub_category_id' => $this->solution->id,
            'step_number' => 1,
            'step_name' => 'Mix',
            'step_type' => SolutionPreparationStepTemplate::STEP_TYPE_REGULAR,
            'ingredient_id' => $this->ingredient->id,
        ]);

        $runService = app(PreparationRunService::class);
        $preparation = $runService->createPreparationRecord([
            'solution_id' => $this->solution->id,
            'quantity_prepared' => 50,
            'uom_id' => $this->solution->reporting_unit,
        ]);

        $step = $preparation->steps->first();
        $runService->completeStep($step);

        $preparation->refresh();
        $this->assertEquals('awaiting_approval', $preparation->status);
    }

    public function test_approve_creates_single_stock_movement_idempotently(): void
    {
        SolutionPreparationStepTemplate::create([
            'id' => (string) Str::uuid(),
            'lab_sub_category_id' => $this->solution->id,
            'step_number' => 1,
            'step_name' => 'Fill',
            'step_type' => SolutionPreparationStepTemplate::STEP_TYPE_REGULAR,
            'ingredient_id' => $this->ingredient->id,
        ]);

        $runService = app(PreparationRunService::class);
        $preparation = $runService->createPreparationRecord([
            'solution_id' => $this->solution->id,
            'quantity_prepared' => 25,
            'uom_id' => $this->solution->reporting_unit,
        ]);

        $runService->completeStep($preparation->steps->first());
        $preparation->update(['status' => 'awaiting_approval']);

        $runService->approve($preparation);
        $preparation = $preparation->fresh(['solution']);
        app(StockMovementService::class)->createPreparationStockMovement($preparation);

        $this->assertEquals(1, LabStockMovement::where('preparation_id', $preparation->id)->count());
        $this->assertEquals('completed', $preparation->fresh()->status);
        $this->assertEquals(25, (float) $this->solution->fresh()->stock);
    }

    public function test_approve_persists_new_batch_expiry_on_solution(): void
    {
        SolutionPreparationStepTemplate::create([
            'id' => (string) Str::uuid(),
            'lab_sub_category_id' => $this->solution->id,
            'step_number' => 1,
            'step_name' => 'Fill',
            'step_type' => SolutionPreparationStepTemplate::STEP_TYPE_REGULAR,
            'ingredient_id' => $this->ingredient->id,
        ]);

        $expiryDate = now()->addYear()->toDateString();
        $runService = app(PreparationRunService::class);
        $preparation = $runService->createPreparationRecord([
            'solution_id' => $this->solution->id,
            'quantity_prepared' => 25,
            'uom_id' => $this->solution->reporting_unit,
            'batch_number' => 'BATCH-EXPIRY-001',
            'expiry_date' => $expiryDate,
            'is_new_batch' => true,
        ]);

        $runService->completeStep($preparation->steps->first());
        $runService->approve($preparation);

        $solution = $this->solution->fresh();
        $this->assertSame('BATCH-EXPIRY-001', $solution->current_batch_number);
        $this->assertSame($expiryDate, $solution->batch_expiry_date);
        $this->assertSame($expiryDate, $preparation->fresh()->expiry_date->toDateString());
    }

    public function test_approve_rejects_new_batch_without_expiry_date(): void
    {
        $preparation = SolutionPreparation::create([
            'solution_id' => $this->solution->id,
            'preparation_number' => SolutionPreparation::generatePreparationNumber(),
            'batch_number' => 'BATCH-NO-EXPIRY',
            'is_new_batch' => true,
            'prepared_by' => $this->user->id,
            'prepared_at' => now(),
            'status' => 'awaiting_approval',
            'quantity_prepared' => 25,
            'uom_id' => $this->solution->reporting_unit,
        ]);

        $this->expectException(ValidationException::class);

        app(PreparationRunService::class)->approve($preparation);
    }

    public function test_template_propagates_to_open_preparation_by_step_name(): void
    {
        $runService = app(PreparationRunService::class);
        $preparation = $runService->createPreparationRecord([
            'solution_id' => $this->solution->id,
            'quantity_prepared' => 10,
            'uom_id' => $this->solution->reporting_unit,
        ]);

        $templateService = app(PreparationTemplateService::class);
        $templateService->addTemplate($this->solution->id, [
            'step_number' => 1,
            'step_name' => 'New dilution step',
            'step_type' => SolutionPreparationStepTemplate::STEP_TYPE_REGULAR,
            'ingredient_id' => $this->ingredient->id,
        ]);

        $this->assertTrue(
            PreparationStep::where('preparation_id', $preparation->id)
                ->where('step_name', 'New dilution step')
                ->exists()
        );
    }

    public function test_preparation_manager_livewire_renders(): void
    {
        $this->get(route('solutions-preparation-index'))
            ->assertOk();
    }
}
