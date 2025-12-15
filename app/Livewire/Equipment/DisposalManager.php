<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Equipments\Equipment;
use Illuminate\Support\Facades\Auth;

class DisposalManager extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    
    // Tab state
    public $activeTab = 'pending';
    public $statusFilter = 'pending';
    
    // Pagination
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    // Preselected equipment for creating disposal request
    public $preselectedEquipmentId = null;

    // Messages
    public $message = '';
    public $messageType = 'success';

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'disposal-submitted' => '$refresh', 
        'disposal-saved' => '$refresh', 
        'disposal-executed' => '$refresh'
    ];

    public function mount(): void
    {
        // Check if equipment_id is passed in query string
        $this->preselectedEquipmentId = request()->query('equipment_id');
        
        // Auto-open disposal request form if equipment is preselected
        if ($this->preselectedEquipmentId) {
            $this->dispatch('open-disposal-form', equipmentId: (int)$this->preselectedEquipmentId);
        }
    }

    public function switchTab($tab): void
    {
        $this->activeTab = $tab;
        $this->statusFilter = $tab;
        $this->resetPage();
    }

    public function getDisposalsProperty()
    {
        $query = \App\Models\Equipments\EquipmentDisposal::with(['equipment', 'requester'])
            ->where('company_id', getUserCompany())
            ->orderBy('created_at', 'desc');

        // Filter by status based on active tab
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('equipment', function($eq) {
                    $eq->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('equipment_number', 'like', '%' . $this->search . '%');
                })
                ->orWhere('justification', 'like', '%' . $this->search . '%');
            });
        }

        // Date filter - From
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        // Date filter - To
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->paginate($this->perPage);
    }



    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }



    public function getPendingCountProperty()
    {
        return \App\Models\Equipments\EquipmentDisposal::where('company_id', getUserCompany())
            ->where('status', 'pending')
            ->count();
    }

    public function getApprovedCountProperty()
    {
        return \App\Models\Equipments\EquipmentDisposal::where('company_id', getUserCompany())
            ->where('status', 'approved')
            ->count();
    }

    public function getRejectedCountProperty()
    {
        return \App\Models\Equipments\EquipmentDisposal::where('company_id', getUserCompany())
            ->where('status', 'rejected')
            ->count();
    }

    public function getDraftCountProperty()
    {
        return \App\Models\Equipments\EquipmentDisposal::where('company_id', getUserCompany())
            ->where('status', 'draft')
            ->count();
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    public function render()
    {
        return view('livewire.equipment.disposal-manager', [
            'disposals' => $this->disposals,
        ]);
    }
}
