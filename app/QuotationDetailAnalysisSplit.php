<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuotationDetailAnalysisSplit extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = "quotation_details_analysis_type";
    protected $fillable= ['quotation_detail_id','analysis_type_id'];

    /**
     * Replace analysis-type links for a quotation detail.
     *
     * @param  list<string>  $analysisTypeIds
     */
    public static function syncForDetail(string $quotationDetailId, array $analysisTypeIds): void
    {
        static::query()->where('quotation_detail_id', $quotationDetailId)->delete();

        foreach (array_values(array_filter(array_map('strval', $analysisTypeIds))) as $analysisTypeId) {
            static::query()->create([
                'quotation_detail_id' => $quotationDetailId,
                'analysis_type_id' => $analysisTypeId,
            ]);
        }
    }
}
