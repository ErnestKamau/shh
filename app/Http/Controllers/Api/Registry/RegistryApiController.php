<?php

namespace App\Http\Controllers\Api\Registry;

use App\Actions\Registry\ApproveRegistryRequestAction;
use App\Actions\Registry\AssignRegistryRequestAction;
use App\Actions\Registry\CreateRegistryRequestAction;
use App\Actions\Registry\RejectRegistryRequestAction;
use App\Actions\Registry\UploadRegistryDocumentAction;
use App\DTOs\Registry\ApproveRegistryRequestDTO;
use App\DTOs\Registry\AssignRegistryRequestDTO;
use App\DTOs\Registry\CreateRegistryRequestDTO;
use App\DTOs\Registry\RegistryFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Registry\ApproveRegistryRequestRequest;
use App\Http\Requests\Registry\AssignRegistryRequestRequest;
use App\Http\Requests\Registry\CreateRegistryRequestRequest;
use App\Http\Requests\Registry\RejectRegistryRequestRequest;
use App\Http\Requests\Registry\UploadRegistryDocumentRequest;
use App\Http\Resources\Registry\RegistryRequestResource;
use App\Models\Registry\RegistryRequest;
use App\Repositories\Registry\RegistryRequestRepository;
use App\Services\Registry\RegistryDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistryApiController extends Controller
{
    public function __construct(
        protected RegistryRequestRepository $repository,
        protected RegistryDashboardService $dashboardService,
        protected CreateRegistryRequestAction $createAction,
        protected ApproveRegistryRequestAction $approveAction,
        protected RejectRegistryRequestAction $rejectAction,
        protected AssignRegistryRequestAction $assignAction,
        protected UploadRegistryDocumentAction $uploadAction,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request): JsonResponse
    {
        $filter = RegistryFilterDTO::fromArray($request->all());
        $paginator = $this->repository->paginate($filter, (int) $request->input('per_page', 15));

        return RegistryRequestResource::collection($paginator)->response();
    }

    public function store(CreateRegistryRequestRequest $request): JsonResponse
    {
        $registryRequest = $this->createAction->execute(
            CreateRegistryRequestDTO::fromArray($request->validated())
        );

        return (new RegistryRequestResource($registryRequest->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): JsonResponse
    {
        $registryRequest = $this->repository->findOrFail($id);
        $this->authorize('view', $registryRequest);

        return (new RegistryRequestResource($registryRequest))->response();
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('update', $registryRequest);

        $registryRequest->update($request->only([
            'subject', 'description', 'priority', 'metadata',
        ]));

        return (new RegistryRequestResource($registryRequest->fresh()))->response();
    }

    public function approve(ApproveRegistryRequestRequest $request, string $id): JsonResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('approve', $registryRequest);

        $updated = $this->approveAction->execute(new ApproveRegistryRequestDTO(
            registryRequestId: $id,
            comment: $request->input('comment'),
            performedBy: $request->user()?->id,
        ));

        return (new RegistryRequestResource($updated))->response();
    }

    public function reject(RejectRegistryRequestRequest $request, string $id): JsonResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('approve', $registryRequest);

        $updated = $this->rejectAction->execute($id, $request->input('comment'), $request->user()?->id);

        return (new RegistryRequestResource($updated))->response();
    }

    public function assign(AssignRegistryRequestRequest $request, string $id): JsonResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('assign', $registryRequest);

        $updated = $this->assignAction->execute(new AssignRegistryRequestDTO(
            registryRequestId: $id,
            assignedTo: (string) $request->input('assigned_to'),
            roleContext: $request->input('role_context'),
            assignedBy: $request->user()?->id,
        ));

        return (new RegistryRequestResource($updated))->response();
    }

    public function uploadDocument(UploadRegistryDocumentRequest $request, string $id): JsonResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('uploadDocument', $registryRequest);

        $document = $this->uploadAction->execute($registryRequest, $request->file('file'));

        return response()->json([
            'document_id' => $document->id,
            'version' => $document->version,
            'original_name' => $document->original_name,
        ], 201);
    }

    public function statistics(): JsonResponse
    {
        return response()->json($this->dashboardService->getDashboardData());
    }
}
