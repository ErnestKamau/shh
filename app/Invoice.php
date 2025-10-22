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
    protected $appends = ['batchcodes','samplecodes','invoicetotal'];

    protected $fillable = ['deleted_at','delete_reason'];

    public function crmCustomer(){
        return $this->belongsTo(CRMCustomer::class,'customer_id');
    }
    public function currencyinfo(){
        return $this->belongsTo(\App\Models\Currency::class,'currency_id');
    }
    
    public function currency(){
        return $this->belongsTo(\App\Models\Currency::class,'currency_id');
    }
    public function getbatchcodesAttribute(){
        return SampleHeader::where('invoice_id',$this->id)->pluck('batch_code')->toArray();
    }
    public function getsamplecodesAttribute(){
        $ids = SampleHeader::where('invoice_id',$this->id)->pluck('id')->toArray();
        return SampleDetails::whereIn('sample_header_id',$ids)->pluck('sample_code')->toArray();
    }
    public function getInvoiceTotalAttribute(){
        return InvoiceDetails::where('invoice_id',$this->id)->sum('total');
    }
    public function details(){
        return $this->hasMany(InvoiceDetails::class,'invoice_id');
    }
}
