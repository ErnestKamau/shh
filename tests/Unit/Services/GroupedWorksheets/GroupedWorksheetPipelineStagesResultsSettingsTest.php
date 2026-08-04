<?php

namespace Tests\Unit\Services\GroupedWorksheets;

use App\Enums\GroupedWorksheetItemType;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\Procedures\ProcedureWorksheet;
use App\Services\GroupedWorksheets\GroupedWorksheetPipelineStages;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GroupedWorksheetPipelineStagesResultsSettingsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_virtual_results_disabled_when_settings_enabled_false(): void
    {
        $procedure = ProcedureWorksheet::create([
            'name' => 'Stage '.uniqid(),
            'is_active' => true,
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'No Results '.uniqid(),
            'is_active' => true,
            'settings' => [
                'pipeline_mode' => 'classic',
                'results_capture' => [
                    'enabled' => false,
                    'label' => 'Results capture',
                    'required' => true,
                ],
            ],
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'A',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        $stages = app(GroupedWorksheetPipelineStages::class)->allStages($holder->fresh('items'));

        $this->assertCount(1, $stages);
        $this->assertNull(
            app(GroupedWorksheetPipelineStages::class)->virtualResultsCaptureItemForDisplay($holder->fresh('items'))
        );
    }

    public function test_virtual_results_uses_custom_label_and_required(): void
    {
        $procedure = ProcedureWorksheet::create([
            'name' => 'Stage '.uniqid(),
            'is_active' => true,
        ]);

        $holder = GroupedWorksheetHolder::create([
            'name' => 'Custom Results '.uniqid(),
            'is_active' => true,
            'settings' => [
                'results_capture' => [
                    'enabled' => true,
                    'label' => 'Post results',
                    'required' => false,
                ],
            ],
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'A',
            'item_type' => GroupedWorksheetItemType::Procedure,
            'reference_id' => $procedure->id,
            'is_required' => true,
        ]);

        $stages = app(GroupedWorksheetPipelineStages::class)->allStages($holder->fresh('items'));

        $this->assertCount(2, $stages);
        $this->assertEquals('Post results', $stages->last()->label);
        $this->assertFalse($stages->last()->is_required);
    }
}
