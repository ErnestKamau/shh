<?php

namespace App\Actions\Registry;

use App\DTOs\Registry\CreateRegistryRequestDTO;
use App\Events\Registry\RegistryRequestCreated;
use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestCategory;
use App\Repositories\Registry\RegistryRequestRepository;
use App\Services\Registry\WorkflowEngineService;
use App\Support\Registry\RegistryReferenceGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateRegistryRequestAction
{
    public function __construct(
        protected RegistryReferenceGenerator $referenceGenerator,
        protected RegistryRequestRepository $repository,
        protected WorkflowEngineService $workflowEngineService,
    ) {
    }

    public function execute(CreateRegistryRequestDTO $dto): RegistryRequest
    {
        $category = RegistryRequestCategory::query()
            ->forCompany()
            ->active()
            ->findOrFail($dto->requestCategoryId);

        return DB::transaction(function () use ($dto, $category) {
            $reference = $this->referenceGenerator->generate($category);

            if ($this->repository->referenceExists($reference)) {
                throw ValidationException::withMessages([
                    'reference_no' => 'Could not generate a unique reference. Please try again.',
                ]);
            }

            $request = RegistryRequest::create([
                'reference_no' => $reference,
                'request_category_id' => $category->id,
                'workflow_definition_id' => $category->workflow_definition_id,
                'subject' => $dto->subject,
                'description' => $dto->description,
                'entity_type' => $dto->entityType,
                'entity_id' => $dto->entityId,
                'metadata' => $dto->metadata,
                'priority' => $dto->priority ?: $category->default_priority,
                'direction' => $dto->direction,
                'status' => RegistryRequest::STATUS_DRAFT,
                'submitting_party' => $dto->submittingParty,
                'submitted_by' => Auth::id(),
                'received_by' => $dto->receivedBy ?? Auth::id(),
                'received_at' => now(),
                'company_id' => getUserCompany(),
            ]);

            $request = $this->workflowEngineService->startWorkflow($request);

            event(new RegistryRequestCreated($request->fresh(['category'])));

            return $request->fresh(['category', 'workflowDefinition.steps']);
        });
    }
}
