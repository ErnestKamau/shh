<?php

namespace App\Livewire\Registry;

use App\Models\Registry\RegistryRequest;
use Livewire\Component;
use Livewire\WithPagination;

class RegistryApprovalQueue extends Component
{
    use WithPagination;

    public function render()
    {
        $requests = RegistryRequest::query()
            ->forCompany()
            ->where('status', RegistryRequest::STATUS_PENDING_APPROVAL)
            ->with(['category', 'assignee'])
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('livewire.registry.registry-approval-queue', compact('requests'));
    }
}
