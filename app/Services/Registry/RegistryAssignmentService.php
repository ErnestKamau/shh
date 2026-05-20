<?php

namespace App\Services\Registry;

use App\DTOs\Registry\AssignRegistryRequestDTO;
use App\Events\Registry\RegistryRequestAssigned;
use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistryAssignmentService
{
    public function __construct(
        protected WorkflowEngineService $workflowEngineService,
    ) {
    }

    public function assign(AssignRegistryRequestDTO $dto): RegistryRequest
    {
        return DB::transaction(function () use ($dto) {
            $request = RegistryRequest::query()->forCompany()->findOrFail($dto->registryRequestId);

            RegistryRequestAssignment::query()
                ->where('registry_request_id', $request->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'unassigned_at' => now(),
                ]);

            RegistryRequestAssignment::create([
                'registry_request_id' => $request->id,
                'assigned_to' => $dto->assignedTo,
                'assigned_by' => $dto->assignedBy ?? Auth::id(),
                'role_context' => $dto->roleContext,
                'is_active' => true,
                'assigned_at' => now(),
            ]);

            $request->update(['assigned_to' => $dto->assignedTo]);

            $this->workflowEngineService->recordAction(
                $request,
                'assigned',
                $request->current_stage,
                $request->current_stage,
                'Assigned to user #' . $dto->assignedTo,
                $dto->assignedBy
            );

            event(new RegistryRequestAssigned($request->fresh()));

            return $request->fresh(['assignee', 'assignments.assignee']);
        });
    }
}
