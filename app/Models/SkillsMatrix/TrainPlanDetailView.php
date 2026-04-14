<?php

namespace App\Models\SkillsMatrix;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class TrainPlanDetailView extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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
