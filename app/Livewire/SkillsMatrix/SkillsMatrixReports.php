<?php

namespace App\Livewire\SkillsMatrix;

use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\SkillsMatrix;
use App\Models\SkillsMatrix\TrainingPlannerHeader;
use Livewire\Component;

class SkillsMatrixReports extends Component
{
    public function render()
    {
        return view('livewire.skills-matrix.skills-matrix-reports', [
            'matrixCount' => SkillsMatrix::count(),
            'capabilityCount' => CapabilityMatrix::whereNull('deleted_at')->count(),
            'planCount' => TrainingPlannerHeader::whereNull('deleted_at')->count(),
        ]);
    }
}
