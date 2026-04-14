<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class SchoolContacts extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'contact_phones';

    protected $fillable = ['name','class','reference','address'];
}
