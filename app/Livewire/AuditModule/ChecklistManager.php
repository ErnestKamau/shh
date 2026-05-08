<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\AuditChecklist;
use App\Models\AuditModule\AuditChecklistItem;
use App\Models\AuditModule\WorkflowAction;
use App\SampleType;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class ChecklistManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    
    public $showModal = false;
    public $isEdit = false;
    public $editId = null;
    
    // Checklist fields
    public $name = '';
    public $code = '';
    public $description = '';
    public $audit_type_id = '';
    public $iso_standard = '';
    public $is_active = true;
    
    // Workflow and Sample Type relationships
    public $selected_workflow_actions = [];
    public $selected_sample_types = [];
    public $available_workflow_actions = [];
    public $available_sample_types = [];
    
    // Items management
    public $showItemsModal = false;
    public $selectedChecklistId = null;
    public $items = [];
    public $editingItemIndex = null;
    
    // Item fields
    public $item_number = '';
    public $iso_clause = '';
    public $requirement = '';
    public $guidance = '';
    public $evidence_required = '';
    public $is_mandatory = false;
    public $order_index = 0;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->loadAvailableData();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function loadAvailableData()
    {
        $this->available_workflow_actions = WorkflowAction::where('is_active', true)
            ->where('company_id', getUserCompany() ?? 0)
            ->orderBy('name')
            ->get()
            ->map(fn($action) => ['id' => $action->id, 'name' => $action->name])
            ->toArray();

        $this->available_sample_types = SampleType::where('active', true)
            ->where('company_id', getUserCompany() ?? 0)
            ->orderBy('name')
            ->get()
            ->map(fn($type) => ['id' => $type->id, 'name' => $type->name])
            ->toArray();
    }

    public function openModal($id = null)
    {
        $this->isEdit = $id !== null;
        $this->editId = $id;
        
        if ($this->isEdit) {
            $checklist = AuditChecklist::forCompany()->findOrFail($id);
            $this->name = $checklist->name;
            $this->code = $checklist->code;
            $this->description = $checklist->description ?? '';
            $this->audit_type_id = $checklist->audit_type_id ?? '';
            $this->iso_standard = $checklist->iso_standard ?? '';
            $this->is_active = $checklist->is_active;
            $this->selected_workflow_actions = $checklist->workflowActions->pluck('id')->toArray();
            $this->selected_sample_types = $checklist->sampleTypes->pluck('id')->toArray();
        } else {
            $this->resetForm();
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->audit_type_id = '';
        $this->iso_standard = '';
        $this->is_active = true;
        $this->selected_workflow_actions = [];
        $this->selected_sample_types = [];
        $this->isEdit = false;
        $this->editId = null;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:audit_checklists,code' . ($this->isEdit ? ',' . $this->editId : ''),
            'audit_type_id' => 'nullable|exists:audit_types,id',
            'iso_standard' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'audit_type_id' => $this->audit_type_id ?: null,
            'iso_standard' => $this->iso_standard,
            'is_active' => $this->is_active,
            'company_id' => getUserCompany() ?? 0,
        ];

        if ($this->isEdit) {
            $checklist = AuditChecklist::forCompany()->findOrFail($this->editId);
            $checklist->update($data);
            $message = 'Checklist updated successfully.';
        } else {
            $data['created_by'] = auth()->id();
            $checklist = AuditChecklist::create($data);
            $message = 'Checklist created successfully.';
        }

        // Sync relationships
        $checklist->workflowActions()->sync($this->selected_workflow_actions);
        $checklist->sampleTypes()->sync($this->selected_sample_types);

        session()->flash('message', $message);
        $this->closeModal();
    }

    public function toggleActive($id)
    {
        $checklist = AuditChecklist::forCompany()->findOrFail($id);
        $checklist->update(['is_active' => !$checklist->is_active]);
        session()->flash('message', 'Checklist status updated.');
    }

    public function delete($id)
    {
        $checklist = AuditChecklist::forCompany()->findOrFail($id);
        
        // Check if checklist is used in any audits
        if ($checklist->auditModuleAudits()->count() > 0) {
            session()->flash('error', 'Cannot delete checklist that is used in audits.');
            return;
        }
        
        $checklist->delete();
        session()->flash('message', 'Checklist deleted successfully.');
    }

    public function openItemsModal($checklistId)
    {
        $this->selectedChecklistId = $checklistId;
        $checklist = AuditChecklist::forCompany()->findOrFail($checklistId);
        $this->items = $checklist->items->map(function($item) {
            return [
                'id' => $item->id,
                'item_number' => $item->item_number,
                'iso_clause' => $item->iso_clause ?? '',
                'requirement' => $item->requirement,
                'guidance' => $item->guidance ?? '',
                'evidence_required' => $item->evidence_required ?? '',
                'order_index' => $item->order_index,
                'is_mandatory' => $item->is_mandatory,
                'is_active' => $item->is_active,
            ];
        })->toArray();
        $this->showItemsModal = true;
    }

    public function closeItemsModal()
    {
        $this->showItemsModal = false;
        $this->selectedChecklistId = null;
        $this->items = [];
        $this->editingItemIndex = null;
        $this->resetItemForm();
    }

    public function addItem()
    {
        $this->validate([
            'item_number' => 'required|string|max:50',
            'requirement' => 'required|string',
        ]);

        $newItem = [
            'id' => null,
            'item_number' => $this->item_number,
            'iso_clause' => $this->iso_clause,
            'requirement' => $this->requirement,
            'guidance' => $this->guidance,
            'evidence_required' => $this->evidence_required,
            'order_index' => count($this->items),
            'is_mandatory' => $this->is_mandatory,
            'is_active' => true,
        ];

        $this->items[] = $newItem;
        $this->resetItemForm();
    }

    public function editItem($index)
    {
        $this->editingItemIndex = $index;
        $item = $this->items[$index];
        $this->item_number = $item['item_number'];
        $this->iso_clause = $item['iso_clause'] ?? '';
        $this->requirement = $item['requirement'];
        $this->guidance = $item['guidance'] ?? '';
        $this->evidence_required = $item['evidence_required'] ?? '';
        $this->is_mandatory = $item['is_mandatory'];
    }

    public function updateItem()
    {
        $this->validate([
            'item_number' => 'required|string|max:50',
            'requirement' => 'required|string',
        ]);

        $this->items[$this->editingItemIndex]['item_number'] = $this->item_number;
        $this->items[$this->editingItemIndex]['iso_clause'] = $this->iso_clause;
        $this->items[$this->editingItemIndex]['requirement'] = $this->requirement;
        $this->items[$this->editingItemIndex]['guidance'] = $this->guidance;
        $this->items[$this->editingItemIndex]['evidence_required'] = $this->evidence_required;
        $this->items[$this->editingItemIndex]['is_mandatory'] = $this->is_mandatory;

        $this->resetItemForm();
    }

    public function deleteItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        // Reindex order
        foreach ($this->items as $i => $item) {
            $this->items[$i]['order_index'] = $i;
        }
    }

    public function toggleItemActive($index)
    {
        $this->items[$index]['is_active'] = !$this->items[$index]['is_active'];
    }

    public function moveItemUp($index)
    {
        if ($index > 0) {
            $temp = $this->items[$index];
            $this->items[$index] = $this->items[$index - 1];
            $this->items[$index - 1] = $temp;
            $this->items[$index]['order_index'] = $index;
            $this->items[$index - 1]['order_index'] = $index - 1;
        }
    }

    public function moveItemDown($index)
    {
        if ($index < count($this->items) - 1) {
            $temp = $this->items[$index];
            $this->items[$index] = $this->items[$index + 1];
            $this->items[$index + 1] = $temp;
            $this->items[$index]['order_index'] = $index;
            $this->items[$index + 1]['order_index'] = $index + 1;
        }
    }

    public function saveItems()
    {
        if (!$this->selectedChecklistId) {
            return;
        }

        $checklist = AuditChecklist::forCompany()->findOrFail($this->selectedChecklistId);
        
        // Delete removed items
        $existingItemIds = collect($this->items)->pluck('id')->filter()->toArray();
        $checklist->items()->whereNotIn('id', $existingItemIds)->delete();
        
        // Update or create items
        foreach ($this->items as $index => $itemData) {
            $itemData['order_index'] = $index;
            
            if ($itemData['id']) {
                // Update existing
                $item = AuditChecklistItem::find($itemData['id']);
                if ($item) {
                    $item->update([
                        'item_number' => $itemData['item_number'],
                        'iso_clause' => $itemData['iso_clause'],
                        'requirement' => $itemData['requirement'],
                        'guidance' => $itemData['guidance'],
                        'evidence_required' => $itemData['evidence_required'],
                        'order_index' => $itemData['order_index'],
                        'is_mandatory' => $itemData['is_mandatory'],
                        'is_active' => $itemData['is_active'],
                    ]);
                }
            } else {
                // Create new
                $itemData['audit_checklist_id'] = $checklist->id;
                AuditChecklistItem::create($itemData);
            }
        }

        session()->flash('message', 'Checklist items saved successfully.');
        $this->closeItemsModal();
    }

    public function resetItemForm()
    {
        $this->editingItemIndex = null;
        $this->item_number = '';
        $this->iso_clause = '';
        $this->requirement = '';
        $this->guidance = '';
        $this->evidence_required = '';
        $this->is_mandatory = false;
        $this->order_index = 0;
    }

    public function render()
    {
        $query = AuditChecklist::forCompany()
            ->with(['auditType', 'items', 'workflowActions', 'sampleTypes'])
            ->orderBy('name');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $checklists = $query->paginate($this->perPage);
        $auditTypes = getActiveAuditTypes();

        return view('livewire.audit-module.checklist-manager', [
            'checklists' => $checklists,
            'auditTypes' => $auditTypes,
            'workflowActions' => $this->available_workflow_actions,
            'sampleTypes' => $this->available_sample_types,
        ]);
    }
}
