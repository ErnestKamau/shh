<?php

namespace App\Livewire\Mas;

use App\Services\Dashboards\LabGeneralDashboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LabGeneral extends BaseMasPage
{
    public array $stats = [];
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->setDefaultDateRange();
        $this->loadStats();
    }

    public function refresh(): void
    {
        $this->loadStats();
    }

    public function applyFilters(): void
    {
        $this->normalizeDateRange();
        $this->loadStats();
    }

    public function resetFilters(): void
    {
        $this->setDefaultDateRange();
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $generalService = app(LabGeneralDashboardService::class);
        $filters = $this->dateFilters();
        
        // Build sample type distribution directly from DB — getLabTatBoard() has no 'charts' key
        $typeDistribution = DB::table('sample_headers')
            ->join('sample_types', 'sample_types.id', '=', 'sample_headers.sample_type_id')
            ->where('sample_headers.isactive', 1)
            ->whereNotNull('sample_headers.sample_type_id')
            ->whereBetween('sample_headers.receipt_date', [
                $filters['start_date'] . ' 00:00:00',
                $filters['end_date'] . ' 23:59:59',
            ])
            ->select('sample_types.name', DB::raw('COUNT(sample_headers.id) as total'))
            ->groupBy('sample_types.id', 'sample_types.name')
            ->orderByDesc('total')
            ->limit(7)
            ->get();

        $topClients = $generalService->getTopClientsData(8, $filters);
        $stageSummary = $generalService->getWorkflowStageSummary($filters);

        $this->stats = [
            'available'                => true,
            'message'                  => null,
            'sample_type_distribution' => $typeDistribution->map(fn ($row) => [
                'name' => $row->name,
                'total' => (int) $row->total,
            ])->all(),
            'geographic_data'          => $generalService->getLabGeographicData(null, $filters),
            'monthly_trends'           => $generalService->getLabMonthlyTrends(null, $filters),
            'top_clients'              => $topClients,
            'testing_matrix'           => $generalService->getTestingMatrixData(null, $filters),
            'charts'                   => [
                'type_labels'    => $typeDistribution->pluck('name')->all(),
                'type_counts'    => $typeDistribution->pluck('total')->all(),
                'client_labels'  => $topClients->pluck('name')->all(),
                'client_counts'  => $topClients->pluck('total')->all(),
            ],
            'summary'                  => $generalService->getSummary($filters),
            'stage_summary'            => $stageSummary,
            'stage_counts'             => $generalService->getWorkflowStageCounts($filters),
            'filters'                  => $filters,
        ];

        $this->dispatch('mas-lab-general-updated', [
            'charts' => $this->stats['charts'],
            'monthly_trends' => $this->stats['monthly_trends'],
            'geographic_data' => $this->stats['geographic_data'],
        ]);
    }

    public function render()
    {
        return view('livewire.mas.lab-general', ['stats' => $this->stats]);
    }

    private function setDefaultDateRange(): void
    {
        $this->startDate = now()->subMonthsNoOverflow(11)->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    private function normalizeDateRange(): void
    {
        $this->startDate = $this->startDate ?: now()->subMonthsNoOverflow(11)->startOfMonth()->toDateString();
        $this->endDate = $this->endDate ?: now()->toDateString();

        if (Carbon::parse($this->startDate)->gt(Carbon::parse($this->endDate))) {
            [$this->startDate, $this->endDate] = [$this->endDate, $this->startDate];
        }
    }

    private function dateFilters(): array
    {
        $this->normalizeDateRange();

        return [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }
}
