<?php

namespace App\Models\SkillsMatrix;

use App\ModulePreConfigs;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class SkillMarixRole extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = "skills_matrix_role";

    public function jobdescription(){
        return $this->belongsTo(ModulePreConfigs::class,'job_description_id');
    }
}
