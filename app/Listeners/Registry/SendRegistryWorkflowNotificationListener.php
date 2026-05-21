<?php

namespace App\Listeners\Registry;

use App\Events\Registry\RegistryRequestApproved;
use App\Events\Registry\RegistryRequestAssigned;
use App\Events\Registry\RegistryRequestClosed;
use App\Events\Registry\RegistryRequestCreated;
use App\Events\Registry\RegistryRequestRejected;
use App\Events\Registry\WorkflowTransitionCompleted;
use App\Services\Registry\RegistryNotificationService;

class SendRegistryWorkflowNotificationListener
{
    public function __construct(
        protected RegistryNotificationService $notificationService,
    ) {
    }

    public function handleCreated(RegistryRequestCreated $event): void
    {
        $this->notificationService->notifyCreated($event->request);
        $this->notificationService->notifyApprovalRequired($event->request);
    }

    public function handleApproved(RegistryRequestApproved $event): void
    {
        $this->notificationService->notifyApprovalRequired($event->request);
    }

    public function handleTransition(WorkflowTransitionCompleted $event): void
    {
        if ($event->actionName === 'approve') {
            $this->notificationService->notifyApprovalRequired($event->request);
        }
    }

    public function handleAssigned(RegistryRequestAssigned $event): void
    {
        $this->notificationService->notifyAssigned($event->request);
    }

    public function handleRejected(RegistryRequestRejected $event): void
    {
        $this->notificationService->notifyRejected($event->request);
    }

    public function handleClosed(RegistryRequestClosed $event): void
    {
        $this->notificationService->notifyCompleted($event->request);
    }

    public function subscribe($events): array
    {
        return [
            RegistryRequestCreated::class => 'handleCreated',
            RegistryRequestApproved::class => 'handleApproved',
            WorkflowTransitionCompleted::class => 'handleTransition',
            RegistryRequestAssigned::class => 'handleAssigned',
            RegistryRequestRejected::class => 'handleRejected',
            RegistryRequestClosed::class => 'handleClosed',
        ];
    }
}
