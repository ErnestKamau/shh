<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class TrainingDetailView extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = "skill_train_detail_view";

    public function capabilitydetail(){
        return $this->belongsTo(CapabilityMatrixDetail::class,'capability_detail_id');
    }
    public function getNeedTrainingAttribute(){
        $detailview = TrainingDetailView::find($this->id);
        $capability_users = TrainingDetailView::where('competency_id',$detailview->competency_id)->where('training_header_id',$this->training_header_id)->pluck('capability_detail_user_id')->toArray();
        return $capability_users;
    }
}
