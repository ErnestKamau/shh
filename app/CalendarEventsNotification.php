<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CalendarEventsNotification extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'calendarevents_notifications';
}
