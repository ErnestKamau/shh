<?php

namespace Tests\Feature\Analysis;

use App\AnalysisElements;
use App\AnalysisType;
use App\CapturedResult;
use App\Enums\GroupedWorksheetItemType;
use App\Livewire\Analysis\SyncWorksheetsModal;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\Procedures\ProcedureWorksheet;
use App\SampleAnalysisTypeRelation;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Analysis\CapturedResultWorksheetSyncService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Run locally when test DB is configured (see phpunit.xml):
 * php artisan test --filter=SyncWorksheetsModalTest
 */
class SyncWorksheetsModalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_worksheet_sync_service_applies_type_and_element_defaults_without_database(): void
    {
        $procedureId = (string) Str::uuid();
        $formularId = (string) Str::uuid();
        $methodSequenceId = (string) Str::uuid();
        $stageHeaderId = (string) Str::uuid();
        $elementId = (string) Str::uuid();

        $analysisType = new AnalysisType([
            'procedure_worksheet_id' => $procedureId,
            'grouped_worksheet_holder_id' => null,
            'hybrid_worksheet_id' => null,
        ]);

        $element = new AnalysisElements([
            'id' => $elementId,
            'result_is_calculated' => true,
            'formular_id' => $formularId,
            'has_method_sequence' => true,
            'method_sequence_id' => $methodSequenceId,
            'stage_header_id' => $stageHeaderId,
            'procedure_worksheet_id' => null,
        ]);

        $captured = new CapturedResult([
            'result' => null,
        ]);

        $service = new CapturedResultWorksheetSyncService();
        $changed = $service->sync($captured, $analysisType, $element);

        $this->assertTrue($changed);
        $this->assertSame($elementId, $captured->analysis_element_id);
        $this->assertSame($formularId, $captured->formular_id);
        $this->assertSame($methodSequenceId, $captured->method_sequence_id);
        $this->assertSame($stageHeaderId, $captured->stage_header_id);
        $this->assertSame($procedureId, $captured->procedure_worksheet_id);
        $this->assertTrue($captured->has_procedure_worksheet);
        $this->assertSame('No attachment', $captured->result);
        $this->assertNull($captured->grouped_worksheet_holder_id);
        $this->assertFalse((bool) $captured->has_grouped_worksheet);
    }

    public function test_worksheet_sync_service_clears_procedure_when_type_uses_grouped_pipeline(): void
    {
        $procedureId = (string) Str::uuid();
        $groupedHolderId = (string) Str::uuid();
        $stageHeaderId = (string) Str::uuid();
        $elementId = (string) Str::uuid();

        $analysisType = new AnalysisType([
            'procedure_worksheet_id' => $procedureId,
            'grouped_worksheet_holder_id' => $groupedHolderId,
            'hybrid_worksheet_id' => null,
        ]);

        $element = new AnalysisElements([
            'id' => $elementId,
            'stage_header_id' => $stageHeaderId,
            'procedure_worksheet_id' => $procedureId,
        ]);

        $captured = new CapturedResult([
            'procedure_worksheet_id' => $procedureId,
            'has_procedure_worksheet' => true,
            'hybrid_worksheet_id' => (string) Str::uuid(),
            'has_hybrid_worksheet' => true,
            'result' => 'No attachment',
        ]);

        $service = new CapturedResultWorksheetSyncService();
        $changed = $service->sync($captured, $analysisType, $element);

        $this->assertTrue($changed);
        $this->assertSame($groupedHolderId, $captured->grouped_worksheet_holder_id);
        $this->assertTrue($captured->has_grouped_worksheet);
        $this->assertSame($stageHeaderId, $captured->stage_header_id);
        $this->assertNull($captured->procedure_worksheet_id);
        $this->assertFalse($captured->has_procedure_worksheet);
        $this->assertNull($captured->hybrid_worksheet_id);
        $this->assertFalse($captured->has_hybrid_worksheet);
    }

    public function test_modal_loads_samples_for_selected_workflow_stage(): void
    {
        $user = User::query()->where('active', 1)->first();
        if (! $user) {
            $this->markTestSkipped('No active user in database.');
        }

        $fixture = $this->createSyncFixture();
        if ($fixture === null) {
            return;
        }

        $this->actingAs($user);

        Livewire::test(SyncWorksheetsModal::class, ['analysisTypeId' => $fixture['analysis_type_id']])
            ->call('openModal')
            ->set('selectedWorkflowStatuses', ['Samples In Lab'])
            ->call('loadSampleRows')
            ->assertSet('sampleRows.0.sample_detail_id', $fixture['sample_detail_id'])
            ->assertSet('selectedSampleIds.0', true);
    }

    public function test_modal_sync_updates_captured_results(): void
    {
        $user = User::query()->where('active', 1)->first();
        if (! $user) {
            $this->markTestSkipped('No active user in database.');
        }

        $fixture = $this->createSyncFixture();
        if ($fixture === null) {
            return;
        }

        $captured = CapturedResult::query()->find($fixture['captured_result_id']);
        $this->assertNotNull($captured);
        $captured->update([
            'procedure_worksheet_id' => null,
            'has_procedure_worksheet' => false,
            'grouped_worksheet_holder_id' => null,
            'has_grouped_worksheet' => false,
            'formular_id' => null,
            'method_sequence_id' => null,
            'result' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(SyncWorksheetsModal::class, ['analysisTypeId' => $fixture['analysis_type_id']])
            ->call('openModal')
            ->set('selectedWorkflowStatuses', ['Samples In Lab'])
            ->call('loadSampleRows')
            ->call('syncWorksheets')
            ->assertSet('messageType', 'success');

        $captured->refresh();

        // Grouped pipelines own procedures as items — sync must not stamp a standalone procedure FK.
        $this->assertNull($captured->procedure_worksheet_id);
        $this->assertFalse((bool) $captured->has_procedure_worksheet);
        $this->assertSame($fixture['grouped_worksheet_holder_id'], $captured->grouped_worksheet_holder_id);
        $this->assertTrue($captured->has_grouped_worksheet);
        if ($fixture['formular_id'] !== '') {
            $this->assertSame($fixture['formular_id'], $captured->formular_id);
        }
    }

    /**
     * @return array<string, string>|null
     */
    protected function createSyncFixture(): ?array
    {
        $analysisType = AnalysisType::query()->first();
        if (! $analysisType) {
            $this->markTestSkipped('No analysis type in database.');

            return null;
        }

        $procedure = ProcedureWorksheet::create([
            'name' => 'Sync Test Procedure '.uniqid(),
            'is_active' => true,
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Sync Test Holder '.uniqid(),
            'is_active' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'Step 1',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        $analysisType->update([
            'procedure_worksheet_id' => null,
            'grouped_worksheet_holder_id' => $holder->id,
            'hybrid_worksheet_id' => null,
        ]);

        $batch = SampleHeader::query()->where('status', 'Samples In Lab')->where('isactive', true)->first();
        if (! $batch) {
            $batch = SampleHeader::query()->where('isactive', true)->first();
        }
        if (! $batch) {
            $this->markTestSkipped('No sample header in database.');

            return null;
        }

        $batch->update(['status' => 'Samples In Lab', 'isactive' => true]);

        $sample = SampleDetails::query()->where('sample_header_id', $batch->id)->first();
        if (! $sample) {
            $this->markTestSkipped('No sample detail for batch.');

            return null;
        }

        $relation = SampleAnalysisTypeRelation::query()
            ->where('batch_id', $batch->id)
            ->where('sample_detail_id', $sample->id)
            ->where('analysis_type_id', $analysisType->id)
            ->first();

        if (! $relation) {
            SampleAnalysisTypeRelation::create([
                'batch_id' => $batch->id,
                'sample_detail_id' => $sample->id,
                'analysis_type_id' => $analysisType->id,
            ]);
        }

        $element = AnalysisElements::query()
            ->where('analysis_type_id', $analysisType->id)
            ->first();

        if (! $element) {
            $this->markTestSkipped('No analysis element for analysis type.');

            return null;
        }

        $element->update([
            'result_is_calculated' => (bool) $element->formular_id,
            'procedure_worksheet_id' => null,
        ]);

        $captured = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('sample_detail_id', $sample->id)
            ->where('analysis_type_id', $analysisType->id)
            ->where('analyte_id', $element->analyte_id)
            ->first();

        if (! $captured) {
            $captured = CapturedResult::create([
                'sample_detail_code' => $sample->sample_code,
                'sample_detail_id' => $sample->id,
                'sample_header_id' => $batch->id,
                'analyte_id' => $element->analyte_id,
                'analyte_code' => 'SYNC-TEST',
                'analysis_type_id' => $analysisType->id,
                'user_id' => User::query()->where('active', 1)->value('id'),
            ]);
        }

        return [
            'analysis_type_id' => (string) $analysisType->id,
            'sample_detail_id' => (string) $sample->id,
            'captured_result_id' => (string) $captured->id,
            'procedure_worksheet_id' => (string) $procedure->id,
            'grouped_worksheet_holder_id' => (string) $holder->id,
            'formular_id' => $element->formular_id ? (string) $element->formular_id : '',
        ];
    }
}
