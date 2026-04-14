<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class LabResultsExcel extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'lab_results_excel';
}
