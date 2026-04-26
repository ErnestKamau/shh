<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\User;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CapabilityMatrix extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;
    
    protected $table = "skills_capability_matrix";

    protected $fillable = ['deleted_at'];

    public function roles(){
        return $this->hasMany(CapabilityMatrixRoles::class,'capability_id')->whereNull('deleted_at');
    }
    public function grouproles(){
        return $this->hasMany(CapabilityMatrixRoles::class,'capability_id');
    }
    public function skillmatrix(){
        return $this->belongsTo(SkillsMatrix::class,'matrix_id');
    }
    public function creator(){
        return $this->belongsTo(User::class,'created_by');
    }
}
