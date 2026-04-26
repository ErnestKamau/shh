<?php

namespace App\Livewire\Mas;

use App\Models\RiskManagement\Risk;
use App\SampleHeader;
use App\Services\AI\PerformanceDashboardService;
use App\Services\Dashboards\InventoryDashboardService;
use App\Services\Documents\Dashboards\EquipmentReliabilityDashboardService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $inventoryService = app(InventoryDashboardService::class);
        $perfService      = app(PerformanceDashboardService::class);
        $equipService     = app(EquipmentReliabilityDashboardService::class);

        $labTotal        = SampleHeader::where('status', '!=', 'Finished Sample')->count();
        $inventoryStats  = $inventoryService->getInventoryRiskBoard();
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

    public function runEtl(): void
    {
        $processedModel = new \App\Models\QcModule\QCProcessedResults();
        $processedTable = $processedModel->getTable();
        $processedConnection = $processedModel->getConnectionName();

        if (!Schema::connection($processedConnection)->hasTable($processedTable)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => "ETL skipped: required table {$processedTable} is missing in the active database."
            ]);

            return;
        }

        // 1. Identify all QC Processed Results
        $processedRecords = \App\Models\QcModule\QCProcessedResults::all();

        foreach ($processedRecords as $up) {
            // 2. Fetch raw results for this analyte
            $raw_results = DB::table('qc_results')
                ->where('analyte_processed_id', $up->id)
                ->pluck('result')
                ->filter(fn($v) => is_numeric($v))
                ->map(fn($v) => (float)$v)
                ->values()
                ->toArray();

            if (count($raw_results) > 0) {
                sort($raw_results);
                $count = count($raw_results);
                $middle = (int) floor($count / 2);
                $median = $count % 2 ? $raw_results[$middle] : ($raw_results[$middle - 1] + $raw_results[$middle]) / 2;
                
                $deviations = array_map(fn($v) => abs($v - $median), $raw_results);
                sort($deviations);
                $mad = $count % 2 ? $deviations[$middle] : ($deviations[$middle - 1] + $deviations[$middle]) / 2;
                
                $rSD = $mad * 1.4826;
                $mean = array_sum($raw_results) / count($raw_results);
                $rCV = $median != 0 ? $rSD / $median : 0;

                $up->robust_standard_deviation = (float)$rSD;
                $up->robust_median = (float)$median;
                $up->robust_mean = (float)$mean;
                $up->robust_cv = (float)$rCV;
                $up->robust_cv_percentage = (float)($rCV * 100);
                $up->save();
            }
        }

        // 3. Mark all results as processed
        DB::table('qc_results')->update(['is_qc_processed' => 1]);

        // 4. Refresh stats
        $this->loadStats();

        // 5. Notify user
        $this->dispatch('notify', [
            'type' => 'success', 
            'message' => 'Analytics ETL executed successfully. All QC statistics have been recalculated.'
        ]);
    }

    public function render()
    {
        return view('livewire.mas.overview', ['stats' => $this->stats]);
    }
}
