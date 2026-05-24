<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\ModulePreConfigs;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CapabilityMatrixDetail extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;
    protected $table = "skill_capability_detail";

    protected $fillable = [
        'capability_id',
        'competency_id',
        'user_id',
        'proficiency_id',
        'skill_matrix_role_id',
        'deleted_at',
    ];

    public function competency(){
        return $this->belongsTo(SkillMatrixDetails::class,'competency_id');
    }
    public function skillproficiency(){
        return SkillMatrixDetailRole::with('proficiency')->where('matrix_detail_id',$this->competency_id)->where('matrix_role_id',$this->skill_matrix_role_id)->first();
    }
    public function proficiency(){
        return $this->belongsTo(ModulePreConfigs::class,'proficiency_id');
    }
}
