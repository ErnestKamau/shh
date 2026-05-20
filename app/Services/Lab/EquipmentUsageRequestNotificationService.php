<?php

namespace App\Services\Lab;

use App\Models\Lab\EquipmentUsageRequest;
use App\Models\Lab\LabUserNotification;
use App\User;
use Illuminate\Support\Collection;

class EquipmentUsageRequestNotificationService
{
    public function buildApprovedMessage(EquipmentUsageRequest $request): string
    {
        $request->loadMissing(['equipment']);

        $equipmentName = $request->equipment?->name ?? 'Equipment';

        return sprintf(
            'Your equipment usage request for %s has been approved.',
            $equipmentName
        );
    }

    public function notifyRequesterApproved(EquipmentUsageRequest $request): LabUserNotification
    {
        $request->loadMissing(['equipment', 'helpingAnalyst', 'approver']);

        $equipmentName = $request->equipment?->name ?? 'Equipment';
        $helpingName = $request->helpingAnalyst?->name;

        $message = $this->buildApprovedMessage($request);

        return LabUserNotification::create([
            'user_id' => $request->requester_id,
            'notifiable_type' => EquipmentUsageRequest::class,
            'notifiable_id' => $request->id,
            'notification_type' => 'equipment_usage_request_approved',
            'title' => 'Equipment request approved',
            'message' => $message,
            'metadata' => [
                'equipment_name' => $equipmentName,
                'approval_comment' => $request->approval_comment,
                'approved_start_at' => $request->approved_start_at?->toIso8601String(),
                'approved_end_at' => $request->approved_end_at?->toIso8601String(),
                'helping_analyst_id' => $request->helping_analyst_id,
                'helping_analyst_name' => $helpingName,
                'approver_name' => $request->approver?->name,
            ],
        ]);
    }

    public function notifyRequesterRejected(EquipmentUsageRequest $request): LabUserNotification
    {
        $request->loadMissing(['equipment', 'rejector']);

        $equipmentName = $request->equipment?->name ?? 'Equipment';

        return LabUserNotification::create([
            'user_id' => $request->requester_id,
            'notifiable_type' => EquipmentUsageRequest::class,
            'notifiable_id' => $request->id,
            'notification_type' => 'equipment_usage_request_rejected',
            'title' => 'Equipment request rejected',
            'message' => sprintf(
                'Your equipment usage request for %s was rejected.',
                $equipmentName
            ),
            'metadata' => [
                'equipment_name' => $equipmentName,
                'approval_comment' => $request->approval_comment,
                'rejector_name' => $request->rejector?->name,
            ],
        ]);
    }

    /**
     * @return Collection<int, LabUserNotification>
     */
    public function notifyApproversNewRequest(EquipmentUsageRequest $request, Collection $approvers): Collection
    {
        $request->loadMissing(['equipment', 'requester']);
        $equipmentName = $request->equipment?->name ?? 'Equipment';
        $requesterName = $request->requester?->name ?? 'A user';

        return $approvers->map(function (User $approver) use ($request, $equipmentName, $requesterName): LabUserNotification {
            return LabUserNotification::create([
                'user_id' => $approver->id,
                'notifiable_type' => EquipmentUsageRequest::class,
                'notifiable_id' => $request->id,
                'notification_type' => 'equipment_usage_request_pending',
                'title' => 'Equipment usage request pending approval',
                'message' => sprintf(
                    '%s requested use of %s.',
                    $requesterName,
                    $equipmentName
                ),
                'metadata' => [
                    'equipment_name' => $equipmentName,
                    'requester_name' => $requesterName,
                    'proposed_start_at' => $request->proposed_start_at?->toIso8601String(),
                    'proposed_end_at' => $request->proposed_end_at?->toIso8601String(),
                ],
            ]);
        });
    }
}
