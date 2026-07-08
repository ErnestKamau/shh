<?php

namespace App\Http\Controllers\Registry;

use App\Actions\Registry\ApproveRegistryRequestAction;
use App\Actions\Registry\AssignRegistryRequestAction;
use App\Actions\Registry\CloseRegistryRequestAction;
use App\Actions\Registry\CreateRegistryRequestAction;
use App\Actions\Registry\EscalateRegistryRequestAction;
use App\Actions\Registry\RejectRegistryRequestAction;
use App\DTOs\Registry\ApproveRegistryRequestDTO;
use App\DTOs\Registry\AssignRegistryRequestDTO;
use App\DTOs\Registry\CreateRegistryRequestDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Registry\ApproveRegistryRequestRequest;
use App\Http\Requests\Registry\AssignRegistryRequestRequest;
use App\Http\Requests\Registry\CreateRegistryRequestRequest;
use App\Http\Requests\Registry\RejectRegistryRequestRequest;
use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestCategory;
use App\Repositories\Registry\RegistryRequestRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistryRequestController extends Controller
{
    public function __construct(
        protected RegistryRequestRepository $repository,
        protected CreateRegistryRequestAction $createAction,
        protected ApproveRegistryRequestAction $approveAction,
        protected RejectRegistryRequestAction $rejectAction,
        protected AssignRegistryRequestAction $assignAction,
        protected CloseRegistryRequestAction $closeAction,
        protected EscalateRegistryRequestAction $escalateAction,
    ) {
        $this->middleware('auth');
    }

    public function index(): View
    {
        return view('layouts.registry.requests.index');
    }

    public function create(): View
    {
        $categories = RegistryRequestCategory::query()->forCompany()->active()->orderBy('name')->get();

        return view('layouts.registry.requests.create', compact('categories'));
    }

    public function store(CreateRegistryRequestRequest $request): RedirectResponse
    {
        $registryRequest = $this->createAction->execute(
            CreateRegistryRequestDTO::fromArray($request->validated())
        );

        return redirect()
            ->route('registry.requests.show', $registryRequest->id)
            ->with('success', 'Request registered successfully.');
    }

    public function show(string $id): View
    {
        $registryRequest = $this->repository->findOrFail($id);
        $this->authorize('view', $registryRequest);

        return view('layouts.registry.requests.show', compact('registryRequest'));
    }

    public function approve(ApproveRegistryRequestRequest $request, string $id): RedirectResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('approve', $registryRequest);

        $this->approveAction->execute(new ApproveRegistryRequestDTO(
            registryRequestId: $id,
            actionName: $request->input('action_name', 'approve'),
            comment: $request->input('comment'),
            performedBy: $request->user()?->id,
        ));

        return back()->with('success', 'Request updated successfully.');
    }

    public function reject(RejectRegistryRequestRequest $request, string $id): RedirectResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('approve', $registryRequest);

        $this->rejectAction->execute($id, $request->input('comment'), $request->user()?->id);

        return back()->with('success', 'Request returned for correction.');
    }

    public function assign(AssignRegistryRequestRequest $request, string $id): RedirectResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('assign', $registryRequest);

        $this->assignAction->execute(new AssignRegistryRequestDTO(
            registryRequestId: $id,
            assignedTo: (string) $request->input('assigned_to'),
            roleContext: $request->input('role_context'),
            assignedBy: $request->user()?->id,
        ));

        return back()->with('success', 'Request assigned successfully.');
    }

    public function close(string $id): RedirectResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('update', $registryRequest);

        $this->closeAction->execute($registryRequest);

        return back()->with('success', 'Request closed.');
    }

    public function escalate(string $id): RedirectResponse
    {
        $registryRequest = RegistryRequest::query()->forCompany()->findOrFail($id);
        $this->authorize('update', $registryRequest);

        $this->escalateAction->execute($registryRequest);

        return back()->with('success', 'Request escalated.');
    }
}
