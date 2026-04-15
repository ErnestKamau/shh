<?php

namespace App\Livewire\Mas;

use App\Services\AI\Repository\ReportingMartDashboardService;
use Livewire\Attributes\Url;

class LabTat extends BaseMasPage
{
    #[Url]
    public string $period = 'active';

    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function setPeriod(string $period): void
    {
        $allowed = ['active', 'week', 'month', 'year', 'lifetime'];
        if (in_array($period, $allowed, true)) {
            $this->period = $period;
            $this->loadStats();
            $this->dispatch('period-changed', stats: $this->stats);
        }
    }

    private function loadStats(): void
    {
        $this->stats = app(ReportingMartDashboardService::class)->getLabTatBoard($this->period);
    }

    public function render()
    {
        return view('livewire.mas.lab-tat', ['stats' => $this->stats]);
    }
}
