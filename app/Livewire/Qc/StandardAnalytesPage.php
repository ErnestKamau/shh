<?php

namespace App\Livewire\Qc;

use App\Analyte;
use App\StandardAnalytes;
use App\Standards;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StandardAnalytesPage extends Component
{
    public string $standardId;

    public ?string $editingAnalyteId = null;
    public string $analyteId = '';
    public ?float $expectedValue = null;
    public bool $useAbsoluteTolerance = false;
    public ?float $tolerance1 = null;
    public ?float $tolerance2 = null;
    public string $comment = '';
    public string $recommendation = '';
    public bool $isActive = true;

    public function mount(string $standardId): void
    {
        $this->standardId = $standardId;
    }

    protected function rules(): array
    {
        return [
            'analyteId' => ['required', 'string', Rule::exists('analytes', 'id')],
            'expectedValue' => ['nullable', 'numeric'],
            'tolerance1' => ['nullable', 'numeric', 'min:0'],
            'tolerance2' => ['nullable', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'recommendation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function resetForm(): void
    {
        $this->editingAnalyteId = null;
        $this->analyteId = '';
        $this->expectedValue = null;
        $this->useAbsoluteTolerance = false;
        $this->tolerance1 = null;
        $this->tolerance2 = null;
        $this->comment = '';
        $this->recommendation = '';
        $this->isActive = true;
    }

    public function editAnalyte(string $id): void
    {
        $record = StandardAnalytes::query()->findOrFail($id);

        $this->editingAnalyteId = (string) $record->id;
        $this->analyteId = (string) $record->analyte_id;
        $this->expectedValue = $record->expected_value !== null ? (float) $record->expected_value : null;
        $this->useAbsoluteTolerance = (bool) $record->absolute_tolerance;
        $this->tolerance1 = $record->tolerance_1 !== null ? (float) $record->tolerance_1 : null;
        $this->tolerance2 = $record->tolerance_2 !== null ? (float) $record->tolerance_2 : null;
        $this->comment = (string) ($record->comments ?? '');
        $this->recommendation = (string) ($record->recommendations ?? '');
        $this->isActive = (bool) $record->is_active;
    }

    public function saveAnalyte(): void
    {
        $this->validate();

        $record = $this->editingAnalyteId
            ? StandardAnalytes::query()->findOrFail($this->editingAnalyteId)
            : new StandardAnalytes();

        $expected = (float) ($this->expectedValue ?? 0);
        $tol1 = (float) ($this->tolerance1 ?? 0);
        $tol2 = $this->tolerance2 !== null ? (float) $this->tolerance2 : $tol1;

        if ($this->useAbsoluteTolerance) {
            $low = $tol1;
            $high = $tol2;
        } else {
            $low = $expected - $tol1;
            $high = $expected + $tol2;
        }

        $record->standard_id = $this->standardId;
        $record->analyte_id = $this->analyteId;
        $record->absolute_tolerance = $this->useAbsoluteTolerance ? 1 : 0;
        $record->expected_value = $this->expectedValue;
        $record->tolerance_1 = $this->tolerance1;
        $record->tolerance_2 = $this->tolerance2;
        $record->low = $low;
        $record->high = $high;
        $record->comments = $this->comment;
        $record->recommendations = $this->recommendation;
        $record->is_active = $this->isActive ? 1 : 0;
        $record->standard_value_id = 0;
        $record->standard_value_type = 'is_range';
        $record->save();

        session()->flash('success', 'Standard analyte saved successfully.');
        $this->resetForm();
    }

    public function deactivateAnalyte(string $id): void
    {
        $record = StandardAnalytes::query()->findOrFail($id);
        $record->is_active = 0;
        $record->save();

        session()->flash('success', 'Standard analyte deactivated successfully.');
    }

    public function render()
    {
        $standard = Standards::query()->findOrFail($this->standardId);

        $standardAnalytes = StandardAnalytes::query()
            ->where('standard_id', $this->standardId)
            ->orderByDesc('updated_at')
            ->get();

        $analytes = Analyte::query()->where('active', 1)->orderBy('name')->get(['id', 'name', 'code']);

        return view('livewire.qc.standard-analytes-page', [
            'standard' => $standard,
            'standardAnalytes' => $standardAnalytes,
            'analytes' => $analytes,
        ]);
    }
}
