<?php

namespace App\Livewire\Mas;

use App\Services\AI\Repository\ReportingMartDashboardService;
use Illuminate\Support\Facades\DB;

class LabGeneral extends BaseMasPage
{
    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function refresh(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $service  = app(ReportingMartDashboardService::class);
        $labBoard = $service->getLabTatBoard();
        
        $topClients = DB::table('crm_customers')
            ->join('sample_headers', 'sample_headers.crm_customer_id', '=', 'crm_customers.id')
            ->select('crm_customers.name', DB::raw('count(sample_headers.id) as total'))
            ->where('sample_headers.isactive', 1)
            ->groupBy('crm_customers.id', 'crm_customers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $this->stats = [
            'sample_type_distribution' => $labBoard['sample_type_distribution'] ?? [],
            'top_clients'              => $topClients,
            'geographic_data'          => $service->getLabGeographicData(),
            'monthly_trends'           => $service->getLabMonthlyTrends(),
            'charts'                   => [
                'type_labels'    => $labBoard['charts']['type_labels'] ?? [],
                'type_counts'    => $labBoard['charts']['type_counts'] ?? [],
                'client_labels'  => $topClients->pluck('name')->all(),
                'client_counts'  => $topClients->pluck('total')->all(),
            ],
            'summary'                  => $labBoard['summary'] ?? [],
        ];
    }

    public function render()
    {
        return view('livewire.mas.lab-general', ['stats' => $this->stats]);
    }
}
