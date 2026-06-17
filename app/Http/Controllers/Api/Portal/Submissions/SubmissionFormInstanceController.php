<?php

namespace App\Http\Controllers\Api\Portal\Submissions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portal\Submissions\StoreSubmissionFormInstanceRequest;
use App\Http\Requests\Api\Portal\Submissions\SubmitSubmissionFormInstanceRequest;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\User;
use App\Services\Commercial\CommercialEnquiryFromFormService;
use App\Services\SubmissionForm\FormSchemaBuilder;
use App\Services\SubmissionForm\PortalDependedFieldResolver;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Services\SubmissionForm\SubmissionFormInstanceDeletionService;
use App\Services\SubmissionForm\SubmissionFormSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Api\Portal\PortalApiException;

class SubmissionFormInstanceController extends Controller
{
    /**
     * Status buckets returned by GET /instances (fixed order for portal UI).
     *
     * @var array<int, string>
     */
    private const INSTANCE_STATUS_GROUPS = [
        'draft',
        'submitted',
        'in_review',
        'approved',
        'rejected',
        'cancelled',
    ];

    /**
     * Statuses the portal may delete via DELETE /instances/{id}.
     *
     * @var array<int, string>
     */
    private const DELETABLE_STATUSES = [
        'draft',
        'submitted',
    ];

    public function __construct(
        private readonly PortalSubmissionFormAccess $access,
        private readonly FormSchemaBuilder $schemaBuilder,
        private readonly SubmissionFormSubmissionService $submissionService,
        private readonly SubmissionFormInstanceDeletionService $deletionService,
        private readonly PortalDependedFieldResolver $dependedFieldResolver,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $customerId = $this->resolveCustomerId($request);
        $portalAccountId = $this->access->portalAccountIdFromRequest($request);

        $query = $this->access
            ->portalInstancesQuery($customerId)
            ->with([
                'submissionForm:id,name,document_code,version,form_type',
                'sampleSubmissionRequest:id,submission_form_instance_id,request_number,status,source_channel',
            ])
            ->orderByDesc('updated_at');

        if ($request->filled('portal_account_id')) {
            $query->where('portal_account_id', $request->string('portal_account_id')->toString());
        }

        if ($request->filled('submission_form_id')) {
            $query->where('submission_form_id', $request->string('submission_form_id')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $instances = $query->get();

        $grouped = [];
        foreach (self::INSTANCE_STATUS_GROUPS as $status) {
            $grouped[$status] = [];
        }

        $counts = [];

        foreach ($instances as $instance) {
            $status = $instance->status;

            if (! array_key_exists($status, $grouped)) {
                $grouped[$status] = [];
            }

            $grouped[$status][] = $this->buildInstanceListItem($instance);
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        return response()->json([
            'data' => $grouped,
            'meta' => [
                'crm_customer_id' => $customerId,
                'portal_account_id' => $portalAccountId,
                'total' => $instances->count(),
                'counts' => $counts,
                'status_groups' => self::INSTANCE_STATUS_GROUPS,
                'deletable_statuses' => self::DELETABLE_STATUSES,
            ],
        ]);
    }

    public function formInstances(Request $request, string $submissionForm): JsonResponse
    {
        $request->merge(['submission_form_id' => $submissionForm]);

        return $this->index($request);
    }

    public function stats(Request $request): JsonResponse
    {
        $customerId = $this->resolveCustomerId($request);

        $query = $this->access
            ->portalInstancesQuery($customerId)
            ->with(['submissionForm:id,form_type']);

        if ($request->filled('submission_form_id')) {
            $query->where('submission_form_id', $request->string('submission_form_id')->toString());
        }

        $instances = $query->get(['id', 'status', 'submission_form_id']);

        $byStatus = [];
        $byFormType = ['template' => 0, 'attachment' => 0];

        foreach ($instances as $instance) {
            $status = $instance->status;
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;

            $formType = $instance->submissionForm?->form_type;
            if ($formType !== null && array_key_exists($formType, $byFormType)) {
                $byFormType[$formType]++;
            }
        }

        return response()->json([
            'total' => $instances->count(),
            'by_status' => $byStatus,
            'by_form_type' => $byFormType,
        ]);
    }

    public function dependedFieldValue(Request $request, string $instance): JsonResponse
    {
        $request->validate([
            'element_name' => ['required', 'string', 'max:255'],
            'depends_on_value' => ['nullable'],
        ]);

        $instanceModel = $this->findAuthorizedInstance($request, $instance);

        $value = $this->dependedFieldResolver->resolve(
            $instanceModel,
            $request->string('element_name')->toString(),
            $request->input('depends_on_value'),
        );

        return response()->json(['value' => $value]);
    }

    public function store(
        StoreSubmissionFormInstanceRequest $request,
        string $submissionForm
    ): JsonResponse {
        $customerId = $this->resolveCustomerId($request);
        $portalAccountId = $this->access->portalAccountIdFromRequest($request);
        $form = $this->access->findPortalForm($submissionForm, $customerId);

        if ($form->isAttachment()) {
            $portalRequestId = $request->input('portal_request_id');
            if (! $portalRequestId) {
                throw ValidationException::withMessages([
                    'portal_request_id' => ['portal_request_id is required when creating an attachment form instance.'],
                ]);
            }
        }

        $instance = SubmissionFormInstance::create([
            'submission_form_id' => $form->id,
            'form_number' => null,
            'sequence_number' => null,
            'title' => $request->input('title') ?: ($form->name.' — '.now()->format('Y-m-d H:i')),
            'submitted_by' => null,
            'portal_account_id' => $portalAccountId,
            'crm_customer_id' => $customerId,
            'portal_request_id' => $request->input('portal_request_id'),
            'status' => 'draft',
            'priority' => $request->input('priority', 'normal'),
            'due_date' => $request->input('due_date'),
        ]);

        $this->prefillPortalCustomerFields($instance, $form, $customerId, $portalAccountId);
        try {
            app(CommercialEnquiryFromFormService::class)->syncFromDraftInstance($instance->fresh(['values.element', 'submissionForm', 'crmCustomer']));
        } catch (\Throwable $th) {
            Log::warning('Commercial enquiry sync failed for draft portal instance.', [
                'instance_id' => $instance->id,
                'message' => $th->getMessage(),
            ]);
        }

        return response()->json([
            'data' => $this->buildInstanceResponse($instance->fresh('submissionForm')),
        ], 201);
    }

    public function show(Request $request, string $instance): JsonResponse
    {
        $instanceModel = $this->findAuthorizedInstance($request, $instance);

        $instanceModel->load([
            'submissionForm',
            'values.element',
        ]);

        return response()->json([
            'data' => $this->buildInstanceResponse($instanceModel, includeValues: true),
        ]);
    }

    public function testRequestFormPdf(Request $request, string $instance)
    {
        $instanceModel = $this->findAuthorizedInstance($request, $instance);
        $trfi = $instanceModel->testRequestFormInstance()->first();

        if (! $trfi) {
            return response()->json([
                'message' => 'No test request form is available for this submission.',
            ], 404);
        }

        $pdfService = app(\App\Services\Sampleworkflow\TestRequestFormPdfService::class);
        $pdfService->ensureStored($trfi);

        $storagePath = $pdfService->resolveStoragePath($trfi);
        $filename = 'test-request-form-' . ($instanceModel->form_number ?: $trfi->id) . '.pdf';

        return Storage::disk('public')->download($storagePath, $filename);
    }

    public function destroy(Request $request, string $instance): JsonResponse
    {
        $instanceModel = $this->findAuthorizedInstance($request, $instance);

        if (! in_array($instanceModel->status, self::DELETABLE_STATUSES, true)) {
            throw PortalApiException::instanceNotDeletable((string) $instanceModel->status);
        }

        try {
            $this->deletionService->deleteInstance($instanceModel);
        } catch (\Throwable $e) {
            Log::error('Portal submission form instance delete failed', [
                'instance_id' => $instanceModel->id,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return response()->json([
            'message' => 'Submission deleted successfully.',
        ]);
    }

    public function submit(
        SubmitSubmissionFormInstanceRequest $request,
        string $instance
    ): JsonResponse {
        $instanceModel = $this->findAuthorizedInstance($request, $instance);

        if ($instanceModel->status !== 'draft') {
            if (in_array($instanceModel->status, ['submitted', 'Submitted'], true)) {
                $instanceModel->load(['submissionForm', 'values.element', 'crmCustomer']);

                try {
                    app(CommercialEnquiryFromFormService::class)->syncFromSubmittedInstance($instanceModel);
                } catch (\Throwable $th) {
                    Log::warning('Commercial enquiry sync failed for already-submitted portal instance.', [
                        'instance_id' => $instanceModel->id,
                        'message' => $th->getMessage(),
                    ]);
                }

                return response()->json([
                    'data' => $this->buildInstanceResponse($instanceModel, includeValues: true),
                    'message' => 'Form was already submitted.',
                ]);
            }

            throw ValidationException::withMessages([
                'action' => ['This submission has already been finalized.'],
            ]);
        }

        $submissionForm = $instanceModel->submissionForm;
        $this->access->findPortalForm($submissionForm->id, (string) $instanceModel->crm_customer_id);

        if ($request->filled('title')) {
            $instanceModel->update(['title' => $request->input('title')]);
        }

        Log::debug('Portal submit incoming', [
            'all_keys'   => array_keys($request->all()),
            'file_keys'  => array_keys($request->allFiles()),
            'files_flat' => collect($request->allFiles())->map(fn($f) => is_array($f) ? array_map(fn($i) => $i->getClientOriginalName(), $f) : $f->getClientOriginalName())->toArray(),
            'fields_raw' => $request->input('fields'),
        ]);

        $this->submissionService->mergeSubmissionFieldsIntoRequest($request);

        $elements = $this->submissionService->elementsForForm($submissionForm);
        $rules = $this->submissionService->buildValidationRules($elements, $request);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            DB::beginTransaction();

            $this->submissionService->processFormData($instanceModel, $request, $elements);
            $instanceModel = $this->submissionService->submitPortalInstance($instanceModel, $submissionForm);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Portal submission form submit failed', [
                'instance_id' => $instanceModel->id,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }

        $instanceModel->load(['submissionForm', 'values.element', 'crmCustomer']);

        try {
            app(CommercialEnquiryFromFormService::class)->syncFromSubmittedInstance($instanceModel);
        } catch (\Throwable $th) {
            Log::warning('Commercial enquiry sync failed after portal form submit.', [
                'instance_id' => $instanceModel->id,
                'message' => $th->getMessage(),
            ]);
        }

        return response()->json([
            'data' => $this->buildInstanceResponse($instanceModel, includeValues: true),
            'message' => 'Form submitted successfully.',
        ]);
    }

    private function findAuthorizedInstance(Request $request, string $instanceId): SubmissionFormInstance
    {
        $customerId = $this->resolveCustomerId($request);
        $portalAccountId = $this->access->portalAccountIdFromRequest($request);

        $instance = SubmissionFormInstance::query()
            ->with('submissionForm')
            ->where('id', $instanceId)
            ->first();

        if (! $instance || ! $instance->submissionForm) {
            throw PortalApiException::instanceNotFound();
        }

        $this->access->findPortalForm($instance->submission_form_id, $customerId);
        $this->access->assertInstanceBelongsToPortalContext($instance, $customerId, $portalAccountId);

        return $instance;
    }

    private function resolveCustomerId(Request $request): string
    {
        $customerId = $this->access->customerIdFromRequest($request);

        if ($customerId === null) {
            throw ValidationException::withMessages([
                'crm_customer_id' => ['X-CRM-Customer-Id header (or crm_customer_id) is required.'],
            ]);
        }

        return $customerId;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInstanceListItem(SubmissionFormInstance $instance): array
    {
        $payload = [
            'id' => $instance->id,
            'submission_form_id' => $instance->submission_form_id,
            'form_number' => $instance->form_number,
            'document_control_number' => $instance->getDocumentControlNumber(),
            'title' => $instance->title,
            'status' => $instance->status,
            'portal_account_id' => $instance->portal_account_id,
            'crm_customer_id' => $instance->crm_customer_id,
            'portal_request_id' => $instance->portal_request_id,
            'submitted_at' => $instance->submitted_at?->toIso8601String(),
            'created_at' => $instance->created_at?->toIso8601String(),
            'updated_at' => $instance->updated_at?->toIso8601String(),
            'can_delete' => in_array($instance->status, self::DELETABLE_STATUSES, true),
        ];

        if ($instance->relationLoaded('submissionForm') && $instance->submissionForm) {
            $payload['form'] = [
                'id' => $instance->submissionForm->id,
                'name' => $instance->submissionForm->name,
                'document_code' => $instance->submissionForm->document_code,
                'version' => $instance->submissionForm->version,
                'form_type' => $instance->submissionForm->form_type,
            ];
        }

        $enquiry = $instance->relationLoaded('sampleSubmissionRequest')
            ? $instance->sampleSubmissionRequest
            : null;

        if ($enquiry !== null) {
            $payload['commercial_enquiry'] = [
                'id' => $enquiry->id,
                'request_number' => $enquiry->request_number,
                'formatted_number' => $enquiry->formatted_number,
                'commercial_status' => $enquiry->status,
                'quotation_status' => $enquiry->status,
                'source_channel' => $enquiry->source_channel,
            ];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInstanceResponse(
        SubmissionFormInstance $instance,
        bool $includeValues = false
    ): array {
        $instance->loadMissing(['testRequestFormInstance', 'sampleSubmissionRequest']);

        $payload = [
            'id' => $instance->id,
            'submission_form_id' => $instance->submission_form_id,
            'form' => $instance->relationLoaded('submissionForm') && $instance->submissionForm
                ? $this->schemaBuilder->buildFormMeta($instance->submissionForm)
                : null,
            'form_number' => $instance->form_number,
            'document_control_number' => $instance->getDocumentControlNumber(),
            'title' => $instance->title,
            'status' => $instance->status,
            'portal_account_id' => $instance->portal_account_id,
            'crm_customer_id' => $instance->crm_customer_id,
            'portal_request_id' => $instance->portal_request_id,
            'test_request_form_instance_id' => $instance->testRequestFormInstance?->id,
            'submitted_at' => $instance->submitted_at?->toIso8601String(),
            'created_at' => $instance->created_at?->toIso8601String(),
            'updated_at' => $instance->updated_at?->toIso8601String(),
        ];

        $enquiry = $instance->sampleSubmissionRequest;
        if ($enquiry !== null) {
            $payload['commercial_enquiry'] = [
                'id' => $enquiry->id,
                'request_number' => $enquiry->request_number,
                'formatted_number' => $enquiry->formatted_number,
                'commercial_status' => $enquiry->status,
                'quotation_status' => $enquiry->status,
                'source_channel' => $enquiry->source_channel,
            ];
        }

        if (! $includeValues) {
            return $payload;
        }

        $payload['fields'] = $instance->values
            ->map(function ($value) use ($instance): array {
                $element = $value->element;
                $file = null;

                if ($value->file_path) {
                    $file = [
                        'path' => $value->file_path,
                        'url' => Storage::disk('public')->url($value->file_path),
                        'filename' => basename($value->file_path),
                    ];
                }

                return [
                    'element_id' => $value->submission_form_element_id,
                    'name' => $element?->name,
                    'type' => $element?->element_type,
                    'array_index' => $value->array_index,
                    'value' => $value->value,
                    'display_value' => $element
                        ? $instance->resolveDisplayValue(
                            $element,
                            $value->value,
                            $value->array_index !== null ? (int) $value->array_index : null,
                        )
                        : $value->getDisplayValue(),
                    'file' => $file,
                ];
            })
            ->values()
            ->all();

        $payload['submission_payload'] = $instance->submissionForm
            ? $this->schemaBuilder->submissionPayloadDocumentation($instance->submissionForm)
            : null;

        return $payload;
    }

    private function prefillPortalCustomerFields(
        SubmissionFormInstance $instance,
        \App\Models\SubmissionForm $form,
        string $customerId,
        ?string $portalAccountId
    ): void {
        $customer = CRMCustomer::query()
            ->where('id', $customerId)
            ->first(['id', 'name', 'physical_address', 'postal_address', 'telephone1', 'telephone2']);

        if ($customer === null) {
            return;
        }

        $contactId = null;
        if ($portalAccountId !== null && $portalAccountId !== '') {
            $contactId = User::query()
                ->where('id', $portalAccountId)
                ->value('crm_contact_id');
        }

        if ($contactId === null || $contactId === '') {
            $contactId = CustomerContact::query()
                ->where('crm_customer_id', $customerId)
                ->orderBy('first_name')
                ->value('id');
        }

        $defaults = [
            'customer_name' => (string) ($customer->name ?? ''),
            'customer_address' => (string) ($customer->physical_address ?: $customer->postal_address ?: ''),
            'customer_phone' => (string) ($customer->telephone1 ?? ''),
            'mobile_number' => (string) ($customer->telephone2 ?: $customer->telephone1 ?: ''),
            'contact_person' => $contactId !== null && $contactId !== '' ? (string) $contactId : null,
            'crm_contact_id' => $contactId !== null && $contactId !== '' ? (string) $contactId : null,
        ];

        $form->loadMissing('sections.elementHolders.elements');
        $elementsByName = $form->sections
            ->flatMap(fn ($section) => $section->elementHolders)
            ->flatMap(fn ($holder) => $holder->elements)
            ->keyBy('name');

        foreach ($defaults as $fieldName => $fieldValue) {
            if ($fieldValue === null || $fieldValue === '') {
                continue;
            }

            $element = $elementsByName->get($fieldName);
            if ($element === null) {
                continue;
            }

            SubmissionFormInstanceValue::query()->updateOrCreate(
                [
                    'submission_form_instance_id' => $instance->id,
                    'submission_form_element_id' => $element->id,
                    'array_index' => null,
                ],
                [
                    'value' => $fieldValue,
                    'file_path' => null,
                ]
            );
        }
    }
}
