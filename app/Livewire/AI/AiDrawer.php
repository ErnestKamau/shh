<?php

namespace App\Livewire\AI;

use Livewire\Component;

class AiDrawer extends Component
{
    public $isOpen = false;
    public $context = 'general';
    public $currentConversationId = null;

    protected $listeners = ['toggleAiDrawer' => 'toggle', 'openAiDrawer' => 'open'];

    public function mount($context = 'general')
    {
        $this->context = $context;
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

    public function render()
    {
        return view('livewire.a-i.ai-drawer');
    }
}
