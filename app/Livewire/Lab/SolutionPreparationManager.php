<?php

namespace App\Livewire\Lab;

use App\LabSubCategory;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Models\SolutionPreparation;
use App\ReportingUnit;
use App\Services\Preparation\PreparationRunService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class SolutionPreparationManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $solutionFilter = '';

    public $perPage = 25;

    /** @var array<int, int> */
    public array $perPageOptions = [25, 50, 75, 100];

    public bool $showCreateModal = false;

    public ?string $editingPreparationId = null;

    public bool $showDeleteModal = false;

    public ?string $deletingPreparationId = null;

    public array $form = [
        'solution_id' => null,
        'quantity_prepared' => '',
        'uom_id' => null,
        'batch_number' => '',
        'expiry_date' => '',
        'is_new_batch' => false,
        'notes' => '',
        'create_with_alternative' => false,
    ];

    public string $message = '';

    public string $messageType = '';

    public string $solutionSearch = '';

    public string $uomSearch = '';

    public bool $showSolutionDropdown = false;

    public bool $showUomDropdown = false;

    public string $filterStatusSearch = '';

    public string $filterSolutionSearch = '';

    public bool $showFilterStatusDropdown = false;

    public bool $showFilterSolutionDropdown = false;

    protected $queryString = ['search', 'statusFilter', 'solutionFilter'];

    public function mount(): void
    {
        $this->syncFilterStatusSearchLabel();
        $this->syncFilterSolutionSearchLabel();

        if ($solutionId = request()->query('solution')) {
            $this->form['solution_id'] = $solutionId;
            $this->updatedFormSolutionId($solutionId);
            $this->syncSolutionSearchLabel();
            $this->solutionFilter = $solutionId;
            $this->syncFilterSolutionSearchLabel();
            $this->showCreateModal = true;
            $this->dispatch('spm-modal-opened');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function statusFilterOptions(): array
    {
        return [
            ['value' => '', 'label' => 'All statuses'],
            ['value' => 'preparing', 'label' => 'Preparing'],
            ['value' => 'awaiting_approval', 'label' => 'Awaiting approval'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'cancelled', 'label' => 'Cancelled'],
        ];
    }

    public function getSelectedFilterStatusLabelProperty(): ?string
    {
        if ($this->statusFilter === '') {
            return null;
        }

        $option = collect($this->statusFilterOptions())->firstWhere('value', $this->statusFilter);

        return $option['label'] ?? null;
    }

    public function getFilteredStatusOptionsProperty(): Collection
    {
        $needle = trim(strtolower($this->filterStatusSearch));

        return collect($this->statusFilterOptions())
            ->filter(function (array $option) use ($needle) {
                if ((string) $this->statusFilter === (string) $option['value']) {
                    return false;
                }

                if ($needle === '') {
                    return true;
                }

                return str_contains(strtolower($option['label']), $needle);
            })
            ->values();
    }

    public function getSelectedFilterSolutionProperty(): ?LabSubCategory
    {
        if ($this->solutionFilter === '') {
            return null;
        }

        return $this->solutions->first(fn ($solution) => (string) $solution->id === (string) $this->solutionFilter);
    }

    public function getFilteredFilterSolutionsProperty(): Collection
    {
        return $this->filterSelectCollection(
            $this->solutions,
            $this->filterSolutionSearch,
            $this->solutionFilter,
            ['name']
        );
    }

    public function openFilterStatusDropdown(): void
    {
        $this->showFilterStatusDropdown = true;
    }

    public function closeFilterStatusDropdown(): void
    {
        $this->showFilterStatusDropdown = false;
    }

    public function openFilterSolutionDropdown(): void
    {
        $this->showFilterSolutionDropdown = true;
    }

    public function closeFilterSolutionDropdown(): void
    {
        $this->showFilterSolutionDropdown = false;
    }

    public function selectStatusFilter(string $value): void
    {
        $this->statusFilter = $value;
        $this->syncFilterStatusSearchLabel();
        $this->showFilterStatusDropdown = false;
        $this->resetPage();
    }

    public function clearStatusFilter(): void
    {
        $this->statusFilter = '';
        $this->filterStatusSearch = '';
        $this->showFilterStatusDropdown = false;
        $this->resetPage();
    }

    public function selectSolutionFilter(string $solutionId): void
    {
        $this->solutionFilter = $solutionId;
        $this->syncFilterSolutionSearchLabel();
        $this->showFilterSolutionDropdown = false;
        $this->resetPage();
    }

    public function clearSolutionFilter(): void
    {
        $this->solutionFilter = '';
        $this->filterSolutionSearch = '';
        $this->showFilterSolutionDropdown = false;
        $this->resetPage();
    }

    protected function syncFilterStatusSearchLabel(): void
    {
        $this->filterStatusSearch = $this->selectedFilterStatusLabel ?? '';
    }

    protected function syncFilterSolutionSearchLabel(): void
    {
        $selected = $this->selectedFilterSolution;
        $this->filterSolutionSearch = $selected ? (string) $selected->name : '';
    }

    public function getPreparationsProperty()
    {
        return SolutionPreparation::query()
            ->with(['solution', 'preparer'])
            ->when($this->search, function ($q) {
                $this->applyCaseInsensitiveSearch($q, ['preparation_number', 'batch_number'], (string) $this->search);
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->solutionFilter, fn ($q) => $q->where('solution_id', $this->solutionFilter))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function getSolutionsProperty()
    {
        return LabSubCategory::where('active', 1)->with('reportingUnit', 'alternativeSolution')->orderBy('name')->get();
    }

    public function getReportingUnitsProperty()
    {
        return ReportingUnit::where('active', 1)->orderBy('name')->get();
    }

    public function getSelectedSolutionProperty(): ?LabSubCategory
    {
        $id = $this->form['solution_id'] ?? null;
        if (! $id) {
            return null;
        }

        return LabSubCategory::with('alternativeSolution')->find($id);
    }

    public function getSelectedReportingUnitProperty(): ?ReportingUnit
    {
        $id = $this->form['uom_id'] ?? null;
        if (! $id) {
            return null;
        }

        return $this->reportingUnits->first(fn ($unit) => (string) $unit->id === (string) $id);
    }

    public function getFilteredSolutionsProperty(): Collection
    {
        return $this->filterSelectCollection(
            $this->solutions,
            $this->solutionSearch,
            $this->form['solution_id'] ?? null,
            ['name']
        );
    }

    public function getFilteredReportingUnitsProperty(): Collection
    {
        return $this->filterSelectCollection(
            $this->reportingUnits,
            $this->uomSearch,
            $this->form['uom_id'] ?? null,
            ['name']
        );
    }

    public function openSolutionDropdown(): void
    {
        if ($this->isEditing) {
            return;
        }

        $this->showSolutionDropdown = true;
    }

    public function closeSolutionDropdown(): void
    {
        $this->showSolutionDropdown = false;
    }

    public function openUomDropdown(): void
    {
        $this->showUomDropdown = true;
    }

    public function closeUomDropdown(): void
    {
        $this->showUomDropdown = false;
    }

    public function selectSolution(string $solutionId): void
    {
        if ($this->isEditing) {
            return;
        }

        $this->form['solution_id'] = $solutionId;
        $this->updatedFormSolutionId($solutionId);
        $this->syncSolutionSearchLabel();
        $this->showSolutionDropdown = false;
    }

    public function clearSolution(): void
    {
        if ($this->isEditing) {
            return;
        }

        $this->form['solution_id'] = null;
        $this->solutionSearch = '';
        $this->showSolutionDropdown = false;
    }

    public function selectUom(string $uomId): void
    {
        $this->form['uom_id'] = $uomId;
        $this->syncUomSearchLabel();
        $this->showUomDropdown = false;
    }

    public function clearUom(): void
    {
        $this->form['uom_id'] = null;
        $this->uomSearch = '';
        $this->showUomDropdown = false;
    }

    public function getIsEditingProperty(): bool
    {
        return $this->editingPreparationId !== null;
    }

    public function getDeletingPreparationProperty(): ?SolutionPreparation
    {
        if (! $this->deletingPreparationId) {
            return null;
        }

        return SolutionPreparation::query()
            ->with([
                'solution.reportingUnit',
                'steps' => fn ($q) => $q->with(['ingredient.reagent', 'ingredient.unitMeasure', 'controls'])->orderBy('step_number'),
            ])
            ->find($this->deletingPreparationId);
    }

    public function showNewPreparationModal(): void
    {
        $this->editingPreparationId = null;
        $this->resetCreateForm();

        if ($this->solutionFilter) {
            $this->form['solution_id'] = $this->solutionFilter;
            $this->updatedFormSolutionId($this->solutionFilter);
            $this->syncSolutionSearchLabel();
        }

        $this->showCreateModal = true;
        $this->dispatch('spm-modal-opened');
    }

    public function showEditPreparationModal(string $preparationId): void
    {
        $preparation = SolutionPreparation::with('solution')->find($preparationId);

        if (! $preparation) {
            $this->message = 'Preparation not found.';
            $this->messageType = 'danger';

            return;
        }

        if (in_array($preparation->status, ['completed', 'cancelled'], true)) {
            $this->message = 'This preparation cannot be edited.';
            $this->messageType = 'danger';

            return;
        }

        $this->editingPreparationId = $preparation->id;
        $this->form = [
            'solution_id' => $preparation->solution_id,
            'quantity_prepared' => (string) $preparation->quantity_prepared,
            'uom_id' => $preparation->uom_id,
            'batch_number' => $preparation->batch_number ?? '',
            'expiry_date' => $preparation->expiry_date?->format('Y-m-d') ?? '',
            'is_new_batch' => (bool) $preparation->is_new_batch,
            'notes' => $preparation->notes ?? '',
            'create_with_alternative' => false,
        ];
        $this->syncSolutionSearchLabel();
        $this->syncUomSearchLabel();
        $this->showSolutionDropdown = false;
        $this->showUomDropdown = false;
        $this->resetValidation();
        $this->showCreateModal = true;
        $this->dispatch('spm-modal-opened');
    }

    public function showDeletePreparationModal(string $preparationId): void
    {
        $preparation = SolutionPreparation::find($preparationId);

        if (! $preparation) {
            $this->message = 'Preparation not found.';
            $this->messageType = 'danger';

            return;
        }

        if ($preparation->status === 'completed') {
            $this->message = 'Cannot delete a completed preparation.';
            $this->messageType = 'danger';

            return;
        }

        $this->deletingPreparationId = $preparation->id;
        $this->showDeleteModal = true;
        $this->dispatch('spm-modal-opened');
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingPreparationId = null;
        $this->dispatch('spm-modal-closed');
    }

    public function confirmDeletePreparation(PreparationRunService $runService): void
    {
        $preparation = $this->deletingPreparation;

        if (! $preparation) {
            $this->closeDeleteModal();

            return;
        }

        try {
            $runService->deletePreparation($preparation);
            $this->closeDeleteModal();
            $this->message = 'Preparation deleted.';
            $this->messageType = 'success';
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first() ?? $e->getMessage();
            $this->messageType = 'danger';
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'danger';
            $this->closeDeleteModal();
        }
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->editingPreparationId = null;
        $this->resetCreateForm();
        $this->dispatch('spm-modal-closed');
    }

    public function updatedFormSolutionId($value): void
    {
        $solution = LabSubCategory::find($value);
        if ($solution?->reporting_unit) {
            $this->form['uom_id'] = $solution->reporting_unit;
            $this->syncUomSearchLabel();
        }
    }

    public function savePreparation(PreparationRunService $runService): void
    {
        $this->validate([
            'form.solution_id' => 'required|uuid|exists:lab_sub_category,id',
            'form.quantity_prepared' => 'required|numeric|min:0.0001',
            'form.uom_id' => 'required|uuid|exists:reporting_units,id',
            'form.batch_number' => 'nullable|string|max:255',
            'form.expiry_date' => 'nullable|date|after_or_equal:today',
        ]);

        if ($this->form['is_new_batch'] && empty($this->form['batch_number'])) {
            $this->addError('form.batch_number', 'Batch number is required for a new batch.');

            return;
        }

        if ($this->form['is_new_batch'] && empty($this->form['expiry_date'])) {
            $this->addError('form.expiry_date', 'Expiry date is required for a new batch.');

            return;
        }

        try {
            $data = [
                'quantity_prepared' => $this->form['quantity_prepared'],
                'uom_id' => $this->form['uom_id'],
                'batch_number' => $this->form['batch_number'] ?: null,
                'expiry_date' => $this->form['is_new_batch'] ? ($this->form['expiry_date'] ?: null) : null,
                'is_new_batch' => (bool) $this->form['is_new_batch'],
                'notes' => $this->form['notes'],
            ];

            if ($this->isEditing) {
                $preparation = SolutionPreparation::find($this->editingPreparationId);

                if (! $preparation) {
                    $this->message = 'Preparation not found.';
                    $this->messageType = 'danger';

                    return;
                }

                $runService->updatePreparationRecord($preparation, $data);
                $this->closeCreateModal();
                $this->message = 'Preparation updated.';
                $this->messageType = 'success';

                return;
            }

            $data['solution_id'] = $this->form['solution_id'];

            $preparation = $runService->createWithAlternative($data, (bool) $this->form['create_with_alternative']);

            $this->redirect(route('solutions-preparation-show', $preparation->id), navigate: true);
        } catch (ValidationException $e) {
            $this->message = collect($e->errors())->flatten()->first() ?? $e->getMessage();
            $this->messageType = 'danger';
        } catch (\Exception $e) {
            $this->message = $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    protected function resetCreateForm(): void
    {
        $this->form = [
            'solution_id' => null,
            'quantity_prepared' => '',
            'uom_id' => null,
            'batch_number' => '',
            'expiry_date' => '',
            'is_new_batch' => false,
            'notes' => '',
            'create_with_alternative' => false,
        ];
        $this->solutionSearch = '';
        $this->uomSearch = '';
        $this->showSolutionDropdown = false;
        $this->showUomDropdown = false;
        $this->resetValidation();
    }

    protected function syncSolutionSearchLabel(): void
    {
        $this->solutionSearch = $this->selectedSolution ? (string) $this->selectedSolution->name : '';
    }

    protected function syncUomSearchLabel(): void
    {
        $this->uomSearch = $this->selectedReportingUnit ? (string) $this->selectedReportingUnit->name : '';
    }

    /**
     * @param  array<int, string>  $searchFields
     */
    protected function filterSelectCollection($collection, string $search, mixed $selectedId, array $searchFields = ['name']): Collection
    {
        $needle = trim(strtolower($search));
        $selectedId = (string) ($selectedId ?? '');

        return collect($collection)
            ->filter(function ($item) use ($needle, $selectedId, $searchFields) {
                if ((string) $item->id === $selectedId) {
                    return false;
                }

                if ($needle === '') {
                    return true;
                }

                foreach ($searchFields as $field) {
                    $value = strtolower((string) ($item->{$field} ?? ''));

                    if ($value !== '' && str_contains($value, $needle)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }

    public function render()
    {
        return view('livewire.lab.solution-preparation-manager');
    }
}
