<?php

namespace App;

use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationHeader extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'quotation_headers';
    protected $appends = ['creator'];

    public function getCreatorAttribute(){
        return User::find($this->prepared_by_id)->name ?? '-';
    }
    public function details(){
        return $this->hasMany(QuotationDetails::class,'quotation_header_id');
    }
    public function contact(){
        return $this->belongsTo(CustomerContact::class,'crm_customer_contact_id');
    }
    public function customer(){
        return $this->belongsTo(CRMCustomer::class,'crm_customer_id');
    }
   
}
