<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\ModulePreConfigs;
use App\User;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CapabilityMatrixRoles extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = "skills_capability_matrix_role";
    protected $fillable = ['capability_id','skill_matrix_role_id','role_id','user_id','code','deleted_at'];

    public function jobdescription(){
        return $this->belongsTo(ModulePreConfigs::class,'role_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function capability(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CapabilityMatrix::class, 'capability_id');
    }
}
