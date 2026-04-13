<?php

namespace App\Livewire\Crm\Customer;

use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\Attributes\On;

class CustomerShow extends BaseCrmComponent
{
    public int $customerId;

    public $customer;

    public $activeTab = 'details';

    /** @var SystemConfiguration|null Used to conditionally show tabs (e.g. Orders, Quotations). */
    public $isQplus = null;

    protected $queryString = [
        'activeTab' => ['except' => 'details', 'as' => 'tab'],
    ];

    #[On('customer-updated')]
    public function refreshCustomer(): void
    {
        $this->customer = CRMCustomer::with(['country'])->findOrFail($this->customerId);
    }

    public function mount(int $customerId): void
    {
        $this->initialize();
        $this->checkPermission('CRM.components.Customer-List.View');

        $this->customerId = $customerId;
        $this->customer = CRMCustomer::with(['country'])->findOrFail($customerId);
        $this->isQplus = SystemConfiguration::where('key', 'is_qplus')->first();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getBreadcrumbItemsProperty()
    {
        return [
            [
                'link' => route('customers-list'),
                'name' => 'CRM',
                'icon' => null
            ],
            [
                'link' => route('customers-list'),
                'name' => 'Customer List',
                'icon' => null
            ],
            [
                'link' => '#',
                'name' => $this->customer->name,
                'icon' => null
            ]
        ];
    }

    public function render()
    {
        return view('livewire.crm.customer.customer-show', [
            'customer' => $this->customer,
            'customerId' => $this->customerId,
        ]);
    }
}
