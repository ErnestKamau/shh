<?php

namespace App\Http\Controllers\Api\Portal\Submissions;

use App\Exceptions\Api\Portal\PortalApiException;
use App\Http\Controllers\Controller;
use App\Services\SubmissionForm\FormSchemaBuilder;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmissionFormController extends Controller
{
    public function __construct(
        private readonly PortalSubmissionFormAccess $access,
        private readonly FormSchemaBuilder $schemaBuilder,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $customerId = $this->access->customerIdFromRequest($request);

        $forms = $this->access->portalFormsQuery($customerId)
            ->withCount('sections')
            ->orderBy('name')
            ->get()
            ->map(fn ($form): array => array_merge(
                $this->schemaBuilder->buildFormMeta($form),
                ['section_count' => $form->sections_count]
            ));

        return response()->json([
            'data' => $forms,
            'meta' => [
                'crm_customer_id' => $customerId,
                'count' => $forms->count(),
            ],
        ]);
    }

    public function show(Request $request, string $submissionForm): JsonResponse
    {
        $customerId = $this->access->customerIdFromRequest($request);
        $form = $this->access->findPortalForm($submissionForm, $customerId);

        $form->loadCount(['sections', 'attachmentForms']);

        return response()->json([
            'data' => array_merge(
                $this->schemaBuilder->buildFormMeta($form),
                [
                    'section_count' => $form->sections_count,
                    'attachment_form_count' => $form->isTemplate() ? $form->attachment_forms_count : 0,
                ]
            ),
        ]);
    }

    public function schema(Request $request, string $submissionForm): JsonResponse
    {
        $customerId = $this->access->customerIdFromRequest($request);
        $form = $this->access->findPortalForm($submissionForm, $customerId);

        return response()->json([
            'data' => $this->schemaBuilder->buildTemplateWithAttachments($form),
        ]);
    }

    public function customerRequest(Request $request): JsonResponse
    {
        $customerId = $this->access->customerIdFromRequest($request);
        $form = $this->access->latestCustomerRequestForm($customerId);

        if (! $form) {
            throw PortalApiException::customerRequestFormNotFound();
        }

        return response()->json([
            'data' => $this->schemaBuilder->buildTemplateWithAttachments($form),
            'meta' => [
                'crm_customer_id' => $customerId,
                'resolved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function resolveTestRequest(Request $request): JsonResponse
    {
        $customerId = $this->access->customerIdFromRequest($request);
        $sampleTypeId = (string) $request->query('sample_type_id', '');

        if ($sampleTypeId === '') {
            return response()->json(['message' => 'sample_type_id is required.'], 422);
        }

        $forms = $this->access->testRequestTemplatesQuery($customerId)
            ->whereHas('sampleTypes', fn ($query) => $query->where('sample_types.id', $sampleTypeId))
            ->with(['sampleTypes:id,name,code'])
            ->get();

        if ($forms->isEmpty()) {
            return response()->json(['message' => 'No test request form found for this sample type.'], 404);
        }

        if ($forms->count() > 1) {
            return response()->json([
                'message' => 'Multiple test request forms match this sample type.',
                'data' => $forms->map(fn ($form): array => $this->schemaBuilder->buildFormMeta($form))->values()->all(),
            ], 409);
        }

        $form = $forms->first();

        return response()->json([
            'data' => array_merge(
                $this->schemaBuilder->buildFormMeta($form),
                [
                    'sample_types' => $form->sampleTypes->map(fn ($type): array => [
                        'id' => $type->id,
                        'name' => $type->name,
                        'code' => $type->code,
                    ])->values()->all(),
                ],
            ),
            'meta' => [
                'sample_type_id' => $sampleTypeId,
                'crm_customer_id' => $customerId,
            ],
        ]);
    }

    public function testRequestTemplates(Request $request): JsonResponse
    {
        $customerId = $this->access->customerIdFromRequest($request);

        $forms = $this->access->testRequestTemplatesQuery($customerId)
            ->with(['sampleTypes:id,name,code'])
            ->withCount('sections')
            ->get()
            ->map(fn ($form): array => array_merge(
                $this->schemaBuilder->buildFormMeta($form),
                [
                    'section_count' => $form->sections_count,
                    'sample_types' => $form->sampleTypes->map(fn ($type): array => [
                        'id' => $type->id,
                        'name' => $type->name,
                        'code' => $type->code,
                    ])->values()->all(),
                ],
            ));

        return response()->json([
            'data' => $forms,
            'meta' => [
                'crm_customer_id' => $customerId,
                'count' => $forms->count(),
            ],
        ]);
    }

    public function attachmentForms(Request $request, string $submissionForm): JsonResponse
    {
        $customerId = $this->access->customerIdFromRequest($request);
        $form = $this->access->findPortalForm($submissionForm, $customerId);

        if (! $form->isTemplate()) {
            return response()->json(['data' => []]);
        }

        $sampleTypeId = $request->filled('sample_type_id')
            ? $request->string('sample_type_id')->toString()
            : null;

        $attachments = $form->attachmentForms()
            ->where('is_customer_portal_form', true)
            ->where('is_published', true)
            ->where('is_active', true)
            ->with('sampleTypes')
            ->orderBy('name')
            ->get()
            ->filter(function ($attachment) use ($sampleTypeId): bool {
                if ($sampleTypeId === null) {
                    return true;
                }

                if ($attachment->sampleTypes->isEmpty()) {
                    return true;
                }

                return $attachment->sampleTypes->contains('id', $sampleTypeId);
            })
            ->values()
            ->map(fn ($attachment): array => array_merge(
                $this->schemaBuilder->buildFormMeta($attachment),
                ['form_type' => $attachment->form_type],
            ))
            ->all();

        return response()->json(['data' => $attachments]);
    }
}
