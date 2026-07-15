<?php

namespace App\Livewire\Lab;

use App\AnalysisMethod;
use App\Models\System\SystemConfiguration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MethodManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filter properties
    public $search = '';
    public $statusFilter = '';
    public $methodTypeFilter = '';
    public $perPage = 10;
    public $perPageOptions = [10, 25, 50, 100];

    // Modal properties
    public $showMethodModal = false;
    public $editingMethod = null;

    // Form data
    public $methodForm = [
        'name' => '',
        'code' => '',
        'description' => '',
        'method_type_id' => '',
        'reference_type_id' => '',
        'active' => true,
    ];

    // Data properties
    public $methodTypes = [];
    public $referenceMethods = [];
    public $ltmMethodTypeId = null;

    // Messages
    public $message = '';
    public $messageType = '';

    protected function rules(): array
    {
        return [
            'methodForm.name' => 'required|string|max:255',
            'methodForm.code' => 'required|string|max:255',
            'methodForm.description' => 'required|string',
            'methodForm.method_type_id' => 'nullable|integer',
            'methodForm.reference_type_id' => 'nullable|integer',
            'methodForm.active' => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->loadStaticData();
    }

    protected function loadStaticData(): void
    {
        // Load method types from system configuration
        $this->methodTypes = SystemConfiguration::where('key', 'method_type')->get();
        
        // Get LTM method type ID
        $ltmConfig = SystemConfiguration::where('key', 'method_ltm_id')->first();
        $this->ltmMethodTypeId = $ltmConfig ? $ltmConfig->value : null;
        
        // Load reference methods
        $referenceConfig = SystemConfiguration::where('key', 'method_reference_id')->first();
        if ($referenceConfig) {
            $this->referenceMethods = AnalysisMethod::where('method_type_id', $referenceConfig->value)
                ->where('active', 1)
                ->get();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMethodTypeFilter(): void
    {
        $this->resetPage();
    }

    public function getMethodsProperty()
    {
        $query = AnalysisMethod::with(['referencemethod', 'methodtype']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter === 'active') {
            $query->where('active', 1);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('active', 0);
        }

        // Apply method type filter
        if ($this->methodTypeFilter) {
            $query->where('method_type_id', $this->methodTypeFilter);
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    public function showCreateMethodModal(): void
    {
        $this->reset(['methodForm', 'editingMethod', 'message']);
        $this->methodForm['active'] = true;
        $this->showMethodModal = true;
        $this->dispatch('method-modal-opened');
    }

    public function showEditMethodModal(string $methodId): void
    {
        $this->editingMethod = AnalysisMethod::find($methodId);
        
        if ($this->editingMethod) {
            $this->methodForm = [
                'name' => $this->editingMethod->name,
                'code' => $this->editingMethod->code,
                'description' => $this->editingMethod->description,
                'method_type_id' => $this->editingMethod->method_type_id,
                'reference_type_id' => $this->editingMethod->reference_type_id,
                'active' => (bool) $this->editingMethod->active,
            ];
            
            $this->showMethodModal = true;
            $this->dispatch('method-modal-opened');
        }
    }

    public function saveMethod(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->methodForm['name'],
                'code' => $this->methodForm['code'],
                'description' => $this->methodForm['description'],
                'method_type_id' => $this->methodForm['method_type_id'] ?: null,
                'reference_type_id' => $this->methodForm['reference_type_id'] ?: null,
                'active' => $this->methodForm['active'] ? 1 : 0,
                'company_id' => getUserCompany(),
            ];

            if ($this->editingMethod) {
                $this->editingMethod->update($data);
                $message = 'Analysis Method updated successfully!';
            } else {
                AnalysisMethod::create($data);
                $message = 'Analysis Method created successfully!';
            }

            DB::commit();

            $this->closeMethodModal();
            $this->setMessage($message, 'success');
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error saving method: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteMethod(string $methodId): void
    {
        try {
            $method = AnalysisMethod::find($methodId);
            
            if ($method) {
                // Check if method has associated analytes
                $analytesCount = $method->analytes()->count();
                
                if ($analytesCount > 0) {
                    $this->setMessage('Cannot delete method with associated analytes. Please remove analytes first.', 'error');
                    return;
                }
                
                $method->delete();
                $this->setMessage('Analysis Method deleted successfully!', 'success');
                $this->resetPage();
            }
        } catch (\Exception $e) {
            $this->setMessage('Error deleting method: ' . $e->getMessage(), 'error');
        }
    }

    public function closeMethodModal(): void
    {
        $this->showMethodModal = false;
        $this->reset(['methodForm', 'editingMethod']);
        $this->dispatch('method-modal-closed');
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'methodTypeFilter']);
        $this->resetPage();
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.lab.method-manager');
    }
}

