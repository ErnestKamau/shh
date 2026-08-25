<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ViewRequestEntity extends RequestEntity implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	protected $table = 'view_request_entities';

	/**
	 * Use the view's stored net_value; avoid parent accessor N+1 on request_entity_items.
	 */
	public function getNetValueAttribute($value = null)
	{
		return $this->attributes['net_value'] ?? $value ?? 0;
	}
}
