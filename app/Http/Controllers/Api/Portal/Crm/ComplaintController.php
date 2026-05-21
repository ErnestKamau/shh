<?php

namespace App\Http\Controllers\Api\Portal\Crm;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portal\PortalPaginatedRequest;
use App\Http\Requests\Api\Portal\StorePortalComplaintRequest;
use App\Services\Portal\PortalComplaintService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Transformers\PortalCrmTransformer;
use Illuminate\Http\JsonResponse;

class ComplaintController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly PortalComplaintService $complaintService,
        private readonly PortalSubmissionFormAccess $portalAccess,
        private readonly PortalCrmTransformer $transformer,
    ) {}

    public function types(): JsonResponse
    {
        $types = array_map(
            fn ($type) => $type->toArray(),
            $this->complaintService->types(),
        );

        return $this->successResponse(
            'Complaint types loaded',
            ['types' => $types],
        );
    }

    public function index(PortalPaginatedRequest $request, string $customerId): JsonResponse
    {
        $paginator = $this->complaintService->list(
            $customerId,
            $request->page(),
            $request->perPage(),
        );

        return $this->successResponse(
            'Complaints loaded successfully',
            $this->transformer->transformPaginated($paginator, 'complaints'),
        );
    }

    public function store(StorePortalComplaintRequest $request, string $customerId): JsonResponse
    {
        $created = $this->complaintService->create(
            $customerId,
            $request->validated(),
            $this->portalAccess->portalAccountIdFromRequest($request),
        );

        return $this->successResponse(
            'Complaint submitted successfully',
            $created->toArray(),
            201,
        );
    }
}
