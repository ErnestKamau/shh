<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Complaint extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    public function customer()
    {
        return $this->belongsTo(CRMCustomer::class, 'client_id');
    }

    public function chainOfCustody()
    {
        return $this->hasMany(Chain_of_Custody_Complaint::class, 'complaint_id');
    }
}
