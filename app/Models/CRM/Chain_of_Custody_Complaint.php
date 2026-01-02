<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Chain_of_Custody_Complaint extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = 'chain_of_custody_complaints';

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'action_taker_id');
    }
}
