<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TrainingDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "skills_training_detail";

    protected $fillable = ['training_header_id','capability_detail_id','skill_matrix_role_proficiency_id','require_training','deleted_at'];

    protected $appends = ['needtraining'];

    public function capabilitydetail(){
        return $this->belongsTo(CapabilityMatrixDetail::class,'capability_detail_id');
    }
    public function getNeedTrainingAttribute(){
        $detailview = TrainingDetailView::find($this->id);
        $capability_users = TrainingDetailView::where('competency_id',$detailview->competency_id)->where('training_header_id',$this->training_header_id)->pluck('capability_detail_user_id')->toArray();
        return $capability_users;
    }
}
