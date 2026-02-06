<?php

namespace App\Livewire\Procedures;

use Livewire\Component;
use App\Models\Procedures\ProcedureWorksheet;
use Livewire\WithPagination;

class ProcedureWorksheetManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 25;
    
    // Modal properties
    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingId = null;
    
    // Form properties
    public $name = '';
    public $description = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $query = ProcedureWorksheet::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }

        $worksheets = $query->latest()->paginate($this->perPage);

        return view('livewire.procedures.procedure-worksheet-manager', [
            'worksheets' => $worksheets,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $worksheet = ProcedureWorksheet::findOrFail($id);
        $this->editingId = $id;
        $this->name = $worksheet->name;
        $this->description = $worksheet->description;
        $this->is_active = $worksheet->is_active;
        $this->showEditModal = true;
    }

    public function save()
    {
        $this->validate();

        ProcedureWorksheet::create([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        session()->flash('message', 'Procedure Worksheet created successfully.');
    }

    public function update()
    {
        $this->validate();

        $worksheet = ProcedureWorksheet::findOrFail($this->editingId);
        $worksheet->update([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->showEditModal = false;
        $this->resetForm();
        session()->flash('message', 'Procedure Worksheet updated successfully.');
    }

    public function toggleActive($id)
    {
        $worksheet = ProcedureWorksheet::findOrFail($id);
        $worksheet->is_active = !$worksheet->is_active;
        $worksheet->save();
    }

    public function delete($id)
    {
        $worksheet = ProcedureWorksheet::findOrFail($id);
        $worksheet->delete();
        session()->flash('message', 'Procedure Worksheet deleted successfully.');
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
    }
}
