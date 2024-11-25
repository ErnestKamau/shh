<?php

namespace App\Models\SkillsMatrix;

use App\JobDescription;
use App\ModulePreConfigs;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SkillsMatrix extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = "skillsmatrices";

    protected $appends = ['jobdescription'];

    public function roles(){
        return $this->hasMany(SkillMarixRole::class,'skills_matrix_id');
    }
    public function getJobDescriptionAttribute(){
        $descriptionIDS = SkillMarixRole::where('skills_matrix_id',$this->id)->pluck('job_description_id')->toArray();
        $descriptionNames = ModulePreConfigs::where('id',$descriptionIDS)->pluck('name')->toArray();
        return ['ids'=>$descriptionIDS,"names"=>$descriptionNames];
    }
}
