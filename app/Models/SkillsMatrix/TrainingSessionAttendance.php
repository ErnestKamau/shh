<?php

namespace App\Models\SkillsMatrix;

use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class TrainingSessionAttendance extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'skills_training_session_attendance';

    protected $fillable = [
        'training_planner_detail_id',
        'user_id',
        'invited_at',
        'confirmed_at',
        'marked_present_at',
        'confirmed_by_user_id',
        'marked_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'marked_present_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plannerDetail(): BelongsTo
    {
        return $this->belongsTo(TrainingPlannerDetails::class, 'training_planner_detail_id');
    }
}
