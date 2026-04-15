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

    /**
     * Show the Agent Personality settings page
     */
    public function agentPersonality()
    {
        $company = getActiveCompany();
        $defaults = $this->inferenceService->getSystemDefaults();
        
        $settings = [
            'system_prompt' => $this->inferenceService->getSetting('system_prompt'),
            'tone_of_voice' => $this->inferenceService->getSetting('tone_of_voice', 'Professional'),
            'agent_name'    => $this->inferenceService->getSetting('agent_name', 'Imarachat AI'),
        ];

        return view('imara-ai.settings.agent-personality', compact('company', 'settings', 'defaults'));
    }

    /**
     * Sync models with local Ollama instance
     */
    public function syncOllamaModels()
    {
        $result = $this->inferenceService->syncLocalModels();
        
        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Save Agent Personality settings
     */
    public function updateAgentPersonality(Request $request)
    {
        $validated = $request->validate([
            'system_prompt' => 'required|string',
            'tone_of_voice' => 'required|string',
            'agent_name'    => 'required|string|max:50',
        ]);

        AiSetting::set('system_prompt', $validated['system_prompt'], 'Core system instructions for the AI');
        AiSetting::set('tone_of_voice', $validated['tone_of_voice'], 'Desired tone for responses');
        AiSetting::set('agent_name', $validated['agent_name'], 'Display name of the AI agent');

        return redirect()->back()->with('success', 'Personality settings updated successfully.');
    }

    /**
     * Show the Model Configuration settings page
     */
    public function modelConfig()
    {
        $company = getActiveCompany();
        $defaults = $this->inferenceService->getSystemDefaults();

        // Get active models from registry
        $availableModels = DB::connection('pgsql_ai')
            ->table('ai.ai_model_registry')
            ->where('is_active', true)
            ->where('is_deprecated', false)
            ->get();

        // If no models available, attempt an auto-sync once
        if ($availableModels->isEmpty()) {
            $this->inferenceService->syncLocalModels();
            $availableModels = DB::connection('pgsql_ai')
                ->table('ai.ai_model_registry')
                ->where('is_active', true)
                ->where('is_deprecated', false)
                ->get();
        }

        $currentDefault = $this->inferenceService->getSetting('default_model', 'qwen2.5:3b');
        
        // Verify current default is actually available
        $isDefaultAvailable = $availableModels->contains(function($model) use ($currentDefault) {
            $fullName = $model->model_name . ($model->version ? ':' . $model->version : '');
            return $fullName === $currentDefault;
        });

        // Fallback if current default is missing
        if (!$isDefaultAvailable && !$availableModels->isEmpty()) {
            $first = $availableModels->first();
            $currentDefault = $first->model_name . ($first->version ? ':' . $first->version : '');
        }

        $settings = [
            'default_model' => $currentDefault,
            'temperature'   => $this->inferenceService->getSetting('temperature', 0.3),
            'max_tokens'    => $this->inferenceService->getSetting('max_tokens', 2048),
            'top_p'         => $this->inferenceService->getSetting('top_p', 0.9),
            'streaming'     => $this->inferenceService->getSetting('streaming', true),
        ];

        return view('imara-ai.settings.model-config', compact('company', 'availableModels', 'settings', 'defaults'));
    }

    /**
     * Save Model Configuration settings
     */
    public function updateModelConfig(Request $request)
    {
        $validated = $request->validate([
            'default_model' => 'required|string',
            'temperature'   => 'required|numeric|min:0|max:2',
            'max_tokens'    => 'required|integer|min:1|max:8192',
            'top_p'         => 'required|numeric|min:0|max:1',
            'streaming'     => 'boolean',
        ]);

        AiSetting::set('default_model', $validated['default_model'], 'Primary LLM model for chat');
        AiSetting::set('temperature', (float) $validated['temperature'], 'Controls randomness (0-2)');
        AiSetting::set('max_tokens', (int) $validated['max_tokens'], 'Maximum response length');
        AiSetting::set('top_p', (float) $validated['top_p'], 'Nucleus sampling threshold');
        AiSetting::set('streaming', (bool) ($request->streaming ?? false), 'Enable real-time token streaming');

        return redirect()->back()->with('success', 'Model configuration updated successfully.');
    }
}
