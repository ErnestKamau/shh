<?php

namespace App\Livewire\SkillsMatrix;

use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\TrainingHeader;
use App\Services\SkillsMatrix\GapAnalysisService;
use App\Services\SkillsMatrix\MatrixQueryService;
use Illuminate\Support\Collection;
use Livewire\Component;

class TrainingNeedsGrid extends Component
{
    public string $trainingNeedId;

    public function mount(string $trainingNeedId): void
    {
        $this->trainingNeedId = $trainingNeedId;
    }

    public function render(GapAnalysisService $gapAnalysis, MatrixQueryService $matrixQuery)
    {
        $header = TrainingHeader::with('capability')->findOrFail($this->trainingNeedId);
        $capability = CapabilityMatrix::findOrFail($header->capability_id);
        $roles = $matrixQuery->activeCapabilityRoles($capability->id);
        $analysis = $gapAnalysis->analyzeCapability($capability);

        /** @var Collection<string, Collection<int, array{competency: string, area: string, gaps: array<int, array<string, mixed>>}>> $grouped */
        $grouped = collect($analysis['rows'])
            ->groupBy(fn (array $row): string => $row['area'] !== '' ? $row['area'] : 'Other');

        return view('livewire.skills-matrix.training-needs-grid', compact(
            'header',
            'analysis',
            'capability',
            'roles',
            'grouped',
        ));
    }
}
