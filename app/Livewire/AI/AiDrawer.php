<?php

namespace App\Livewire\AI;

use Livewire\Component;

class AiDrawer extends Component
{
    public $isOpen = false;
    public $context = 'general';
    public $currentConversationId = null;
    
    protected $listeners = ['toggleAiDrawer' => 'toggle', 'openAiDrawer' => 'open'];

    public function getIconProperty()
    {
        return match($this->context) {
            'lab' => 'flask',
            'inventory' => 'package-variant',
            'crm' => 'face-agent',
            'audit' => 'clipboard-check-multiple',
            default => 'head-snowflake',
        };
    }

    public function getWelcomeMessageProperty()
    {
        return match($this->context) {
            'lab' => 'I can help with sample tracking, TAT analysis, and lab QC metrics.',
            'inventory' => 'I can help with stock levels, reagent expiry, and supply chain data.',
            'crm' => 'I can help with client history, support tickets, and account analytics.',
            'audit' => 'I can help with CAPA tracking, finding categories, and compliance status.',
            default => 'I can help with SOPs, general queries, and LIMS navigation.',
        };
    }

    public function mount($context = null)
    {
        if ($context) {
            $this->context = $context;
        } else {
            // Auto-detect based on route name
            $routeName = optional(request()->route())->getName() ?? '';
            if (str_contains($routeName, 'lab')) {
                $this->context = 'lab';
            } elseif (str_contains($routeName, 'inventory')) {
                $this->context = 'inventory';
            } elseif (str_contains($routeName, 'crm')) {
                $this->context = 'crm';
            } elseif (str_contains($routeName, 'audit')) {
                $this->context = 'audit';
            } else {
                $this->context = 'general';
            }
        }
        $this->isOpen = false;
    }

    public function toggle()
    {
        \Illuminate\Support\Facades\Log::info('AiDrawer: toggle called, current state: ' . ($this->isOpen ? 'open' : 'closed'));
        $this->isOpen = !$this->isOpen;
    }

    public function open()
    {
        \Illuminate\Support\Facades\Log::info('AiDrawer: open called');
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    /**
     * Safe serialization for frontend usage patterns that probe $wire.toJSON.
     */
    public function toJSON(): array
    {
        return ['id' => $this->getId()];
    }

    public function render()
    {
        return view('livewire.a-i.ai-drawer');
    }
}
