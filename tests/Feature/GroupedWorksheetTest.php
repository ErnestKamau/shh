<?php

namespace Tests\Feature;

use App\AnalysisType;
use App\CapturedResult;
use App\Enums\GroupedWorksheetItemType;
use App\Enums\GroupedWorksheetRunStatus;
use App\Models\Formulars\Formula;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\HybridWorksheets\HybridWorksheet;
use App\Models\HybridWorksheets\HybridWorksheetVersion;
use App\Models\Procedures\ProcedureWorksheet;
use App\SampleHeader;
use App\Services\GroupedWorksheets\GroupedWorksheetAssignmentService;
use App\Services\GroupedWorksheets\GroupedWorksheetPipelineStages;
use App\Services\GroupedWorksheets\GroupedResultsCaptureService;
use App\Services\GroupedWorksheets\GroupedWorksheetRunService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GroupedWorksheetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_grouped_worksheet_manage_route(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user in database.');
        }

        $response = $this->actingAs($user)->get(route('formulars.grouped-worksheets.manage'));

        $response->assertStatus(200);
        $response->assertSee('Grouped worksheet pipelines');
    }

    public function test_can_access_hybrid_worksheet_manage_route(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user in database.');
        }

        $response = $this->actingAs($user)->get(route('formulars.hybrid-worksheets.manage'));

        $response->assertStatus(200);
        $response->assertSee('Hybrid worksheets');
    }

    public function test_can_create_holder_with_ordered_items(): void
    {
        $procedure = ProcedureWorksheet::create([
            'name' => 'Test Recovery '.uniqid(),
            'is_active' => true,
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'DNA Test '.uniqid(),
            'is_active' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'Recovery',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        $formula = Formula::create([
            'name' => 'Extraction '.uniqid(),
            'is_active' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 2,
            'label' => 'Extraction',
            'item_type' => GroupedWorksheetItemType::Formula,
            'reference_id' => $formula->id,
            'is_required' => true,
        ]);

        $holder->refresh();
        $this->assertCount(2, $holder->items);
        $this->assertEquals('Recovery', $holder->items->first()->label);
    }

    public function test_run_service_advances_through_stages(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user in database.');
        }

        $batch = SampleHeader::first();
        if (! $batch) {
            $this->markTestSkipped('No sample header in database.');
        }

        $procedure = ProcedureWorksheet::create([
            'name' => 'Stage A '.uniqid(),
            'is_active' => true,
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Pipeline '.uniqid(),
            'is_active' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'A',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        $runService = app(GroupedWorksheetRunService::class);
        $run = $runService->findOrCreateRun($batch, $holder, $user);

        $this->assertEquals(0, $run->current_item_index);
        $this->assertCount(1, $run->runItems);

        $run = $runService->completeCurrentStage($run, $user);
        $this->assertEquals(1, $run->current_item_index);
        $this->assertEquals(GroupedWorksheetRunStatus::InProgress, $run->status);

        $run = $runService->completeCurrentStage($run, $user);
        $this->assertEquals(GroupedWorksheetRunStatus::Completed, $run->status);
    }

    public function test_pipeline_includes_virtual_results_capture_stage(): void
    {
        $procedure = ProcedureWorksheet::create([
            'name' => 'Stage A '.uniqid(),
            'is_active' => true,
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Pipeline '.uniqid(),
            'is_active' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'A',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 2,
            'label' => 'B',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        $stages = app(GroupedWorksheetPipelineStages::class)->allStages($holder->fresh());

        $this->assertCount(3, $stages);
        $this->assertEquals(
            GroupedWorksheetItemType::ResultsCapture,
            $stages->last()->getItemTypeEnum()
        );
        $this->assertEquals('Results capture', $stages->last()->label);
    }

    public function test_assignment_summary_includes_virtual_results_capture_stage(): void
    {
        $batch = SampleHeader::first();
        $analysisType = AnalysisType::first();
        if (! $batch || ! $analysisType) {
            $this->markTestSkipped('Requires batch and analysis type.');
        }

        $procedure = ProcedureWorksheet::create([
            'name' => 'Summary Stage '.uniqid(),
            'is_active' => true,
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Summary Pipeline '.uniqid(),
            'is_active' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'Configured',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        $analysisType->update(['grouped_worksheet_holder_id' => $holder->id]);

        $captured = CapturedResult::where('sample_header_id', $batch->id)->first();
        if (! $captured) {
            $this->markTestSkipped('No captured results for batch.');
        }

        $captured->update(['analysis_type_id' => $analysisType->id]);

        $summaries = app(GroupedWorksheetAssignmentService::class)
            ->summariesForAnalysisTypeIds([$analysisType->id], $batch);

        $summary = collect($summaries)->firstWhere('holder_id', $holder->id);
        $this->assertNotNull($summary);
        $this->assertEquals(2, $summary['step_count']);
        $this->assertEquals('results_capture', $summary['stages'][1]['item_type']);
    }

    public function test_results_capture_service_saves_draft_without_posting(): void
    {
        $captured = CapturedResult::query()->first();
        if (! $captured) {
            $this->markTestSkipped('No captured results in database.');
        }

        $batch = SampleHeader::query()->find($captured->sample_header_id);
        $holderId = $captured->grouped_worksheet_holder_id;
        if (! $batch || ! $holderId) {
            $this->markTestSkipped('Captured result missing batch or grouped worksheet holder.');
        }

        $holder = GroupedWorksheetHolder::query()->find($holderId);
        if (! $holder) {
            $this->markTestSkipped('Grouped worksheet holder not found.');
        }

        $originalResult = $captured->result;
        $originalSymbol = $captured->result_reporting_symbol;

        $service = app(GroupedResultsCaptureService::class);
        $service->saveDrafts($batch, $holder, [
            (string) $captured->id => [
                'result' => '99.9',
                'reporting_symbol' => '<=',
                'remark' => null,
            ],
        ], []);

        $captured->refresh();
        $this->assertEquals($originalResult, $captured->result);
        $this->assertEquals($originalSymbol, $captured->result_reporting_symbol);

        $draft = \App\Models\GroupedWorksheets\GroupedWorksheetResultsCaptureDraft::query()
            ->where('captured_result_id', $captured->id)
            ->first();
        $this->assertNotNull($draft);
        $this->assertEquals('99.9', $draft->result);
        $this->assertEquals('<=', $draft->reporting_symbol);

        $draft?->delete();
    }

    public function test_results_capture_service_posts_symbol_and_result(): void
    {
        $captured = CapturedResult::query()->first();
        if (! $captured) {
            $this->markTestSkipped('No captured results in database.');
        }

        $batch = SampleHeader::query()->find($captured->sample_header_id);
        $holderId = $captured->grouped_worksheet_holder_id;
        if (! $batch || ! $holderId) {
            $this->markTestSkipped('Captured result missing batch or grouped worksheet holder.');
        }

        $holder = GroupedWorksheetHolder::query()->find($holderId);
        if (! $holder) {
            $this->markTestSkipped('Grouped worksheet holder not found.');
        }

        $originalResult = $captured->result;
        $originalSymbol = $captured->result_reporting_symbol;

        app(GroupedResultsCaptureService::class)->postResults($batch, $holder, [
            (string) $captured->id => [
                'result' => '42.5',
                'reporting_symbol' => '<=',
                'remark' => null,
            ],
        ], []);

        $captured->refresh();
        $this->assertEquals('42.5', $captured->result);
        $this->assertEquals('<=', $captured->result_reporting_symbol);

        $captured->update([
            'result' => $originalResult,
            'result_reporting_symbol' => $originalSymbol,
        ]);

        \App\Models\GroupedWorksheets\GroupedWorksheetResultsCaptureDraft::query()
            ->where('captured_result_id', $captured->id)
            ->delete();
    }

    public function test_assignment_service_resolves_holder_from_analysis_type(): void
    {
        $batch = SampleHeader::first();
        $analysisType = AnalysisType::first();
        if (! $batch || ! $analysisType) {
            $this->markTestSkipped('Requires batch and analysis type.');
        }

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Assigned '.uniqid(),
            'is_active' => true,
        ]);

        $analysisType->update(['grouped_worksheet_holder_id' => $holder->id]);

        $captured = CapturedResult::where('sample_header_id', $batch->id)->first();
        if (! $captured) {
            $this->markTestSkipped('No captured results for batch.');
        }

        $captured->update(['analysis_type_id' => $analysisType->id]);

        $resolved = app(GroupedWorksheetAssignmentService::class)->resolveHoldersForBatch($batch);

        $this->assertTrue($resolved->contains('id', $holder->id));
    }

    public function test_hybrid_worksheet_version_and_reference_block(): void
    {
        $user = User::first();
        $formula = Formula::create(['name' => 'Hybrid Formula '.uniqid(), 'is_active' => true]);

        $hybrid = HybridWorksheet::create(['name' => 'Hybrid '.uniqid(), 'is_active' => true]);
        $version = HybridWorksheetVersion::create([
            'hybrid_worksheet_id' => $hybrid->id,
            'version_number' => 1,
            'is_active' => true,
            'created_by' => $user?->id,
        ]);

        $version->blocks()->create([
            'sort_order' => 1,
            'block_type' => 'formula_reference',
            'reference_id' => $formula->id,
            'label' => 'Calc block',
        ]);

        $this->assertCount(1, $version->fresh()->blocks);
    }
}
