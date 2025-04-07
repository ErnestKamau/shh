<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Result extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    //

    protected $guarded  =['id'];

    public function captured(){
        return $this->belongsTo(CapturedResult::class,'captured_result_id');
    }
}
