<?php

namespace App\Models\SkillsMatrix;

use App\ModulePreConfigs;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class TrainingEvaluation extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'skills_training_evaluations';

    protected $fillable = [
        'training_planner_detail_id',
        'user_id',
        'competency_id',
        'evaluator_id',
        'proposed_proficiency_id',
        'score_notes',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function proposedProficiency(): BelongsTo
    {
        return $this->belongsTo(ModulePreConfigs::class, 'proposed_proficiency_id');
    }

    public function plannerDetail(): BelongsTo
    {
        return $this->belongsTo(TrainingPlannerDetails::class, 'training_planner_detail_id');
    }
}
