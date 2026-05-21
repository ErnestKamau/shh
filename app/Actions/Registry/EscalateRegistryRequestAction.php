<?php

namespace App\Actions\Registry;

use App\Models\Registry\RegistryRequest;
use App\Services\Registry\WorkflowEngineService;
use Illuminate\Support\Facades\Auth;

class EscalateRegistryRequestAction
{
    public function __construct(
        protected WorkflowEngineService $workflowEngineService,
    ) {
    }

    public function execute(RegistryRequest $request, ?string $comment = null): RegistryRequest
    {
        $this->workflowEngineService->recordAction(
            $request,
            'escalated',
            $request->current_stage,
            $request->current_stage,
            $comment ?? 'Request escalated',
            Auth::id()
        );

        $metadata = array_merge($request->metadata ?? [], ['escalated' => true, 'escalated_at' => now()->toIso8601String()]);
        $request->update(['metadata' => $metadata, 'priority' => 'high']);

        return $request->fresh();
    }
}
