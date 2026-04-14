<?php

namespace App\Models\Lab;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class TatCapturedView extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "tat_captured_view";
    
}
