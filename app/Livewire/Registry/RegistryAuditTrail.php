<?php

namespace App\Livewire\Registry;

use App\Models\Registry\RegistryRequestAction;
use Livewire\Component;
use Livewire\WithPagination;

class RegistryAuditTrail extends Component
{
    use WithPagination;

    public ?string $requestId = null;

    public function render()
    {
        $query = RegistryRequestAction::query()
            ->with(['request', 'performer'])
            ->orderByDesc('performed_at');

        if ($this->requestId !== null) {
            $query->where('registry_request_id', $this->requestId);
        }

        $actions = $query->paginate(20);

        return view('livewire.registry.registry-audit-trail', compact('actions'));
    }
}
