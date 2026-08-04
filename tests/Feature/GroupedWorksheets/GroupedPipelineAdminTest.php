<?php

namespace Tests\Feature\GroupedWorksheets;

use App\Enums\GroupedWorksheetItemType;
use App\Livewire\GroupedWorksheets\GroupedWorksheetHolderManager;
use App\Livewire\GroupedWorksheets\GroupedWorksheetItemEditor;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\StageHeader;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class GroupedPipelineAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_holder_manager_persists_pipeline_mode_and_results_settings(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user in database.');
        }

        $this->actingAs($user);

        $name = 'Admin Pipeline '.uniqid();

        Livewire::test(GroupedWorksheetHolderManager::class)
            ->set('name', $name)
            ->set('pipeline_mode', 'phased')
            ->set('results_capture_enabled', true)
            ->set('results_capture_label', 'Final results')
            ->set('results_capture_required', false)
            ->call('save')
            ->assertHasNoErrors();

        $holder = GroupedWorksheetHolder::query()->where('name', $name)->first();
        $this->assertNotNull($holder);
        $this->assertEquals('phased', $holder->getSettingValue('pipeline_mode'));
        $this->assertEquals('Final results', data_get($holder->settings, 'results_capture.label'));
        $this->assertFalse((bool) data_get($holder->settings, 'results_capture.required'));
    }

    public function test_item_editor_saves_stage_header_phase_config(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user in database.');
        }

        $stageHeader = StageHeader::query()->first();
        if (! $stageHeader) {
            $this->markTestSkipped('No stage header in database.');
        }

        $this->actingAs($user);

        $procedure = ProcedureWorksheet::create([
            'name' => 'Matrix Proc '.uniqid(),
            'is_active' => true,
            'layout_settings' => [
                'mode' => 'sectioned_matrix',
                'sections' => [
                    [
                        'key' => 'enrichment',
                        'label' => 'Enrichment',
                        'rows' => [
                            ['key' => 'pre_enrichment', 'label' => 'Pre'],
                            ['key' => 'rv_enrichment', 'label' => 'RV'],
                        ],
                        'columns' => [
                            [
                                'key' => 'observation',
                                'label' => 'Observation',
                                'type' => 'step_select',
                                'shared' => false,
                                'step_key_by_row' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Phased Holder '.uniqid(),
            'is_active' => true,
            'settings' => ['pipeline_mode' => 'phased'],
        ]);

        Livewire::test(GroupedWorksheetItemEditor::class, ['holder' => $holder])
            ->call('openAddModal')
            ->set('label', 'Pre-enrichment')
            ->set('item_type', GroupedWorksheetItemType::StageHeader->value)
            ->set('reference_id', $stageHeader->id)
            ->set('config_stage_order', 1)
            ->set('config_procedure_worksheet_id', $procedure->id)
            ->set('config_section_key', 'enrichment')
            ->set('config_row_keys', ['pre_enrichment'])
            ->set('config_show_config_fields', false)
            ->call('addItem')
            ->assertHasNoErrors();

        $item = GroupedWorksheetItem::query()
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->first();

        $this->assertNotNull($item);
        $this->assertEquals(1, $item->getConfigValue('stage_order'));
        $this->assertEquals($procedure->id, $item->getConfigValue('procedure_worksheet_id'));
        $this->assertEquals('enrichment', $item->getConfigValue('section_key'));
        $this->assertEquals(['pre_enrichment'], $item->getConfigValue('row_keys'));
        $this->assertFalse((bool) $item->getConfigValue('show_config_fields'));
    }

    public function test_phased_stage_header_requires_stage_order(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user in database.');
        }

        $stageHeader = StageHeader::query()->first();
        if (! $stageHeader) {
            $this->markTestSkipped('No stage header in database.');
        }

        $this->actingAs($user);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Phased Holder '.uniqid(),
            'is_active' => true,
            'settings' => ['pipeline_mode' => 'phased'],
        ]);

        Livewire::test(GroupedWorksheetItemEditor::class, ['holder' => $holder])
            ->call('openAddModal')
            ->set('label', 'Missing order')
            ->set('item_type', GroupedWorksheetItemType::StageHeader->value)
            ->set('reference_id', $stageHeader->id)
            ->set('config_stage_order', null)
            ->call('addItem')
            ->assertHasErrors(['config_stage_order']);
    }
}
