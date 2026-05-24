<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\User;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\SkillsMatrix\TrainingHeader;

class TrainingPlannerHeader extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;
    protected $table = "skills_training_planner_header";

    protected $fillable = ['name', 'training_need_header_id', 'created_by', 'deleted_at'];

    public function trainneed(){
        return $this->belongsTo(TrainingHeader::class,'training_need_header_id');
    }
    public function details(){
        return $this->hasMany(TrainingPlannerDetails::class,'training_plan_header_id')->where('is_others',0);
    }
    public function groupdetails(){
        return $this->hasMany(TrainPlanDetailView::class,'training_plan_header_id')->where('is_others',0)->groupBy('competency_id');
    }
    public function others(){
        return $this->hasMany(TrainingPlannerDetails::class,'training_plan_header_id')->where('is_others',1);
    }
    public function creator(){
        return $this->belongsTo(User::class,'created_by');
    }
}
