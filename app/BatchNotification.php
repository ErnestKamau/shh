<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BatchNotification extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'batch_notifications';

	protected $casts = [
		'position_id' => 'string',
		'active' => 'boolean',
	];
}
