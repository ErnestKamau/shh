<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\AnalysisType;

class InvoiceDetails extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

    protected $fillable = ["crm_customer_id","analysis_type","analysis_type_name","sample_header_id","sample_detail_id","invoice_id","selling_price","cost_price","zoho_item_id","zoho_item_name","quantity","total","final_unit_price",'discount','discount_type','analysis_title'];

    protected $appends = ['samplecodes'];
    
    public function analysisType(){
        return $this->belongsTo(AnalysisType::class,'analysis_type');
    }
    public function getZohoID(){
        $analysis = AnalysisType::find($this->analysis_type);
        $sample_type_category = SampleTypeCategory::find($analysis->sample_type()->sample_type_category);
        return $sample_type_category;

    }
    public function getSamplecodesAttribute(){
        $sample_arr = explode(',',$this->sample_detail_id);
        sort($sample_arr);
        if(sizeof($sample_arr) > 1){
            $codes = 'Our Report Nos. '.SampleDetails::find($sample_arr[0])->sample_code.' - '.SampleDetails::find($sample_arr[sizeof($sample_arr) - 1])->sample_code;
        }else{
            $codes = $this->sample_detail_id != 0 ?  'Our Report No. '.SampleDetails::find($sample_arr[0])->sample_code : '';
        }
        return $codes;
    }
    public function invoice(){
        return $this->belongsTo(Invoice::class,'invoice_id');
    }
}
