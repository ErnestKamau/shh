<?php

namespace App\Actions\Registry;

use App\Events\Registry\RegistryRequestClosed;
use App\Models\Registry\RegistryRequest;
use App\Services\Registry\RegistryTATService;
use App\Services\Registry\WorkflowEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CloseRegistryRequestAction
{
    public function __construct(
        protected WorkflowEngineService $workflowEngineService,
        protected RegistryTATService $tatService,
    ) {
    }

    public function execute(RegistryRequest $request, ?string $comment = null): RegistryRequest
    {
        return DB::transaction(function () use ($request, $comment) {
            if ($request->current_stage !== null) {
                $this->tatService->exitStage($request, $request->current_stage);
            }

            $request->update([
                'status' => RegistryRequest::STATUS_CLOSED,
                'closed_at' => now(),
            ]);

            $this->workflowEngineService->recordAction(
                $request,
                'closed',
                $request->current_stage,
                null,
                $comment ?? 'Request closed',
                Auth::id()
            );

            event(new RegistryRequestClosed($request->fresh()));

            return $request->fresh();
        });
    }
}
