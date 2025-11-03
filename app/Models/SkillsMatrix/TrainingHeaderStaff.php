<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TrainingHeaderStaff extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = "skill_training_header_staff";
}
