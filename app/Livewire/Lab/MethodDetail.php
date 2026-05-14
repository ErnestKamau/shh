<?php

namespace App\Livewire\Lab;

use App\AnalysisMethod;
use App\Company;
use App\LabSubCategory;
use App\MethodReagent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MethodDetail extends Component
{
    public AnalysisMethod $method;
    public $activeTab = 'analytes';
    
    // Form data for editing method
    public $methodForm = [
        'name' => '',
        'code' => '',
        'description' => '',
        'active' => true,
        'is_sampling_method' => false,
        'is_ltm' => false,
    ];
    
    // Reagents management
    public $reagents = [];
    public $reagentSearch = '';
    public $showReagentDropdown = false;
    public $filteredReagents = [];
    public $pendingReagent = [
        'reagent_id' => null,
        'reagent_name' => '',
        'volume' => '',
        'unit' => '',
        'batch_number' => '',
    ];
    
    // Messages
    public $message = '';
    public $messageType = '';
    
    // Companies (for super admin)
    public $companies = [];

    protected function rules(): array
    {
        return [
            'methodForm.name' => 'required|string|max:255',
            'methodForm.code' => 'required|string|max:255',
            'methodForm.description' => 'required|string',
            'methodForm.active' => 'boolean',
            'methodForm.is_sampling_method' => 'boolean',
            'methodForm.is_ltm' => 'boolean',
        ];
    }

    public function mount(string $methodId): void
    {
        $this->method = AnalysisMethod::with(['referencemethod', 'methodtype'])->findOrFail($methodId);
        
        $this->methodForm = [
            'name' => $this->method->name,
            'code' => $this->method->code,
            'description' => $this->method->description,
            'active' => (bool) $this->method->active,
            'is_sampling_method' => (bool) $this->method->is_sampling_method,
            'is_ltm' => (bool) $this->method->is_ltm,
        ];
        
        $this->loadReagents();
        
        // Load companies for super admin
        if (Auth::user()->company_id == 0) {
            $this->companies = Company::all();
        }
    }

    public function loadReagents(): void
    {
        $this->reagents = MethodReagent::join('inventory_sub_categories as isc', 'isc.id', '=', 'method_reagents.inventory_sub_category_id')
            ->selectRaw('method_reagents.id, method_reagents.inventory_sub_category_id as reagent_id, isc.name as reagent_name, method_reagents.quantity, method_reagents.reporting_unit as reagent_unit')
            ->where('method_id', $this->method->id)
            ->get()
            ->toArray();
    }

    public function updateMethod(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $this->method->update([
                'name' => $this->methodForm['name'],
                'code' => $this->methodForm['code'],
                'description' => $this->methodForm['description'],
                'active' => $this->methodForm['active'] ? 1 : 0,
                'is_sampling_method' => $this->methodForm['is_sampling_method'] ? 1 : 0,
                'is_ltm' => $this->methodForm['is_ltm'] ? 1 : 0,
            ]);

            DB::commit();

            $this->setMessage('Method updated successfully!', 'success');
            
            // Reload method to get fresh data
            $this->method = AnalysisMethod::with(['referencemethod', 'methodtype'])->find($this->method->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error updating method: ' . $e->getMessage(), 'error');
        }
    }

    public function searchReagents(): void
    {
        if (strlen($this->reagentSearch) < 2) {
            $this->filteredReagents = [];
            return;
        }

        $reagentsCategory = 20004;
        
        $this->filteredReagents = LabSubCategory::where('category_id', $reagentsCategory)
            ->where('name', 'LIKE', '%' . $this->reagentSearch . '%')
            ->where('active', 1)
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit' => $item->reporting_unit ?? ''
                ];
            })
            ->toArray();
        
        $this->showReagentDropdown = true;
    }

    public function selectReagent(int $reagentId, string $reagentName, string $unit): void
    {
        $this->pendingReagent['reagent_id'] = $reagentId;
        $this->pendingReagent['reagent_name'] = $reagentName;
        $this->pendingReagent['unit'] = $unit;
        $this->reagentSearch = $reagentName;
        $this->showReagentDropdown = false;
    }

    public function addReagent(): void
    {
        if (!$this->pendingReagent['reagent_id'] || !$this->pendingReagent['volume']) {
            $this->setMessage('Please select a reagent and enter quantity.', 'error');
            return;
        }

        try {
            // Check if reagent already exists
            $exists = MethodReagent::where('method_id', $this->method->id)
                ->where('inventory_sub_category_id', $this->pendingReagent['reagent_id'])
                ->exists();

            if ($exists) {
                $this->setMessage('This reagent is already added to this method.', 'error');
                return;
            }

            MethodReagent::create([
                'method_id' => $this->method->id,
                'inventory_sub_category_id' => $this->pendingReagent['reagent_id'],
                'quantity' => $this->pendingReagent['volume'],
                'reporting_unit' => $this->pendingReagent['unit'],
            ]);

            $this->loadReagents();
            $this->resetPendingReagent();
            $this->setMessage('Reagent added successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error adding reagent: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteReagent(int $reagentId): void
    {
        try {
            MethodReagent::where('id', $reagentId)->delete();
            $this->loadReagents();
            $this->setMessage('Reagent removed successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error removing reagent: ' . $e->getMessage(), 'error');
        }
    }

    protected function resetPendingReagent(): void
    {
        $this->pendingReagent = [
            'reagent_id' => null,
            'reagent_name' => '',
            'volume' => '',
            'unit' => '',
            'batch_number' => '',
        ];
        $this->reagentSearch = '';
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
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
        return view('livewire.lab.method-detail');
    }
}

