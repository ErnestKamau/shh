<?php

namespace App\Models;

use App\AnalysisType;
use App\Models\CRM\CRMCustomer;
use App\Standards;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAnalysisTypeStandard extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'customer_analysis_type_standards';

    protected $fillable = [
        'crm_customer_id',
        'analysis_type_id',
        'standard_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    public function analysisType(): BelongsTo
    {
        return $this->belongsTo(AnalysisType::class, 'analysis_type_id');
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standards::class, 'standard_id');
    }
}
