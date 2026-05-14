<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EmailSent extends Model implements Auditable
{
	use HasUuids;

	protected $keyType = 'string';
	public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    //
}
