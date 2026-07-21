<?php

namespace App\Livewire\MethodSequences;

use App\Models\MethodSequences\MethodSequence;
use App\Models\MethodSequences\MethodSequenceVersion;
use App\Models\MethodSequences\MethodSequenceStage;
use App\Analyte;
use App\AnalysisMethod;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MethodSequenceManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    public $perPageOptions = [10, 25, 50, 100];

    // Method sequence creation/editing
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showVersionModal = false;
    public $editingSequence = null;
    public $editingVersion = null;

    // Form fields
    public $sequenceName = '';
    public $sequenceDescription = '';
    public $analyteId = '';
    public $methodId = '';
    public $sequenceIsActive = true;
    public $versionDescription = '';

    // Searchable select fields
    public $analyteSearch = '';
    public $methodSearch = '';
    public $showAnalyteDropdown = false;
    public $showMethodDropdown = false;
    public $filteredAnalytes;
    public $filteredMethods;
    public $selectedAnalyteName = '';
    public $selectedMethodName = '';

    // Messages
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'sequenceName' => 'required|string|max:255',
        'sequenceDescription' => 'nullable|string',
        'analyteId' => 'required|exists:analytes,id',
        'methodId' => 'required|exists:analysis_methods,id',
        'sequenceIsActive' => 'boolean',
    ];

    public function mount(): void
    {
        $this->perPage = 10;
    }

    public function render()
    {
        $query = MethodSequence::with(['analyte', 'method', 'versions' => function ($query) {
            $query->where('is_active', true);
        }]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('analyte', function ($aq) {
                      $aq->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('method', function ($mq) {
                      $mq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->statusFilter) {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $sequences = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        $analytes = Analyte::orderBy('name')->get();
        $methods = AnalysisMethod::orderBy('name')->get();

        return view('livewire.method-sequences.method-sequence-manager', [
            'sequences' => $sequences,
            'analytes' => $analytes,
            'methods' => $methods,
        ]);
    }

    public function showCreateSequenceModal(): void
    {
        $this->resetForm();
        $this->searchMethods();
        $this->showCreateModal = true;
        $this->dispatch('modal-opened');
    }

    public function showEditSequenceModal(MethodSequence $sequence): void
    {
        $this->editingSequence = $sequence;
        $this->sequenceName = $sequence->name;
        $this->sequenceDescription = $sequence->description;
        $this->analyteId = $sequence->analyte_id;
        $this->methodId = $sequence->method_id;
        $this->sequenceIsActive = $sequence->is_active;
        
        // Populate analyte display fields for tag-style dropdown
        if ($sequence->analyte) {
            $this->selectedAnalyteName = $sequence->analyte->name;
            $this->analyteSearch = $sequence->analyte->name;
        }
        
        // Populate method display fields for tag-style dropdown
        if ($sequence->method) {
            $this->selectedMethodName = $sequence->method->name;
            $this->methodSearch = $sequence->method->name;
        }

        $this->searchMethods();
        $this->showEditModal = true;
        $this->dispatch('modal-opened');
    }

    public function showVersionModal(MethodSequence $sequence): void
    {
        $this->editingSequence = $sequence;
        $this->showVersionModal = true;
        $this->dispatch('modal-opened');
    }

    public function createSequence()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $sequence = MethodSequence::create([
                'name' => $this->sequenceName,
                'description' => $this->sequenceDescription,
                'analyte_id' => $this->analyteId,
                'method_id' => $this->methodId,
                'is_active' => $this->sequenceIsActive,
            ]);

            // Create initial version
            $version = MethodSequenceVersion::create([
                'method_sequence_id' => $sequence->id,
                'version_number' => 1,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            $this->showCreateModal = false;
            $this->resetForm();
            $this->setMessage('Method sequence created successfully! Redirecting to add stages...', 'success');
            
            // Redirect to stage editor
            return redirect()->route('method-sequences.stages', $version);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error creating method sequence: ' . $e->getMessage(), 'error');
        }
    }

    public function updateSequence(): void
    {
        $this->validate();

        try {
            $this->editingSequence->update([
                'name' => $this->sequenceName,
                'description' => $this->sequenceDescription,
                'analyte_id' => $this->analyteId,
                'method_id' => $this->methodId,
                'is_active' => $this->sequenceIsActive,
            ]);

            $this->showEditModal = false;
            $this->resetForm();
            $this->setMessage('Method sequence updated successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating method sequence: ' . $e->getMessage(), 'error');
        }
    }

    public function createNewVersion(): void
    {
        $this->validate([
            'versionDescription' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Get the latest version number
            $latestVersion = $this->editingSequence->versions()
                ->orderBy('version_number', 'desc')
                ->first();

            $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

            // Deactivate current active version
            $this->editingSequence->versions()
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Create new version
            $newVersion = MethodSequenceVersion::create([
                'method_sequence_id' => $this->editingSequence->id,
                'version_number' => $newVersionNumber,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            // Copy stages from previous version if exists
            if ($latestVersion) {
                $stages = $latestVersion->stages;
                foreach ($stages as $stage) {
                    MethodSequenceStage::create([
                        'method_sequence_version_id' => $newVersion->id,
                        'name' => $stage->name,
                        'description' => $stage->description,
                        'order' => $stage->order,
                        'equipment_ids' => $stage->equipment_ids,
                        'media_ids' => $stage->media_ids,
                        'control_ids' => $stage->control_ids,
                        'is_result_stage' => $stage->is_result_stage,
                        'fail_move_next_stage' => $stage->fail_move_next_stage,
                        'duration' => $stage->duration,
                        'move_to_next_stage_safe_duration' => $stage->move_to_next_stage_safe_duration,
                    ]);
                }
            }

            DB::commit();

            $this->showVersionModal = false;
            $this->versionDescription = '';
            $this->setMessage('New version created successfully!', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error creating new version: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteSequence(MethodSequence $sequence): void
    {
        try {
            $sequence->delete();
            $this->setMessage('Method sequence deleted successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error deleting method sequence: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleSequenceStatus(MethodSequence $sequence): void
    {
        try {
            $sequence->update(['is_active' => !$sequence->is_active]);
            $status = $sequence->is_active ? 'activated' : 'deactivated';
            $this->setMessage("Method sequence {$status} successfully!", 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating method sequence status: ' . $e->getMessage(), 'error');
        }
    }

    public function cloneSequence(MethodSequence $sequence)
    {
        return redirect()->route('method-sequences.clone', $sequence);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
        $this->dispatch('filters-cleared');
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function searchAnalytes(): void
    {
        if (strlen($this->analyteSearch) >= 2) {
            $this->filteredAnalytes = Analyte::where('name', 'like', '%' . $this->analyteSearch . '%')
                ->limit(10)
                ->get();
            $this->showAnalyteDropdown = true;
        } else {
            $this->showAnalyteDropdown = false;
            $this->filteredAnalytes = collect();
        }
    }

    public function searchMethods(): void
    {
        $query = AnalysisMethod::query()
            ->where('active', 1)
            ->orderBy('name');

        if ($this->methodSearch !== '') {
            $search = $this->methodSearch;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $this->filteredMethods = $query->get();
        $this->showMethodDropdown = true;
    }

    public function openMethodDropdown(): void
    {
        $this->filteredMethods = AnalysisMethod::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get();
        $this->showMethodDropdown = true;
    }

    public function selectAnalyte($id, $name): void
    {
        $this->analyteId = $id;
        $this->selectedAnalyteName = $name;
        $this->analyteSearch = $name;
        $this->showAnalyteDropdown = false;
        $this->filteredAnalytes = collect();
    }

    public function selectMethod($id, $name): void
    {
        $this->methodId = $id;
        $this->selectedMethodName = $name;
        $this->methodSearch = $name;
        $this->showMethodDropdown = false;
        $this->filteredMethods = collect();
    }

    public function clearAnalyte(): void
    {
        $this->analyteId = '';
        $this->selectedAnalyteName = '';
        $this->analyteSearch = '';
        $this->showAnalyteDropdown = false;
        $this->filteredAnalytes = collect();
    }

    public function clearMethod(): void
    {
        $this->methodId = '';
        $this->selectedMethodName = '';
        $this->methodSearch = '';
        $this->showMethodDropdown = false;
        $this->filteredMethods = collect();
    }

    protected function resetForm(): void
    {
        $this->sequenceName = '';
        $this->sequenceDescription = '';
        $this->analyteId = '';
        $this->methodId = '';
        $this->sequenceIsActive = true;
        $this->versionDescription = '';
        $this->editingSequence = null;
        $this->editingVersion = null;
        
        // Reset searchable select fields
        $this->analyteSearch = '';
        $this->methodSearch = '';
        $this->showAnalyteDropdown = false;
        $this->showMethodDropdown = false;
        $this->filteredAnalytes = collect();
        $this->filteredMethods = collect();
        $this->selectedAnalyteName = '';
        $this->selectedMethodName = '';
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    protected function rules(): array
    {
        return [
            'sequenceName' => 'required|string|max:255',
            'sequenceDescription' => 'nullable|string',
            'analyteId' => 'required|exists:analytes,id',
            'methodId' => 'required|exists:analysis_methods,id',
            'sequenceIsActive' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'sequenceName.required' => 'The sequence name field is required.',
            'sequenceName.max' => 'The sequence name may not be greater than 255 characters.',
            'analyteId.required' => 'The analyte field is required.',
            'analyteId.exists' => 'The selected analyte is invalid.',
            'methodId.required' => 'The method field is required.',
            'methodId.exists' => 'The selected method is invalid.',
        ];
    }
}

