<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentAttachment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'equipment_attachments';
}
