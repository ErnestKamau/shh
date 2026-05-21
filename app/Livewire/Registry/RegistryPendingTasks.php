<?php

namespace App\Livewire\Registry;

use App\Models\Registry\RegistryRequest;
use Livewire\Component;

class RegistryPendingTasks extends Component
{
    public function render()
    {
        $pending = RegistryRequest::query()
            ->forCompany()
            ->whereIn('status', [RegistryRequest::STATUS_OPEN, RegistryRequest::STATUS_PENDING_APPROVAL])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('livewire.registry.registry-pending-tasks', compact('pending'));
    }
}
