<?php

namespace App\Models\SkillsMatrix;

use App\ModulePreConfigs;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SkillMatrixDetails extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = "skills_matrix_detail";

    public function roles(){
        return $this->hasMany(SkillMatrixDetailRole::class,'matrix_detail_id')->orderBy('role_id','ASC');
    }
    public function competencyarea(){
        return $this->belongsTo(ModulePreConfigs::class,'competency_area_id');
    }
    public function competencytype(){
        return $this->belongsTo(ModulePreConfigs::class,'competency_type_id');
    }
    public function competencydescription(){
        return $this->belongsTo(ModulePreConfigs::class,'competency_description_id');
    }
    public function capabilityusers(){
        return $this->hasMany(CapabilityMatrixDetail::class,'competency_id');
    }
}
