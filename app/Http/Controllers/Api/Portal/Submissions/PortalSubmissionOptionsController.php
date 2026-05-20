<?php

namespace App\Http\Controllers\Api\Portal\Submissions;

use App\Http\Controllers\Controller;
use App\Services\SubmissionForm\PortalDynamicOptionsService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalSubmissionOptionsController extends Controller
{
    public function __construct(
        private readonly PortalSubmissionFormAccess $access,
        private readonly PortalDynamicOptionsService $optionsService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $crmCustomerId = $this->access->customerIdFromRequest($request);
        $payload = $this->optionsService->resolve($request, $crmCustomerId);

        return response()->json($payload);
    }
}
