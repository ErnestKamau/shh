<?php

namespace App\Http\Controllers\Registry;

use App\Http\Controllers\Controller;
use App\Repositories\Registry\WorkflowRepository;
use Illuminate\View\View;

class RegistryWorkflowController extends Controller
{
    public function __construct(
        protected WorkflowRepository $workflowRepository,
    ) {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $definitions = $this->workflowRepository->getActiveDefinitions();

        return view('layouts.registry.config.workflows', compact('definitions'));
    }
}
