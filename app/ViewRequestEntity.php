<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ViewRequestEntity extends RequestEntity implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = 'view_request_entities';

}
