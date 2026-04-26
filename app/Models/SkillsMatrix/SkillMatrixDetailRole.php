<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\ModulePreConfigs;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SkillMatrixDetailRole extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;
    protected $table = "skills_matrix_detail_role";
    protected $fillable = ['matrix_detail_id','role_id','matrix_role_id','proficiency_id'];

    public function role(){
        return $this->belongsTo(ModulePreConfigs::class,'role_id');
    }
    public function proficiency(){
        return $this->belongsTo(ModulePreConfigs::class,'proficiency_id');
    }

    
}
