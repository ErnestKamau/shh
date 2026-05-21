<?php

namespace App\Services\Registry;

use App\Models\AuditModule\AuditWorkflowApprover;
use App\Models\Registry\RegistryRequest;
use App\Models\Registry\WorkflowStep;
use App\Support\Registry\RegistryWorkflowResolver;
use Illuminate\Support\Collection;

class RegistryRoutingService
{
    public function __construct(
        protected RegistryWorkflowResolver $resolver,
    ) {
    }

    public function resolveApproversForRequest(RegistryRequest $request): Collection
    {
        $step = $this->resolver->currentStep($request);
        if ($step === null) {
            return collect();
        }

        return $this->resolveApproversForStep($step);
    }

    public function resolveApproversForStep(WorkflowStep $step): Collection
    {
        if ($step->role_name === null) {
            return collect();
        }

        return AuditWorkflowApprover::query()
            ->forModule('registry')
            ->forWorkflowStep($step->sequence)
            ->required()
            ->with('user')
            ->get();
    }
}
