<?php

namespace App\Models\QcModule\Data;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QcResults extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;


    protected $guarded = ['id'];
    protected $table = "qc_results";

    protected function casts(): array
    {
        return [
            'resolved_qc_rules' => 'array',
        ];
    }
}
