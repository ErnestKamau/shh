<?php

namespace App;

use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationHeader extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'quotation_headers';

    protected $guarded = ['id'];
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
    
    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }
   
}
