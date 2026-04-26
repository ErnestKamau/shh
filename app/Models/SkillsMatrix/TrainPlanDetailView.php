<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class TrainPlanDetailView extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

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
