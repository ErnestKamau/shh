<?php

namespace App\Livewire\Lab;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisMethod;
use App\CapturedResult;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Models\Equipments\Equipment;
use App\ReportingUnit;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class AnalyteManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    // Pagination
    public $perPage = 10;

    public $perPageOptions = [10, 25, 50, 100];

    // Filters
    public $search = '';

    public $statusFilter = '';

    // Modal state
    public bool $showModal = false;

    public ?string $editingAnalyteId = null;

    public bool $deleteModalVisible = false;

    public ?string $analyteToDeleteId = null;

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
    public string $methodSearch = '';

    public string $equipmentSearch = '';

    public bool $showMethodDropdown = false;

    public bool $showEquipmentDropdown = false;

    // Status tag-select
    public bool $showStatusDropdown = false;

    // Reporting unit single-select properties
    public string $reportingUnitSearch = '';

    public bool $showReportingUnitDropdown = false;

    // Message
    public $message = '';

    public $messageType = 'success';

    protected string $paginationTheme = 'bootstrap';

    protected int $searchResultLimit = 30;

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

    protected function validationAttributes(): array
    {
        return [
            'analyteForm.code' => 'report display',
            'analyteForm.name' => 'analyte name',
            'analyteForm.decimal_places' => 'decimal places',
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

    public function selectStatus($value): void
    {
        $this->statusFilter = $value;
        $this->showStatusDropdown = false;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    public function showCreateModal(): void
    {
        $this->resetAnalyteFormState();
        $this->reset(['message', 'messageType']);
        $this->showModal = true;
    }

    public function showEditModal(string $analyteId): void
    {
        $analyte = Analyte::findOrFail($analyteId);
        $this->editingAnalyteId = (string) $analyte->id;

        $this->analyteForm = [
            'code' => $analyte->code,
            'name' => $analyte->name,
            'common_name' => $analyte->common_name,
            'decimal_places' => $analyte->decimal_places,
            'equivalent_weight' => $analyte->equivalent_weight,
            'reporting_symbol' => $analyte->reporting_symbol,
            'reporting_unit' => $analyte->reporting_unit,
            'method' => $this->normalizeIdList(
                $analyte->analysisMethods()->pluck('analysis_methods.id')->all()
            ),
            'equipment_id' => $this->normalizeIdList(
                $analyte->equipmentItems()->pluck('equipment.id')->all()
            ),
            'is_italic' => (bool) $analyte->is_italic,
            'non_detectable' => (bool) $analyte->non_detectable,
            'non_accredited' => (bool) $analyte->non_accredited,
            'show_on_report' => (bool) $analyte->show_on_report,
            'active' => (bool) $analyte->active,
        ];

        $this->methodSearch = '';
        $this->equipmentSearch = '';
        $this->reportingUnitSearch = '';
        $this->showMethodDropdown = false;
        $this->showEquipmentDropdown = false;
        $this->showReportingUnitDropdown = false;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetAnalyteFormState();
    }

    protected function resetAnalyteFormState(): void
    {
        $this->editingAnalyteId = null;
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
        $this->methodSearch = '';
        $this->equipmentSearch = '';
        $this->reportingUnitSearch = '';
        $this->showMethodDropdown = false;
        $this->showEquipmentDropdown = false;
        $this->showReportingUnitDropdown = false;
    }

    public function saveAnalyte(): void
    {
        $this->validate();

        try {
            $methodIds = $this->normalizeIdList($this->analyteForm['method'] ?? []);
            $equipmentIds = $this->normalizeIdList($this->analyteForm['equipment_id'] ?? []);

            $data = [
                'code' => $this->analyteForm['code'],
                'name' => $this->analyteForm['name'],
                'common_name' => $this->analyteForm['common_name'],
                'decimal_places' => $this->analyteForm['decimal_places'],
                'equivalent_weight' => $this->analyteForm['equivalent_weight'],
                'reporting_symbol' => $this->analyteForm['reporting_symbol'],
                'reporting_unit' => $this->analyteForm['reporting_unit'],
                'method' => $methodIds !== [] ? implode(',', $methodIds) : null,
                'equipment_id' => $equipmentIds[0] ?? null,
                'is_italic' => ($this->analyteForm['is_italic'] ?? false) ? 1 : 0,
                'non_detectable' => ($this->analyteForm['non_detectable'] ?? false) ? 1 : 0,
                'non_accredited' => ($this->analyteForm['non_accredited'] ?? false) ? 1 : 0,
                'show_on_report' => ($this->analyteForm['show_on_report'] ?? true) ? 1 : 0,
                'active' => ($this->analyteForm['active'] ?? true) ? 1 : 0,
                'company_id' => getUserCompany(),
            ];

            if ($this->editingAnalyteId) {
                $analyte = Analyte::findOrFail($this->editingAnalyteId);
                $analyte->update($data);
                $this->message = 'Analyte updated successfully!';
            } else {
                $analyte = Analyte::create($data);
                $this->message = 'Analyte created successfully!';
            }

            $analyte->analysisMethods()->sync($methodIds);
            $analyte->equipmentItems()->sync($equipmentIds);

            $this->messageType = 'success';
            $this->showModal = false;
            $this->resetAnalyteFormState();
        } catch (\Exception $e) {
            $this->message = 'Error: '.$e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function showDeleteModal(string $analyteId): void
    {
        $this->analyteToDeleteId = $analyteId;
        $this->deleteModalVisible = true;
    }

    public function closeDeleteModal(): void
    {
        $this->deleteModalVisible = false;
        $this->analyteToDeleteId = null;
    }

    public function getAnalyteToDeleteProperty(): ?Analyte
    {
        if ($this->analyteToDeleteId === null) {
            return null;
        }

        return Analyte::withoutGlobalScope('notDeleted')->find($this->analyteToDeleteId);
    }

    public function confirmDelete(): void
    {
        try {
            $analyteToDelete = $this->analyteToDelete;

            if (! $analyteToDelete) {
                $this->message = 'Error: No analyte selected for deletion.';
                $this->messageType = 'danger';
                return;
            }

            // Check if analyte has any captured results tied to it
            $hasCapturedResults = CapturedResult::where('analyte_id', $analyteToDelete->id)->exists();

            // Check if used in any results (through captured_results relationship)
            $capturedResultIds = CapturedResult::where('analyte_id', $analyteToDelete->id)
                ->pluck('id')
                ->toArray();
            
            $hasResults = !empty($capturedResultIds) && Result::whereIn('captured_result_id', $capturedResultIds)->exists();

            if ($hasCapturedResults || $hasResults) {
                // Soft delete: Set deleted_at and active = 0
                $analyteToDelete->update([
                    'deleted_at' => now(),
                    'active' => 0
                ]);
                $this->message = 'Analyte has been soft deleted (has associated samples/results). It is now inactive and hidden from listings.';
            } else {
                // Hard delete: Remove from analysis_elements first, then delete analyte
                AnalysisElements::where('analyte_id', $analyteToDelete->id)->delete();
                    
                // Use withoutGlobalScope to ensure we can delete even if it has deleted_at
                Analyte::withoutGlobalScope('notDeleted')
                    ->where('id', $analyteToDelete->id)
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

    public function openMethodDropdown(): void
    {
        $this->showMethodDropdown = true;
        $this->showEquipmentDropdown = false;
        $this->showReportingUnitDropdown = false;
    }

    public function closeMethodDropdown(): void
    {
        $this->showMethodDropdown = false;
    }

    public function addMethod(string $methodId): void
    {
        $methodId = (string) $methodId;
        $selected = $this->normalizeIdList($this->analyteForm['method'] ?? []);

        if (! in_array($methodId, $selected, true)) {
            $selected[] = $methodId;
        }

        $this->analyteForm['method'] = $selected;
        $this->methodSearch = '';
        $this->showMethodDropdown = false;
    }

    public function removeMethod(string $methodId): void
    {
        $methodId = (string) $methodId;
        $this->analyteForm['method'] = array_values(array_filter(
            $this->normalizeIdList($this->analyteForm['method'] ?? []),
            fn (string $id): bool => $id !== $methodId
        ));
    }

    public function updatedMethodSearch(): void
    {
        $this->showMethodDropdown = true;
        $this->showEquipmentDropdown = false;
        $this->showReportingUnitDropdown = false;
    }

    public function openEquipmentDropdown(): void
    {
        $this->showEquipmentDropdown = true;
        $this->showMethodDropdown = false;
        $this->showReportingUnitDropdown = false;
    }

    public function closeEquipmentDropdown(): void
    {
        $this->showEquipmentDropdown = false;
    }

    public function addEquipment(string $equipmentId): void
    {
        $equipmentId = (string) $equipmentId;
        $selected = $this->normalizeIdList($this->analyteForm['equipment_id'] ?? []);

        if (! in_array($equipmentId, $selected, true)) {
            $selected[] = $equipmentId;
        }

        $this->analyteForm['equipment_id'] = $selected;
        $this->equipmentSearch = '';
        $this->showEquipmentDropdown = false;
    }

    public function removeEquipment(string $equipmentId): void
    {
        $equipmentId = (string) $equipmentId;
        $this->analyteForm['equipment_id'] = array_values(array_filter(
            $this->normalizeIdList($this->analyteForm['equipment_id'] ?? []),
            fn (string $id): bool => $id !== $equipmentId
        ));
    }

    public function updatedEquipmentSearch(): void
    {
        $this->showEquipmentDropdown = true;
        $this->showMethodDropdown = false;
        $this->showReportingUnitDropdown = false;
    }

    public function getFilteredMethodsProperty(): Collection
    {
        if (! $this->showMethodDropdown) {
            return collect();
        }

        $query = $this->methodOptionsQuery($this->methodSearch);
        $selectedIds = $this->normalizeIdList($this->analyteForm['method'] ?? []);

        if ($selectedIds !== []) {
            $query->whereNotIn('id', $selectedIds);
        }

        return $query->limit($this->searchResultLimit)->get();
    }

    public function getFilteredEquipmentProperty(): Collection
    {
        if (! $this->showEquipmentDropdown) {
            return collect();
        }

        $query = $this->equipmentOptionsQuery($this->equipmentSearch);
        $selectedIds = $this->normalizeIdList($this->analyteForm['equipment_id'] ?? []);

        if ($selectedIds !== []) {
            $query->whereNotIn('id', $selectedIds);
        }

        return $query->limit($this->searchResultLimit)->get();
    }

    public function getSelectedMethodsProperty(): Collection
    {
        $ids = $this->normalizeIdList($this->analyteForm['method'] ?? []);

        if ($ids === []) {
            return collect();
        }

        return AnalysisMethod::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    public function getSelectedEquipmentProperty(): Collection
    {
        $ids = $this->normalizeIdList($this->analyteForm['equipment_id'] ?? []);

        if ($ids === []) {
            return collect();
        }

        return Equipment::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return list<string>
     */
    protected function normalizeIdList(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map(
                static fn ($id): string => trim((string) ($id ?? '')),
                $ids
            ),
            static fn (string $id): bool => $id !== ''
        )));
    }

    /**
     * @return Builder<\App\AnalysisMethod>
     */
    protected function methodOptionsQuery(?string $search = null): Builder
    {
        $query = AnalysisMethod::query()
            ->where('active', true)
            ->orderBy('name');

        $this->applyCaseInsensitiveSearch($query, ['name', 'code'], (string) $search);

        return $query;
    }

    /**
     * @return Builder<\App\Models\Equipments\Equipment>
     */
    protected function equipmentOptionsQuery(?string $search = null): Builder
    {
        $query = Equipment::query()
            ->where('active', true)
            ->orderBy('name');

        $this->applyCaseInsensitiveSearch($query, ['name', 'equipment_number'], (string) $search);

        return $query;
    }

    /**
     * @return Builder<\App\ReportingUnit>
     */
    protected function reportingUnitOptionsQuery(?string $search = null): Builder
    {
        $query = ReportingUnit::query()
            ->where('active', true)
            ->orderBy('name');

        $this->applyCaseInsensitiveSearch($query, ['name'], (string) $search);

        return $query;
    }

    public function openReportingUnitDropdown(): void
    {
        $this->showReportingUnitDropdown = true;
        $this->showMethodDropdown = false;
        $this->showEquipmentDropdown = false;
    }

    public function closeReportingUnitDropdown(): void
    {
        $this->showReportingUnitDropdown = false;
    }

    public function selectReportingUnit(string $unit): void
    {
        $this->analyteForm['reporting_unit'] = $unit;
        $this->reportingUnitSearch = '';
        $this->showReportingUnitDropdown = false;
    }

    public function updatedReportingUnitSearch(): void
    {
        $this->showReportingUnitDropdown = true;
        $this->showMethodDropdown = false;
        $this->showEquipmentDropdown = false;
    }

    public function getFilteredReportingUnitsProperty(): Collection
    {
        if (! $this->showReportingUnitDropdown) {
            return collect();
        }

        return $this->reportingUnitOptionsQuery($this->reportingUnitSearch)
            ->limit($this->searchResultLimit)
            ->get();
    }

    public function render()
    {
        $query = Analyte::query()->where('company_id', getUserCompany());

        if ($this->search) {
            $this->applyCaseInsensitiveSearch($query, ['name', 'code', 'common_name'], (string) $this->search);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter === '1');
        }

        $analytes = $query->with(['analysisMethods', 'equipmentItems'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.lab.analyte-manager', [
            'analytes' => $analytes,
        ]);
    }
}
