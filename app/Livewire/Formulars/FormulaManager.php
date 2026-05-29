<?php

namespace App\Livewire\Formulars;

use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaVersion;
use App\Services\Formulars\FormulaCloneService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class FormulaManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    public $perPageOptions = [10, 25, 50, 100];

    // Formula creation/editing
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showVersionModal = false;
    public $showCloneModal = false;
    public $editingFormula = null;
    public $editingVersion = null;
    public $cloningFormula = null;

    // Form fields
    public $formulaName = '';
    public $formulaDescription = '';
    public $formulaIsActive = true;
    public $versionDescription = '';
    public $cloneFormulaName = '';

    // Messages
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'formulaName' => 'required|string|max:255',
        'formulaDescription' => 'nullable|string',
        'formulaIsActive' => 'boolean',
    ];

    public function mount()
    {
        $this->perPage = 10;
    }

    public function render()
    {
        $query = Formula::with(['formulaVersions' => function ($query) {
            $query->where('is_active', true);
        }]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $formulas = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.formulars.formula-manager', [
            'formulas' => $formulas,
        ]);
    }

    public function showCreateFormulaModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function showEditFormulaModal(string $formulaId): void
    {
        $formula = Formula::findOrFail($formulaId);
        $this->editingFormula = $formula;
        $this->formulaName = $formula->name;
        $this->formulaDescription = $formula->description;
        $this->formulaIsActive = $formula->is_active;
        $this->showEditModal = true;
    }

    public function showVersionModal(string $formulaId): void
    {
        $formula = Formula::findOrFail($formulaId);
        $this->editingFormula = $formula;
        $this->showVersionModal = true;
    }

    public function showCloneFormulaModal(string $formulaId): void
    {
        $formula = Formula::findOrFail($formulaId);
        $this->cloningFormula = $formula;
        $this->cloneFormulaName = $formula->name.' (Copy)';
        $this->showCloneModal = true;
    }

    public function cloneFormula(FormulaCloneService $cloneService): void
    {
        $this->validate([
            'cloneFormulaName' => 'required|string|max:255',
        ]);

        if (! $this->cloningFormula) {
            $this->setMessage('No formula selected to clone.', 'error');

            return;
        }

        try {
            $cloned = $cloneService->cloneFormula(
                $this->cloningFormula,
                trim($this->cloneFormulaName)
            );

            $this->showCloneModal = false;
            $this->cloningFormula = null;
            $this->cloneFormulaName = '';
            $this->setMessage("Formula cloned successfully as \"{$cloned->name}\".", 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error cloning formula: '.$e->getMessage(), 'error');
        }
    }

    public function createFormula()
    {
        $this->validate();

        try {
            $formula = Formula::create([
                'name' => $this->formulaName,
                'description' => $this->formulaDescription,
                'is_active' => $this->formulaIsActive,
            ]);

            // Create initial version
            $version = FormulaVersion::create([
                'formula_id' => $formula->id,
                'version_number' => 1,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            $this->showCreateModal = false;
            $this->resetForm();
            $this->setMessage('Formula created successfully! Redirecting to add steps...', 'success');
            
            // Redirect to step editor
            return redirect()->route('formulars.steps', $version);
        } catch (\Exception $e) {
            $this->setMessage('Error creating formula: ' . $e->getMessage(), 'error');
        }
    }

    public function updateFormula()
    {
        $this->validate();

        try {
            $this->editingFormula->update([
                'name' => $this->formulaName,
                'description' => $this->formulaDescription,
                'is_active' => $this->formulaIsActive,
            ]);

            $this->showEditModal = false;
            $this->resetForm();
            $this->setMessage('Formula updated successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating formula: ' . $e->getMessage(), 'error');
        }
    }

    public function createNewVersion()
    {
        $this->validate([
            'versionDescription' => 'required|string|max:500',
        ]);

        try {
            // Get the latest version number
            $latestVersion = $this->editingFormula->formulaVersions()
                ->orderBy('version_number', 'desc')
                ->first();

            $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

            // Deactivate current active version
            $this->editingFormula->formulaVersions()
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Create new version
            $newVersion = FormulaVersion::create([
                'formula_id' => $this->editingFormula->id,
                'version_number' => $newVersionNumber,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            if ($latestVersion) {
                app(FormulaCloneService::class)->copyVersionConfiguration($latestVersion, $newVersion);
            }

            $this->showVersionModal = false;
            $this->versionDescription = '';
            $this->setMessage('New version created successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error creating new version: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteFormula(string $formulaId): void
    {
        try {
            $formula = Formula::findOrFail($formulaId);
            $formula->delete();
            $this->setMessage('Formula deleted successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error deleting formula: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleFormulaStatus(string $formulaId): void
    {
        try {
            $formula = Formula::findOrFail($formulaId);
            $formula->update(['is_active' => !$formula->is_active]);
            $status = $formula->is_active ? 'activated' : 'deactivated';
            $this->setMessage("Formula {$status} successfully!", 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating formula status: ' . $e->getMessage(), 'error');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    protected function resetForm()
    {
        $this->formulaName = '';
        $this->formulaDescription = '';
        $this->formulaIsActive = true;
        $this->versionDescription = '';
        $this->cloneFormulaName = '';
        $this->editingFormula = null;
        $this->editingVersion = null;
        $this->cloningFormula = null;
    }

    protected function setMessage(string $message, string $type)
    {
        $this->message = $message;
        $this->messageType = $type;
    }

}