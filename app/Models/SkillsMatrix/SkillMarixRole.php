<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class SkillMarixRole extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = "skills_matrix_role";
}
