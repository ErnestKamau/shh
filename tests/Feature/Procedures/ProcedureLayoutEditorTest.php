<?php

namespace Tests\Feature\Procedures;

use App\LabSubCategory;
use App\Livewire\Procedures\ProcedureLayoutEditor;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ProcedureLayoutEditorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_saves_sectioned_matrix_layout_with_mixed_columns(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user in database.');
        }

        $this->actingAs($user);

        $worksheet = ProcedureWorksheet::create([
            'name' => 'Layout Test '.uniqid(),
            'is_active' => true,
        ]);

        ProcedureWorksheetStep::create([
            'procedure_worksheet_id' => $worksheet->id,
            'step' => 'Pre-enrichment – Observation',
            'value_type' => 'select',
            'is_active' => true,
            'order' => 1,
            'select_options' => ['Clear', 'Turbid'],
        ]);

        Livewire::test(ProcedureLayoutEditor::class, ['worksheetId' => $worksheet->id])
            ->set('layoutMode', 'sectioned_matrix')
            ->set('sections', [
                [
                    'key' => 'enrichment',
                    'label' => 'Enrichment',
                    'rows' => [
                        ['key' => 'pre_enrichment', 'label' => 'Pre'],
                        ['key' => 'rv_enrichment', 'label' => 'RV'],
                    ],
                    'columns' => [
                        [
                            'key' => 'incubation',
                            'label' => 'Incubation',
                            'type' => 'text',
                            'shared' => true,
                            'default_value' => '',
                            'default_value_by_row' => [
                                'pre_enrichment' => '35C',
                                'rv_enrichment' => '42C',
                            ],
                            'default_uom' => 'mL',
                            'uom_selectable' => true,
                            'lab_sub_category_id' => null,
                            'lab_sub_category_id_by_row' => [],
                            'multiply_by_sample_count' => false,
                            'step_key_by_row' => [],
                        ],
                        [
                            'key' => 'observation',
                            'label' => 'Observation',
                            'type' => 'step_select',
                            'shared' => false,
                            'default_value' => '',
                            'default_value_by_row' => [],
                            'default_uom' => 'mL',
                            'uom_selectable' => true,
                            'lab_sub_category_id' => null,
                            'lab_sub_category_id_by_row' => [],
                            'multiply_by_sample_count' => false,
                            'step_key_by_row' => [
                                'pre_enrichment' => 'Pre-enrichment – Observation',
                            ],
                        ],
                        [
                            'key' => 'media_volume',
                            'label' => 'Media Vol.',
                            'type' => 'reagent_input',
                            'shared' => true,
                            'default_value' => '225',
                            'default_value_by_row' => [],
                            'default_uom' => 'mL',
                            'uom_selectable' => true,
                            'lab_sub_category_id' => null,
                            'lab_sub_category_id_by_row' => [],
                            'multiply_by_sample_count' => true,
                            'step_key_by_row' => [],
                        ],
                    ],
                ],
            ])
            ->call('saveLayout')
            ->assertHasNoErrors();

        $worksheet->refresh();
        $this->assertTrue($worksheet->isSectionedMatrix());
        $section = $worksheet->getMatrixSection('enrichment');
        $this->assertNotNull($section);
        $this->assertCount(2, $section['rows']);
        $this->assertCount(3, $section['columns']);
        $this->assertEquals('text', $section['columns'][0]['type']);
        $this->assertEquals('step_select', $section['columns'][1]['type']);
        $this->assertEquals('reagent_input', $section['columns'][2]['type']);
        $this->assertTrue($section['columns'][2]['multiply_by_sample_count']);
    }

    public function test_saves_reagent_column_with_by_row_media_ids(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user in database.');
        }

        $mediaA = LabSubCategory::query()->first();
        $mediaB = LabSubCategory::query()->skip(1)->first() ?? $mediaA;
        if (! $mediaA) {
            $this->markTestSkipped('No lab_sub_category rows.');
        }

        $this->actingAs($user);

        $worksheet = ProcedureWorksheet::create([
            'name' => 'Stock Layout '.uniqid(),
            'is_active' => true,
        ]);

        Livewire::test(ProcedureLayoutEditor::class, ['worksheetId' => $worksheet->id])
            ->set('layoutMode', 'sectioned_matrix')
            ->set('sections', [
                [
                    'key' => 'enrichment',
                    'label' => 'Enrichment',
                    'rows' => [
                        ['key' => 'pre_enrichment', 'label' => 'Pre'],
                        ['key' => 'rv_enrichment', 'label' => 'RV'],
                    ],
                    'columns' => [
                        [
                            'key' => 'media_volume',
                            'label' => 'Media Vol.',
                            'type' => 'reagent_input',
                            'shared' => true,
                            'default_value' => '',
                            'default_value_by_row' => [
                                'pre_enrichment' => '225',
                                'rv_enrichment' => '10',
                            ],
                            'default_uom' => 'mL',
                            'uom_selectable' => true,
                            'lab_sub_category_id' => null,
                            'lab_sub_category_id_by_row' => [
                                'pre_enrichment' => $mediaA->id,
                                'rv_enrichment' => $mediaB->id,
                            ],
                            'multiply_by_sample_count' => true,
                            'step_key_by_row' => [],
                        ],
                    ],
                ],
            ])
            ->call('saveLayout')
            ->assertHasNoErrors();

        $worksheet->refresh();
        $column = $worksheet->getMatrixSection('enrichment')['columns'][0];
        $this->assertNull($column['lab_sub_category_id']);
        $this->assertEquals($mediaA->id, $column['lab_sub_category_id_by_row']['pre_enrichment']);
        $this->assertEquals($mediaB->id, $column['lab_sub_category_id_by_row']['rv_enrichment']);
        $this->assertTrue($column['multiply_by_sample_count']);
    }

    public function test_clearing_layout_mode_nulls_settings(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user in database.');
        }

        $this->actingAs($user);

        $worksheet = ProcedureWorksheet::create([
            'name' => 'Clear Layout '.uniqid(),
            'is_active' => true,
            'layout_settings' => [
                'mode' => 'sectioned_matrix',
                'sections' => [
                    [
                        'key' => 'enrichment',
                        'label' => 'Enrichment',
                        'rows' => [['key' => 'pre', 'label' => 'Pre']],
                        'columns' => [[
                            'key' => 't',
                            'label' => 'T',
                            'type' => 'text',
                            'shared' => true,
                        ]],
                    ],
                ],
            ],
        ]);

        Livewire::test(ProcedureLayoutEditor::class, ['worksheetId' => $worksheet->id])
            ->set('layoutMode', 'default')
            ->call('saveLayout')
            ->assertHasNoErrors();

        $worksheet->refresh();
        $this->assertNull($worksheet->layout_settings);
    }
}
