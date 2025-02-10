<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SchoolContacts extends Model
{
    protected $table = 'contact_phones';

    protected $fillable = ['name','class','reference','address'];
}
