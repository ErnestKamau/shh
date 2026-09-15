<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use App\RequestEntity;
use App\Services\CRM\CRMCustomerService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;

class CompanyUnitsManager extends BaseCrmComponent
{
    public CRMCustomer $customer;

    public int $perPage = 15;

    protected $paginationTheme = 'bootstrap';

    public bool $showCreateModal = false;

    public ?string $editingUnitId = null;

    public string $name = '';

    public bool $active = true;

    public string $modalError = '';

    public bool $showLabelModal = false;

    public string $labelColumn = '';

    public string $labelValue = '';

    public function mount(string $customerId): void
    {
        $this->initialize();
        $this->customer = CRMCustomer::query()->findOrFail($customerId);
    }

    public function getUnitsProperty(): LengthAwarePaginator
    {
        return CRMCompanyUnit::query()
            ->where('crm_customer_id', $this->customer->id)
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function getIsQplusProperty(): bool
    {
        return SystemConfiguration::query()->where('key', 'is_qplus')->exists();
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'active', 'editingUnitId', 'modalError']);
        $this->active = true;
        $this->showCreateModal = true;
    }

    public function editUnit(string $unitId): void
    {
        $unit = CRMCompanyUnit::query()
            ->where('crm_customer_id', $this->customer->id)
            ->findOrFail($unitId);

        $this->editingUnitId = $unitId;
        $this->name = (string) $unit->name;
        $this->active = (string) $unit->active === '1' || (bool) $unit->active;
        $this->modalError = '';
        $this->showCreateModal = true;
    }

    public function saveUnit(): void
    {
        $this->modalError = '';
        $this->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        $isEdit = $this->editingUnitId !== null;

        if ($isEdit) {
            $this->checkPermission('crm.company-units.edit');
            $unit = CRMCompanyUnit::query()
                ->where('crm_customer_id', $this->customer->id)
                ->findOrFail($this->editingUnitId);
        } else {
            $this->checkPermission('crm.company-units.add');
            $unit = new CRMCompanyUnit();
            $unit->crm_customer_id = $this->customer->id;
            $unit->company_id = $this->getUserCompany();
        }

        $unit->name = $this->name;
        $unit->active = $this->active ? 1 : 0;
        $unit->save();

        $this->showCreateModal = false;
        $this->reset(['editingUnitId', 'name', 'active', 'modalError']);
        $this->showSuccess($isEdit ? 'Company unit updated.' : 'Company unit added.');
    }

    public function deleteUnit(string $unitId): void
    {
        if ($this->isQplus) {
            $this->showError('Company unit deletion is not allowed in QPLUS.');

            return;
        }

        if (RequestEntity::query()->where('client_unit_id', $unitId)->exists()) {
            $this->showError('Company unit has issuances and cannot be deleted.');

            return;
        }

        $this->checkPermission('crm.company-units.edit');

        $unit = CRMCompanyUnit::query()
            ->where('crm_customer_id', $this->customer->id)
            ->findOrFail($unitId);

        $unit->active = 0;
        $unit->save();
        $unit->sample_points()->update(['active' => 0]);

        $this->showSuccess('Company unit deleted.');
    }

    public function openLabelModal(string $column): void
    {
        $allowed = ['unit_configurable_name', 'sample_point_configurable_name', 'product_configurable_name'];
        if (! in_array($column, $allowed, true)) {
            return;
        }

        $this->labelColumn = $column;
        $this->labelValue = (string) ($this->customer->{$column} ?? '');
        $this->showLabelModal = true;
    }

    public function saveLabel(): void
    {
        $this->validate([
            'labelValue' => 'required|string|max:100',
        ]);

        app(CRMCustomerService::class)->updateLabel(
            (string) $this->customer->id,
            $this->labelColumn,
            $this->labelValue,
        );

        $this->customer->refresh();
        $this->showLabelModal = false;
        $this->reset(['labelColumn', 'labelValue']);
        $this->showSuccess('Label updated.');
        $this->dispatch('customer-updated');
    }

    public function render(): View
    {
        return view('livewire.crm.customer.tabs.company-units-manager', [
            'customer' => $this->customer,
        ]);
    }
}
