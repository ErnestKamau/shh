<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanySection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompanySectionsManager extends BaseCrmComponent
{
    public CRMCustomer $customer;

    public int $perPage = 15;

    protected $paginationTheme = 'bootstrap';

    public bool $showCreateModal = false;

    public ?string $editingSectionId = null;

    public string $name = '';

    public bool $active = true;

    public string $modalError = '';

    public function mount(CRMCustomer $customer): void
    {
        $this->customer = $customer;
    }

    public function getSectionsProperty(): LengthAwarePaginator
    {
        return CRMCompanySection::where('crm_customer_id', $this->customer->id)
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'active', 'editingSectionId', 'modalError']);
        $this->active = true;
        $this->showCreateModal = true;
    }

    public function editSection(string $sectionId): void
    {
        $section = CRMCompanySection::findOrFail($sectionId);
        $this->editingSectionId = $sectionId;
        $this->name = $section->name;
        $this->active = (bool) $section->active;
        $this->modalError = '';
        $this->showCreateModal = true;
    }

    public function createSection(): void
    {
        $this->modalError = '';
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $section = new CRMCompanySection;
        $section->name = $this->name;
        $section->company_id = getUserCompany();
        $section->crm_customer_id = $this->customer->id;
        $section->active = $this->active ? 1 : 0;
        $section->save();

        $this->showCreateModal = false;
        $this->showSuccess('Company section added.');
    }

    public function updateSection(): void
    {
        $this->modalError = '';
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $section = CRMCompanySection::findOrFail($this->editingSectionId);
        $section->name = $this->name;
        $section->company_id = getUserCompany();
        $section->crm_customer_id = $this->customer->id;
        $section->active = $this->active ? 1 : 0;
        $section->save();

        $this->showCreateModal = false;
        $this->reset(['editingSectionId', 'name', 'active']);
        $this->showSuccess('Company section updated.');
    }

    public function saveSection(): void
    {
        if ($this->editingSectionId) {
            $this->updateSection();
        } else {
            $this->createSection();
        }
    }

    public function deleteSection(string $sectionId): void
    {
        CRMCompanySection::findOrFail($sectionId)->delete();
        $this->showSuccess('Company section deleted.');
    }

    public function close(): void
    {
        $this->showCreateModal = false;
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.company-sections-manager', [
            'customer' => $this->customer,
        ]);
    }
}
