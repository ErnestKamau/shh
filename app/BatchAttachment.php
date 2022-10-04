<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BatchAttachment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'batch_attachments';
}
