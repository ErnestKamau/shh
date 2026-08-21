<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationDetails extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'quotation_details';
    
    protected $fillable = [
        'quotation_header_id',
        'invoicable_item_id',
        'analyte_id',
        'quantity',
        'quantity_required',
        'part_no',
        'unit_price',
        'tax',
        'sample_type',
        'item_name',
        'description',
        'test_method',
        'loq',
        'mu_percent',
        'show_loq_analytes',
        'show_mu_analytes',
        'tat',
        'photo_url',
        'subcontracted_analytes',
        'accredited_analytes',
        'default_analytes',
        'sub_acc_analytes',
        'is_package',
    ];

    protected $casts = [
        'is_package' => 'boolean',
        'tat' => 'integer',
    ];

    public function sampletype(){
        return $this->belongsTo(SampleType::class,'sample_type');
    }

    public function invoicableItem(){
        return $this->belongsTo(InvoicableItem::class,'invoicable_item_id');
    }
}
