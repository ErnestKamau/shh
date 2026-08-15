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
        $form->loadMissing(['sampleTypeCategories']);

        $response = [
            'data' => $this->schemaBuilder->buildTemplateWithAttachments($form),
        ];

        if ($form->isTestRequestTemplate()) {
            $resolvedSampleTypes = $this->sampleTypeResolver->resolveForForm($form);
            $tied = $resolvedSampleTypes->isNotEmpty();

            $response['meta'] = [
                'crm_customer_id' => $customerId,
                'sample_type_id' => $tied ? $resolvedSampleTypes->first()?->id : null,
                'sample_types' => $this->sampleTypeResolver->mapForApi($form),
                'sample_types_tied' => $tied,
                'sample_type_options' => $this->sampleTypeResolver->selectableOptionsForApi($form),
                'sample_type_categories' => $form->sampleTypeCategories->map(fn ($cat): array => [
                    'id' => (int) $cat->id,
                    'name' => (string) $cat->sample_type_category,
                ])->values()->all(),
            ];
        }

        return response()->json($response);
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

        $form->loadMissing(['sections.elementHolders.elements']);

        $resolvedSampleTypes = $this->sampleTypeResolver->resolveForForm($form);

        return response()->json([
            'data' => $this->schemaBuilder->buildTemplateWithAttachments($form),
            'meta' => [
                'crm_customer_id' => $customerId,
                'sample_type_id' => $resolvedSampleTypes->first()?->id,
            ],
        ]);
    }

    public function resolveTestRequest(Request $request): JsonResponse
    {
        $customerId = $this->access->customerIdFromRequest($request);
        $sampleTypeId = (string) $request->query('sample_type_id', '');
        $categoryId = (string) $request->query('sample_type_category_id', '');

        // Prefer category-based lookup when supplied.
        if ($categoryId !== '' && is_numeric($categoryId)) {
            $form = $this->access->testRequestFormForSampleTypeCategory((int) $categoryId, $customerId);

            if ($form === null) {
                return response()->json(['message' => 'No test request form found for this sample type category.'], 404);
            }

            $form->loadMissing(['sampleTypeCategories']);

            return response()->json([
                'data' => array_merge(
                    $this->schemaBuilder->buildFormMeta($form),
                    [
                        'sample_type_categories' => $form->sampleTypeCategories->map(fn ($cat): array => [
                            'id' => (int) $cat->id,
                            'name' => (string) $cat->sample_type_category,
                        ])->values()->all(),
                        'sample_types' => $this->sampleTypeResolver->mapForApi($form),
                    ],
                ),
                'meta' => [
                    'sample_type_category_id' => (int) $categoryId,
                    'crm_customer_id' => $customerId,
                ],
            ]);
        }

        if ($sampleTypeId === '') {
            return response()->json(['message' => 'sample_type_id or sample_type_category_id is required.'], 422);
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
        $form->loadMissing(['sampleTypeCategories']);

        return response()->json([
            'data' => array_merge(
                $this->schemaBuilder->buildFormMeta($form),
                [
                    'sample_type_categories' => $form->sampleTypeCategories->map(fn ($cat): array => [
                        'id' => (int) $cat->id,
                        'name' => (string) $cat->sample_type_category,
                    ])->values()->all(),
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
            ->with(['sampleTypeCategories:id,sample_type_category'])
            ->withCount('sections')
            ->get()
            ->map(fn ($form): array => array_merge(
                $this->schemaBuilder->buildFormMeta($form),
                [
                    'section_count' => $form->sections_count,
                    'sample_types' => $this->sampleTypeResolver->mapForApi($form),
                    'sample_type_categories' => $form->sampleTypeCategories->map(fn ($cat): array => [
                        'id' => (int) $cat->id,
                        'name' => (string) $cat->sample_type_category,
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
            ->with('sampleTypeCategories')
            ->orderBy('name')
            ->get()
            ->filter(function ($attachment) use ($sampleTypeId): bool {
                if ($sampleTypeId === null) {
                    return true;
                }

                if ($attachment->sampleTypeCategories->isEmpty()) {
                    return true;
                }

                $sampleType = \App\SampleType::query()->find($sampleTypeId);
                if ($sampleType === null || $sampleType->sample_type_category === null) {
                    return false;
                }

                return $attachment->sampleTypeCategories->contains(
                    fn ($category): bool => (int) $category->id === (int) $sampleType->sample_type_category
                );
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
            ->with(['sampleTypeCategories:id,sample_type_category'])
            ->get()
            ->filter(function ($form) use ($sampleTypeId): bool {
                return $this->sampleTypeResolver->resolveForForm($form)
                    ->contains(fn ($type): bool => (string) $type->id === (string) $sampleTypeId);
            })
            ->values();
    }
}
