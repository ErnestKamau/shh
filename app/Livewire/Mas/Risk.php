<?php

namespace App\Livewire\Mas;

use App\Models\RiskManagement\Risk as RiskModel;
use Illuminate\Support\Facades\DB;

class Risk extends BaseMasPage
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
        $activeCount = RiskModel::where('workflow_step', '<', 8)->count();
        $byLevel = RiskModel::select('risk_level', DB::raw('count(*) as count'))
            ->where('workflow_step', '<', 8)
            ->groupBy('risk_level')
            ->orderByDesc('count')
            ->get();

        $this->stats = [
            'available'        => $activeCount > 0,
            'message'          => $activeCount > 0 ? null : __('mas/risk.data_unavailable'),
            'active_count'     => $activeCount,
            'critical_count'   => RiskModel::where('risk_level', 'Critical')->where('workflow_step', '<', 8)->count(),
            'requiring_review' => RiskModel::where('next_review_date', '<=', now())->where('workflow_step', '<', 8)->count(),
            'by_level'         => $byLevel,
            'data_note'        => __('mas/risk.data_note'),
        ];
    }

    public function render()
    {
        return view('livewire.mas.risk', ['stats' => $this->stats]);
    }
}
