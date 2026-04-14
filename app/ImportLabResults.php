<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class ImportLabResults extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'import_lab_results';
}
