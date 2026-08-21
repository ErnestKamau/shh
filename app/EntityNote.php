<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EntityNote extends Model implements Auditable
{
	use HasUuids;
	use \OwenIt\Auditing\Auditable;

	protected $keyType = 'string';

	public $incrementing = false;
}
