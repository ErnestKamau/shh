<?php

namespace App\Services\GroupedWorksheets;

use App\Enums\GroupedWorksheetItemType;
use App\Enums\HybridWorksheetBlockType;
use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaStep;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\HybridWorksheets\HybridWorksheet;
use App\Models\HybridWorksheets\HybridWorksheetBlock;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use App\Models\LogEntryWorksheets\LogEntryWorksheetColumn;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\Models\StageHeader;
use App\Models\TestStage;

class GroupedWorksheetCapturePreviewService
{
    /**
     * @return array<string, mixed>
     */
    public function previewForItem(GroupedWorksheetItem $item): array
    {
        $base = [
            'pipeline_label' => $item->label,
            'pipeline_description' => $item->description,
            'item_type' => $item->getItemTypeEnum()->value,
            'item_type_label' => $item->getItemTypeEnum()->label(),
            'reference_name' => $item->referenceName(),
            'is_required' => $item->is_required,
            'display_mode' => 'empty',
            'context' => [],
            'steps' => [],
            'blocks' => [],
            'sidebar_stages' => [],
        ];

        return match ($item->getItemTypeEnum()) {
            GroupedWorksheetItemType::Procedure => $this->previewProcedure($item, $base),
            GroupedWorksheetItemType::Formula => $this->previewFormula($item, $base),
            GroupedWorksheetItemType::StageHeader => $this->previewStageHeader($item, $base),
            GroupedWorksheetItemType::HybridWorksheet => $this->previewHybrid($item, $base),
            GroupedWorksheetItemType::LogEntryWorksheet => $this->previewLogEntryWorksheet($item, $base),
            GroupedWorksheetItemType::ResultsCapture => $this->previewVirtualResultsCapture($item, $base),
        };
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    protected function previewVirtualResultsCapture(GroupedWorksheetItem $item, array $base): array
    {
        $base['display_mode'] = 'steps';
        $base['reference_name'] = GroupedWorksheetItemType::ResultsCapture->label();
        $base['context'] = [
            'layout' => 'Parameters as rows, sample codes as columns',
        ];
        $base['steps'] = [
            [
                'order' => 1,
                'title' => 'Results matrix',
                'description' => 'Enter reporting symbol and result for each parameter across all samples.',
                'capture_field' => [
                    'type' => 'table',
                    'label' => 'Loci/Parameters × samples',
                ],
            ],
        ];
        $base['sidebar_stages'] = [
            ['order' => 1, 'title' => 'Results matrix'],
        ];

        return $base;
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    protected function previewProcedure(GroupedWorksheetItem $item, array $base): array
    {
        $worksheet = ProcedureWorksheet::with([
            'steps' => fn ($q) => $q->orderBy('order'),
            'steps.tableColumns' => fn ($q) => $q->orderBy('order'),
        ])->find($item->reference_id);

        if (! $worksheet) {
            return $base;
        }

        $base['display_mode'] = 'steps';
        $base['context'] = [
            'worksheet' => $worksheet->name,
        ];

        $base['steps'] = $worksheet->steps->map(function (ProcedureWorksheetStep $step) {
            return $this->formatProcedureStep($step);
        })->values()->all();

        $base['sidebar_stages'] = collect($base['steps'])->map(fn (array $s) => [
            'order' => $s['order'],
            'title' => $s['title'],
        ])->all();

        return $base;
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    protected function previewFormula(GroupedWorksheetItem $item, array $base): array
    {
        $formula = Formula::with([
            'activeVersion.formulaSteps' => fn ($q) => $q->orderBy('step_number'),
            'activeVersion.formulaSteps.tableColumns' => fn ($q) => $q->orderBy('order'),
        ])->find($item->reference_id);

        if (! $formula?->activeVersion) {
            return $base;
        }

        $base['display_mode'] = 'steps';
        $base['context'] = [
            'formula' => $formula->name,
            'version' => $formula->activeVersion->version_number ?? null,
        ];

        $base['steps'] = $formula->activeVersion->formulaSteps->map(function (FormulaStep $step) {
            return [
                'order' => $step->step_number,
                'title' => $step->label ?: $step->variable_name,
                'subtitle' => ucfirst(str_replace('_', ' ', (string) $step->step_type)),
                'badges' => array_values(array_filter([$step->step_type !== 'input' ? ucfirst(str_replace('_', ' ', (string) $step->step_type)) : null])),
                'capture_field' => $this->formulaCaptureFieldForStep($step),
            ];
        })->values()->all();

        $base['sidebar_stages'] = collect($base['steps'])->map(fn (array $s) => [
            'order' => $s['order'],
            'title' => $s['title'],
        ])->all();

        return $base;
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    protected function previewStageHeader(GroupedWorksheetItem $item, array $base): array
    {
        $header = StageHeader::with([
            'testStages' => fn ($q) => $q->orderBy('order'),
            'method',
            'analyte',
            'sampleType',
        ])->find($item->reference_id);

        if (! $header) {
            return $base;
        }

        $base['display_mode'] = 'steps';
        $base['context'] = [
            'method' => $header->method?->name,
            'analyte' => $header->analyte?->name,
            'sample_type' => $header->sampleType?->name ?? 'All sample types',
            'total_days' => $header->total_days,
        ];

        $base['steps'] = $header->testStages->map(function (TestStage $stage) {
            $badges = [];
            if ($stage->is_result_stage) {
                $badges[] = 'Result';
            }
            if ($stage->is_end_stage ?? false) {
                $badges[] = 'End';
            }
            if ($stage->order) {
                $badges[] = 'Order '.$stage->order;
            }
            if (! empty($stage->duration_hours)) {
                $badges[] = $stage->duration_hours.'h';
            }

            return [
                'order' => $stage->order,
                'title' => $stage->stage_name ?? 'Stage',
                'subtitle' => $stage->instructions ?? 'Method sequence stage',
                'badges' => $badges,
                'capture_field' => [
                    'type' => 'capture',
                    'placeholder' => 'Stage capture panel (equipment, media, results)…',
                ],
            ];
        })->values()->all();

        $base['sidebar_stages'] = collect($base['steps'])->map(fn (array $s) => [
            'order' => $s['order'],
            'title' => $s['title'],
        ])->all();

        return $base;
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    protected function previewHybrid(GroupedWorksheetItem $item, array $base): array
    {
        $hybrid = HybridWorksheet::with([
            'activeVersion.blocks' => fn ($q) => $q->orderBy('sort_order'),
            'activeVersion.blocks.formulaSteps',
            'activeVersion.blocks.procedureSteps',
            'activeVersion.blocks.sequenceStages',
        ])->find($item->reference_id);

        if (! $hybrid?->activeVersion) {
            return $base;
        }

        $base['display_mode'] = 'blocks';
        $base['context'] = [
            'hybrid' => $hybrid->name,
        ];

        $base['blocks'] = $hybrid->activeVersion->blocks->map(function (HybridWorksheetBlock $block) {
            $type = $block->getBlockTypeEnum();

            return [
                'order' => $block->sort_order,
                'title' => $block->label ?? $type->label(),
                'type_label' => $type->label(),
                'reference_name' => $type->isReference() ? $block->referenceName() : null,
                'steps' => $this->stepsForHybridBlock($block),
            ];
        })->values()->all();

        $base['sidebar_stages'] = collect($base['blocks'])->map(fn (array $b) => [
            'order' => $b['order'],
            'title' => $b['title'],
        ])->all();

        return $base;
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    protected function previewLogEntryWorksheet(GroupedWorksheetItem $item, array $base): array
    {
        $worksheet = LogEntryWorksheet::with([
            'columns' => fn ($q) => $q->orderBy('order'),
            'mandatoryFields' => fn ($q) => $q->orderBy('order'),
        ])->find($item->reference_id);

        if (! $worksheet) {
            return $base;
        }

        $base['display_mode'] = 'steps';
        $base['context'] = [
            'worksheet' => $worksheet->name,
            'row_driver' => str_replace('_', ' ', (string) $worksheet->row_driver),
            'mandatory_fields' => $worksheet->mandatoryFields->count(),
        ];

        $base['steps'] = $worksheet->columns->map(function (LogEntryWorksheetColumn $column) {
            return [
                'order' => $column->order,
                'title' => $column->label ?: $column->column_key,
                'subtitle' => ucfirst(str_replace('_', ' ', (string) $column->column_type)),
                'badges' => array_filter([
                    $column->column_type !== 'text' ? ucfirst((string) $column->column_type) : null,
                ]),
                'capture_field' => [
                    'type' => 'text',
                    'placeholder' => 'Analyst enters value…',
                ],
            ];
        })->values()->all();

        $base['sidebar_stages'] = collect($base['steps'])->map(fn (array $s) => [
            'order' => $s['order'],
            'title' => $s['title'],
        ])->all();

        return $base;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function stepsForHybridBlock(HybridWorksheetBlock $block): array
    {
        $type = $block->getBlockTypeEnum();

        if ($type === HybridWorksheetBlockType::FormulaInline) {
            return $block->formulaSteps->map(fn ($step) => [
                'order' => $step->step_number,
                'title' => $step->label,
                'subtitle' => $step->step_type,
                'badges' => [],
                'capture_field' => ['type' => 'text', 'placeholder' => 'Analyst enters value…'],
            ])->values()->all();
        }

        if ($type === HybridWorksheetBlockType::ProcedureInline) {
            return $block->procedureSteps->map(fn ($step) => [
                'order' => $step->order,
                'title' => $step->step,
                'subtitle' => $step->value_type ?? 'text',
                'badges' => $step->is_active ? ['Active'] : ['Inactive'],
                'capture_field' => ['type' => 'text', 'placeholder' => 'Analyst enters value…'],
            ])->values()->all();
        }

        if ($type === HybridWorksheetBlockType::SequenceInline) {
            return $block->sequenceStages->map(fn ($stage) => [
                'order' => $stage->order,
                'title' => $stage->name,
                'subtitle' => 'Sequence stage',
                'badges' => $stage->is_result_stage ? ['Result'] : [],
                'capture_field' => ['type' => 'capture', 'placeholder' => 'Stage capture panel…'],
            ])->values()->all();
        }

        if ($type === HybridWorksheetBlockType::ProcedureReference && $block->reference_id) {
            $worksheet = ProcedureWorksheet::with([
                'steps' => fn ($q) => $q->orderBy('order'),
                'steps.tableColumns' => fn ($q) => $q->orderBy('order'),
            ])->find($block->reference_id);

            return $worksheet?->steps->map(fn (ProcedureWorksheetStep $step) => $this->formatProcedureStep($step))->values()->all() ?? [];
        }

        if ($type === HybridWorksheetBlockType::FormulaReference && $block->reference_id) {
            $formula = Formula::with([
                'activeVersion.formulaSteps' => fn ($q) => $q->orderBy('step_number'),
                'activeVersion.formulaSteps.tableColumns' => fn ($q) => $q->orderBy('order'),
            ])->find($block->reference_id);

            return $formula?->activeVersion?->formulaSteps->map(fn (FormulaStep $step) => [
                'order' => $step->step_number,
                'title' => $step->label ?: $step->variable_name,
                'subtitle' => ucfirst(str_replace('_', ' ', (string) $step->step_type)),
                'badges' => array_values(array_filter([$step->step_type !== 'input' ? ucfirst(str_replace('_', ' ', (string) $step->step_type)) : null])),
                'capture_field' => $this->formulaCaptureFieldForStep($step),
            ])->values()->all() ?? [];
        }

        if ($type === HybridWorksheetBlockType::StageHeaderReference && $block->reference_id) {
            $header = StageHeader::with(['testStages' => fn ($q) => $q->orderBy('order')])
                ->find($block->reference_id);

            return $header?->testStages->map(fn (TestStage $stage) => [
                'order' => $stage->order,
                'title' => $stage->stage_name ?? 'Stage',
                'subtitle' => $stage->instructions ?? 'Method sequence',
                'badges' => array_filter([
                    $stage->is_result_stage ? 'Result' : null,
                    $stage->is_end_stage ? 'End' : null,
                ]),
                'capture_field' => ['type' => 'capture', 'placeholder' => 'Stage capture panel…'],
            ])->values()->all() ?? [];
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatProcedureStep(ProcedureWorksheetStep $step): array
    {
        $valueTypeLabels = [
            'text' => 'Text',
            'number' => 'Number',
            'date' => 'Date',
            'time' => 'Time',
            'datetime' => 'Date & Time',
            'method_select' => 'Method',
            'equipment_select' => 'Equipment',
            'custom_select' => 'Custom List',
            'static_text' => 'Static Text',
            'checkbox' => 'Checkbox',
            'custom_table' => 'Custom Table',
        ];

        $badges = [];
        $vt = $step->value_type ?: 'text';
        $badges[] = $valueTypeLabels[$vt] ?? $vt;
        if (! $step->is_active) {
            $badges[] = 'Inactive';
        }
        if ($step->is_result_step) {
            $badges[] = 'Result';
        }
        if ($step->attracts_equipment_logbook) {
            $badges[] = 'Logbook';
        }

        return [
            'order' => $step->order,
            'title' => $step->step,
            'subtitle' => 'Procedure worksheet step',
            'badges' => $badges,
            'capture_field' => $this->procedureCaptureFieldForStep($step, $vt),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formulaCaptureFieldForStep(FormulaStep $step): array
    {
        $config = is_array($step->step_config) ? $step->step_config : [];

        return match ((string) $step->step_type) {
            'derived', 'lookup' => ['type' => 'readonly', 'placeholder' => 'Calculated automatically…'],
            'parameter_result' => [
                'type' => 'parameter_result',
                'placeholder' => 'Enter result…',
                'analyte_ids' => $step->analyteIds(),
            ],
            'static_text' => [
                'type'    => 'static',
                'content' => $step->staticTextContent(),
            ],
            'checkbox' => [
                'type'    => 'checkbox',
                'mode'    => $config['options_mode'] ?? 'static',
                'options' => array_values($config['static_options'] ?? []),
                'preset'  => $config['preset_model'] ?? null,
            ],
            'custom_table' => [
                'type'    => 'table',
                'columns' => $step->tableColumns->map(fn ($c) => $c->label)->values()->all(),
            ],
            'pcr_plate_map' => [
                'type'      => 'plate',
                'has_qc'    => (bool) ($config['has_std_controls_buffers'] ?? false),
                'standards' => array_values($config['standards'] ?? []),
                'controls'  => array_values($config['controls']  ?? []),
                'buffers'   => array_values($config['buffers']   ?? []),
            ],
            default => ['type' => 'text', 'placeholder' => 'Analyst enters value…'],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function procedureCaptureFieldForStep(ProcedureWorksheetStep $step, string $valueType): array
    {
        return match ($valueType) {
            'number'   => ['type' => 'number',   'placeholder' => 'Enter numeric value…'],
            'date'     => ['type' => 'date',      'placeholder' => ''],
            'time'     => ['type' => 'time',      'placeholder' => ''],
            'datetime' => ['type' => 'datetime',  'placeholder' => ''],
            'method_select'    => ['type' => 'select', 'placeholder' => 'Select method…',    'options' => []],
            'equipment_select' => ['type' => 'select', 'placeholder' => 'Select equipment…', 'options' => []],
            'custom_select' => [
                'type'        => 'select',
                'placeholder' => 'Select option…',
                'options'     => array_values($step->select_options ?? []),
            ],
            'static_text' => [
                'type'    => 'static',
                'content' => $step->step,
            ],
            'checkbox' => [
                'type'    => 'checkbox',
                'mode'    => 'static',
                'options' => array_values($step->select_options ?? []),
                'preset'  => null,
            ],
            'custom_table' => [
                'type'    => 'table',
                'columns' => $step->tableColumns->map(fn ($c) => $c->label)->values()->all(),
            ],
            default => ['type' => 'text', 'placeholder' => 'Enter value…'],
        };
    }
}
