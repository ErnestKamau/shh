<?php

namespace App\Livewire\Formulars;

use App\Models\Formulars\GlobalVariable;
use Livewire\Component;
use Livewire\WithPagination;

class GlobalVariableManager extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $perPage = 10;

    public $perPageOptions = [10, 25, 50, 100];

    // Variable creation/editing
    public $showCreateModal = false;

    public $showEditModal = false;

    public $editingVariable = null;

    public $closingModal = false;

    // Form fields
    public $variableName = '';
    public $variableValue = '';
    public $dataType = 'string';
    public $variableDescription = '';
    public $isActive = true;

    // Messages
    public $message = '';

    public $messageType = '';

    public function mount(): void
    {
        $this->perPage = 10;
    }

    public function render()
    {
        $query = GlobalVariable::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('value', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            if ($this->statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($this->statusFilter === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $variables = $query->orderBy('name')->paginate($this->perPage);

        return view('livewire.formulars.global-variable-manager', [
            'variables' => $variables,
        ]);
    }

    public function showCreateVariableModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function showEditVariableModal(string $variableId): void
    {
        $variable = GlobalVariable::findOrFail($variableId);

        $this->editingVariable = $variable;
        $this->variableName = $variable->name;
        $this->variableValue = $variable->value;
        $this->dataType = $variable->data_type;
        $this->variableDescription = $variable->description ?? '';
        $this->isActive = $variable->is_active;
        $this->showEditModal = true;
    }

    public function createVariable(): void
    {
        $this->validate($this->variableValidationRules());

        try {
            GlobalVariable::create([
                'name' => $this->variableName,
                'value' => $this->variableValue,
                'data_type' => $this->dataType,
                'description' => $this->variableDescription,
                'is_active' => $this->isActive,
            ]);

            $this->showCreateModal = false;
            $this->resetForm();
            $this->setMessage('Global variable created successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error creating global variable: ' . $e->getMessage(), 'error');
        }
    }

    public function updateVariable(): void
    {
        $this->validate($this->variableValidationRules($this->editingVariable->id));

        try {
            $this->editingVariable->update([
                'name' => $this->variableName,
                'value' => $this->variableValue,
                'data_type' => $this->dataType,
                'description' => $this->variableDescription,
                'is_active' => $this->isActive,
            ]);

            $this->showEditModal = false;
            $this->resetForm();
            $this->setMessage('Global variable updated successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating global variable: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteVariable(string $variableId): void
    {
        try {
            GlobalVariable::findOrFail($variableId)->delete();
            $this->setMessage('Global variable deleted successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error deleting global variable: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleVariableStatus(string $variableId): void
    {
        try {
            $variable = GlobalVariable::findOrFail($variableId);
            $variable->update(['is_active' => ! $variable->is_active]);
            $status = $variable->is_active ? 'activated' : 'deactivated';
            $this->setMessage("Global variable {$status} successfully!", 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating variable status: ' . $e->getMessage(), 'error');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function closeCreateModal()
    {
        $this->closingModal = true;
        $this->showCreateModal = false;
        $this->resetForm();
        $this->closingModal = false;
    }

    public function closeEditModal()
    {
        $this->closingModal = true;
        $this->showEditModal = false;
        $this->resetForm();
        $this->closingModal = false;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    protected function variableValidationRules(?string $ignoreId = null): array
    {
        $nameRule = 'required|string|max:255|unique:global_variables,name';

        if ($ignoreId !== null) {
            $nameRule .= ','.$ignoreId;
        }

        return [
            'variableName' => $nameRule,
            'variableValue' => 'required|string',
            'dataType' => 'required|in:string,number,boolean',
            'variableDescription' => 'nullable|string',
            'isActive' => 'boolean',
        ];
    }

    protected function resetForm(): void
    {
        $this->variableName = '';
        $this->variableValue = '';
        $this->dataType = 'string';
        $this->variableDescription = '';
        $this->isActive = true;
        $this->editingVariable = null;
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}