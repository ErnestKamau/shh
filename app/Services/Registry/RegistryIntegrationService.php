<?php

namespace App\Services\Registry;

use App\Models\DMS\Document;
use App\Models\Registry\RegistryRequest;
use App\Models\SubmissionFormInstance;
use App\Services\DMS\AmendmentWorkflowService;
use App\User;
use Illuminate\Support\Facades\Auth;

class RegistryIntegrationService
{
    public function __construct(
        protected AmendmentWorkflowService $amendmentWorkflowService,
    ) {
    }

    public function handleCategoryTransition(RegistryRequest $request): void
    {
        $code = $request->category?->code;

        if ($code === 'amendment') {
            $this->linkAmendment($request);
        }

        if ($code === 'analysis') {
            $this->createPendingSampleReceiving($request);
        }
    }

    public function linkAmendment(RegistryRequest $request): void
    {
        if ($request->entity_type !== Document::class && $request->entity_type !== 'App\Models\DMS\Document') {
            return;
        }

        $document = Document::query()->find($request->entity_id);
        if ($document === null) {
            return;
        }

        $user = Auth::user() ?? User::query()->first();
        if ($user === null) {
            return;
        }

        $reason = (string) ($request->metadata['amendment_reason'] ?? 'Registry amendment request');
        $description = $request->description;

        $amendment = $this->amendmentWorkflowService->processAmendmentRequest(
            $document,
            $reason,
            $description,
            $user
        );

        $request->update([
            'entity_type' => get_class($amendment),
            'entity_id' => (string) $amendment->id,
            'metadata' => array_merge($request->metadata ?? [], [
                'dms_amendment_id' => $amendment->id,
            ]),
        ]);
    }

    public function createPendingSampleReceiving(RegistryRequest $request): void
    {
        $metadata = array_merge($request->metadata ?? [], [
            'pending_sample_receiving' => true,
            'integration' => 'analysis',
            'registry_reference' => $request->reference_no,
        ]);

        $existingInstanceId = $metadata['submission_form_instance_id'] ?? null;
        if ($existingInstanceId !== null && class_exists(SubmissionFormInstance::class)) {
            $instance = SubmissionFormInstance::query()->find($existingInstanceId);
            if ($instance !== null) {
                $request->update([
                    'entity_type' => SubmissionFormInstance::class,
                    'entity_id' => (string) $instance->id,
                    'metadata' => $metadata,
                ]);

                return;
            }
        }

        $request->update(['metadata' => $metadata]);
    }
}
