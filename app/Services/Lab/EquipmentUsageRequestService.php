<?php

namespace App\Services\Lab;

use App\Models\Equipments\Equipment;
use App\Models\Lab\EquipmentUsageRequest;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EquipmentUsageRequestService
{
    public function __construct(
        private readonly UserZoneResolver $userZoneResolver,
        private readonly EquipmentZoneResolver $equipmentZoneResolver,
        private readonly SampleZoneQueryService $sampleZoneQueryService,
        private readonly EquipmentUsageRequestNotificationService $notificationService,
    ) {}

    /**
     * @param  array{
     *     equipment_id: string,
     *     request_comment?: string|null,
     *     proposed_start_at: string,
     *     proposed_end_at: string,
     *     sample_detail_ids: array<int, string>
     * }  $data
     */
    public function submit(User $requester, array $data): EquipmentUsageRequest
    {
        $zoneIds = $this->userZoneResolver->zoneIdsForUser($requester);

        if ($zoneIds === []) {
            throw ValidationException::withMessages([
                'equipment_id' => ['You are not assigned to a zone. Contact your administrator.'],
            ]);
        }

        $equipment = Equipment::query()
            ->where('active', true)
            ->where(function ($q): void {
                $q->where('is_disposal', false)->orWhereNull('is_disposal');
            })
            ->inZones($zoneIds)
            ->find($data['equipment_id']);

        if (! $equipment) {
            throw ValidationException::withMessages([
                'equipment_id' => ['Selected equipment is not available in your zone.'],
            ]);
        }

        $zoneId = $this->equipmentZoneResolver->zoneIdForEquipment($equipment);

        if ($zoneId === null || ! in_array($zoneId, $zoneIds, true)) {
            throw ValidationException::withMessages([
                'equipment_id' => ['Equipment zone could not be verified for your assignment.'],
            ]);
        }

        $sampleIds = array_values(array_unique($data['sample_detail_ids'] ?? []));

        if ($sampleIds === []) {
            throw ValidationException::withMessages([
                'sample_detail_ids' => ['Select at least one sample.'],
            ]);
        }

        foreach ($sampleIds as $sampleId) {
            if (! $this->sampleZoneQueryService->sampleDetailInZones($sampleId, $zoneIds)) {
                throw ValidationException::withMessages([
                    'sample_detail_ids' => ['One or more selected samples are not in your zone.'],
                ]);
            }
        }

        $proposedStart = $data['proposed_start_at'];
        $proposedEnd = $data['proposed_end_at'];

        if (strtotime($proposedEnd) <= strtotime($proposedStart)) {
            throw ValidationException::withMessages([
                'proposed_end_at' => ['Proposed end time must be after the start time.'],
            ]);
        }

        return DB::transaction(function () use ($requester, $data, $equipment, $zoneId, $sampleIds, $proposedStart, $proposedEnd): EquipmentUsageRequest {
            $request = EquipmentUsageRequest::create([
                'equipment_id' => $equipment->id,
                'requester_id' => $requester->id,
                'status' => EquipmentUsageRequest::STATUS_PENDING,
                'request_comment' => $data['request_comment'] ?? null,
                'proposed_start_at' => $proposedStart,
                'proposed_end_at' => $proposedEnd,
                'zone_id' => $zoneId,
            ]);

            $request->sampleDetails()->sync($sampleIds);

            $approvers = $this->userZoneResolver->usersWithApprovePermissionInZone($zoneId);
            $this->notificationService->notifyApproversNewRequest($request, $approvers);

            return $request->load(['equipment', 'sampleDetails', 'requester']);
        });
    }

    /**
     * @param  array{
     *     approved_start_at: string,
     *     approved_end_at: string,
     *     approval_comment?: string|null,
     *     helping_analyst_id?: int|null
     * }  $data
     */
    public function approve(User $approver, EquipmentUsageRequest $request, array $data): EquipmentUsageRequest
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['This request is no longer pending.'],
            ]);
        }

        $approvedStart = $data['approved_start_at'];
        $approvedEnd = $data['approved_end_at'];

        if (strtotime($approvedEnd) <= strtotime($approvedStart)) {
            throw ValidationException::withMessages([
                'approved_end_at' => ['Approved end time must be after the start time.'],
            ]);
        }

        if (! empty($data['helping_analyst_id']) && $request->zone_id) {
            $helpingInZone = User::query()
                ->where('id', $data['helping_analyst_id'])
                ->where('active', 1)
                ->where(function ($query) use ($request): void {
                    $zoneId = $request->zone_id;
                    $query->where('zone_id', $zoneId)
                        ->orWhereHas('assignedZones', fn ($q) => $q->where('zones.id', $zoneId))
                        ->orWhereHas('assignedLabs', fn ($q) => $q->where('labs.zone_id', $zoneId));
                })
                ->exists();

            if (! $helpingInZone) {
                throw ValidationException::withMessages([
                    'helping_analyst_id' => ['Selected helping analyst must be in the request zone.'],
                ]);
            }
        }

        return DB::transaction(function () use ($approver, $request, $data, $approvedStart, $approvedEnd): EquipmentUsageRequest {
            $request->update([
                'status' => EquipmentUsageRequest::STATUS_APPROVED,
                'approved_start_at' => $approvedStart,
                'approved_end_at' => $approvedEnd,
                'approval_comment' => $data['approval_comment'] ?? null,
                'helping_analyst_id' => $data['helping_analyst_id'] ?? null,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            $request->refresh();
            $this->notificationService->notifyRequesterApproved($request);

            return $request->load(['equipment', 'sampleDetails', 'requester', 'helpingAnalyst', 'approver']);
        });
    }

    /**
     * @param  array{approval_comment: string}  $data
     */
    public function reject(User $approver, EquipmentUsageRequest $request, array $data): EquipmentUsageRequest
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['This request is no longer pending.'],
            ]);
        }

        return DB::transaction(function () use ($approver, $request, $data): EquipmentUsageRequest {
            $request->update([
                'status' => EquipmentUsageRequest::STATUS_REJECTED,
                'approval_comment' => $data['approval_comment'],
                'rejected_by' => $approver->id,
                'rejected_at' => now(),
            ]);

            $request->refresh();
            $this->notificationService->notifyRequesterRejected($request);

            return $request->load(['equipment', 'sampleDetails', 'requester', 'rejector']);
        });
    }

    public function cancel(User $requester, EquipmentUsageRequest $request): EquipmentUsageRequest
    {
        if ((string) $request->requester_id !== (string) $requester->id) {
            throw ValidationException::withMessages([
                'request' => ['You can only cancel your own requests.'],
            ]);
        }

        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['Only pending requests can be cancelled.'],
            ]);
        }

        $request->update(['status' => EquipmentUsageRequest::STATUS_CANCELLED]);

        return $request->fresh();
    }
}
