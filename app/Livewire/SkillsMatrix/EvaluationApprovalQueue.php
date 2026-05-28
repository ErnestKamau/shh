<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Livewire\SkillsMatrix\Concerns\HasMatrixListFilters;
use App\Models\SkillsMatrix\TrainingEvaluation;
use App\Services\SkillsMatrix\EvaluationApprovalService;
use Livewire\Component;

class EvaluationApprovalQueue extends Component
{
    use HasMatrixFlash;
    use HasMatrixListFilters;

    public function approve(string $evaluationId, EvaluationApprovalService $approval): void
    {
        $this->authorize('skills-matrix.components.training-plan.evaluation.approve');
        $approval->approve($evaluationId);
        $this->flash('Evaluation approved and capability updated.');
    }

    public function reject(string $evaluationId, EvaluationApprovalService $approval): void
    {
        $this->authorize('skills-matrix.components.training-plan.evaluation.approve');
        $approval->reject($evaluationId);
        $this->flash('Evaluation rejected.', 'warning');
    }

    public function render()
    {
        $query = TrainingEvaluation::query()
            ->with(['attendee', 'evaluator', 'proposedProficiency', 'plannerDetail'])
            ->where('status', TrainingEvaluation::STATUS_SUBMITTED)
            ->orderByDesc('updated_at');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term): void {
                $q->whereHas('attendee', fn ($sq) => $sq->where('name', 'like', $term))
                    ->orWhereHas('evaluator', fn ($sq) => $sq->where('name', 'like', $term))
                    ->orWhere('score_notes', 'like', $term);
            });
        }

        $pending = $query->paginate($this->perPage);

        return view('livewire.skills-matrix.evaluation-approval-queue', compact('pending'));
    }
}
