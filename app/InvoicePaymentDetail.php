<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;

class InvoicePaymentDetail extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    
	protected $table = "invoice_payment_details";
	protected $appends =['receivername'];
	public function getReceiverNameAttribute(){
		return User::find($this->received_by)->name ?? '-';
	}
}
