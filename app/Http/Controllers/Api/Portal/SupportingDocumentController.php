<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Portal\SupportingDocumentInstanceResource;
use App\Support\SupportingDocumentElementValidation;
use App\Http\Resources\Portal\SupportingDocumentTemplateDetailResource;
use App\Http\Resources\Portal\SupportingDocumentTemplateResource;
use App\Models\SupportingDocumentInstance;
use App\Models\SupportingDocumentInstanceValue;
use App\Models\SupportingDocumentTemplate;
use App\SampleHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportingDocumentController extends Controller
{
    /**
     * GET /api/portal/supporting-document-templates
     * List all published + active templates available in the portal.
     */
    public function templates(Request $request)
    {
        $user = $request->user();

        $query = SupportingDocumentTemplate::query()
            ->where('is_published', true)
            ->where('is_active', true)
            ->orderBy('title');

        if (! empty($user->company_id)) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('company_id')
                    ->orWhere('company_id', $user->company_id);
            });
        }

        return SupportingDocumentTemplateResource::collection($query->get());
    }

    /**
     * GET /api/portal/supporting-document-templates/{template}
     * Returns template structure for rendering (sections + elements).
     */
    public function template(Request $request, int $template)
    {
        $user = $request->user();

        $tpl = SupportingDocumentTemplate::with(['sections' => function ($q) {
            $q->orderBy('sort_order')
                ->with(['elements' => function ($q) {
                    $q->orderBy('sort_order');
                }]);
        }])
            ->where('id', $template)
            ->where('is_published', true)
            ->where('is_active', true)
            ->first();

        if (! $tpl) {
            return response()->json(['message' => 'Supporting document template not found.'], 404);
        }

        if (! empty($user->company_id) && ! empty($tpl->company_id) && (int) $tpl->company_id !== (int) $user->company_id) {
            return response()->json(['message' => 'Supporting document template not found.'], 404);
        }

        return new SupportingDocumentTemplateDetailResource($tpl);
    }

    /**
     * GET /api/portal/test-requests/{id}/supporting-documents
     * List submitted/draft instances for a test request.
     */
    public function instancesForTestRequest(Request $request, int $id)
    {
        $user       = $request->user();
        $customerId = (int) $user->client_id;

        $batch = SampleHeader::where('id', $id)
            ->where('crm_customer_id', $customerId)
            ->first();

        if (! $batch) {
            return response()->json(['message' => 'Test request not found.'], 404);
        }

        $instances = SupportingDocumentInstance::query()
            ->where('sample_header_id', $batch->id)
            ->orderByDesc('created_at')
            ->get();

        return SupportingDocumentInstanceResource::collection($instances);
    }

    /**
     * POST /api/portal/test-requests/{id}/supporting-documents/submit
     * Create a submitted instance for a template and persist values.
     */
    public function submitForTestRequest(Request $request, int $id)
    {
        $user       = $request->user();
        $customerId = (int) $user->client_id;

        $batch = SampleHeader::where('id', $id)
            ->where('crm_customer_id', $customerId)
            ->first();

        if (! $batch) {
            return response()->json(['message' => 'Test request not found.'], 404);
        }

        $validated = $request->validate([
            'template_id' => 'required|integer|exists:supporting_document_templates,id',
            'values' => 'required|array',
        ]);

        $template = SupportingDocumentTemplate::with(['sections.elements'])
            ->where('id', (int) $validated['template_id'])
            ->where('is_published', true)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return response()->json(['message' => 'Supporting document template not found.'], 404);
        }

        $elements = $template->sections->flatMap(function ($section) {
            return $section->elements;
        })->keyBy('id');

        $rules = [];
        foreach ($elements as $element) {
            if (empty($element->name) || $element->element_type === 'static_text') {
                continue;
            }

            $rules['values.' . $element->id] = SupportingDocumentElementValidation::rulesForElement($element);
        }

        $request->validate($rules);

        $instance = DB::transaction(function () use ($validated, $template, $batch, $user, $elements, $request) {
            $instance = SupportingDocumentInstance::create([
                'supporting_document_template_id' => $template->id,
                'template_version' => (int) $template->version,
                'sample_header_id' => (int) $batch->id,
                'status' => 'submitted',
                'submitted_at' => now(),
                'created_by' => $user->id,
            ]);

            $values = $validated['values'];
            foreach ($elements as $element) {
                if (! array_key_exists($element->id, $values)) {
                    continue;
                }

                SupportingDocumentInstanceValue::create([
                    'supporting_document_instance_id' => $instance->id,
                    'supporting_document_element_id' => $element->id,
                    'value' => is_array($values[$element->id]) ? json_encode($values[$element->id]) : (string) $values[$element->id],
                ]);
            }

            return $instance;
        });

        $instance->load('values');

        return (new SupportingDocumentInstanceResource($instance))
            ->response()
            ->setStatusCode(201);
    }
}
