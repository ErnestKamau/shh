<?php

namespace App\Http\Controllers\Api\Portal\Crm;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portal\CompletePortalFeedbackRequest;
use App\Http\Requests\Api\Portal\PortalPaginatedRequest;
use App\Http\Requests\Api\Portal\StorePortalFeedbackRequest;
use App\Transformers\PortalCrmTransformer;
use App\Services\Portal\PortalFeedbackService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly PortalFeedbackService $feedbackService,
        private readonly PortalSubmissionFormAccess $portalAccess,
        private readonly PortalCrmTransformer $transformer,
    ) {}

    public function index(PortalPaginatedRequest $request, string $customerId): JsonResponse
    {
        $paginator = $this->feedbackService->list(
            $customerId,
            $request->page(),
            $request->perPage(),
        );

        return $this->successResponse(
            'Feedback loaded successfully',
            $this->transformer->transformPaginated($paginator, 'feedback'),
        );
    }

    public function metrics(Request $request): JsonResponse
    {
        return $this->successResponse(
            'Feedback metrics loaded',
            [
                'metrics' => array_map(
                    fn ($metric) => $metric->toArray(),
                    $this->feedbackService->metrics()
                ),
            ],
        );
    }

    public function store(StorePortalFeedbackRequest $request, string $customerId): JsonResponse
    {
        $feedback = $this->feedbackService->submitSpontaneous(
            $customerId,
            $request->validated(),
            $this->portalAccess->portalAccountIdFromRequest($request),
        );

        return $this->successResponse(
            'Feedback submitted successfully',
            $feedback->toArray(),
            201,
        );
    }

    public function complete(CompletePortalFeedbackRequest $request, string $customerId): JsonResponse
    {
        $feedback = $this->feedbackService->completeTokenRequest(
            $customerId,
            $request->validated(),
            $this->portalAccess->portalAccountIdFromRequest($request),
        );

        return $this->successResponse(
            'Feedback submitted successfully',
            $feedback->toArray(),
            201,
        );
    }
}
