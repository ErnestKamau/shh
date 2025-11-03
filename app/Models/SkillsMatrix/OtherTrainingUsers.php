<?php

namespace App\Models\SkillsMatrix;

use App\User;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class OtherTrainingUsers extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table  = "skill_other_training_users";

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    
}
