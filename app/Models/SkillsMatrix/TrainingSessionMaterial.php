<?php

namespace App\Models\SkillsMatrix;

use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class TrainingSessionMaterial extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'skills_training_session_materials';

    protected $fillable = [
        'training_planner_detail_id',
        'uploaded_by',
        'original_name',
        'storage_path',
        'mime',
        'size',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function plannerDetail(): BelongsTo
    {
        return $this->belongsTo(TrainingPlannerDetails::class, 'training_planner_detail_id');
    }
}
