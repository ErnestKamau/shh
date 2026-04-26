<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\ModulePreConfigs;
use App\User;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class SkillMarixRole extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;
    protected $table = "skills_matrix_role";

    public function jobdescription(){
        return $this->belongsTo(ModulePreConfigs::class,'job_description_id');
    }
    public function users(){
        return $this->hasMany(User::class, 'position', 'job_description_id');
    }
}
