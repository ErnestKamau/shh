<?php

namespace App\Http\Controllers\Api\Portal\Submissions;

use App\Http\Controllers\Controller;
use App\Services\SubmissionForm\FormSchemaBuilder;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Services\SubmissionForm\PortalTestRequestFormSampleTypeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SubmissionFormController extends Controller
{
    public function __construct(
        private readonly PortalSubmissionFormAccess $access,
        private readonly FormSchemaBuilder $schemaBuilder,
        private readonly PortalTestRequestFormSampleTypeResolver $sampleTypeResolver,
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
        return response()->json([
            'message' => 'The customer-request form is deprecated. Use test-request-templates or resolve-test-request instead.',
            'error' => [
                'code' => 'customer_request_form_deprecated',
                'alternatives' => [
                    'GET /api/v1/portal/submissions/forms/test-request-templates',
                    'GET /api/v1/portal/submissions/forms/resolve-test-request?sample_type_id={uuid}',
                ],
            ],
        ], 410);
    }

    public function testRequestFormSchema(Request $request, string $submissionForm): JsonResponse
    {
        $customerId = $this->access->customerIdFromRequest($request);
        $form = $this->access->findPortalForm($submissionForm, $customerId);

        $documentCode = strtoupper((string) ($form->document_code ?? ''));
        if (! str_starts_with($documentCode, 'TRF-')) {
            return response()->json(['message' => 'This form is not a test request form template.'], 422);
        }

        $form->loadMissing('sampleTypes');
        $sampleTypeId = $form->sampleTypes->first()?->id;

        if ($sampleTypeId === null) {
            return response()->json(['message' => 'No sample type linked to this test request form.'], 404);
        }

        $testRequestForm = \App\Models\TestRequestForm::query()
            ->where('sample_type_id', $sampleTypeId)
            ->where('is_active', true)
            ->first();

        if ($testRequestForm === null) {
            return response()->json(['message' => 'No linked TestRequestForm found for this sample type.'], 404);
        }

        return response()->json([
            'data' => [
                'submission_form_id' => $form->id,
                'document_code' => $form->document_code,
                'test_request_form_id' => $testRequestForm->id,
                'sample_type_id' => $sampleTypeId,
                'form_fields' => $testRequestForm->form_fields,
            ],
            'meta' => [
                'crm_customer_id' => $customerId,
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

        $forms = $this->formsMatchingSampleType($customerId, $sampleTypeId);

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
                    'sample_types' => $this->sampleTypeResolver->mapForApi($form),
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
                    'sample_types' => $this->sampleTypeResolver->mapForApi($form),
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

    /**
     * @return Collection<int, \App\Models\SubmissionForm>
     */
    private function formsMatchingSampleType(?string $customerId, string $sampleTypeId): Collection
    {
        return $this->access->testRequestTemplatesQuery($customerId)
            ->with(['sampleTypes:id,name,code'])
            ->get()
            ->filter(function ($form) use ($sampleTypeId): bool {
                return $this->sampleTypeResolver->resolveForForm($form)
                    ->contains(fn ($type): bool => (string) $type->id === (string) $sampleTypeId);
            })
            ->values();
    }
}
