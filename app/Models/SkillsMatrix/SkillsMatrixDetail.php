<?php

namespace App\Models\SkillsMatrix;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class SkillsMatrixDetail extends Model implements Auditable
{
    use HasUuids;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'skills_matrix_detail';
}
