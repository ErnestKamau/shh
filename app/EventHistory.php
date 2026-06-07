<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EventHistory extends Model implements Auditable
{
    use HasUuids;
	use \OwenIt\Auditing\Auditable;
    protected $table = 'event_history';
}
