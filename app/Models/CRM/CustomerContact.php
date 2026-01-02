<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustomerContact extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = "crm_customer_contacts";
    
    protected $fillable = [
		'first_name',
		'middle_name',
		'last_name',
		'job_occupation',
		'unit_name',
		'email',
		'telephone',
		'mobile',
		'receive_price_list',
		'receive_invoice',
		'receive_report',
		'company_id',
		'crm_customer_id',
		'active',
		'can_login',
		'can_submit_sample',
		'signature',
		'title_id'
	];
}
