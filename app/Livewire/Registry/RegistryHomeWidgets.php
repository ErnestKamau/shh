<?php

namespace App\Livewire\Registry;

use App\Models\Registry\RegistryRequest;
use App\Services\Registry\RegistryDashboardService;
use Livewire\Component;

class RegistryHomeWidgets extends Component
{
    public array $kpis = [];

    public function mount(RegistryDashboardService $dashboardService): void
    {
        $data = $dashboardService->getDashboardData();
        $this->kpis = $data['kpis'] ?? [];
    }

    public function render()
    {
        $recent = RegistryRequest::query()
            ->forCompany()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'reference_no', 'subject', 'status', 'created_at']);

        return view('livewire.registry.registry-home-widgets', [
            'recent' => $recent,
        ]);
    }
}
