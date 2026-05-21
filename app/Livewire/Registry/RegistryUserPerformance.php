<?php

namespace App\Livewire\Registry;

use App\Models\Registry\RegistryRequestAssignment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RegistryUserPerformance extends Component
{
    public function render()
    {
        $workload = RegistryRequestAssignment::query()
            ->select('assigned_to', DB::raw('COUNT(*) as total'))
            ->where('is_active', true)
            ->groupBy('assigned_to')
            ->with('assignee:id,name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return view('livewire.registry.registry-user-performance', compact('workload'));
    }
}
