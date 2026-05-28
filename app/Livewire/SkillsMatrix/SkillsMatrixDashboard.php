<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Services\SkillsMatrix\DashboardAnalyticsService;
use App\Services\SkillsMatrix\SkillsMatrixDemoDataset;
use Livewire\Component;

class SkillsMatrixDashboard extends Component
{
    use HasMatrixFlash;

    public string $selectedCapabilityId = '';

    public function mount(): void
    {
        $demo = CapabilityMatrix::query()
            ->where('name', SkillsMatrixDemoDataset::CAPABILITY_NAME)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->value('id');

        if ($demo) {
            $this->selectedCapabilityId = (string) $demo;

            return;
        }

        $first = CapabilityMatrix::query()
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->value('id');

        $this->selectedCapabilityId = $first ? (string) $first : '';
    }

    public function updatedSelectedCapabilityId(): void
    {
        $this->dispatch('skills-matrix-charts-updated');
    }

    public function render(DashboardAnalyticsService $analytics)
    {
        $capabilities = CapabilityMatrix::where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $capId = $this->selectedCapabilityId !== '' ? $this->selectedCapabilityId : null;
        $dashboard = $analytics->buildDashboard($capId);
        $isPlaceholder = (bool) ($dashboard['is_placeholder'] ?? false);

        return view('livewire.skills-matrix.skills-matrix-dashboard', [
            'capabilities' => $capabilities,
            'dashboard' => $dashboard,
            'metrics' => $dashboard['metrics'],
            'charts' => $dashboard['charts'],
            'heatmap' => $dashboard['heatmap'],
            'capability' => $dashboard['capability'],
            'isPlaceholder' => $isPlaceholder,
        ]);
    }
}
