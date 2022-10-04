<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MyTestUsers extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'mytestusers';

    protected $fillable = ['firstname','lastname','gender','country','age','date'];

}
