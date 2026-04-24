<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\AI\AiSetting;
use App\Services\AI\AiInferenceService;
use Illuminate\Support\Facades\DB;

class KnowledgeBaseManagerController extends Controller
{
    protected $inferenceService;

    public function __construct(AiInferenceService $inferenceService)
    {
        $this->inferenceService = $inferenceService;
    }

    /**
     * Show the Knowledge Base Manager page
     */
    public function index()
    {
        $company = getActiveCompany();
        return view('imara-ai.knowledge.manager', compact('company'));
    }

    /**
     * Show the Imarachat AI Settings page
     */
    public function settings()
    {
        $company = getActiveCompany();
        return view('imara-ai.settings.index', compact('company'));
    }

}
