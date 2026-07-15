<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use App\Casts\SafeEncrypted;

class SamplesCategory extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'samples_by_category';
    protected $appends = ['analysisTypeNames'];

    /**
     * Encrypted columns inherited from sample_details via the samples_by_category view.
     * Without these casts, report templates print ciphertext (eyJ...) instead of remarks text.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'barcode' => SafeEncrypted::class,
        'comments' => SafeEncrypted::class,
        'gps' => SafeEncrypted::class,
        'main_body' => SafeEncrypted::class,
        'header_body' => SafeEncrypted::class,
        'notes_body' => SafeEncrypted::class,
        'report_number' => SafeEncrypted::class,
    ];

    public function samplePointArea()
    {
        return $this->belongsTo('App\Models\SamplePointArea', 'sample_point_area_id');
    }

    public function getSampleByAnalysisType($exclude_pesticides = 0){
        if($exclude_pesticides == 1){
            return SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->where('is_pesticide',0)->where('batch_id',$this->sample_header_id)->orderBy('analysis_level','ASC')->get();
        }
        return SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->where('batch_id',$this->sample_header_id)->orderBy('analysis_level','ASC')->get();
    }
    public function getAccredittedStatus(){
        // $total = CapturedResult::where('sample_detail_id',$this->id)->count();
        return CapturedResult::where('sample_detail_id',$this->id)->where('analyte_status_contracted',0)->count();
       
    }
    public function getAccredittedCount(){
        // $total = CapturedResult::where('sample_detail_id',$this->id)->count();
        return CapturedResult::where('sample_detail_id',$this->id)->where('analyte_accredited',1)->count();
       
    }
    public function getAnalysisRelation(){
		return implode(', ',array_unique(SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->where('batch_id',$this->sample_header_id)->pluck('analysis_type_name')->toArray()) ?? []);
	}
    public function getAnalysisTypeNamesAttribute(){
		return implode(', ',array_unique(SampleAnalysisTypeRelationView::where('sample_detail_id',$this->id)->where('batch_id',$this->sample_header_id)->pluck('analysis_type_name')->toArray() ?? []));
	}
    public function getAcredditedStatus(){
        $allCapturedResultsCount = CapturedResult::where('sample_detail_id',$this->id)->where('sample_header_id',$this->sample_header_id)->get()->count();
        $isAccreditedCount = CapturedResult::where('sample_detail_id',$this->id)->where('sample_header_id',$this->sample_header_id)->where('analyte_accredited',1)->get()->count();
        $perc = ($isAccreditedCount * 100) / $allCapturedResultsCount;
        return round($perc) >= 60 ? 1 : 0;
    }
    public function getAnalytesName(){
        return implode(', ',CapturedResult::where('sample_detail_id',$this->id)->pluck('analyte_code')->toArray()) ;
    }
}
