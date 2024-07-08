<?php

namespace App;

use App\Models\CRM\CRMCustomer;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\ModulePreConfigs;

class Invoice extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'customer_invoice';

    public function crmCustomer(){
        return $this->belongsTo(CRMCustomer::class,'customer_id');
    }
    public function currencyinfo(){
        return $this->belongsTo(ModulePreConfigs::class,'currency_id');
    }
}
