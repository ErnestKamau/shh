<?php

namespace App\Models\Inspection;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InspectionDetail extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;
    
    protected $table = "inspection_detail";

    protected $fillable = ['deleted_at','delete_reason','deleted_by'];
}
