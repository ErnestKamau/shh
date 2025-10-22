<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationDetails extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'quotation_details';
    
    protected $fillable = [
        'quotation_header_id',
        'invoicable_item_id',
        'analyte_id',
        'quantity',
        'part_no',
        'unit_price',
        'tax',
        'sample_type',
        'item_name',
        'description',
        'photo_url',
        'subcontracted_analytes',
        'accredited_analytes',
        'default_analytes',
        'sub_acc_analytes'
    ];

    public function sampletype(){
        return $this->belongsTo(SampleType::class,'sample_type');
    }

    public function invoicableItem(){
        return $this->belongsTo(InvoicableItem::class,'invoicable_item_id');
    }
}
