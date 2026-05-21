<?php

namespace App\Listeners\Registry;

use App\Events\Registry\RegistryRequestAssigned;
use App\Events\Registry\WorkflowTransitionCompleted;
use App\Services\Registry\WorkflowEngineService;

class CreateRegistryAuditLogListener
{
    public function __construct(
        protected WorkflowEngineService $workflowEngineService,
    ) {
    }

    public function handleTransition(WorkflowTransitionCompleted $event): void
    {
        // Primary audit rows are written in WorkflowEngineService::transition.
    }

    public function handleAssigned(RegistryRequestAssigned $event): void
    {
        // Assignment audit is written in RegistryAssignmentService.
    }

    public function subscribe($events): array
    {
        return [
            WorkflowTransitionCompleted::class => 'handleTransition',
            RegistryRequestAssigned::class => 'handleAssigned',
        ];
    }
}
