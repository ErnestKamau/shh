<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Model;

class TrainPlanDetailView extends Model
{
    protected $table = "skill_train_plan_detail_view";
    public function trainneeddetail(){
        return $this->belongsTo(TrainingDetail::class,'training_need_detail_id');
    }
    public function capabilitydetail(){
        return $this->belongsTo(CapabilityMatrixDetail::class,'capability_detail_id');
    }
    public function competency(){
        return $this->belongsTo(SkillMatrixDetails::class,'competency_id');
    }
}
