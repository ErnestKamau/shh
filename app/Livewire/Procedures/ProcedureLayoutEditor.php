<?php

namespace App\Livewire\Procedures;

use App\LabSubCategory;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use Illuminate\Support\Str;
use Livewire\Component;

class ProcedureLayoutEditor extends Component
{
    use AppliesCaseInsensitiveSearch;

    public string $worksheetId;

    /** default | sectioned_matrix */
    public string $layoutMode = 'default';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $sections = [];

    public ?int $expandedSectionIndex = null;

    public string $message = '';

    public string $messageType = '';

    public string $mediaSearch = '';

    public bool $showMediaDropdown = false;

    public ?int $mediaTargetSectionIndex = null;

    public ?int $mediaTargetColumnIndex = null;

    public ?string $mediaTargetRowKey = null;

    public function mount(string $worksheetId): void
    {
        $this->worksheetId = $worksheetId;
        $this->loadFromWorksheet();
    }

    public function render()
    {
        return view('livewire.procedures.procedure-layout-editor', [
            'stepOptions' => $this->stepOptions(),
            'mediaOptions' => $this->mediaOptions(),
        ]);
    }

    public function loadFromWorksheet(): void
    {
        $worksheet = ProcedureWorksheet::findOrFail($this->worksheetId);
        $settings = $worksheet->layout_settings ?? [];
        $mode = data_get($settings, 'mode', 'default');
        $this->layoutMode = $mode === 'sectioned_matrix' ? 'sectioned_matrix' : 'default';
        $this->sections = collect(data_get($settings, 'sections', []))
            ->map(fn ($section) => $this->normalizeSection($section))
            ->values()
            ->all();
    }

    public function updatedLayoutMode(string $value): void
    {
        if ($value === 'sectioned_matrix' && $this->sections === []) {
            $this->addSection();
        }
    }

    public function addSection(): void
    {
        $this->sections[] = $this->normalizeSection([
            'key' => 'section_'.(count($this->sections) + 1),
            'label' => 'Section '.(count($this->sections) + 1),
            'rows' => [
                ['key' => 'row_1', 'label' => 'Row 1'],
            ],
            'columns' => [
                $this->blankColumn('text'),
            ],
        ]);
        $this->expandedSectionIndex = count($this->sections) - 1;
    }

    public function removeSection(int $index): void
    {
        if (! isset($this->sections[$index])) {
            return;
        }

        array_splice($this->sections, $index, 1);
        $this->sections = array_values($this->sections);
        if ($this->expandedSectionIndex === $index) {
            $this->expandedSectionIndex = null;
        } elseif ($this->expandedSectionIndex !== null && $this->expandedSectionIndex > $index) {
            $this->expandedSectionIndex--;
        }
    }

    public function moveSection(int $index, int $direction): void
    {
        $target = $index + $direction;
        if ($target < 0 || $target >= count($this->sections)) {
            return;
        }

        $tmp = $this->sections[$index];
        $this->sections[$index] = $this->sections[$target];
        $this->sections[$target] = $tmp;
        $this->sections = array_values($this->sections);
        $this->expandedSectionIndex = $target;
    }

    public function toggleSection(int $index): void
    {
        $this->expandedSectionIndex = $this->expandedSectionIndex === $index ? null : $index;
    }

    public function addRow(int $sectionIndex): void
    {
        if (! isset($this->sections[$sectionIndex])) {
            return;
        }

        $n = count($this->sections[$sectionIndex]['rows']) + 1;
        $this->sections[$sectionIndex]['rows'][] = [
            'key' => 'row_'.$n,
            'label' => 'Row '.$n,
        ];
    }

    public function removeRow(int $sectionIndex, int $rowIndex): void
    {
        if (! isset($this->sections[$sectionIndex]['rows'][$rowIndex])) {
            return;
        }

        array_splice($this->sections[$sectionIndex]['rows'], $rowIndex, 1);
        $this->sections[$sectionIndex]['rows'] = array_values($this->sections[$sectionIndex]['rows']);
    }

    public function addColumn(int $sectionIndex, string $type = 'text'): void
    {
        if (! isset($this->sections[$sectionIndex])) {
            return;
        }

        if (! in_array($type, ['text', 'reagent_input', 'step_select'], true)) {
            $type = 'text';
        }

        $this->sections[$sectionIndex]['columns'][] = $this->blankColumn($type);
    }

    public function removeColumn(int $sectionIndex, int $columnIndex): void
    {
        if (! isset($this->sections[$sectionIndex]['columns'][$columnIndex])) {
            return;
        }

        array_splice($this->sections[$sectionIndex]['columns'], $columnIndex, 1);
        $this->sections[$sectionIndex]['columns'] = array_values($this->sections[$sectionIndex]['columns']);
    }

    public function updatedSections($value, string $key): void
    {
        // When column type changes, ensure type-specific defaults exist.
        if (preg_match('/^(\d+)\.columns\.(\d+)\.type$/', $key, $m)) {
            $sectionIndex = (int) $m[1];
            $columnIndex = (int) $m[2];
            $column = $this->sections[$sectionIndex]['columns'][$columnIndex] ?? null;
            if (is_array($column)) {
                $this->sections[$sectionIndex]['columns'][$columnIndex] = array_merge(
                    $this->blankColumn((string) ($column['type'] ?? 'text')),
                    array_filter($column, fn ($v) => $v !== null && $v !== '')
                );
                $this->sections[$sectionIndex]['columns'][$columnIndex]['type'] = $column['type'] ?? 'text';
            }
        }
    }

    public function openMediaPicker(int $sectionIndex, int $columnIndex, ?string $rowKey = null): void
    {
        $this->mediaTargetSectionIndex = $sectionIndex;
        $this->mediaTargetColumnIndex = $columnIndex;
        $this->mediaTargetRowKey = $rowKey;
        $this->mediaSearch = '';
        $this->showMediaDropdown = true;
    }

    public function selectMedia(string $id, string $name): void
    {
        $s = $this->mediaTargetSectionIndex;
        $c = $this->mediaTargetColumnIndex;
        if ($s === null || $c === null || ! isset($this->sections[$s]['columns'][$c])) {
            return;
        }

        if ($this->mediaTargetRowKey) {
            $map = $this->sections[$s]['columns'][$c]['lab_sub_category_id_by_row'] ?? [];
            if (! is_array($map)) {
                $map = [];
            }
            $map[$this->mediaTargetRowKey] = $id;
            $this->sections[$s]['columns'][$c]['lab_sub_category_id_by_row'] = $map;
            $this->sections[$s]['columns'][$c]['lab_sub_category_id'] = null;
        } else {
            $this->sections[$s]['columns'][$c]['lab_sub_category_id'] = $id;
            $this->sections[$s]['columns'][$c]['lab_sub_category_label'] = $name;
        }

        $this->showMediaDropdown = false;
        $this->mediaTargetSectionIndex = null;
        $this->mediaTargetColumnIndex = null;
        $this->mediaTargetRowKey = null;
    }

    public function clearMedia(int $sectionIndex, int $columnIndex, ?string $rowKey = null): void
    {
        if (! isset($this->sections[$sectionIndex]['columns'][$columnIndex])) {
            return;
        }

        if ($rowKey) {
            $map = $this->sections[$sectionIndex]['columns'][$columnIndex]['lab_sub_category_id_by_row'] ?? [];
            unset($map[$rowKey]);
            $this->sections[$sectionIndex]['columns'][$columnIndex]['lab_sub_category_id_by_row'] = $map;
        } else {
            $this->sections[$sectionIndex]['columns'][$columnIndex]['lab_sub_category_id'] = null;
            $this->sections[$sectionIndex]['columns'][$columnIndex]['lab_sub_category_label'] = null;
        }
    }

    public function saveLayout(): void
    {
        $this->validate([
            'layoutMode' => 'required|in:default,sectioned_matrix',
            'sections' => 'array',
            'sections.*.key' => 'required_if:layoutMode,sectioned_matrix|string|max:100',
            'sections.*.label' => 'required_if:layoutMode,sectioned_matrix|string|max:255',
            'sections.*.rows' => 'array',
            'sections.*.rows.*.key' => 'required|string|max:100',
            'sections.*.rows.*.label' => 'required|string|max:255',
            'sections.*.columns' => 'array',
            'sections.*.columns.*.key' => 'required|string|max:100',
            'sections.*.columns.*.label' => 'required|string|max:255',
            'sections.*.columns.*.type' => 'required|in:text,reagent_input,step_select',
        ]);

        $worksheet = ProcedureWorksheet::findOrFail($this->worksheetId);

        if ($this->layoutMode !== 'sectioned_matrix') {
            $worksheet->update(['layout_settings' => null]);
            $this->setMessage('Layout cleared (classic steps-only mode).', 'success');

            return;
        }

        $payload = [
            'mode' => 'sectioned_matrix',
            'sections' => array_map(fn ($section) => $this->persistSection($section), $this->sections),
        ];

        $worksheet->update(['layout_settings' => $payload]);
        $this->loadFromWorksheet();
        $this->setMessage('Layout settings saved.', 'success');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function stepOptions(): array
    {
        return ProcedureWorksheetStep::query()
            ->where('procedure_worksheet_id', $this->worksheetId)
            ->orderBy('order')
            ->get(['id', 'step', 'order'])
            ->map(fn ($step) => [
                'value' => (string) $step->step,
                'label' => (string) $step->step,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    protected function mediaOptions(): array
    {
        $search = $this->mediaSearch;

        return LabSubCategory::query()
            ->when($search !== '', fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], $search))
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name'])
            ->map(fn ($row) => ['id' => $row->id, 'name' => $row->name])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    protected function normalizeSection(array $section): array
    {
        $rows = collect(data_get($section, 'rows', []))
            ->map(fn ($row) => [
                'key' => (string) ($row['key'] ?? Str::slug((string) ($row['label'] ?? 'row'), '_')),
                'label' => (string) ($row['label'] ?? $row['key'] ?? 'Row'),
            ])
            ->values()
            ->all();

        $columns = collect(data_get($section, 'columns', []))
            ->map(fn ($column) => $this->normalizeColumn(is_array($column) ? $column : []))
            ->values()
            ->all();

        return [
            'key' => (string) ($section['key'] ?? 'section'),
            'label' => (string) ($section['label'] ?? $section['key'] ?? 'Section'),
            'rows' => $rows,
            'columns' => $columns,
        ];
    }

    /**
     * @param  array<string, mixed>  $column
     * @return array<string, mixed>
     */
    protected function normalizeColumn(array $column): array
    {
        $type = (string) ($column['type'] ?? 'text');
        if (! in_array($type, ['text', 'reagent_input', 'step_select'], true)) {
            $type = 'text';
        }

        $base = $this->blankColumn($type);
        $merged = array_merge($base, $column);
        $merged['type'] = $type;
        $merged['shared'] = (bool) ($merged['shared'] ?? ($type !== 'step_select'));
        $merged['uom_selectable'] = (bool) ($merged['uom_selectable'] ?? true);
        $merged['multiply_by_sample_count'] = (bool) ($merged['multiply_by_sample_count'] ?? false);
        $merged['default_value_by_row'] = is_array($merged['default_value_by_row'] ?? null)
            ? $merged['default_value_by_row']
            : [];
        $merged['step_key_by_row'] = is_array($merged['step_key_by_row'] ?? null)
            ? $merged['step_key_by_row']
            : [];
        $merged['lab_sub_category_id_by_row'] = is_array($merged['lab_sub_category_id_by_row'] ?? null)
            ? $merged['lab_sub_category_id_by_row']
            : [];

        if (! empty($merged['lab_sub_category_id']) && empty($merged['lab_sub_category_label'])) {
            $merged['lab_sub_category_label'] = LabSubCategory::query()
                ->where('id', $merged['lab_sub_category_id'])
                ->value('name');
        }

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    protected function blankColumn(string $type): array
    {
        $key = $type.'_'.Str::lower(Str::random(4));

        return [
            'key' => $key,
            'label' => match ($type) {
                'reagent_input' => 'Media Vol.',
                'step_select' => 'Observation',
                default => 'Text',
            },
            'type' => $type,
            'shared' => $type !== 'step_select',
            'default_value' => '',
            'default_value_by_row' => [],
            'default_uom' => 'mL',
            'uom_selectable' => true,
            'lab_sub_category_id' => null,
            'lab_sub_category_label' => null,
            'lab_sub_category_id_by_row' => [],
            'multiply_by_sample_count' => $type === 'reagent_input',
            'step_key_by_row' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array<string, mixed>
     */
    protected function persistSection(array $section): array
    {
        return [
            'key' => (string) $section['key'],
            'label' => (string) $section['label'],
            'rows' => collect($section['rows'] ?? [])
                ->map(fn ($row) => [
                    'key' => (string) $row['key'],
                    'label' => (string) $row['label'],
                ])
                ->values()
                ->all(),
            'columns' => collect($section['columns'] ?? [])
                ->map(fn ($column) => $this->persistColumn($column))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $column
     * @return array<string, mixed>
     */
    protected function persistColumn(array $column): array
    {
        $type = (string) ($column['type'] ?? 'text');
        $out = [
            'key' => (string) $column['key'],
            'label' => (string) $column['label'],
            'type' => $type,
            'shared' => (bool) ($column['shared'] ?? true),
        ];

        if ($type === 'text') {
            $out['default_value'] = (string) ($column['default_value'] ?? '');
            $byRow = array_filter(
                is_array($column['default_value_by_row'] ?? null) ? $column['default_value_by_row'] : [],
                fn ($v) => $v !== null && $v !== ''
            );
            if ($byRow !== []) {
                $out['default_value_by_row'] = $byRow;
            }
        }

        if ($type === 'reagent_input') {
            $out['default_value'] = (string) ($column['default_value'] ?? '');
            $byRow = array_filter(
                is_array($column['default_value_by_row'] ?? null) ? $column['default_value_by_row'] : [],
                fn ($v) => $v !== null && $v !== ''
            );
            if ($byRow !== []) {
                $out['default_value_by_row'] = $byRow;
            }
            $out['default_uom'] = (string) ($column['default_uom'] ?? 'mL');
            $out['uom_selectable'] = (bool) ($column['uom_selectable'] ?? true);
            $out['multiply_by_sample_count'] = (bool) ($column['multiply_by_sample_count'] ?? false);

            $byRowMedia = array_filter(
                is_array($column['lab_sub_category_id_by_row'] ?? null) ? $column['lab_sub_category_id_by_row'] : [],
                fn ($v) => filled($v)
            );
            if ($byRowMedia !== []) {
                $out['lab_sub_category_id_by_row'] = $byRowMedia;
                $out['lab_sub_category_id'] = null;
            } else {
                $out['lab_sub_category_id'] = filled($column['lab_sub_category_id'] ?? null)
                    ? $column['lab_sub_category_id']
                    : null;
            }
        }

        if ($type === 'step_select') {
            $out['shared'] = false;
            $out['step_key_by_row'] = array_filter(
                is_array($column['step_key_by_row'] ?? null) ? $column['step_key_by_row'] : [],
                fn ($v) => filled($v)
            );
        }

        return $out;
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}
