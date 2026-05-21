<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\DashboardCustomerRequest;
use App\Http\Requests\Api\Dashboard\DashboardPaginatedRequest;
use App\Http\Resources\Dashboard\AnalyticsResource;
use App\Http\Resources\Dashboard\DashboardResource;
use App\Services\Dashboard\DashboardService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Transformers\DashboardTransformer;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly PortalSubmissionFormAccess $portalAccess,
        private readonly DashboardTransformer $transformer,
    ) {}

    public function show(DashboardCustomerRequest $request, string $customerId): JsonResponse
    {
        $dashboard = $this->dashboardService->getDashboard(
            $customerId,
            $this->portalAccess->portalAccountIdFromRequest($request),
        );

        return $this->successResponse(
            'Dashboard loaded successfully',
            (new DashboardResource($dashboard))->resolve(),
        );
    }

    public function analytics(DashboardCustomerRequest $request, string $customerId): JsonResponse
    {
        $analytics = $this->dashboardService->getAnalytics(
            $customerId,
            $this->portalAccess->portalAccountIdFromRequest($request),
            queueRecalculation: $request->boolean('refresh'),
        );

        return $this->successResponse(
            'Dashboard analytics loaded successfully',
            (new AnalyticsResource($analytics))->resolve(),
        );
    }

    public function notifications(DashboardPaginatedRequest $request, string $customerId): JsonResponse
    {
        $paginator = $this->dashboardService->getNotifications(
            $customerId,
            $request->page(),
            $request->perPage(),
            $this->portalAccess->portalAccountIdFromRequest($request),
        );

        return $this->successResponse(
            'Notifications loaded successfully',
            $this->transformer->transformPaginated($paginator, 'notifications'),
        );
    }

    public function reports(DashboardPaginatedRequest $request, string $customerId): JsonResponse
    {
        $paginator = $this->dashboardService->getReports(
            $customerId,
            $request->page(),
            $request->perPage(),
            $this->portalAccess->portalAccountIdFromRequest($request),
        );

        return $this->successResponse(
            'Reports loaded successfully',
            $this->transformer->transformPaginated($paginator, 'reports'),
        );
    }

    public function complaints(DashboardPaginatedRequest $request, string $customerId): JsonResponse
    {
        $paginator = $this->dashboardService->getComplaints(
            $customerId,
            $request->page(),
            $request->perPage(),
            $this->portalAccess->portalAccountIdFromRequest($request),
        );

        return $this->successResponse(
            'Complaints loaded successfully',
            $this->transformer->transformPaginated($paginator, 'complaints'),
        );
    }
}
