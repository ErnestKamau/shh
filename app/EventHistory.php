<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EventHistory extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'event_history';
}
