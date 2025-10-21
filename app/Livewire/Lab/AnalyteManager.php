<?php

namespace App\Livewire\Lab;

use App\Analyte;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use App\ReportingUnit;
use App\CapturedResult;
use App\Result;
use App\AnalysisElements;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class AnalyteManager extends Component
{
    use WithPagination;

    // Pagination
    public $perPage = 10;
    public $perPageOptions = [10, 25, 50, 100];
    
    // Filters
    public $search = '';
    public $statusFilter = '';
    
    // Modal state
    public $showModal = false;
    public $editingAnalyte = null;
    public $deleteModalVisible = false;
    public $analyteToDelete = null;
    
    // Form data
    public $analyteForm = [
        'code' => '',
        'name' => '',
        'common_name' => '',
        'decimal_places' => 0,
        'equivalent_weight' => null,
        'reporting_symbol' => '',
        'reporting_unit' => '',
        'method' => [],
        'equipment_id' => [],
        'is_italic' => false,
        'non_detectable' => false,
        'non_accredited' => false,
        'show_on_report' => true,
        'active' => true,
    ];
    
    // Tag-based multi-select properties
    public $methodSearch = '';
    public $equipmentSearch = '';
    public $showMethodDropdown = false;
    public $showEquipmentDropdown = false;
    
    // Reporting unit single-select properties
    public $reportingUnitSearch = '';
    public $showReportingUnitDropdown = false;
    
    // Message
    public $message = '';
    public $messageType = 'success';
    
    protected string $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        return [
            'analyteForm.code' => 'required|string|max:255',
            'analyteForm.name' => 'required|string|max:255',
            'analyteForm.common_name' => 'nullable|string|max:255',
            'analyteForm.decimal_places' => 'required|integer|min:0',
            'analyteForm.equivalent_weight' => 'nullable|numeric',
            'analyteForm.reporting_symbol' => 'nullable|string|max:255',
            'analyteForm.reporting_unit' => 'nullable|string|max:255',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    public function showCreateModal(): void
    {
        $this->reset(['analyteForm', 'editingAnalyte', 'message', 'methodSearch', 'equipmentSearch', 'reportingUnitSearch']);
        $this->analyteForm = [
            'code' => '',
            'name' => '',
            'common_name' => '',
            'decimal_places' => 0,
            'equivalent_weight' => null,
            'reporting_symbol' => '',
            'reporting_unit' => '',
            'method' => [],
            'equipment_id' => [],
            'is_italic' => false,
            'non_detectable' => false,
            'non_accredited' => false,
            'show_on_report' => true,
            'active' => true,
        ];
        $this->showModal = true;
    }

    public function showEditModal($analyteId): void
    {
        $analyte = Analyte::findOrFail($analyteId);
        $this->editingAnalyte = $analyte;
        
        $this->analyteForm = [
            'code' => $analyte->code,
            'name' => $analyte->name,
            'common_name' => $analyte->common_name,
            'decimal_places' => $analyte->decimal_places,
            'equivalent_weight' => $analyte->equivalent_weight,
            'reporting_symbol' => $analyte->reporting_symbol,
            'reporting_unit' => $analyte->reporting_unit,
            'method' => $analyte->method ? array_map('intval', explode(',', $analyte->method)) : [],
            'equipment_id' => $analyte->equipment_id ? array_map('intval', explode(',', $analyte->equipment_id)) : [],
            'is_italic' => (bool) $analyte->is_italic,
            'non_detectable' => (bool) $analyte->non_detectable,
            'non_accredited' => (bool) $analyte->non_accredited,
            'show_on_report' => (bool) $analyte->show_on_report,
            'active' => (bool) $analyte->active,
        ];
        
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['analyteForm', 'editingAnalyte', 'methodSearch', 'equipmentSearch', 'reportingUnitSearch']);
    }

    public function saveAnalyte(): void
    {
        $this->validate();
        
        try {
            $data = [
                'code' => $this->analyteForm['code'],
                'name' => $this->analyteForm['name'],
                'common_name' => $this->analyteForm['common_name'],
                'decimal_places' => $this->analyteForm['decimal_places'],
                'equivalent_weight' => $this->analyteForm['equivalent_weight'],
                'reporting_symbol' => $this->analyteForm['reporting_symbol'],
                'reporting_unit' => $this->analyteForm['reporting_unit'],
                'method' => !empty($this->analyteForm['method']) ? implode(',', $this->analyteForm['method']) : null,
                'equipment_id' => !empty($this->analyteForm['equipment_id']) ? implode(',', $this->analyteForm['equipment_id']) : null,
                'is_italic' => ($this->analyteForm['is_italic'] ?? false) ? 1 : 0,
                'non_detectable' => ($this->analyteForm['non_detectable'] ?? false) ? 1 : 0,
                'non_accredited' => ($this->analyteForm['non_accredited'] ?? false) ? 1 : 0,
                'show_on_report' => ($this->analyteForm['show_on_report'] ?? true) ? 1 : 0,
                'active' => ($this->analyteForm['active'] ?? true) ? 1 : 0,
                'company_id' => getUserCompany(),
            ];
            
            if ($this->editingAnalyte) {
                $this->editingAnalyte->update($data);
                $this->message = 'Analyte updated successfully!';
            } else {
                Analyte::create($data);
                $this->message = 'Analyte created successfully!';
            }
            
            $this->messageType = 'success';
            $this->closeModal();
        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function showDeleteModal($analyteId): void
    {
        $this->analyteToDelete = Analyte::withoutGlobalScope('notDeleted')->findOrFail($analyteId);
        $this->deleteModalVisible = true;
    }

    public function closeDeleteModal(): void
    {
        $this->deleteModalVisible = false;
        $this->analyteToDelete = null;
    }

    public function confirmDelete(): void
    {
        try {
            if (!$this->analyteToDelete) {
                $this->message = 'Error: No analyte selected for deletion.';
                $this->messageType = 'danger';
                return;
            }

            // Check if analyte has any captured results tied to it
            $hasCapturedResults = CapturedResult::where('analyte_id', $this->analyteToDelete->id)->exists();

            // Check if used in any results (through captured_results relationship)
            $capturedResultIds = CapturedResult::where('analyte_id', $this->analyteToDelete->id)
                ->pluck('id')
                ->toArray();
            
            $hasResults = !empty($capturedResultIds) && Result::whereIn('captured_result_id', $capturedResultIds)->exists();

            if ($hasCapturedResults || $hasResults) {
                // Soft delete: Set deleted_at and active = 0
                $this->analyteToDelete->update([
                    'deleted_at' => now(),
                    'active' => 0
                ]);
                $this->message = 'Analyte has been soft deleted (has associated samples/results). It is now inactive and hidden from listings.';
            } else {
                // Hard delete: Remove from analysis_elements first, then delete analyte
                AnalysisElements::where('analyte_id', $this->analyteToDelete->id)->delete();
                    
                // Use withoutGlobalScope to ensure we can delete even if it has deleted_at
                Analyte::withoutGlobalScope('notDeleted')
                    ->where('id', $this->analyteToDelete->id)
                    ->delete();
                    
                $this->message = 'Analyte and its analysis elements have been permanently deleted.';
            }

            $this->messageType = 'success';
            $this->closeDeleteModal();
            
        } catch (\Exception $e) {
            $this->message = 'Error deleting analyte: ' . $e->getMessage();
            $this->messageType = 'danger';
            $this->closeDeleteModal();
        }
    }

    public function dismissMessage(): void
    {
        $this->reset(['message', 'messageType']);
    }

    // Tag-based multi-select methods for Methods
    public function addMethod($methodId): void
    {
        if (!in_array($methodId, $this->analyteForm['method'])) {
            $this->analyteForm['method'][] = $methodId;
        }
        $this->methodSearch = '';
        $this->showMethodDropdown = false;
    }

    public function removeMethod($methodId): void
    {
        $this->analyteForm['method'] = array_values(array_filter(
            $this->analyteForm['method'], 
            fn($id) => $id != $methodId
        ));
    }

    public function updatedMethodSearch(): void
    {
        $this->showMethodDropdown = !empty($this->methodSearch);
    }

    // Tag-based multi-select methods for Equipment
    public function addEquipment($equipmentId): void
    {
        if (!in_array($equipmentId, $this->analyteForm['equipment_id'])) {
            $this->analyteForm['equipment_id'][] = $equipmentId;
        }
        $this->equipmentSearch = '';
        $this->showEquipmentDropdown = false;
    }

    public function removeEquipment($equipmentId): void
    {
        $this->analyteForm['equipment_id'] = array_values(array_filter(
            $this->analyteForm['equipment_id'], 
            fn($id) => $id != $equipmentId
        ));
    }

    public function updatedEquipmentSearch(): void
    {
        $this->showEquipmentDropdown = !empty($this->equipmentSearch);
    }

    public function getFilteredMethodsProperty()
    {
        if (empty($this->methodSearch)) {
            return [];
        }
        
        return AnalysisMethod::where('name', 'like', '%' . $this->methodSearch . '%')
            ->where('active', 1)
            ->limit(10)
            ->get();
    }

    public function getFilteredEquipmentProperty()
    {
        if (empty($this->equipmentSearch)) {
            return [];
        }
        
        return Equipment::where('name', 'like', '%' . $this->equipmentSearch . '%')
            ->where('active', 1)
            ->limit(10)
            ->get();
    }

    public function getSelectedMethodsProperty()
    {
        if (empty($this->analyteForm['method'])) {
            return collect();
        }
        
        return AnalysisMethod::whereIn('id', $this->analyteForm['method'])->get();
    }

    public function getSelectedEquipmentProperty()
    {
        if (empty($this->analyteForm['equipment_id'])) {
            return collect();
        }
        
        return Equipment::whereIn('id', $this->analyteForm['equipment_id'])->get();
    }

    // Reporting unit single-select methods
    public function selectReportingUnit($unit): void
    {
        $this->analyteForm['reporting_unit'] = $unit;
        $this->reportingUnitSearch = '';
        $this->showReportingUnitDropdown = false;
    }

    public function updatedReportingUnitSearch(): void
    {
        $this->showReportingUnitDropdown = !empty($this->reportingUnitSearch);
    }

    public function getFilteredReportingUnitsProperty()
    {
        if (empty($this->reportingUnitSearch)) {
            return [];
        }
        
        return ReportingUnit::where('name', 'like', '%' . $this->reportingUnitSearch . '%')
            ->where('active', 1)
            ->limit(50)
            ->get();
    }

    public function render()
    {
        $query = Analyte::query()->where('company_id', getUserCompany());
        
        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('common_name', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply status filter
        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }
        
        $analytes = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
        
        $reportingUnits = ReportingUnit::where('active', 1)->get();
        $methods = AnalysisMethod::where('active', 1)->get();
        $equipment = Equipment::where('active', 1)->get();
        
        return view('livewire.lab.analyte-manager', [
            'analytes' => $analytes,
            'reportingUnits' => $reportingUnits,
            'methods' => $methods,
            'equipment' => $equipment,
        ]);
    }
}
