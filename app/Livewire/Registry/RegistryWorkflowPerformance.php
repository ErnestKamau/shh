<?php

namespace App\Livewire\Registry;

use App\Models\Registry\RegistryRequestStatusLog;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RegistryWorkflowPerformance extends Component
{
    public function render()
    {
        $stageMetrics = RegistryRequestStatusLog::query()
            ->select('stage_code', DB::raw('AVG(duration_seconds) as avg_seconds'), DB::raw('COUNT(*) as total'))
            ->whereNotNull('duration_seconds')
            ->groupBy('stage_code')
            ->orderByDesc('avg_seconds')
            ->limit(8)
            ->get();

        return view('livewire.registry.registry-workflow-performance', compact('stageMetrics'));
    }
}
