<?php

namespace App\Livewire\Mas;

use Illuminate\Support\Facades\DB;

class Audit extends BaseMasPage
{
    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function refresh(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $this->stats = [
            'recent_audits' => DB::table('audits')->orderByDesc('created_at')->limit(50)->get(),
            'total_events'  => DB::table('audits')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.mas.audit', ['stats' => $this->stats]);
    }
}
