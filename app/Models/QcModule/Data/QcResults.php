<?php

namespace App\Models\QcModule\Data;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QcResults extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;


    protected $guarded = ['id'];
    protected $table = "qc_results"; 
    
    
}
