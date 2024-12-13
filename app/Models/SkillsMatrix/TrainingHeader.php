<?php

namespace App\Models\SkillsMatrix;

use App\User;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TrainingHeader extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = "skill_training_header";

    protected $appends = ['users'];

    protected $fillable = ['deleted_at'];

    public function creator(){
        return $this->belongsTo(User::class,'created_by');
    }

    public function capability(){
        return $this->belongsTo(CapabilityMatrix::class,'capability_id');
    }
    public function details(){
        return $this->hasMany(TrainingDetailView::class,'training_header_id')->groupBy('competency_id');
    }
    public function getUsersAttribute(){
        $capability_details_user_ids  =TrainingHeaderStaff::where('training_header_id',$this->id)->pluck('capability_matrix_role_id')->toArray();
        $users = CapabilityMatrixRoles::with(['user','jobdescription'])->whereIn('id',$capability_details_user_ids)->get();
        return $users;
    }
}
