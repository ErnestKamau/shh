<?php

namespace App\Livewire\Billing;

use App\TaxRegime;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class TaxRegimeManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties
    public $search = '';
    public $statusFilter = '1'; // Default to active
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    // Modal and form properties
    public $showTaxModal = false;
    public $editingTax = false;
    public $taxForm = [];

    // Message properties
    public $message = '';
    public $messageType = 'success';

    protected function rules(): array
    {
        $rules = [
            'taxForm.value' => 'required|numeric|min:0|max:100',
            'taxForm.active' => 'boolean',
        ];

        return $rules;
    }

    public function mount(): void
    {
        $this->resetFilters();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function getTaxRegimesProperty()
    {
        $query = TaxRegime::query()->with('registeredBy');

        // Search filter
        if ($this->search) {
            $query->where('value', 'like', '%' . $this->search . '%');
        }

        // Status filter
        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    public function showCreateTaxModal(): void
    {
        $this->resetTaxForm();
        $this->editingTax = false;
        $this->showTaxModal = true;
    }

    public function showEditTaxModal(string $taxId): void
    {
        $tax = TaxRegime::findOrFail($taxId);
        
        $this->taxForm = [
            'id' => $tax->id,
            'value' => $tax->value,
            'active' => $tax->active,
        ];

        $this->editingTax = true;
        $this->showTaxModal = true;
    }

    public function saveTax(): void
    {
        $this->validate();

        try {
            if ($this->editingTax) {
                $tax = TaxRegime::findOrFail($this->taxForm['id']);
                
                // If setting as active, deactivate all others and set end_date
                if ($this->taxForm['active']) {
                    TaxRegime::where('active', 1)->update([
                        'active' => 0,
                        'end_date' => now()
                    ]);
                }
                
                $tax->update([
                    'value' => $this->taxForm['value'],
                    'active' => $this->taxForm['active'],
                    'end_date' => $this->taxForm['active'] ? null : $tax->end_date,
                ]);
                
                $this->message = 'Tax regime updated successfully!';
            } else {
                // If setting as active, deactivate all others and set end_date
                if ($this->taxForm['active']) {
                    TaxRegime::where('active', 1)->update([
                        'active' => 0,
                        'end_date' => now()
                    ]);
                }
                
                TaxRegime::create([
                    'registered_by' => Auth::id(),
                    'value' => $this->taxForm['value'],
                    'active' => $this->taxForm['active'],
                    'end_date' => null,
                ]);
                
                $this->message = 'Tax regime created successfully!';
            }

            $this->messageType = 'success';
            $this->showTaxModal = false;
            $this->resetTaxForm();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->message = 'Error saving tax regime: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function toggleStatus(string $taxId): void
    {
        try {
            $tax = TaxRegime::findOrFail($taxId);
            
            // If activating this tax, deactivate all others
            if (!$tax->active) {
                TaxRegime::where('active', 1)->update([
                    'active' => 0,
                    'end_date' => now()
                ]);
                $tax->active = true;
                $tax->end_date = null;
            } else {
                $tax->active = false;
                $tax->end_date = now();
            }
            
            $tax->save();

            $this->message = 'Tax regime status updated successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error updating tax regime status: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function resetTaxForm(): void
    {
        $this->taxForm = [
            'value' => 0,
            'active' => false,
        ];
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showTaxModal = false;
        $this->resetTaxForm();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '1';
        $this->perPage = 25;
        $this->resetPage();
    }

    public function clearMessage(): void
    {
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.billing.tax-regime-manager', [
            'taxRegimes' => $this->taxRegimes,
        ]);
    }
}
