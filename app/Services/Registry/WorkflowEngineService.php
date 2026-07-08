<?php

namespace App\Services\Registry;

use App\DTOs\Registry\WorkflowTransitionDTO;
use App\Events\Registry\RegistryRequestClosed;
use App\Events\Registry\WorkflowTransitionCompleted;
use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestAction;
use App\Models\Registry\RegistryRequestStatusLog;
use App\Models\Registry\WorkflowStep;
use App\Support\Registry\RegistryStatusManager;
use App\Support\Registry\RegistryWorkflowResolver;
use App\Support\Registry\WorkflowEngine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowEngineService
{
    public function __construct(
        protected WorkflowEngine $workflowEngine,
        protected RegistryWorkflowResolver $resolver,
        protected RegistryStatusManager $statusManager,
        protected RegistryTATService $tatService,
    ) {
    }

    public function startWorkflow(RegistryRequest $request): RegistryRequest
    {
        $definition = $request->workflowDefinition;
        if ($definition === null) {
            throw ValidationException::withMessages(['workflow' => 'No workflow definition linked to this request.']);
        }

        $firstStep = $this->resolver->firstStep($definition);
        if ($firstStep === null) {
            throw ValidationException::withMessages(['workflow' => 'Workflow has no steps configured.']);
        }

        return DB::transaction(function () use ($request, $firstStep) {
            $request->update([
                'current_stage' => $firstStep->step_code,
                'status' => RegistryRequest::STATUS_OPEN,
            ]);

            $this->tatService->enterStage($request, $firstStep->step_code);

            $this->recordAction($request, 'workflow_started', null, $firstStep->step_code, 'Workflow started');

            return $request->fresh();
        });
    }

    public function transition(WorkflowTransitionDTO $dto): RegistryRequest
    {
        $request = RegistryRequest::query()->forCompany()->findOrFail($dto->registryRequestId);

        if ($request->isClosed()) {
            throw ValidationException::withMessages(['status' => 'Request is already closed.']);
        }

        $fromStep = $this->resolver->currentStep($request);
        if ($fromStep === null) {
            throw ValidationException::withMessages(['workflow' => 'Request has no current workflow stage.']);
        }

        $transition = $this->workflowEngine->findTransition($request, $fromStep, $dto->actionName);
        if ($transition === null || $transition->toStep === null) {
            throw ValidationException::withMessages(['action' => 'Transition not allowed from current stage.']);
        }

        return DB::transaction(function () use ($request, $fromStep, $transition, $dto) {
            $toStep = $transition->toStep;

            $this->tatService->exitStage($request, $fromStep->step_code);

            $request->update([
                'current_stage' => $toStep->step_code,
                'status' => $this->statusManager->statusForStage($toStep),
            ]);

            $this->tatService->enterStage($request, $toStep->step_code);

            if ($toStep->is_final) {
                $request->update([
                    'status' => RegistryRequest::STATUS_CLOSED,
                    'closed_at' => now(),
                ]);
                event(new RegistryRequestClosed($request->fresh()));
            }

            $this->recordAction(
                $request,
                $dto->actionName,
                $fromStep->step_code,
                $toStep->step_code,
                $dto->comment,
                $dto->performedBy
            );

            event(new WorkflowTransitionCompleted($request->fresh(), $dto->actionName));

            return $request->fresh();
        });
    }

    public function recordAction(
        RegistryRequest $request,
        string $actionType,
        ?string $fromStage,
        ?string $toStage,
        ?string $comment = null,
        ?string $performedBy = null,
        array $payload = []
    ): RegistryRequestAction {
        return RegistryRequestAction::create([
            'registry_request_id' => $request->id,
            'action_type' => $actionType,
            'from_stage' => $fromStage,
            'to_stage' => $toStage,
            'comment' => $comment,
            'payload' => $payload,
            'performed_by' => $performedBy ?? Auth::id(),
            'performed_at' => now(),
        ]);
    }
}
