<?php

namespace Tests\Feature\GroupedWorksheets;

use App\AnalysisElements;
use App\AnalysisType;
use App\CapturedResult;
use App\Enums\GroupedWorksheetItemType;
use App\Livewire\Worksheets\GroupedWorksheetWizard;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\GroupedWorksheets\GroupedWorksheetResultsCapturePost;
use App\Models\GroupedWorksheets\GroupedWorksheetRun;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\StageHeader;
use App\SampleHeader;
use App\Services\Analysis\CapturedResultWorksheetSyncService;
use App\Services\GroupedWorksheets\GroupedResultsCaptureService;
use App\Services\GroupedWorksheets\GroupedWorksheetAssignmentService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Run locally when test DB is safe/isolated:
 * php artisan test --filter=GroupedPipelineSafetyTest
 */
class GroupedPipelineSafetyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_assignment_service_collects_pipeline_reference_ids(): void
    {
        $procedure = ProcedureWorksheet::create([
            'name' => 'Safety Proc '.uniqid(),
            'is_active' => true,
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Safety Holder '.uniqid(),
            'is_active' => true,
        ]);

        $stageHeaderId = (string) Str::uuid();

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'MS',
            'item_type' => GroupedWorksheetItemType::StageHeader,
            'reference_id' => $stageHeaderId,
            'is_required' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 2,
            'label' => 'Confirmation',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        $holder->load('items');

        $refs = app(GroupedWorksheetAssignmentService::class)
            ->pipelineReferenceIdsForHolders(collect([$holder]));

        $this->assertSame([$stageHeaderId], $refs[GroupedWorksheetItemType::StageHeader->value]);
        $this->assertSame([(string) $procedure->id], $refs[GroupedWorksheetItemType::Procedure->value]);
    }

    public function test_results_capture_has_posted_results_helper(): void
    {
        $batch = SampleHeader::query()->first();
        $holder = GroupedWorksheetHolder::query()->first();
        if (! $batch || ! $holder) {
            $this->markTestSkipped('Requires a sample header and grouped holder.');
        }

        $service = app(GroupedResultsCaptureService::class);
        $this->assertFalse($service->hasPostedResults($batch, $holder));

        GroupedWorksheetResultsCapturePost::query()->updateOrCreate(
            [
                'sample_header_id' => $batch->id,
                'grouped_worksheet_holder_id' => $holder->id,
            ],
            [
                'posted_at' => now(),
                'posted_by_user_id' => User::query()->value('id'),
            ]
        );

        $this->assertTrue($service->hasPostedResults($batch, $holder));
    }

    public function test_wizard_blocks_completing_results_capture_without_post(): void
    {
        $user = User::query()->first();
        $batch = SampleHeader::query()->first();
        if (! $user || ! $batch) {
            $this->markTestSkipped('Requires user and sample header.');
        }

        $procedure = ProcedureWorksheet::create([
            'name' => 'Gate Proc '.uniqid(),
            'is_active' => true,
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Gate Pipeline '.uniqid(),
            'is_active' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'Docs',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(GroupedWorksheetWizard::class, [
            'batch' => $batch,
            'holder' => $holder->fresh('items'),
        ]);

        // Advance past the configured procedure stage onto virtual Results Capture.
        $component->call('completeStage');

        $run = GroupedWorksheetRun::query()
            ->where('sample_header_id', $batch->id)
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->first();
        $this->assertNotNull($run);
        $this->assertSame(1, (int) $run->current_item_index);

        $component->call('completeStage')
            ->assertSet('messageType', 'error')
            ->assertSet('message', 'Post Results Capture before completing the pipeline.');

        $run->refresh();
        $this->assertSame(1, (int) $run->current_item_index);
    }

    public function test_sync_keeps_stage_header_when_clearing_procedure_for_grouped_type(): void
    {
        $stageHeader = StageHeader::query()->first();
        $stageHeaderId = $stageHeader?->id ?? (string) Str::uuid();

        $analysisType = new AnalysisType([
            'procedure_worksheet_id' => (string) Str::uuid(),
            'grouped_worksheet_holder_id' => (string) Str::uuid(),
            'hybrid_worksheet_id' => null,
        ]);

        $element = new AnalysisElements([
            'id' => (string) Str::uuid(),
            'stage_header_id' => $stageHeaderId,
            'procedure_worksheet_id' => (string) Str::uuid(),
        ]);

        $captured = new CapturedResult([
            'procedure_worksheet_id' => (string) Str::uuid(),
            'has_procedure_worksheet' => true,
        ]);

        $changed = app(CapturedResultWorksheetSyncService::class)
            ->sync($captured, $analysisType, $element);

        $this->assertTrue($changed);
        $this->assertSame($stageHeaderId, $captured->stage_header_id);
        $this->assertNull($captured->procedure_worksheet_id);
        $this->assertFalse($captured->has_procedure_worksheet);
        $this->assertTrue($captured->has_grouped_worksheet);
    }
}
