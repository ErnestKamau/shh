<?php

namespace App\Livewire\Lab;

use Livewire\Component;
use Livewire\WithPagination;
use App\LabSubCategory;
use App\LabStockMovement;
use App\ReportingUnit;
use App\UnitOfMeasureConversion;
use Illuminate\Support\Facades\DB;

class SolutionsMovementTracker extends Component
{
    use WithPagination;

    public $subCategoryId;
    public $subCategory;
    
    // Movement Form
    public $showMovementModal = false;
    public $movementForm = [
        'stock_type' => '',
        'amount' => '',
        'uom_id' => null,
        'description' => '',
    ];

    // UI State
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    // Supporting Data
    public $reportingUnits = [];

    protected $rules = [
        'movementForm.stock_type' => 'required|in:stock_in,stock_out',
        'movementForm.amount' => 'required|numeric|min:0.01',
        'movementForm.uom_id' => 'required|integer|exists:reporting_units,id',
        'movementForm.description' => 'required|string',
    ];

    protected $messages = [
        'movementForm.stock_type.required' => 'Stock type selection is required.',
        'movementForm.amount.required' => 'Amount is required.',
        'movementForm.amount.min' => 'Amount must be greater than 0.',
        'movementForm.uom_id.required' => 'Unit of measure is required.',
        'movementForm.description.required' => 'Description is required.',
    ];

    public function mount($subCategoryId): void
    {
        $this->subCategoryId = $subCategoryId;
        $this->loadSubCategory();
        $this->loadReportingUnits();
    }

    public function loadSubCategory(): void
    {
        $this->subCategory = LabSubCategory::with(['category', 'reportingUnit'])
            ->findOrFail($this->subCategoryId);
    }

    public function loadReportingUnits(): void
    {
        $this->reportingUnits = ReportingUnit::all();
    }

    public function getStockMovementsProperty()
    {
        return LabStockMovement::where('lab_sub_category_id', $this->subCategoryId)
            ->with(['uom', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function showAddMovementModal(): void
    {
        $this->resetMovementForm();
        $this->showMovementModal = true;
        $this->dispatch('movement-modal-opened');
    }

    public function saveMovement(): void
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $movement = new LabStockMovement();
                $movement->description = $this->movementForm['description'];
                $movement->lab_sub_category_id = $this->subCategoryId;
                $movement->stock_type = $this->movementForm['stock_type'];
                $movement->uom_id = $this->movementForm['uom_id'];
                $movement->created_by = auth()->user()->id;

                $convertedAmount = $this->convertAmount(
                    $this->movementForm['amount'],
                    $this->movementForm['uom_id'],
                    $this->subCategory->reporting_unit
                );

                if ($this->movementForm['stock_type'] == 'stock_in') {
                    $movement->stock_in = $this->movementForm['amount'];
                    $movement->stock_out = 0;
                    $this->subCategory->stock = ($this->subCategory->stock ?? 0) + $convertedAmount;
                } else {
                    $movement->stock_in = 0;
                    $movement->stock_out = $this->movementForm['amount'];
                    
                    // Check if stock out exceeds available stock
                    if (($this->subCategory->stock ?? 0) < $convertedAmount) {
                        throw new \Exception('Stock out amount exceeds available stock');
                    }
                    
                    $this->subCategory->stock = ($this->subCategory->stock ?? 0) - $convertedAmount;
                }

                // Prevent negative stock
                if ($this->subCategory->stock < 0) {
                    throw new \Exception('Your stock out amount is higher than the available quantity');
                }

                $this->subCategory->save();
                $movement->save();

                $this->message = 'Stock movement added successfully!';
                $this->messageType = 'success';
                $this->showMovementModal = false;
                $this->dispatch('movement-modal-closed');
                $this->resetMovementForm();
                $this->loadSubCategory();
            });
        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    protected function convertAmount($amount, $fromUomId, $toUomId): float
    {
        // If same UOM, no conversion needed
        if ($fromUomId == $toUomId) {
            return floatval($amount);
        }

        // Try direct conversion
        $conversion = UnitOfMeasureConversion::where('uom1', $toUomId)
            ->where('uom2', $fromUomId)
            ->first();

        if ($conversion) {
            return floatval($amount) / floatval($conversion->conversion);
        }

        // Try inverse conversion
        $inverseConversion = UnitOfMeasureConversion::where('uom1', $fromUomId)
            ->where('uom2', $toUomId)
            ->first();

        if ($inverseConversion) {
            return floatval($amount) * floatval($inverseConversion->conversion);
        }

        // No conversion found, get unit names for error message
        $fromUnit = ReportingUnit::find($fromUomId);
        $toUnit = ReportingUnit::find($toUomId);
        
        throw new \Exception('Kindly set the UOM conversion units between ' . ($toUnit->name ?? 'Unknown') . ' and ' . ($fromUnit->name ?? 'Unknown'));
    }

    public function resetMovementForm(): void
    {
        $this->movementForm = [
            'stock_type' => '',
            'amount' => '',
            'uom_id' => null,
            'description' => '',
        ];
        $this->resetValidation();
    }

    public function closeMovementModal(): void
    {
        $this->showMovementModal = false;
        $this->resetMovementForm();
        $this->dispatch('movement-modal-closed');
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.lab.solutions-movement-tracker');
    }
}
