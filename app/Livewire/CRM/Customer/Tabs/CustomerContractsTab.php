<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CrmCustomerContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class CustomerContractsTab extends BaseCrmComponent
{
    use WithFileUploads;

    public CRMCustomer $customer;

    public int $perPage = 10;

    public string $valid_from = '';

    public string $valid_to = '';

    public $contractFile = null;

    public function mount(CRMCustomer $customer): void
    {
        $this->initialize();
        $this->customer = $customer->loadMissing('currentContract');

        if (! $this->customer->has_contract) {
            abort(403, 'This customer does not have a contract.');
        }
    }

    public function getContractsProperty()
    {
        return CrmCustomerContract::query()
            ->where('crm_customer_id', $this->customer->id)
            ->orderByDesc('is_current')
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function getCurrentContractProperty(): ?CrmCustomerContract
    {
        return $this->customer->currentContract
            ?? CrmCustomerContract::query()
                ->where('crm_customer_id', $this->customer->id)
                ->where('is_current', true)
                ->latest('created_at')
                ->first();
    }

    public function openNewContractModal(): void
    {
        $this->checkPermission('crm.customers.edit');

        $this->reset(['valid_from', 'valid_to', 'contractFile']);
        $this->resetValidation();
        $this->dispatch('show-customer-contract-modal');
    }

    public function saveContract(): void
    {
        $this->checkPermission('crm.customers.edit');

        $this->validate([
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'contractFile' => 'nullable|file|max:20480',
        ], [
            'valid_to.after_or_equal' => __('crm.contract_end_after_start'),
        ]);

        try {
            DB::beginTransaction();

            $this->customer->contracts()->where('is_current', true)->update(['is_current' => false]);

            $contract = new CrmCustomerContract();
            $contract->crm_customer_id = $this->customer->id;
            $contract->valid_from = $this->valid_from !== '' ? $this->valid_from : null;
            $contract->valid_to = $this->valid_to !== '' ? $this->valid_to : null;
            $contract->posted_by = Auth::user()->name ?? 'System';
            $contract->is_current = true;

            if ($this->contractFile) {
                $extension = strtolower((string) $this->contractFile->getClientOriginalExtension());
                $storedName = (string) Str::uuid() . ($extension !== '' ? '.' . $extension : '');
                $path = $this->contractFile->storeAs('crm-customer-contracts', $storedName, 'public');

                $contract->original_name = $this->contractFile->getClientOriginalName();
                $contract->file_path = '/storage/' . $path;
                $contract->file_extension = $extension !== '' ? $extension : null;
                $contract->mime_type = $this->contractFile->getMimeType();
                $contract->file_size = $this->contractFile->getSize();
            }

            $contract->save();

            $this->customer->has_contract = true;
            $this->customer->contract_valid_from = $contract->valid_from;
            $this->customer->contract_valid_to = $contract->valid_to;
            $this->customer->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->showError(__('crm.contract_save_failed') . ': ' . $e->getMessage());

            return;
        }

        $this->customer->refresh()->load('currentContract');
        $this->reset(['valid_from', 'valid_to', 'contractFile']);
        $this->resetValidation();

        $this->showSuccess(__('crm.contract_created_previous_invalidated'));
        $this->dispatch('close-customer-contract-modal');
        $this->dispatch('customer-updated');
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.customer-contracts-tab', [
            'contracts' => $this->contracts,
            'currentContract' => $this->currentContract,
        ]);
    }
}
