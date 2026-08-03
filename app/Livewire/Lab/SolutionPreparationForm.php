<?php

namespace App\Livewire\Lab;

use App\LabSubCategory;
use App\ReportingUnit;
use App\Services\Preparation\PreparationRunService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SolutionPreparationForm extends Component
{
    public $form = [
        'solution_id' => null,
        'quantity_prepared' => '',
        'uom_id' => null,
        'batch_number' => '',
        'expiry_date' => '',
        'is_new_batch' => false,
        'notes' => '',
        'create_with_alternative' => false,
    ];

    public $message = '';

    public $messageType = '';

    public function mount(): void
    {
        if ($solutionId = request()->query('solution')) {
            $this->form['solution_id'] = $solutionId;
            $this->updatedFormSolutionId($solutionId);
        }
    }

    public function getSolutionsProperty()
    {
        return LabSubCategory::where('active', 1)->with('reportingUnit')->orderBy('name')->get();
    }

    public function getReportingUnitsProperty()
    {
        return ReportingUnit::where('active', 1)->orderBy('name')->get();
    }

    public function updatedFormSolutionId($value): void
    {
        $solution = LabSubCategory::find($value);
        if ($solution?->reporting_unit) {
            $this->form['uom_id'] = $solution->reporting_unit;
        }
    }

    public function save(PreparationRunService $runService): void
    {
        $this->validate([
            'form.solution_id' => 'required|uuid|exists:lab_sub_category,id',
            'form.quantity_prepared' => 'required|numeric|min:0.0001',
            'form.uom_id' => 'required|uuid|exists:reporting_units,id',
            'form.batch_number' => 'nullable|string|max:255',
            'form.expiry_date' => 'nullable|date|after_or_equal:today',
        ]);

        if ($this->form['is_new_batch'] && empty($this->form['batch_number'])) {
            $this->addError('form.batch_number', 'Batch number is required for a new batch.');

            return;
        }

        if ($this->form['is_new_batch'] && empty($this->form['expiry_date'])) {
            $this->addError('form.expiry_date', 'Expiry date is required for a new batch.');

            return;
        }

        try {
            $data = [
                'solution_id' => $this->form['solution_id'],
                'quantity_prepared' => $this->form['quantity_prepared'],
                'uom_id' => $this->form['uom_id'],
                'batch_number' => $this->form['batch_number'] ?: null,
                'expiry_date' => $this->form['is_new_batch'] ? ($this->form['expiry_date'] ?: null) : null,
                'is_new_batch' => (bool) $this->form['is_new_batch'],
                'notes' => $this->form['notes'],
            ];

            $preparation = $runService->createWithAlternative($data, (bool) $this->form['create_with_alternative']);

            $this->redirect(route('solutions-preparation-show', $preparation->id), navigate: true);
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first();
            $this->messageType = 'danger';
        }
    }

    public function render()
    {
        return view('livewire.lab.solution-preparation-form');
    }
}
