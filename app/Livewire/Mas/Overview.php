<?php

namespace App\Livewire\Mas;

use App\Models\RiskManagement\Risk;
use App\SampleHeader;
use App\Services\AI\PerformanceDashboardService;
use App\Services\AI\Repository\ReportingMartDashboardService;
use App\Services\Documents\Dashboards\EquipmentReliabilityDashboardService;
use Illuminate\Support\Facades\DB;

class Overview extends BaseMasPage
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
        $reportingService = app(ReportingMartDashboardService::class);
        $perfService      = app(PerformanceDashboardService::class);
        $equipService     = app(EquipmentReliabilityDashboardService::class);

        $labTotal        = SampleHeader::where('status', '!=', 'Finished Sample')->count();
        $inventoryStats  = $reportingService->getInventoryRiskBoard();
        $inventoryAlerts = $inventoryStats['summary']['items_below_minimum'] ?? 0;
        $billingTotal    = DB::table('customer_invoice')->sum('total') ?? 0;
        $riskHigh        = Risk::where('risk_level', 'Critical')->where('workflow_step', '<', 8)->count();
        $aiHealth        = $perfService->getDashboardOverview()['kpis']['health_score'] ?? 0;
        $equipMetrics    = $equipService->getMetrics();
        $staffCount      = DB::table('users')->where('active', 1)->count();
        $qcPending       = DB::table('qc_results')->whereNotIn('status_code', ['PASSED', 'FAILED'])->count();
        $auditRecent     = DB::table('audits')->where('created_at', '>=', now()->subDays(7))->count();

        $sampleTypesCount = DB::table('sample_types')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('sample_headers')
                    ->whereColumn('sample_headers.sample_type_id', 'sample_types.id')
                    ->where('sample_headers.isactive', 1);
            })
            ->count();

        $this->stats = [
            'total_samples'     => $labTotal,
            'sample_types_count'=> $sampleTypesCount,
            'inventory_alerts'  => $inventoryAlerts,
            'unpaid_billing'    => number_format($billingTotal / 1000, 1) . 'k',
            'high_risks'        => $riskHigh,
            'ai_health'         => $aiHealth,
            'equip_overdue'     => $equipMetrics['calibrationOverdue'] ?? 0,
            'staff_count'       => $staffCount,
            'qc_pending'        => $qcPending,
            'audit_alerts'      => $auditRecent,
        ];
    }

    public function render()
    {
        return view('livewire.mas.overview', ['stats' => $this->stats]);
    }
}
