<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TrainingPlannerDetails extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;
    protected $table = "skills_training_planner_detail";

    protected $appends = ['otheruserids'];

    protected $fillable = [
        'training_plan_header_id',
        'training_need_detail_id',
        'training_start_date',
        'training_end_date',
        'status',
        'week_no',
        'organizer_trainer',
        'remark',
        'other_competency',
        'is_others',
    ];

    public function trainingdetail(){
        return $this->belongsTo(TrainingDetailView::class,'training_need_detail_id');
    }

    public function trainingNeedDetail(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TrainingDetail::class, 'training_need_detail_id');
    }
    public function otheruser(){
        return $this->hasMany(OtherTrainingUsers::class,'train_plan_detail_id');
    }
    public function getOtherUserIdsAttribute(){
        return OtherTrainingUsers::where('train_plan_detail_id',$this->id)->pluck('user_id')->toArray();
    }

    public function attendanceRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrainingSessionAttendance::class, 'training_planner_detail_id');
    }

    public function materials(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrainingSessionMaterial::class, 'training_planner_detail_id');
    }

    public function evaluations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrainingEvaluation::class, 'training_planner_detail_id');
    }
}
