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
        'sample_detail_code' => \App\Casts\SafeEncrypted::class,
        'analyte_code' => \App\Casts\SafeEncrypted::class,
        'result' => \App\Casts\SafeEncrypted::class,
        'guide' => \App\Casts\SafeEncrypted::class,
        'comments' => \App\Casts\SafeEncrypted::class,
        'recommendations' => \App\Casts\SafeEncrypted::class,
        'remarks' => \App\Casts\SafeEncrypted::class,
        'scienctific_result' => \App\Casts\SafeEncrypted::class,
    ];

    public function captured(){
        return $this->belongsTo(CapturedResult::class,'captured_result_id');
    }
}
