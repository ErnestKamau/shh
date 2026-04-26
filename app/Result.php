<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class Result extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    //

    protected $guarded  =['id'];

    protected $casts = [
        'sample_detail_code' => 'encrypted',
        'analyte_code' => 'encrypted',
        'result' => 'encrypted',
        'guide' => 'encrypted',
        'comments' => 'encrypted',
        'recommendations' => 'encrypted',
        'remarks' => 'encrypted',
        'scienctific_result' => 'encrypted',
    ];

    public function captured(){
        return $this->belongsTo(CapturedResult::class,'captured_result_id');
    }
}
