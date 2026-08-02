<?php

namespace App\Http\Controllers\Api\Portal\Crm;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portal\PortalPaginatedRequest;
use App\Http\Requests\Api\Portal\StorePortalAmendmentRequest;
use App\Services\Portal\PortalAmendmentService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Transformers\PortalCrmTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AmendmentController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly PortalAmendmentService $amendments,
        private readonly PortalSubmissionFormAccess $portalAccess,
        private readonly PortalCrmTransformer $transformer,
    ) {}

    public function index(PortalPaginatedRequest $request, string $customer_id): JsonResponse
    {
        $paginator = $this->amendments->list(
            $customer_id,
            $request->page(),
            $request->perPage(),
        );

        $payload = $this->transformer->transformPaginated($paginator, 'amendments');
        $payload['in_progress_count'] = $this->amendments->countInProgress($customer_id);

        return $this->successResponse('Amendments loaded successfully', $payload);
    }

    public function eligibleReports(Request $request, string $customer_id): JsonResponse
    {
        return $this->successResponse(
            'Eligible reports loaded successfully',
            [
                'reports' => $this->amendments->eligibleReports($customer_id),
            ],
        );
    }

    public function samples(Request $request, string $customer_id, string $batchId): JsonResponse
    {
        return $this->successResponse(
            'Report samples loaded successfully',
            [
                'samples' => $this->amendments->samplesForReport($customer_id, $batchId),
            ],
        );
    }

    public function store(StorePortalAmendmentRequest $request, string $customer_id): JsonResponse
    {
        $validated = $request->validated();

        $created = $this->amendments->raise(
            $customer_id,
            (string) $validated['batch_id'],
            array_values($validated['sample_ids']),
            (string) $validated['reason'],
            $this->portalAccess->limsUserIdFromRequest($request),
            $this->portalAccess->portalAccountIdFromRequest($request),
            'portal',
        );

        return $this->successResponse(
            'Amendment raised. The batch is now in Sample Verification.',
            $created->toArray(),
            201,
        );
    }
}
