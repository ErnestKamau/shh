<?php

namespace App\Models\QcModule\Configurations;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QcSchemes extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [];
    protected $table = "qc_scheme";
   
}
