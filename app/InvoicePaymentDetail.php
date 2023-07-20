<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;

class InvoicePaymentDetail extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    
	protected $table = "invoice_payment_details";
	protected $appends =['receivername'];
	public function getReceiverNameAttribute(){
		return User::find($this->received_by)->name ?? '-';
	}
}
