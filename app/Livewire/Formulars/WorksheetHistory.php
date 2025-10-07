<?php

namespace App\Livewire\Formulars;

use App\Models\Formulars\WorksheetExecution;
use App\Services\Formulars\WorksheetService;
use Livewire\Component;
use Livewire\WithPagination;

class WorksheetHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $formulaFilter = '';
    public $executionModeFilter = '';
    public $perPage = 20;
    public $perPageOptions = [10, 20, 50, 100];

    // Viewing execution details
    public $showExecutionModal = false;
    public $selectedExecution = null;

    // Messages
    public $message = '';
    public $messageType = '';

    public function mount()
    {
        $this->perPage = 20;
    }

    public function render()
    {
        $query = WorksheetExecution::with(['formulaVersion.formula', 'executor', 'sample', 'batch'])
            ->where('is_saved', true);

        if ($this->search) {
            $query->whereHas('formulaVersion.formula', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->formulaFilter) {
            $query->where('formula_version_id', $this->formulaFilter);
        }

        if ($this->executionModeFilter) {
            $query->where('execution_mode', $this->executionModeFilter);
        }

        $executions = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        $formulas = \App\Models\Formulars\FormulaVersion::with('formula')
            ->where('is_active', true)
            ->get()
            ->map(function ($version) {
                return [
                    'id' => $version->id,
                    'name' => $version->formula->name . ' (v' . $version->version_number . ')',
                ];
            });

        return view('livewire.formulars.worksheet-history', [
            'executions' => $executions,
            'formulas' => $formulas,
        ]);
    }

    public function showExecutionDetails(WorksheetExecution $execution)
    {
        $this->selectedExecution = $execution;
        $this->showExecutionModal = true;
    }

    public function deleteExecution(WorksheetExecution $execution)
    {
        try {
            $execution->delete();
            $this->setMessage('Execution deleted successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error deleting execution: ' . $e->getMessage(), 'error');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->formulaFilter = '';
        $this->executionModeFilter = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function closeExecutionModal()
    {
        $this->showExecutionModal = false;
        $this->selectedExecution = null;
    }

    protected function setMessage(string $message, string $type)
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}