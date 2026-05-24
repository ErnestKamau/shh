<?php

namespace App\Services\SkillsMatrix;

use App\Models\SkillsMatrix\TrainingEvaluation;
use App\Models\SkillsMatrix\TrainingPlannerDetails;
use App\Models\SkillsMatrix\TrainingSessionAttendance;
use App\Models\SkillsMatrix\TrainingSessionMaterial;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrainingSessionService
{
    /**
     * @return Collection<int, TrainingSessionAttendance>
     */
    public function attendanceForSession(string $plannerDetailId): Collection
    {
        return TrainingSessionAttendance::with('user')
            ->where('training_planner_detail_id', $plannerDetailId)
            ->orderBy('created_at')
            ->get();
    }

    public function inviteAttendee(string $plannerDetailId, string $userId): TrainingSessionAttendance
    {
        return TrainingSessionAttendance::firstOrCreate(
            [
                'training_planner_detail_id' => $plannerDetailId,
                'user_id' => $userId,
            ],
            ['invited_at' => now()]
        );
    }

    public function confirmAttendance(string $attendanceId, ?string $userId = null): void
    {
        $attendance = TrainingSessionAttendance::findOrFail($attendanceId);
        $actorId = $userId ?? (string) Auth::id();

        if ((string) $attendance->user_id !== $actorId && ! Auth::user()->can('skills-matrix.components.training-plan.attend.manage')) {
            abort(403);
        }

        $attendance->update([
            'confirmed_at' => now(),
            'confirmed_by_user_id' => $actorId,
        ]);
    }

    public function markPresent(string $attendanceId): void
    {
        $attendance = TrainingSessionAttendance::findOrFail($attendanceId);
        $attendance->update([
            'marked_present_at' => now(),
            'marked_by_user_id' => Auth::id(),
        ]);
    }

    public function uploadMaterial(string $plannerDetailId, UploadedFile $file): TrainingSessionMaterial
    {
        $path = $file->store('skills-matrix/training/'.$plannerDetailId, 'local');

        return TrainingSessionMaterial::create([
            'training_planner_detail_id' => $plannerDetailId,
            'uploaded_by' => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * @return Collection<int, TrainingSessionMaterial>
     */
    public function materialsForSession(string $plannerDetailId): Collection
    {
        return TrainingSessionMaterial::query()
            ->where('training_planner_detail_id', $plannerDetailId)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();
    }

    public function deleteMaterial(string $materialId): void
    {
        TrainingSessionMaterial::where('id', $materialId)->update(['deleted_at' => now()]);
    }

    public function downloadMaterial(string $materialId): StreamedResponse
    {
        $material = TrainingSessionMaterial::whereNull('deleted_at')->findOrFail($materialId);

        return Storage::disk('local')->download(
            $material->storage_path,
            $material->original_name
        );
    }

    public function saveEvaluation(
        string $plannerDetailId,
        string $userId,
        ?string $competencyId,
        string $proposedProficiencyId,
        ?string $notes,
        bool $submit = false,
    ): TrainingEvaluation {
        $evaluation = TrainingEvaluation::firstOrNew([
            'training_planner_detail_id' => $plannerDetailId,
            'user_id' => $userId,
            'competency_id' => $competencyId,
        ]);

        $evaluation->evaluator_id = Auth::id();
        $evaluation->proposed_proficiency_id = $proposedProficiencyId;
        $evaluation->score_notes = $notes;
        $evaluation->status = $submit ? TrainingEvaluation::STATUS_SUBMITTED : TrainingEvaluation::STATUS_DRAFT;
        $evaluation->save();

        return $evaluation;
    }

    /**
     * @return Collection<int, TrainingEvaluation>
     */
    public function pendingApprovals(): Collection
    {
        return TrainingEvaluation::with(['attendee', 'proposedProficiency', 'plannerDetail'])
            ->where('status', TrainingEvaluation::STATUS_SUBMITTED)
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function activeStaffOptions(): Collection
    {
        return User::query()->where('active', 1)->orderBy('name')->get();
    }
}
