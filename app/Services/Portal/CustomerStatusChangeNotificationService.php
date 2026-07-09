<?php

namespace App\Services\Portal;

use App\Models\CRM\CustomerNotification;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;

final class CustomerStatusChangeNotificationService
{
    public function recordSubmissionRequestStatusChange(
        SampleSubmissionRequest $request,
        string $fromStatus,
        string $toStatus
    ): void {
        $customerId = trim((string) ($request->crm_customer_id ?? ''));
        if ($customerId === '') {
            return;
        }

        $requestNo = trim((string) ($request->request_number ?? ''));
        $label = $requestNo !== '' ? "request {$requestNo}" : 'your submission request';

        $message = $fromStatus !== ''
            ? "Status changed from {$fromStatus} to {$toStatus}."
            : "Status changed to {$toStatus}.";

        $this->createNotification(
            customerId: $customerId,
            entityType: SampleSubmissionRequest::class,
            entityId: (string) $request->getKey(),
            notificationType: 'Submission Request Status Updated',
            notificationDescription: ucfirst($label).". {$message}",
        );
    }

    public function recordFormInstanceStatusChange(
        SubmissionFormInstance $instance,
        string $fromStatus,
        string $toStatus
    ): void {
        $customerId = trim((string) ($instance->crm_customer_id ?? ''));
        if ($customerId === '') {
            return;
        }

        $reference = trim((string) ($instance->form_number ?? $instance->title ?? ''));
        $label = $reference !== '' ? "form {$reference}" : 'your form submission';

        $message = $fromStatus !== ''
            ? "Status changed from {$fromStatus} to {$toStatus}."
            : "Status changed to {$toStatus}.";

        $this->createNotification(
            customerId: $customerId,
            entityType: SubmissionFormInstance::class,
            entityId: (string) $instance->getKey(),
            notificationType: 'Form Status Updated',
            notificationDescription: ucfirst($label).". {$message}",
        );
    }

    private function createNotification(
        string $customerId,
        string $entityType,
        string $entityId,
        string $notificationType,
        string $notificationDescription
    ): void {
        if ($entityId === '') {
            return;
        }

        $duplicate = CustomerNotification::query()
            ->where('customer_id', $customerId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('notification_type', $notificationType)
            ->where('notification_description', $notificationDescription)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($duplicate) {
            return;
        }

        CustomerNotification::query()->create([
            'customer_id' => $customerId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'notification_type' => $notificationType,
            'notification_description' => $notificationDescription,
        ]);
    }
}
